<?php
/**
 * QMAX Realty — Database Layer
 *
 * Single SQLite connection (WAL mode), auto-creates all tables on first run.
 * Public API:
 *   qmx_db()                          → PDO
 *   qmx_generate_reference()          → string  e.g. QMX-A3F9B2
 *   qmx_create_inquiry(array)         → string  (reference)
 *   qmx_update_inquiry_status(ref, status) → bool
 *   qmx_get_inquiry(ref)              → ?array
 *   qmx_get_agents(active_only)       → array
 *   qmx_get_promoters(active_only)    → array  (alias for agents — kept for compat)
 */
declare(strict_types=1);

require_once __DIR__ . '/../config/booking.php';

function qmx_db(): PDO
{
    static $pdo = null;
    if ($pdo !== null) return $pdo;

    $dir = dirname(DB_PATH);
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }

    $pdo = new PDO('sqlite:' . DB_PATH, null, null, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    $pdo->exec("PRAGMA journal_mode=WAL");

    // ── 1. INQUIRIES ──────────────────────────────────────────────────────────
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS inquiries (
            id             INTEGER PRIMARY KEY AUTOINCREMENT,
            reference      TEXT    NOT NULL UNIQUE,
            property_slug  TEXT    NOT NULL,
            property_title TEXT    NOT NULL,
            property_type  TEXT    NOT NULL DEFAULT 'property',
            inquiry_type   TEXT    NOT NULL DEFAULT 'viewing', -- viewing | information | offer
            viewing_date   TEXT,       -- YYYY-MM-DD, nullable (info requests skip this)
            viewing_time   TEXT,       -- morning | afternoon | evening, nullable
            first_name     TEXT    NOT NULL,
            last_name      TEXT    NOT NULL,
            email          TEXT    NOT NULL,
            phone          TEXT    NOT NULL,
            language       TEXT    NOT NULL DEFAULT 'English',
            status         TEXT    NOT NULL DEFAULT 'pending',
                                      -- pending | scheduled | completed | cancelled
            notes          TEXT,
            created_by     TEXT    NOT NULL DEFAULT 'customer',
            agent_code     TEXT,
            ip             TEXT,
            created_at     TEXT    NOT NULL DEFAULT (datetime('now'))
        )
    ");

    // Add inquiry_type column if it doesn't exist yet (safe migration for
    // existing databases that were created before this column was introduced).
    try {
        $pdo->exec("ALTER TABLE inquiries ADD COLUMN inquiry_type TEXT NOT NULL DEFAULT 'viewing'");
    } catch (\PDOException) {
        // Column already exists — ignore.
    }
    // Add ip column if missing
    try {
        $pdo->exec("ALTER TABLE inquiries ADD COLUMN ip TEXT");
    } catch (\PDOException) {
        // Column already exists — ignore.
    }

    // ── 2. AGENTS (replaces 'promoters') ─────────────────────────────────────
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS agents (
            id         INTEGER PRIMARY KEY AUTOINCREMENT,
            name       TEXT    NOT NULL,
            code       TEXT    NOT NULL UNIQUE,
            active     INTEGER NOT NULL DEFAULT 1,
            created_at TEXT    NOT NULL DEFAULT (datetime('now'))
        )
    ");

    // ── 3. NEWSLETTER ─────────────────────────────────────────────────────────
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS newsletter (
            id         INTEGER PRIMARY KEY AUTOINCREMENT,
            email      TEXT    NOT NULL UNIQUE,
            created_at TEXT    NOT NULL DEFAULT (datetime('now')),
            ip         TEXT
        )
    ");

    // ── 4. CONTACT MESSAGES ───────────────────────────────────────────────────
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS contact_messages (
            id         INTEGER PRIMARY KEY AUTOINCREMENT,
            first_name TEXT    NOT NULL,
            last_name  TEXT    NOT NULL,
            email      TEXT    NOT NULL,
            phone      TEXT,
            subject    TEXT    NOT NULL,
            message    TEXT    NOT NULL,
            ip         TEXT,
            is_read    INTEGER NOT NULL DEFAULT 0,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
        )
    ");

    return $pdo;
}

// ── Reference generator ───────────────────────────────────────────────────────
function qmx_generate_reference(): string
{
    do {
        $ref    = 'QMX-' . strtoupper(substr(bin2hex(random_bytes(3)), 0, 6));
        $exists = qmx_db()->prepare("SELECT 1 FROM inquiries WHERE reference = ?");
        $exists->execute([$ref]);
    } while ($exists->fetchColumn());

    return $ref;
}

// ── CRUD ──────────────────────────────────────────────────────────────────────

/**
 * Insert a new inquiry row and return its reference string.
 *
 * Required keys: property_slug, property_title, first_name, last_name, email, phone
 * Optional keys: property_type, inquiry_type, viewing_date, viewing_time,
 *                language, notes, created_by, agent_code, ip
 */
function qmx_create_inquiry(array $data): string
{
    $ref = qmx_generate_reference();

    $stmt = qmx_db()->prepare("
        INSERT INTO inquiries
            (reference, property_slug, property_title, property_type,
             inquiry_type, viewing_date, viewing_time,
             first_name, last_name, email, phone,
             language, notes, created_by, agent_code, ip)
        VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)
    ");

    $stmt->execute([
        $ref,
        trim($data['property_slug']),
        trim($data['property_title']),
        trim($data['property_type']  ?? 'property'),
        trim($data['inquiry_type']   ?? 'viewing'),
        $data['viewing_date']        ?? null,
        $data['viewing_time']        ?? null,
        trim($data['first_name']),
        trim($data['last_name']),
        strtolower(trim($data['email'])),
        trim($data['phone']),
        trim($data['language']       ?? 'English'),
        trim($data['notes']          ?? ''),
        trim($data['created_by']     ?? 'customer'),
        $data['agent_code']          ?? null,
        $data['ip']                  ?? ($_SERVER['REMOTE_ADDR'] ?? null),
    ]);

    return $ref;
}

/**
 * Update inquiry status.
 * Valid statuses: pending | scheduled | completed | cancelled
 */
function qmx_update_inquiry_status(string $reference, string $status): bool
{
    $allowed = ['pending', 'scheduled', 'completed', 'cancelled'];
    if (!in_array($status, $allowed, true)) return false;

    $stmt = qmx_db()->prepare(
        "UPDATE inquiries SET status = ? WHERE reference = ?"
    );
    $stmt->execute([$status, $reference]);
    return $stmt->rowCount() > 0;
}

/**
 * Fetch a single inquiry by reference. Returns null if not found.
 */
function qmx_get_inquiry(string $reference): ?array
{
    $stmt = qmx_db()->prepare("SELECT * FROM inquiries WHERE reference = ?");
    $stmt->execute([$reference]);
    $row = $stmt->fetch();
    return $row ?: null;
}

// ── Agents ────────────────────────────────────────────────────────────────────

function qmx_get_agents(bool $active_only = false): array
{
    $sql = $active_only
        ? "SELECT * FROM agents WHERE active = 1 ORDER BY name ASC"
        : "SELECT * FROM agents ORDER BY name ASC";
    return qmx_db()->query($sql)->fetchAll();
}

/**
 * Alias kept so any admin pages that still call qmx_get_promoters() don't break
 * while they're being migrated to the new naming.
 */
function qmx_get_promoters(bool $active_only = false): array
{
    return qmx_get_agents($active_only);
}
<?php
// ─────────────────────────────────────────────
// Shared Database Connection (MySQLi)
// Used by resume persistence and future DB features
// ─────────────────────────────────────────────

define('DB_HOST', 'localhost');
define('DB_NAME', 'easy resume');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

/**
 * Returns a shared MySQLi connection.
 */
function db(): mysqli
{
    static $mysqli = null;

    if ($mysqli === null) {
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

        try {
            $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
            $mysqli->set_charset(DB_CHARSET);
        } catch (mysqli_sql_exception $e) {
            error_log("Database connection failed: " . $e->getMessage());
            http_response_code(500);
            exit("Database connection failed.");
        }
    }

    return $mysqli;
}
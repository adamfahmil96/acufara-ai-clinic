<?php

namespace App\Database\Connectors;

use Illuminate\Database\Connectors\PostgresConnector as BasePostgresConnector;
use PDO;

/**
 * Custom PostgresConnector that always uses `new PDO()`.
 *
 * PHP 8.4 introduced PDO::connect() which replaces `new PDO()`.
 * In some environments (e.g., Supabase Supavisor Transaction Mode),
 * this causes server-side prepared statements to be used even when
 * ATTR_EMULATE_PREPARES is set to true, resulting in:
 *   "prepared statement does not exist" errors.
 *
 * This connector forces `new PDO()` so ATTR_EMULATE_PREPARES from
 * config/database.php (DB_EMULATE_PREPARES) is honored as configured,
 * instead of being hardcoded here.
 */
class PostgresConnector extends BasePostgresConnector
{
    /**
     * Override to force using `new PDO()` instead of `PDO::connect()`.
     */
    protected function createPdoConnection($dsn, $username, #[\SensitiveParameter] $password, $options)
    {
        return new PDO($dsn, $username, $password, $options);
    }
}

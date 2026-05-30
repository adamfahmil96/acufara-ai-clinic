<?php

namespace App\Database\Connectors;

use Illuminate\Database\Connectors\PostgresConnector as BasePostgresConnector;
use PDO;

/**
 * Custom PostgresConnector that forces emulated prepares.
 *
 * PHP 8.4 introduced PDO::connect() which replaces `new PDO()`.
 * In some environments (e.g., Supabase Supavisor Transaction Mode),
 * this causes server-side prepared statements to be used even when
 * ATTR_EMULATE_PREPARES is set to true, resulting in:
 *   "prepared statement does not exist" errors.
 *
 * This connector forces `new PDO()` and explicitly sets
 * ATTR_EMULATE_PREPARES = true to prevent this issue.
 */
class PostgresConnector extends BasePostgresConnector
{
    /**
     * Override to force using `new PDO()` instead of `PDO::connect()`.
     * This ensures ATTR_EMULATE_PREPARES is properly applied.
     */
    protected function createPdoConnection($dsn, $username, #[\SensitiveParameter] $password, $options)
    {
        $options[PDO::ATTR_EMULATE_PREPARES] = true;

        return new PDO($dsn, $username, $password, $options);
    }
}

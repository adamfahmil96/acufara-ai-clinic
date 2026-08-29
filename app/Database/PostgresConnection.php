<?php

namespace App\Database;

use DateTimeInterface;
use Illuminate\Database\PostgresConnection as BasePostgresConnection;

/**
 * Custom PostgresConnection that binds PHP booleans as 'true'/'false'
 * instead of integers.
 *
 * Laravel's base Connection::prepareBindings() casts booleans to (int) 0/1
 * for MySQL/SQLite compatibility. With emulated prepares (required here
 * for the Supabase Supavisor pooler, see docs/03-supabase-setup.md),
 * PDO embeds that integer as a literal in the SQL text, and Postgres has
 * no implicit cast from integer to boolean:
 *   SQLSTATE[42804]: column "is_active" is of type boolean but
 *   expression is of type integer
 *
 * Binding 'true'/'false' as strings instead lets Postgres parse them via
 * its boolean input function, which works under both emulated and native
 * prepared statements.
 */
class PostgresConnection extends BasePostgresConnection
{
    public function prepareBindings(array $bindings)
    {
        $grammar = $this->getQueryGrammar();

        foreach ($bindings as $key => $value) {
            if ($value instanceof DateTimeInterface) {
                $bindings[$key] = $value->format($grammar->getDateFormat());
            } elseif (is_bool($value)) {
                $bindings[$key] = $value ? 'true' : 'false';
            }
        }

        return $bindings;
    }
}

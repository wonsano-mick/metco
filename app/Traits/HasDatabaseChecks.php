<?php

namespace App\Traits;

use Illuminate\Support\Facades\Schema;

trait HasDatabaseChecks
{
    /**
     * Check if a table has a specific column
     */
    public static function tableHasColumn(string $table, string $column): bool
    {
        static $cache = [];

        $key = "{$table}.{$column}";

        if (!isset($cache[$key])) {
            try {
                $cache[$key] = Schema::hasColumn($table, $column);
            } catch (\Exception $e) {
                $cache[$key] = false;
            }
        }

        return $cache[$key];
    }

    /**
     * Scope query to use column only if it exists
     */
    public function scopeWhenColumnExists($query, string $column, callable $callback)
    {
        if (self::tableHasColumn($this->getTable(), $column)) {
            return $callback($query);
        }

        return $query;
    }
}

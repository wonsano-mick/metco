<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Schema;

class DatabaseHelper
{
    /**
     * Check if a table has a specific column (cached)
     */
    public static function hasTableColumn(string $table, string $column): bool
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
     * Get table columns
     */
    public static function getTableColumns(string $table): array
    {
        static $cache = [];

        if (!isset($cache[$table])) {
            try {
                $cache[$table] = Schema::getColumnListing($table);
            } catch (\Exception $e) {
                $cache[$table] = [];
            }
        }

        return $cache[$table];
    }

    /**
     * Check if column exists and is accessible
     */
    public static function columnExistsAndAccessible(string $table, string $column): bool
    {
        return self::hasTableColumn($table, $column);
    }

    public static function schemaHasColumn($table, $column)
    {
        return Schema::hasColumn($table, $column);
    }
}

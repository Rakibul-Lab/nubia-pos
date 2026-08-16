<?php

declare(strict_types=1);

namespace App\Core;

use PDO;
use PDOException;
use PDOStatement;
use RuntimeException;

/**
 * Thin, secure PDO wrapper (singleton).
 *
 * All queries use prepared statements to prevent SQL injection.
 *
 * @package App\Core
 */
final class Database
{
    private static ?Database $instance = null;

    private PDO $pdo;

    private function __construct()
    {
        $cfg = config('database');

        $dsn = sprintf(
            'mysql:host=%s;port=%d;dbname=%s;charset=%s',
            $cfg['host'],
            $cfg['port'],
            $cfg['name'],
            $cfg['charset']
        );

        try {
            $this->pdo = new PDO($dsn, $cfg['user'], $cfg['pass'], [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
                PDO::ATTR_STRINGIFY_FETCHES  => false,
            ]);
        } catch (PDOException $e) {
            if (config('app.debug')) {
                throw new RuntimeException('Database connection failed: ' . $e->getMessage(), (int) $e->getCode(), $e);
            }
            http_response_code(500);
            exit('Database connection error. Please check your configuration.');
        }
    }

    public static function getInstance(): self
    {
        return self::$instance ??= new self();
    }

    public function pdo(): PDO
    {
        return $this->pdo;
    }

    /**
     * Run a prepared statement and return the executed statement.
     *
     * @param array<int|string,mixed> $params
     */
    public function query(string $sql, array $params = []): PDOStatement
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    /**
     * Fetch a single row.
     *
     * @param array<int|string,mixed> $params
     * @return array<string,mixed>|null
     */
    public function fetch(string $sql, array $params = []): ?array
    {
        $row = $this->query($sql, $params)->fetch();
        return $row === false ? null : $row;
    }

    /**
     * Fetch all rows.
     *
     * @param array<int|string,mixed> $params
     * @return array<int,array<string,mixed>>
     */
    public function fetchAll(string $sql, array $params = []): array
    {
        return $this->query($sql, $params)->fetchAll();
    }

    /**
     * Fetch a single scalar column value.
     *
     * @param array<int|string,mixed> $params
     */
    public function scalar(string $sql, array $params = []): mixed
    {
        return $this->query($sql, $params)->fetchColumn();
    }

    /**
     * Insert a row and return the last insert id.
     *
     * @param array<string,mixed> $data
     */
    public function insert(string $table, array $data): int
    {
        $columns      = array_keys($data);
        $placeholders = array_map(static fn ($c) => ':' . $c, $columns);

        $sql = sprintf(
            'INSERT INTO `%s` (%s) VALUES (%s)',
            $table,
            '`' . implode('`, `', $columns) . '`',
            implode(', ', $placeholders)
        );

        $this->query($sql, $this->bindable($data));

        return (int) $this->pdo->lastInsertId();
    }

    /**
     * Update rows matching a where clause.
     *
     * @param array<string,mixed> $data
     * @param array<string,mixed> $where
     */
    public function update(string $table, array $data, array $where): int
    {
        $set    = implode(', ', array_map(static fn ($c) => "`$c` = :set_$c", array_keys($data)));
        $clause = implode(' AND ', array_map(static fn ($c) => "`$c` = :where_$c", array_keys($where)));

        $params = [];
        foreach ($data as $k => $v) {
            $params["set_$k"] = $this->normalise($v);
        }
        foreach ($where as $k => $v) {
            $params["where_$k"] = $this->normalise($v);
        }

        $sql = sprintf('UPDATE `%s` SET %s WHERE %s', $table, $set, $clause);

        return $this->query($sql, $params)->rowCount();
    }

    /**
     * Delete rows matching a where clause.
     *
     * @param array<string,mixed> $where
     */
    public function delete(string $table, array $where): int
    {
        $clause = implode(' AND ', array_map(static fn ($c) => "`$c` = :$c", array_keys($where)));
        $sql    = sprintf('DELETE FROM `%s` WHERE %s', $table, $clause);
        return $this->query($sql, $this->bindable($where))->rowCount();
    }

    // ---------------------- Transactions -------------------------

    public function beginTransaction(): void
    {
        if (!$this->pdo->inTransaction()) {
            $this->pdo->beginTransaction();
        }
    }

    public function inTransaction(): bool
    {
        return $this->pdo->inTransaction();
    }

    public function commit(): void
    {
        if ($this->pdo->inTransaction()) {
            $this->pdo->commit();
        }
    }

    public function rollBack(): void
    {
        if ($this->pdo->inTransaction()) {
            $this->pdo->rollBack();
        }
    }

    /**
     * Execute a callback inside a transaction, rolling back on error.
     * Nested calls reuse the open transaction (no early commit).
     */
    public function transaction(callable $callback): mixed
    {
        $started = !$this->pdo->inTransaction();
        if ($started) {
            $this->pdo->beginTransaction();
        }
        try {
            $result = $callback($this);
            if ($started) {
                $this->pdo->commit();
            }
            return $result;
        } catch (\Throwable $e) {
            if ($started && $this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $e;
        }
    }

    // ---------------------- Internals ----------------------------

    /**
     * @param array<string,mixed> $data
     * @return array<string,mixed>
     */
    private function bindable(array $data): array
    {
        $out = [];
        foreach ($data as $k => $v) {
            $out[$k] = $this->normalise($v);
        }
        return $out;
    }

    private function normalise(mixed $value): mixed
    {
        if (is_bool($value)) {
            return $value ? 1 : 0;
        }
        return $value;
    }
}

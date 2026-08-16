<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Base Active-Record-lite model.
 *
 * Child classes set $table and (optionally) $fillable.
 *
 * @package App\Core
 */
abstract class Model
{
    protected string $table = '';

    protected string $primaryKey = 'id';

    /** @var string[] Columns that are mass-assignable. Empty = all allowed. */
    protected array $fillable = [];

    protected Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    protected function db(): Database
    {
        return $this->db;
    }

    /**
     * @return array<string,mixed>|null
     */
    public function find(int|string $id): ?array
    {
        return $this->db->fetch(
            "SELECT * FROM `{$this->table}` WHERE `{$this->primaryKey}` = ? LIMIT 1",
            [$id]
        );
    }

    /**
     * @param array<string,mixed> $conditions
     * @return array<string,mixed>|null
     */
    public function findBy(array $conditions): ?array
    {
        [$where, $params] = $this->buildWhere($conditions);
        return $this->db->fetch("SELECT * FROM `{$this->table}` {$where} LIMIT 1", $params);
    }

    /**
     * @param array<string,mixed> $conditions
     * @return array<int,array<string,mixed>>
     */
    public function all(array $conditions = [], string $orderBy = ''): array
    {
        [$where, $params] = $this->buildWhere($conditions);
        $order = $orderBy !== '' ? " ORDER BY {$orderBy}" : '';
        return $this->db->fetchAll("SELECT * FROM `{$this->table}` {$where}{$order}", $params);
    }

    /**
     * @param array<string,mixed> $conditions
     */
    public function count(array $conditions = []): int
    {
        [$where, $params] = $this->buildWhere($conditions);
        return (int) $this->db->scalar("SELECT COUNT(*) FROM `{$this->table}` {$where}", $params);
    }

    /**
     * @param array<string,mixed> $data
     */
    public function create(array $data): int
    {
        return $this->db->insert($this->table, $this->filterFillable($data));
    }

    /**
     * @param array<string,mixed> $data
     */
    public function update(int|string $id, array $data): int
    {
        return $this->db->update(
            $this->table,
            $this->filterFillable($data),
            [$this->primaryKey => $id]
        );
    }

    public function delete(int|string $id): int
    {
        return $this->db->delete($this->table, [$this->primaryKey => $id]);
    }

    /**
     * Paginate results with optional search and where clause.
     *
     * @param array<string,mixed> $bindings
     * @return array{data:array<int,array<string,mixed>>,total:int,page:int,per_page:int,last_page:int}
     */
    public function paginate(
        string $sql,
        array $bindings = [],
        int $page = 1,
        int $perPage = 15,
        string $countSql = ''
    ): array {
        $page    = max(1, $page);
        $perPage = max(1, $perPage);
        $offset  = ($page - 1) * $perPage;

        if ($countSql === '') {
            $countSql = 'SELECT COUNT(*) FROM (' . $sql . ') AS sub';
        }
        $total = (int) $this->db->scalar($countSql, $bindings);

        $data = $this->db->fetchAll(
            $sql . ' LIMIT ' . (int) $perPage . ' OFFSET ' . (int) $offset,
            $bindings
        );

        return [
            'data'      => $data,
            'total'     => $total,
            'page'      => $page,
            'per_page'  => $perPage,
            'last_page' => (int) max(1, ceil($total / $perPage)),
        ];
    }

    /**
     * @param array<string,mixed> $data
     * @return array<string,mixed>
     */
    protected function filterFillable(array $data): array
    {
        if ($this->fillable === []) {
            return $data;
        }
        return array_intersect_key($data, array_flip($this->fillable));
    }

    /**
     * @param array<string,mixed> $conditions
     * @return array{0:string,1:array<int,mixed>}
     */
    protected function buildWhere(array $conditions): array
    {
        if ($conditions === []) {
            return ['', []];
        }
        $parts  = [];
        $params = [];
        foreach ($conditions as $column => $value) {
            $parts[]  = "`{$column}` = ?";
            $params[] = $value;
        }
        return ['WHERE ' . implode(' AND ', $parts), $params];
    }
}

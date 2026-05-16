<?php

namespace App;

use PDO;
use PDOException;

class GameRepository implements GameRepositoryInterface
{
    private PDO $db;

    private const SORT_MAP = [
        'rating'   => 'rating DESC',
        'price'    => 'price',
        'title'    => 'title',
        'discount' => 'ABS(COALESCE(discount,0)) DESC',
        'year'     => 'year DESC',
        'default'  => 'id',
    ];

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function all(): array
    {
        $rows = $this->db->query('SELECT * FROM games ORDER BY id')->fetchAll();
        return array_map([$this, 'decode'], $rows);
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM games WHERE id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ? $this->decode($row) : null;
    }

    public function filter(array $params): array
    {
        [$where, $binds] = $this->buildWhere($params);

        $orderBy = $this->buildOrder($params);
        $limit   = max(1, min(100, (int) ($params['limit']  ?? 50)));
        $offset  = max(0, (int) ($params['offset'] ?? 0));

        $sql  = "SELECT * FROM games{$where} ORDER BY {$orderBy} LIMIT ? OFFSET ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([...$binds, $limit, $offset]);

        return array_map([$this, 'decode'], $stmt->fetchAll());
    }

    public function count(array $params = []): int
    {
        [$where, $binds] = $this->buildWhere($params);
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM games{$where}");
        $stmt->execute($binds);
        return (int) $stmt->fetchColumn();
    }

    public function insert(array $data): array
    {
        $stmt = $this->db->prepare(<<<SQL
            INSERT INTO games
              (title, genre, developer, year, price, old_price, rating, emoji, logo, discount, platform, players, description, tags)
            VALUES
              (:title, :genre, :developer, :year, :price, :old_price, :rating, :emoji, :logo, :discount, :platform, :players, :description, :tags)
        SQL);

        $stmt->execute([
            ':title'       => $data['title'],
            ':genre'       => $data['genre'],
            ':developer'   => $data['developer'],
            ':year'        => (int) $data['year'],
            ':price'       => (int) $data['price'],
            ':old_price'   => isset($data['oldPrice']) ? (int) $data['oldPrice'] : null,
            ':rating'      => (float) ($data['rating'] ?? 0),
            ':emoji'       => $data['emoji']       ?? null,
            ':logo'        => $data['logo']        ?? null,
            ':discount'    => isset($data['discount']) ? (int) $data['discount'] : null,
            ':platform'    => $data['platform']    ?? null,
            ':players'     => $data['players']     ?? null,
            ':description' => $data['description'] ?? null,
            ':tags'        => json_encode($data['tags'] ?? [], JSON_UNESCAPED_UNICODE),
        ]);

        $newId = (int) $this->db->lastInsertId();
        return $this->find($newId);
    }

    public function update(int $id, array $data): ?array
    {
        $allowed = ['title','genre','developer','year','price','old_price','rating','emoji','logo','discount','platform','players','description','tags'];
        $sets    = [];
        $params  = [];

        foreach ($allowed as $col) {
            $key = lcfirst(str_replace('_', '', ucwords($col, '_')));
            if (array_key_exists($key, $data)) {
                $sets[]   = "{$col} = ?";
                $params[] = $col === 'tags' ? json_encode($data[$key], JSON_UNESCAPED_UNICODE) : $data[$key];
            } elseif (array_key_exists($col, $data)) {
                $sets[]   = "{$col} = ?";
                $params[] = $col === 'tags' ? json_encode($data[$col], JSON_UNESCAPED_UNICODE) : $data[$col];
            }
        }

        if (empty($sets)) {
            return null;
        }

        $sets[]   = "updated_at = datetime('now')";
        $params[] = $id;

        $this->db->prepare('UPDATE games SET ' . implode(', ', $sets) . ' WHERE id = ?')
                 ->execute($params);

        return $this->find($id);
    }

    public function delete(int $id): bool
    {
        $this->db->prepare('DELETE FROM games WHERE id = ?')->execute([$id]);
        return true;
    }

    private function buildWhere(array $params): array
    {
        $where  = [];
        $binds  = [];

        if (!empty($params['genre'])) {
            $where[]  = 'genre = ?';
            $binds[]  = $params['genre'];
        }

        if (!empty($params['search'])) {
            $where[]  = '(title LIKE ? OR developer LIKE ?)';
            $s        = '%' . $params['search'] . '%';
            $binds[]  = $s;
            $binds[]  = $s;
        }

        if (isset($params['free']) && $params['free'] === '1') {
            $where[] = 'price = 0';
        }

        if (!empty($params['max_price'])) {
            $where[]  = 'price <= ?';
            $binds[]  = (int) $params['max_price'];
        }

        $clause = $where ? ' WHERE ' . implode(' AND ', $where) : '';
        return [$clause, $binds];
    }

    private function buildOrder(array $params): string
    {
        $sortKey = $params['sort'] ?? 'default';
        $order   = ($params['order'] ?? 'desc') === 'asc' ? 'ASC' : 'DESC';
        $orderBy = self::SORT_MAP[$sortKey] ?? 'id';

        if (!str_contains($orderBy, ' ')) {
            $orderBy .= " {$order}";
        }

        return $orderBy;
    }

    private function decode(array $row): array
    {
        $row['id']       = (int)   $row['id'];
        $row['year']     = (int)   $row['year'];
        $row['price']    = (int)   $row['price'];
        $row['oldPrice'] = $row['old_price'] !== null ? (int) $row['old_price'] : null;
        $row['rating']   = (float) $row['rating'];
        $row['discount'] = $row['discount'] !== null ? (int) $row['discount'] : null;
        $row['tags']     = json_decode($row['tags'] ?? '[]', true) ?? [];
        unset($row['old_price']);
        return $row;
    }
}

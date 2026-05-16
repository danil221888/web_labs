<?php

namespace App;

class ApiController
{
    private GameRepositoryInterface $repository;

    public function __construct(GameRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function handle(): void
    {
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, X-Partial');
        header('Content-Type: application/json; charset=utf-8');

        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(204);
            exit;
        }

        $method = $_SERVER['REQUEST_METHOD'];
        $id     = isset($_GET['id']) ? (int) $_GET['id'] : null;

        try {
            match ($method) {
                'GET'    => $this->handleGet($id),
                'POST'   => $this->handlePost(),
                'PUT'    => $this->handlePut($id),
                'DELETE' => $this->handleDelete($id),
                default  => $this->error(405, 'Method not allowed'),
            };
        } catch (\Throwable $e) {
            $this->error(500, $e->getMessage());
        }
    }

    private function handleGet(?int $id): void
    {
        if ($id !== null) {
            $game = $this->repository->find($id);
            if (!$game) {
                $this->error(404, 'Game not found');
            }
            $this->ok($game);
        }

        $params = $_GET;
        $limit  = max(1, min(100, (int) ($params['limit']  ?? 50)));
        $offset = max(0, (int) ($params['offset'] ?? 0));
        $total  = $this->repository->count($params);
        $games  = $this->repository->filter($params);

        $this->ok($games, [
            'total'  => $total,
            'limit'  => $limit,
            'offset' => $offset,
            'page'   => (int) floor($offset / $limit) + 1,
        ]);
    }

    private function handlePost(): void
    {
        $body = $this->jsonBody();
        $this->validate($body, ['title', 'genre', 'developer', 'year', 'price']);

        $game = $this->repository->insert($body);
        http_response_code(201);
        $this->ok($game);
    }

    private function handlePut(?int $id): void
    {
        if (!$id) {
            $this->error(400, 'id required');
        }

        $body   = $this->jsonBody();
        $result = $this->repository->update($id, $body);

        if ($result === null) {
            $this->error(400, 'Nothing to update or game not found');
        }

        $this->ok($result);
    }

    private function handleDelete(?int $id): void
    {
        if (!$id) {
            $this->error(400, 'id required');
        }

        $this->repository->delete($id);
        $this->ok(['deleted' => true, 'id' => $id]);
    }

    private function ok(mixed $data, ?array $meta = null): never
    {
        $resp = ['success' => true, 'data' => $data];
        if ($meta !== null) {
            $resp['meta'] = $meta;
        }
        echo json_encode($resp, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    private function error(int $code, string $message): never
    {
        http_response_code($code);
        echo json_encode([
            'success' => false,
            'error'   => ['code' => $code, 'message' => $message],
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    private function jsonBody(): array
    {
        $raw  = file_get_contents('php://input');
        $body = json_decode($raw ?: '{}', true);
        if (!is_array($body)) {
            $this->error(400, 'Invalid JSON body');
        }
        return $body;
    }

    private function validate(array $body, array $required): void
    {
        foreach ($required as $field) {
            if (!isset($body[$field]) || $body[$field] === '') {
                $this->error(422, "Field '{$field}' is required");
            }
        }
    }
}

<?php

namespace App;

interface GameRepositoryInterface
{
    public function all(): array;
    public function find(int $id): ?array;
    public function filter(array $params): array;
    public function count(array $params = []): int;
}

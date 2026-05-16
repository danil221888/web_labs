<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

$db         = App\Database::connect(__DIR__ . '/../db/kpi_store.sqlite');
$repository = new App\GameRepository($db);
$controller = new App\ApiController($repository);

$controller->handle();

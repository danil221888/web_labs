<?php

declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';

$controller = new App\PageController();

$page      = $controller->getPage();
$isPartial = $controller->isPartial();

$controller->show($page, $isPartial);

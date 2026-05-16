<?php

namespace App;

class PageController
{
    private array $allowedPages = ['catalog', 'about', 'faq'];

    public function show(string $page, bool $isPartial): void
    {
        if ($isPartial) {
            header('Content-Type: text/html; charset=utf-8');
            header('X-Partial: true');
            $this->render($page);
            exit;
        }

        $controller = $this;
        include __DIR__ . '/../views/layout.php';
    }

    public function render(string $page): void
    {
        $file = __DIR__ . '/../pages/' . $page . '.php';

        if (is_file($file)) {
            include $file;
        }
    }

    public function getPage(): string
    {
        $page = $_GET['page'] ?? 'catalog';
        return in_array($page, $this->allowedPages, true) ? $page : 'catalog';
    }

    public function isPartial(): bool
    {
        return !empty($_GET['partial']) || !empty($_SERVER['HTTP_X_PARTIAL']);
    }
}

<?php
namespace App\Core;

final class View
{
    private static string $layout = 'layouts/main';

    public static function setLayout(string $layout): void
    {
        self::$layout = $layout;
    }

    public static function render(string $view, array $data = []): void
    {
        // make $data keys available as variables in the view
        extract($data, EXTR_SKIP);

        // capture the view output
        ob_start();
        require __DIR__ . '/../Views/' . trim($view, '/'). '.php';
        $content = ob_get_clean(); // <-- available inside layout

        // optional page title
        $title = $data['title'] ?? null;

        // render layout
        require __DIR__ . '/../Views/' . self::$layout . '.php';
        exit;
    }
}

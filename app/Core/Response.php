<?php
namespace App\Core;

final class Response {
    public static function redirect(string $to): void {
        header('Location: '.$to); exit;
    }
    public static function back(): void {
        $to = $_SERVER['HTTP_REFERER'] ?? '/';
        header('Location: '.$to); exit;
    }
}

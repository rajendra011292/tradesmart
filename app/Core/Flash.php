<?php
namespace App\Core;

final class Flash
{
    private const KEY = '_flash';

    public static function add(string $type, string $message): void
    {
        if (!isset($_SESSION)) { session_start(); }
        $_SESSION[self::KEY][] = ['type' => $type, 'message' => $message];
    }

    /** Get & clear all flashes (call once per request, when rendering) */
    public static function read(): array
    {
        if (!isset($_SESSION)) { session_start(); }
        $msgs = $_SESSION[self::KEY] ?? [];
        unset($_SESSION[self::KEY]);
        return $msgs;
    }

    // Sugar
    public static function success(string $m): void { self::add('success', $m); }
    public static function error(string $m): void   { self::add('error',   $m); }
    public static function info(string $m): void    { self::add('info',    $m); }
    public static function warn(string $m): void    { self::add('warning', $m); }
}

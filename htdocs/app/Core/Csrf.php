<?php
declare(strict_types=1);

namespace App\Core;

final class Csrf
{
    public static function token(): string
    {
        if (!Session::has('_csrf')) {
            Session::set('_csrf', bin2hex(random_bytes(32)));
        }
        return (string) Session::get('_csrf');
    }

    public static function valid(?string $token): bool
    {
        if (!is_string($token) || $token === '') {
            return false;
        }
        $expected = Csrf::token();
        return hash_equals($expected, $token);
    }
}

function csrf_field(): string
{
    return '<input type="hidden" name="_csrf" value="' . Csrf::token() . '">';
}

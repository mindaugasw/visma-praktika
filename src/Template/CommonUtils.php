<?php

declare(strict_types=1);

namespace App\Template;

class CommonUtils
{
    public static function includeBase()
    {
        include __DIR__ . '/base.tpl.php';
    }
}

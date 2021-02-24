<?php

declare(strict_types=1);

use App\Template\CommonUtils;

global $tpl;

/*
 * Params:
 * - status, int, status code
 * - message, string, error message
 */

$tpl['body'] = <<<TPL
<div>
    ERRRROR
</div>
TPL;

CommonUtils::includeBase();

<?php

declare(strict_types=1);

use App\Template\CommonUtils;

global $tpl;
$error = $tpl['error'];

/*
 * Params:
 * - status, int, status code
 * - message, string, error message
 */

$tpl['body'] = <<<TPL
<div class="fs-2 mt-5 pt-5">
    error <span class="fw-bold lh-1 ms-2 d-inline-block" style="font-size: 4em">{$error['status']}</span><br/>
    {$error['message']}
</div>
TPL;

CommonUtils::includeBase();

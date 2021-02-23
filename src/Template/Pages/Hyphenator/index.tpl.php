<?php

use App\Template\CommonUtils;

$tpl = $GLOBALS['tpl'];

$tpl['body'] =
    '<div>This is Hyphenator main page.</div><br/>'
    .'W:' . $tpl['word'] . ';'
;

CommonUtils::includeBase();

<?php

use App\Template\CommonUtils;

global $tpl;

$tpl['body'] = <<<TPL
<div class="mt-5">
    <label for="hyphenationInput" class="form-label">Enter word or text to hyphenate:</label>
    <div class="input-group mb-3">
        <input type="text" class="form-control" id="hyphenationInput">
        <button class="btn btn-outline-primary" type="button" id="hyphenationButton">Do stuff</button>
    </div>
</div>
<div class="mt-5">
    <label for="hyphenationResult" class="form-label">Hyphenated text:</label>
    <input type="text" class="form-control" id="hyphenationResult">
</div>
TPL;

CommonUtils::includeBase();

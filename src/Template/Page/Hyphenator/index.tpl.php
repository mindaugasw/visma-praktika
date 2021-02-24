<?php

use App\Template\CommonUtils;

global $tpl;

$loadingIcon = CommonUtils::getLoadingSpinner();

$tpl['body'] = <<<TPL
<div class="pt-5">
    <div class="mt-5 pt-5" id="hypBlockInput">
        <label for="hypInput" class="form-label">Enter word or text to hyphenate:</label>
        <div class="input-group mb-3">
            <input type="text" class="form-control" id="hypInput">
            <button class="btn btn-outline-primary" type="button" id="hypButton">Do stuff</button>
        </div>
    </div>
</div>

<div class="mt-5">
    <!-- Loading icon -->
    <div id="hypBlockResultLoading" class="d-none">
        {$loadingIcon}
    </div>
    
    <!-- Hyphenated word result -->
    <div id="hypBlockResultWord" class="d-none">
        Hyphenated word: <span id="hypResultWord" class="fw-bold"></span><br/>
        Found <span id="hypResultWordCount"></span> patterns at position:
        <ul id="hypResultWordList">
        </ul>
    </div>
    
    <!-- Hyphenated text result -->
    <div id="hypBlockResultText" class="d-none">
        <label for="hypResult" class="form-label">Hyphenated text:</label>
        <textarea class="form-control" id="hypResultText" rows="3" readonly></textarea>
    </div>
</div>
TPL;

$tpl['scripts'] = <<<TPL
    <script type="module" src="/js/hyphenator.js"></script>
TPL;

CommonUtils::includeBase();

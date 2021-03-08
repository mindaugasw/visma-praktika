<?php

declare(strict_types=1);

use App\Entity\HyphenationPattern;
use App\Entity\WordResult;
use App\Template\CommonUtils;

global $tpl;

/*
 * Params:
 * - word, WordResult
 */

/**
 * @var WordResult
 */
$word = $tpl['word'];

$patternsList = '';
foreach ($word->getMatchedPatterns() as $pattern) {
    $patternsList .= sprintf('%s @ %d<br/>', $pattern->getPattern(), $pattern->getPosition());
}

$tpl['body'] = <<<TPL
<div class="mt-5 content-justify-center">
    <h3>Word details</h3>
    
    <table id="patternDetailsTable">
        <thead></thead>
        <tbody>
            <tr>
                <th>Word ID</th>
                <td>{$word->getId()}</td>
            </tr>
            <tr>
                <th>Original word</th>
                <td>{$word->getInput()}</td>
            </tr>
            <tr>
                <th>Hyphenated word</th>
                <td>{$word->getResult()}</td>
            </tr>
            <tr>
                <th>Matched patterns,<br/>at position</th>
                <td>{$patternsList}</td>
            </tr>
        </tbody>
    </table>
    <br/>
    <a href="javascript:history.back()">Go Back</a> &nbsp;&nbsp;
    <a href="#">Edit</a> &nbsp;&nbsp;
    <a href="#">Delete</a>
</div>
TPL;

CommonUtils::includeBase();

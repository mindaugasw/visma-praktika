<?php

declare(strict_types=1);

use App\Entity\HyphenationPattern;
use App\Template\CommonUtils;

global $tpl;

/*
 * Params:
 * - pattern, HyphenationPattern
 */

/**
 * @var HyphenationPattern
 */
$pattern = $tpl['pattern'];

$tpl['body'] = <<<TPL
<div class="mt-5 content-justify-center">
    <h3>Pattern details</h3>
    
    <table id="patternDetailsTable">
        <thead></thead>
        <tbody>
            <tr>
                <th>Pattern ID</th>
                <td>{$pattern->getId()}</td>
            </tr>
            <tr>
                <th>Pattern</th>
                <td>{$pattern->getPattern()}</td>
            </tr>
            <tr>
                <th>Pattern, without dot</th>
                <td>{$pattern->getPatternNoDot()}</td>
            </tr>
            <tr>
                <th>Pattern, without numbers</th>
                <td>{$pattern->getPatternNoNumbers()}</td>
            </tr>
            <tr>
                <th>Pattern, only text</th>
                <td>{$pattern->getPatternText()}</td>
            </tr>
            <tr>
                <th>Pattern type</th>
                <td>{$pattern->getPatternText()}</td>
            </tr>
        </tbody>
    </table>
    <br/>
    <a href="javascript:history.back()">Go Back</a>
</div>
TPL;

CommonUtils::includeBase();

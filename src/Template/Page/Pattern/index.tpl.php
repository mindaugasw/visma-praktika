<?php

use App\Entity\HyphenationPattern;
use App\Service\Paginator\PaginatedList;
use App\Template\CommonUtils;

global $tpl;

/**
 * @var PaginatedList<HyphenationPattern>
 */
$patterns = $tpl['patterns'];

$tableRows = '';
foreach ($patterns->getItems() as $pattern) {
    $tableRows .= <<<TPL
        <tr>
            <th>{$pattern->getId()}</th>
            <td>{$pattern->getPattern()}</td>
            <td><a href="#">View</a></td>
        </tr>
    TPL;
}

$tpl['pagination'] = $patterns;
$paginator = CommonUtils::includeString(__DIR__ . '/../../Common/paginator.tpl.php');

$tpl['body'] = <<<TPL
<div class="mt-5" id="hypBlockInput">
    <h3>Pattern list</h3>
    <p>Patterns used in hyphenation algorithm. There's {$patterns->getCountTotal()} of them.</p>
    
<table class="table table-striped table-hover">
    <thead>
        <tr>
            <th scope="col">#</th>
            <th scope="col">Pattern</th>
            <th scope="col">View</th>
        </tr>
    </thead>
    <tbody>
        {$tableRows}
    </tbody>
</table>
{$paginator}
</div>
TPL;

CommonUtils::includeBase();

<?php

use App\Entity\HyphenationPattern;
use App\Entity\WordResult;
use App\Service\Paginator\PaginatedList;
use App\Template\CommonUtils;

global $tpl;

/*
 * Params:
 * - words, PaginatedList<WordResult>
 */

/**
 * @var PaginatedList<WordResult>
 */
$words = $tpl['words'];

$tableRows = '';
foreach ($words->items as $word) {
    $tableRows .= <<<TPL
        <tr>
            <th>{$word->getId()}</th>
            <td>{$word->getInput()}</td>
            <td>{$word->getResult()}</td>
            <td><a href="/word/view?word={$word->getId()}">View</a></td>
        </tr>
    TPL;
}

$tpl['pagination'] = $words;
$tpl['paginationLinkFormat'] = '/word?page=%d';
$paginator = CommonUtils::includeString(__DIR__ . '/../../Common/paginator.tpl.php');

$tpl['body'] = <<<TPL
<div>
    <h3>Word list</h3>
    <p>Hyphenated words, saved in database. There's {$words->countTotal} of them.</p>
    
    <table class="table table-striped table-hover">
        <thead>
            <tr>
                <th scope="col">#</th>
                <th scope="col">Original word</th>
                <th scope="col">Hyphenated word</th>
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

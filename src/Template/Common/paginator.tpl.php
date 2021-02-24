<?php

declare(strict_types=1);

use App\Service\Paginator\PaginatedList;

global $tpl;

/*
 * Params:
 * - pagination - PaginatedList object
 * - paginationLinkFormat - format string for sprintf to generate page link. Will
 *                          be passed 1 integer (page number)
 */

/**
 * @var PaginatedList
 */
$pagination = $tpl['pagination'];

function getPageItemHtml(
    string|int $pageNumber,
    string|int $buttonText,
    bool $isDisabled = false,
    bool $isActive = false
): string {
    global $tpl;
    $linkFormat = $tpl['paginationLinkFormat'];
    
    return sprintf(
        '<li class="page-item %s %s"><a class="page-link" href="%s">%s</a></li>',
        $isDisabled ? 'disabled' : '',
        $isActive ? 'active' : '',
        sprintf($linkFormat, $pageNumber),
        $buttonText
    );
}

// first page
$pagesHtml = getPageItemHtml(1, '«', $pagination->page === $pagination->first, false);

// previous page
$pagesHtml .= getPageItemHtml($pagination->previous, '‹', $pagination->previous === -1, false);

// page range
foreach ($pagination->pageRange as $page) {
    $pagesHtml .= getPageItemHtml($page, $page, false, $pagination->page === $page);
}

// next page
$pagesHtml .= getPageItemHtml($pagination->next, '›', $pagination->next === -1, false);

// last page
$pagesHtml .= getPageItemHtml($pagination->last, '»', $pagination->last === $pagination->page, false);

echo <<<TPL
    <nav>
        <ul class="pagination justify-content-center">
            {$pagesHtml}
        </ul>
    </nav>
TPL;

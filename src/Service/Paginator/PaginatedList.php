<?php

declare(strict_types=1);

namespace App\Service\Paginator;

use JsonSerializable;

class PaginatedList implements JsonSerializable
{
    /**
     * Number of items in $pageRange from both sides of current page
     * i.e. if $page = 5 and PAGE_RANGE_DISTANCE = 2, $pageRange will be
     * [3, 4, 5, 6, 7]
     */
    private const PAGE_RANGE_DISTANCE = 3;
    
    /**
     * Items list for this page
     */
    public array $items;
    
    /**
     * Items per page, requested number
     */
    public int $perPage;
    
    /**
     * Actual number of items in this page. Can be less than $perPage
     * on last page
     */
    public int $count;
    
    /**
     * Total number of all items matching criteria
     */
    public int $countTotal;
    
    /**
     * Page range for navigation, $pageName => $number
     */
    public array $pageRange;
    
    /**
     * Current page
     */
    public int $page;
    
    public int $first;
    
    public int $last;
    
    /**
     * Previous page number. Will be -1 if there's no previous page
     */
    public int $previous;
    
    /**
     * Next page number. Will be -1 if there's no next page
     */
    public int $next;
       
    public function __construct(array $items, int $limit, int $offset, int $countTotal)
    {
        $this->items = $items;
        $this->count = count($items);
        $this->perPage = $limit;
        $this->countTotal = $countTotal;
        
        $this->page = intval($offset / $limit) + 1;
        $this->first = 1;
        $this->last = intval($this->countTotal / $limit);
        $this->previous = $this->page - 1 < 1 ? -1 : $this->page - 1;
        $this->next = $this->page + 1 > $this->last ? -1 : $this->page + 1;
        
        $this->buildPageRange();
    }
    
    public function jsonSerialize()
    {
        return get_object_vars($this);
    }
    
    private function buildPageRange(): void
    {
        // TODO add tests
        $page = $this->page;
        
        $pagesBefore = self::PAGE_RANGE_DISTANCE;
        $pagesAfter = self::PAGE_RANGE_DISTANCE;
        
        // ensure that $pageRange doesn't go below 1
        if ($page - $pagesBefore < 1) {
            $diff = $page - $pagesBefore - 1;
            $pagesBefore += $diff;
        }
        
        // ensure that $pageRange doesn't above $lastPage
        if ($page + $pagesAfter > $this->last) {
            $diff = $this->last - $page - $pagesAfter;
            $pagesAfter += $diff;
        }
    
        // range before $page
        for ($i = $page - $pagesBefore; $i < $page; $i++) {
            $pageRange[] = $i;
        }
        
        // current $page
        $pageRange[] = $page;
        
        // range after $page
        for ($i = $page + 1; $i < $page + $pagesAfter + 1; $i++) {
            $pageRange[] = $i;
        }
        
        $this->pageRange = $pageRange;
    }
}

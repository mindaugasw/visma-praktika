<?php

declare(strict_types=1);

namespace App\Service\Paginator;

use JsonSerializable;

class PaginatedList implements JsonSerializable
{
    /**
     * Number of items in $pageRange from both sides of current page, not
     * including first, previous, next, last pages.
     * i.e. if $page = 5 and PAGE_RANGE_DISTANCE = 2, $pageRange will be
     * [3, 4, 5, 6, 7]
     */
    private const PAGE_RANGE_DISTANCE = 2;
    
    /**
     * Items list for this page
     */
    private array $items;
    
    /**
     * Page number
     */
    private int $page;
    
    /**
     * Items per page, requested number
     */
    private int $perPage;
    
    /**
     * Actual number of items in this page. Can be less than $perPage
     * on last page
     */
    private int $count;
    
    /**
     * Total number of all items matching criteria
     */
    private int $countTotal;
    
    /**
     * Page range for navigation, $pageName => $number
     */
    private array $pageRange;
       
    public function __construct(array $items, int $limit, int $offset, int $countTotal)
    {
        $this->items = $items;
        $this->count = count($items);
        $this->perPage = $limit;
        $this->countTotal = $countTotal;
        $this->page = intval($offset / $limit) + 1;
        
        $this->buildPageRange($limit);
    }
    
    public function jsonSerialize()
    {
        return get_object_vars($this);
    }
    
    /**
     * @return array
     */
    public function getItems(): array
    {
        return $this->items;
    }
    
    /**
     * @return int
     */
    public function getPage(): int
    {
        return $this->page;
    }
    
    /**
     * @return int
     */
    public function getPerPage(): int
    {
        return $this->perPage;
    }
    
    /**
     * @return int|void
     */
    public function getCount(): int
    {
        return $this->count;
    }
    
    /**
     * @return int
     */
    public function getCountTotal(): int
    {
        return $this->countTotal;
    }
    
    /**
     * @return array
     */
    public function getPageRange(): array
    {
        return $this->pageRange;
    }
    
    private function buildPageRange(int $limit): void
    {
        // TODO add tests
        $page = $this->page;
        $lastPage = intval($this->countTotal / $limit);
        
        $pagesBefore = self::PAGE_RANGE_DISTANCE;
        $pagesAfter = self::PAGE_RANGE_DISTANCE;
        
        // ensure that $pageRange doesn't go below 1
        if ($page - $pagesBefore < 1) {
            $diff = $page - $pagesBefore - 1;
            $pagesBefore += $diff;
        }
        
        // ensure that $pageRange doesn't above $lastPage
        if ($page + $pagesAfter > $lastPage) {
            $diff = $lastPage - $page - $pagesAfter;
            $pagesAfter += $diff;
        }
        
        // first page. Only add if it isn't current $page
        if ($page > 1) {
            $pageRange = [
                'first' => 1
            ];
        }
    
        // previous page. Only add if it's at least 2nd page
        if ($page > 1) {
            $pageRange['previous'] = $page - 1;
        }
        
        // range before $page
        for ($i = $page - $pagesBefore; $i < $page; $i++) {
            $pageRange[$i] = $i;
        }
        
        // current $page
        $pageRange[$page] = $page;
        
        // range after $page
        for ($i = $page + 1; $i < $page + $pagesAfter + 1; $i++) {
            $pageRange[$i] = $i;
        }
    
        // next page. Only add if it's not $lastPage page
        if ($page < $lastPage) {
            $pageRange['next'] = $page + 1;
        }
        
        // last page
        if ($page < $lastPage) {
            $pageRange['last'] = $lastPage;
        }
        
        $this->pageRange = $pageRange;
    }
}

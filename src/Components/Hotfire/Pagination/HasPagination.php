<?php

declare(strict_types=1);

namespace Components\Hotfire\Pagination;

/**
 * Trait for components that support pagination.
 * 
 * Provides methods for:
 * - Page management
 * - Sort management
 * - Search management
 * - Per-page management
 * - Reset on filter change
 * - URL synchronization
 */
trait HasPagination
{
    /**
     * Pagination helper instance.
     * 
     * @var PaginationHelper|null
     */
    private ?PaginationHelper $pagination = null;

    /**
     * Get or create the pagination helper.
     * 
     * @return PaginationHelper
     */
    protected function pagination(): PaginationHelper
    {
        if ($this->pagination === null) {
            $this->pagination = new PaginationHelper();
        }
        
        return $this->pagination;
    }

    /**
     * Get current page.
     * 
     * @return int Current page number
     */
    public function getPage(): int
    {
        return $this->pagination()->page;
    }

    /**
     * Set current page.
     * 
     * @param int $page Page number
     * @return void
     */
    public function setPage(int $page): void
    {
        $this->pagination()->goToPage($page);
    }

    /**
     * Get items per page.
     * 
     * @return int Items per page
     */
    public function getPerPage(): int
    {
        return $this->pagination()->perPage;
    }

    /**
     * Set items per page.
     * 
     * @param int $perPage Items per page
     * @return void
     */
    public function setPerPage(int $perPage): void
    {
        $this->pagination()->setPerPage($perPage);
    }

    /**
     * Get total items.
     * 
     * @return int Total items
     */
    public function getTotal(): int
    {
        return $this->pagination()->total;
    }

    /**
     * Set total items.
     * 
     * @param int $total Total items
     * @return void
     */
    public function setTotal(int $total): void
    {
        $this->pagination()->total = $total;
    }

    /**
     * Get sort field.
     * 
     * @return string|null Sort field
     */
    public function getSortField(): ?string
    {
        return $this->pagination()->sortField;
    }

    /**
     * Set sort field and direction.
     * 
     * @param string $field Sort field
     * @param string $direction Sort direction
     * @return void
     */
    public function setSort(string $field, string $direction = 'asc'): void
    {
        $this->pagination()->setSort($field, $direction);
    }

    /**
     * Get sort direction.
     * 
     * @return string Sort direction
     */
    public function getSortDirection(): string
    {
        return $this->pagination()->sortDirection;
    }

    /**
     * Get search query.
     * 
     * @return string Search query
     */
    public function getSearch(): string
    {
        return $this->pagination()->search;
    }

    /**
     * Set search query.
     * 
     * @param string $query Search query
     * @return void
     */
    public function setSearch(string $query): void
    {
        $this->pagination()->setSearch($query);
    }

    /**
     * Clear search query.
     * 
     * @return void
     */
    public function clearSearch(): void
    {
        $this->pagination()->clearSearch();
    }

    /**
     * Go to next page.
     * 
     * @return void
     */
    public function nextPage(): void
    {
        $this->pagination()->nextPage();
    }

    /**
     * Go to previous page.
     * 
     * @return void
     */
    public function previousPage(): void
    {
        $this->pagination()->previousPage();
    }

    /**
     * Reset to first page.
     * 
     * @return void
     */
    public function resetPage(): void
    {
        $this->pagination()->resetPage();
    }

    /**
     * Get pagination data for view.
     * 
     * @return array Pagination data
     */
    public function getPaginationData(): array
    {
        return $this->pagination()->toArray();
    }

    /**
     * Get URL parameters for pagination.
     * 
     * @return array URL parameters
     */
    public function getPaginationUrlParams(): array
    {
        return $this->pagination()->getUrlParams();
    }

    /**
     * Sync pagination from URL parameters.
     * 
     * @param array $params URL parameters
     * @return void
     */
    public function syncPaginationFromUrl(array $params): void
    {
        $this->pagination()->syncFromUrl($params);
    }
}

<?php

declare(strict_types=1);

namespace Components\Hotfire\Pagination;

/**
 * Pagination helper for Hotfire components.
 * 
 * Provides methods for:
 * - Page management
 * - Sort management
 * - Search management
 * - Per-page management
 * - Reset on filter change
 * - Optional URL synchronization
 */
final class PaginationHelper
{
    /**
     * Current page number.
     * 
     * @var int
     */
    public int $page = 1;

    /**
     * Items per page.
     * 
     * @var int
     */
    public int $perPage = 15;

    /**
     * Total items.
     * 
     * @var int
     */
    public int $total = 0;

    /**
     * Sort field.
     * 
     * @var string|null
     */
    public ?string $sortField = null;

    /**
     * Sort direction.
     * 
     * @var 'asc'|'desc'
     */
    public string $sortDirection = 'asc';

    /**
     * Search query.
     * 
     * @var string
     */
    public string $search = '';

    /**
     * URL synchronization enabled.
     * 
     * @var bool
     */
    public bool $syncWithUrl = false;

    /**
     * Calculate offset for pagination.
     * 
     * @return int Offset value
     */
    public function getOffset(): int
    {
        return ($this->page - 1) * $this->perPage;
    }

    /**
     * Calculate total pages.
     * 
     * @return int Total pages
     */
    public function getTotalPages(): int
    {
        if ($this->perPage === 0) {
            return 0;
        }
        
        return (int) ceil($this->total / $this->perPage);
    }

    /**
     * Check if there is a next page.
     * 
     * @return bool Whether there is a next page
     */
    public function hasNextPage(): bool
    {
        return $this->page < $this->getTotalPages();
    }

    /**
     * Check if there is a previous page.
     * 
     * @return bool Whether there is a previous page
     */
    public function hasPreviousPage(): bool
    {
        return $this->page > 1;
    }

    /**
     * Go to next page.
     * 
     * @return void
     */
    public function nextPage(): void
    {
        if ($this->hasNextPage()) {
            $this->page++;
        }
    }

    /**
     * Go to previous page.
     * 
     * @return void
     */
    public function previousPage(): void
    {
        if ($this->hasPreviousPage()) {
            $this->page--;
        }
    }

    /**
     * Go to specific page.
     * 
     * @param int $page Page number
     * @return void
     */
    public function goToPage(int $page): void
    {
        $totalPages = max(1, $this->getTotalPages());
        $this->page = max(1, min($page, $totalPages));
    }

    /**
     * Set sort field and direction.
     * 
     * @param string $field Sort field
     * @param 'asc'|'desc' $direction Sort direction
     * @return void
     */
    public function setSort(string $field, string $direction = 'asc'): void
    {
        $this->sortField = $field;
        $this->sortDirection = $direction === 'desc' ? 'desc' : 'asc';
    }

    /**
     * Toggle sort direction for current field.
     * 
     * @return void
     */
    public function toggleSort(): void
    {
        $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
    }

    /**
     * Reset to first page (typically when filters change).
     * 
     * @return void
     */
    public function resetPage(): void
    {
        $this->page = 1;
    }

    /**
     * Set search query and reset page.
     * 
     * @param string $query Search query
     * @return void
     */
    public function setSearch(string $query): void
    {
        $this->search = $query;
        $this->resetPage();
    }

    /**
     * Clear search query and reset page.
     * 
     * @return void
     */
    public function clearSearch(): void
    {
        $this->search = '';
        $this->resetPage();
    }

    /**
     * Set items per page and reset page.
     * 
     * @param int $perPage Items per page
     * @return void
     */
    public function setPerPage(int $perPage): void
    {
        $this->perPage = max(1, $perPage);
        $this->resetPage();
    }

    /**
     * Get pagination data for view.
     * 
     * @return array{page: int, perPage: int, total: int, totalPages: int, hasNext: bool, hasPrevious: bool, offset: int}
     */
    public function toArray(): array
    {
        return [
            'page' => $this->page,
            'perPage' => $this->perPage,
            'total' => $this->total,
            'totalPages' => $this->getTotalPages(),
            'hasNext' => $this->hasNextPage(),
            'hasPrevious' => $this->hasPreviousPage(),
            'offset' => $this->getOffset(),
            'sortField' => $this->sortField,
            'sortDirection' => $this->sortDirection,
            'search' => $this->search,
        ];
    }

    /**
     * Build URL parameters for pagination.
     * 
     * @return array<string, string|int> URL parameters
     */
    public function getUrlParams(): array
    {
        $params = [
            'page' => $this->page,
            'perPage' => $this->perPage,
        ];

        if ($this->sortField !== null) {
            $params['sort'] = $this->sortField;
            $params['direction'] = $this->sortDirection;
        }

        if ($this->search !== '') {
            $params['search'] = $this->search;
        }

        return $params;
    }

    /**
     * Sync from URL parameters.
     * 
     * @param array<string, string|int> $params URL parameters
     * @return void
     */
    public function syncFromUrl(array $params): void
    {
        if (isset($params['page'])) {
            $this->page = max(1, (int) $params['page']);
        }
        
        if (isset($params['perPage'])) {
            $this->perPage = max(1, (int) $params['perPage']);
        }
        
        if (isset($params['sort'])) {
            $this->sortField = (string) $params['sort'];
        }
        
        if (isset($params['direction'])) {
            $this->sortDirection = (string) $params['direction'] === 'desc' ? 'desc' : 'asc';
        }
        
        if (isset($params['search'])) {
            $this->search = (string) $params['search'];
        }
    }
}

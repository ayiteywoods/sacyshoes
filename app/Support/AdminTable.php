<?php

namespace App\Support;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Http\Request;

class AdminTable
{
    public const PER_PAGE = 10;

    /**
     * @param  array<string, string|callable(EloquentBuilder|QueryBuilder, string): void>  $sortable
     */
    public static function paginate(
        EloquentBuilder|QueryBuilder $query,
        Request $request,
        array $sortable,
        string $defaultSort = 'created_at',
        string $defaultDirection = 'desc',
        int $perPage = self::PER_PAGE,
        string $sortKey = 'sort',
        string $directionKey = 'direction',
        string $pageName = 'page',
    ): LengthAwarePaginator {
        $sort = $request->string($sortKey)->toString() ?: $defaultSort;
        $direction = strtolower($request->string($directionKey, $defaultDirection)->toString());

        if (! isset($sortable[$sort])) {
            $sort = $defaultSort;
            $direction = $defaultDirection;
        }

        if (! in_array($direction, ['asc', 'desc'], true)) {
            $direction = $defaultDirection;
        }

        $sorter = $sortable[$sort];

        if (is_callable($sorter)) {
            $sorter($query, $direction);
        } else {
            $query->orderBy($sorter, $direction);
        }

        return $query
            ->paginate($perPage, ['*'], $pageName)
            ->withQueryString();
    }
}

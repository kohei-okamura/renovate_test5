<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\Finder;

use Domain\Common\Pagination;
use Domain\Finder;
use Domain\FinderResult;
use Domain\Model;
use Domain\ModelCompat;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Arr;
use Illuminate\Support\LazyCollection;
use Infrastructure\Domainable;
use Lib\Exceptions\InvalidArgumentException;
use ScalikePHP\Seq;

/**
 * Eloquent Finder
 */
abstract class EloquentFinder implements Finder
{
    /** {@inheritdoc} */
    public function find(array $filterParams, array $paginationParams): FinderResult
    {
        $sortBy = Arr::get($paginationParams, 'sortBy', '');
        $desc = Arr::get($paginationParams, 'desc', false);
        $query = $this->getQueryBuilder();
        $this->setConditions($query, $filterParams);
        $this->setSortBy($query, $sortBy, $desc);
        if (empty($paginationParams['all'])) {
            $itemsPerPage = Arr::get($paginationParams, 'itemsPerPage', Finder::DEFAULT_ITEMS_PER_PAGE);
            $page = Arr::get($paginationParams, 'page', 1);
            return $this->paginate($query, $sortBy, $desc, $itemsPerPage, $page);
        } else {
            return $this->all($query, $sortBy, $desc);
        }
    }

    /** {@inheritdoc} */
    public function cursor(array $filterParams, array $orderParams): LazyCollection
    {
        $sortBy = Arr::get($orderParams, 'sortBy', '');
        $desc = Arr::get($orderParams, 'desc', false);
        $query = $this->getQueryBuilder();
        $this->setConditions($query, $filterParams);
        $this->setSortBy($query, $sortBy, $desc);
        return $query->cursor()->map(fn (Domainable $x): Model => $x->toDomain());
    }

    /**
     * クエリビルダーを取得する.
     *
     * @return \Illuminate\Database\Eloquent\Builder&QueryBuilder
     */
    abstract protected function getQueryBuilder(): EloquentBuilder;

    /**
     * 抽出対象のカラム名.
     *
     * @return array
     */
    protected function columns(): array
    {
        return ['*'];
    }

    /**
     * クエリビルダーに検索条件を設定する.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $key
     * @param mixed $value
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function setCondition(EloquentBuilder $query, string $key, $value): EloquentBuilder
    {
        return match ($key) {
            'organizationId' => $query->where('organization_id', '=', $value),
            default => $query,
        };
    }

    /**
     * クエリビルダーに検索条件を設定する.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param array $filterParams
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function setConditions(EloquentBuilder $query, array $filterParams): EloquentBuilder
    {
        foreach ($filterParams as $key => $value) {
            if ($value !== null && $value !== '' && $value !== []) {
                $this->setCondition($query, $key, $value);
            }
        }
        return $query;
    }

    /**
     * キーワードを用いた検索条件を設定する.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param array $keywords
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function setKeywordCondition(EloquentBuilder $query, array $keywords): EloquentBuilder
    {
        return empty($keywords)
            ? $query
            : $query->whereExists(function (QueryBuilder $q) use ($keywords): void {
                $base = $this->baseTableName();
                $words = Seq::fromArray($keywords)->map(fn (string $x): string => "+{$x}");
                $q->selectRaw(1)
                    ->from($base . '_keyword')
                    ->whereColumn($base . '_id', $base . '.id')
                    ->whereRaw('MATCH (keyword) AGAINST (? IN BOOLEAN MODE)', [$words->mkString(' ')]);
            });
    }

    /**
     * ソートするカラム名を取得する.
     *
     * @param string $orderBy ソート指定
     * @return string カラム名
     */
    protected function getOrderByColumnName(string $orderBy): string
    {
        return match ($orderBy) {
            'id' => $this->baseTableName() . '.id',
            'date' => 'created_at',
            default => throw new InvalidArgumentException("unsupported orderBy: {$orderBy}"),
        };
    }

    /**
     * クエリビルダーにソート順を設定する.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $sortBy
     * @param bool $desc
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function setSortBy(EloquentBuilder $query, string $sortBy, bool $desc): EloquentBuilder
    {
        if (strlen($sortBy) === 0) {
            throw new InvalidArgumentException('sortBy is empty');
        }
        return $query->orderBy($this->getOrderByColumnName($sortBy), $desc ? 'desc' : 'asc');
    }

    /**
     * 検索対象のテーブル名.
     *
     * @codeCoverageIgnore テーブル名の定義なので外す
     *
     * @return string
     */
    protected function baseTableName(): string
    {
        return '';
    }

    /**
     * 検索結果をパースする.
     *
     * @param \Infrastructure\Domainable&\Infrastructure\Model $x
     * @return \Domain\ModelCompat
     */
    protected function parseResult(Domainable $x): ModelCompat
    {
        return $x->toDomain();
    }

    /**
     * ページ繰りされたエンティティの一覧を取得する.
     *
     * @param \Illuminate\Database\Eloquent\Builder&\Illuminate\Database\Query\Builder $query
     * @param string $sortBy
     * @param bool $desc
     * @param int $itemsPerPage
     * @param int $page
     * @return \Domain\FinderResult
     */
    private function paginate(
        EloquentBuilder $query,
        string $sortBy,
        bool $desc,
        int $itemsPerPage,
        int $page
    ): FinderResult {
        $paginator = $query->paginate($itemsPerPage, $query->getQuery()->columns ?? $this->columns(), 'page', $page);
        $pagination = Pagination::create([
            'count' => $paginator->total(),
            'desc' => $desc,
            'itemsPerPage' => $itemsPerPage,
            'page' => $page,
            'pages' => $paginator->lastPage(),
            'sortBy' => $sortBy,
        ]);
        $items = $paginator->items();
        return FinderResult::from($items, $pagination)->map(fn (Domainable $x): ModelCompat => $this->parseResult($x));
    }

    /**
     * ページ繰りしていないエンティティの一覧を取得する.
     *
     * @param \Illuminate\Database\Eloquent\Builder&\Illuminate\Database\Query\Builder $query
     * @param string $sortBy
     * @param bool $desc
     * @return \Domain\FinderResult
     */
    private function all(EloquentBuilder $query, string $sortBy, bool $desc): FinderResult
    {
        $items = $query->get($query->getQuery()->columns ?? $this->columns());
        $count = $items->count();
        $pagination = Pagination::create([
            'count' => $count,
            'desc' => $desc,
            'itemsPerPage' => $count,
            'page' => 1,
            'pages' => 1,
            'sortBy' => $sortBy,
        ]);
        return FinderResult::from($items, $pagination)->map(fn (Domainable $x): ModelCompat => $this->parseResult($x));
    }
}

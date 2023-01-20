<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase;

use Domain\Context\Context;
use Domain\Finder;
use Domain\FinderResult;
use Domain\Office\Office;
use Domain\Permission\Permission;
use Illuminate\Support\Arr;
use Lib\Exceptions\NotFoundException;
use ScalikePHP\Option;
use ScalikePHP\Seq;

/**
 * 検索ユースケース標準実装（複数権限対応版）.
 */
trait FindWithPermissionsInteractorFeature
{
    protected Finder $finder;

    /**
     * 検索する.
     *
     * @param \Domain\Context\Context $context
     * @param array|\Domain\Permission\Permission[] $permissions
     * @param array $filterParams
     * @param array $paginationParams
     * @return \Domain\FinderResult
     */
    public function handle(Context $context, array $permissions, array $filterParams, array $paginationParams): FinderResult
    {
        $sortBy = Arr::get($paginationParams, 'sortBy');
        $filter = $this->defaultFilterParams($context, $permissions) + $filterParams;
        return $this->finder->find(
            $filter,
            ['sortBy' => $sortBy ?: $this->defaultSortBy()] + $paginationParams
        );
    }

    /**
     * デフォルトのフィルタパラメータ.
     *
     * @param \Domain\Context\Context $context
     * @param array|\Domain\Permission\Permission[] $permissions
     * @return array
     */
    protected function defaultFilterParams(Context $context, array $permissions): array
    {
        return [];
    }

    /**
     * ソート順のデフォルト値.
     *
     * @return string
     */
    abstract protected function defaultSortBy(): string;

    /**
     * OfficeIDによるフィルタを取得する.
     *
     * @param \Domain\Context\Context $context
     * @param array|\Domain\Permission\Permission[] $permissions
     * @param string $key
     * @return array
     */
    protected function getOfficeIdFilter(Context $context, array $permissions, string $key = 'officeIds'): array
    {
        $officesOptions = Seq::from(...$permissions)
            // getPermittedOffices に持っていない権限を指定すると例外になるのでここでフィルタする
            ->filter(fn (Permission $x): bool => $context->isAuthorizedTo($x))
            ->map(fn (Permission $x): Option => $context->getPermittedOffices($x))
            ->computed();

        // 全ての事業所が認可されている場合はフィルタしないので空配列を返す
        if ($officesOptions->exists(fn (Option $officesOption): bool => $officesOption->isEmpty())) {
            return [];
        }

        $offices = $officesOptions->flatMap(fn (Option $officesOption): Seq => $officesOption->toSeq()->flatten());

        if ($offices->isEmpty()) {
            throw new NotFoundException('Permitted Office not found.');
        }

        $officeIds = $offices->map(fn (Office $x): int => $x->id)->toArray();
        return [$key => $officeIds];
    }
}

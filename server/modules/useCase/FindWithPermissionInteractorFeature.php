<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
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

/**
 * 検索ユースケース標準実装.
 */
trait FindWithPermissionInteractorFeature
{
    protected Finder $finder;

    /**
     * 検索する.
     *
     * @param \Domain\Context\Context $context
     * @param \Domain\Permission\Permission $permission
     * @param array $filterParams
     * @param array $paginationParams
     * @return \Domain\FinderResult
     */
    public function handle(Context $context, Permission $permission, array $filterParams, array $paginationParams): FinderResult
    {
        $sortBy = Arr::get($paginationParams, 'sortBy');
        $filter = $this->defaultFilterParams($context, $permission) + $filterParams;
        return $this->finder->find(
            $filter,
            ['sortBy' => $sortBy ?: $this->defaultSortBy()] + $paginationParams
        );
    }

    /**
     * デフォルトのフィルタパラメータ.
     *
     * @param \Domain\Context\Context $context
     * @param \Domain\Permission\Permission $permission
     * @return array
     */
    protected function defaultFilterParams(Context $context, Permission $permission): array
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
     * @param \Domain\Permission\Permission $permission
     * @param string $key
     * @return array
     */
    protected function getOfficeIdFilter(Context $context, Permission $permission, string $key = 'officeIds'): array
    {
        $officesOption = $context->getPermittedOffices($permission);

        // 全ての事業所が認可されている場合はフィルタしないので空配列を返す
        if ($officesOption->isEmpty()) {
            return [];
        }

        /** @var \Domain\Office\Office[]|\ScalikePHP\Seq $offices */
        $offices = $officesOption->get();

        if ($offices->isEmpty()) {
            throw new NotFoundException('Permitted Office not found.');
        }

        $officeIds = $offices->map(fn (Office $x): int => $x->id)->toArray();
        return [$key => $officeIds];
    }
}

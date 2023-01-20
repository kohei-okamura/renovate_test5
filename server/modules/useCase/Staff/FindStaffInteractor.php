<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Staff;

use Domain\Context\Context;
use Domain\FinderResult;
use Domain\Permission\Permission;
use Domain\Staff\StaffFinder;
use Illuminate\Support\Arr;
use Lib\Exceptions\LogicException;
use ScalikePHP\Seq;
use UseCase\BuildFinderResultHolder;
use UseCase\FindWithPermissionInteractorFeature;

/**
 * スタッフ検索ユースケース実装.
 */
final class FindStaffInteractor implements FindStaffUseCase
{
    use FindWithPermissionInteractorFeature;
    use BuildFinderResultHolder;

    /**
     * Constructor.
     *
     * @param \Domain\Staff\StaffFinder $finder
     */
    public function __construct(StaffFinder $finder)
    {
        $this->finder = $finder;
    }

    // TODO: DEV-4820 一時的にオーバーライドして処理している

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
        $sortBy = Arr::get($paginationParams, 'sortBy') ?: $this->defaultSortBy();
        $defaultFilterParams = $this->defaultFilterParams($context, $permission);
        $filterOfficeIds = $filterParams['officeIds'] ?? [];
        if (!Seq::fromArray($filterOfficeIds)->forAll(fn ($x): bool => is_numeric($x))) {
            throw new LogicException('Non numeric value specified: officeIds');
        }
        $accessibleOfficeIds = $defaultFilterParams['officeIds'] ?? [];
        if (empty($filterOfficeIds) || empty($accessibleOfficeIds)) {
            return $this->finder->find(
                ['officeIds' => $filterOfficeIds + $accessibleOfficeIds] + $defaultFilterParams + $filterParams,
                ['sortBy' => $sortBy] + $paginationParams
            );
        } else {
            $officeIds = Seq::fromArray($filterOfficeIds)
                ->filter(fn ($x): bool => in_array((int)$x, $accessibleOfficeIds, true));
            return $officeIds->isEmpty()
                ? $this->buildFinderResult(Seq::empty(), $paginationParams, $sortBy)
                : $this->finder->find(
                    ['officeIds' => $officeIds->toArray()] + $defaultFilterParams + $filterParams,
                    ['sortBy' => $sortBy] + $paginationParams
                );
        }
    }

    /** {@inheritdoc} */
    protected function defaultFilterParams(Context $context, Permission $permission): array
    {
        return $this->getOfficeIdFilter($context, $permission)
            + ['organizationId' => $context->organization->id];
    }

    /** {@inheritdoc} */
    protected function defaultSortBy(): string
    {
        return 'name';
    }
}

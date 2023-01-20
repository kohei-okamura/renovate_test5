<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Office;

use Domain\Common\Pagination;
use Domain\Context\Context;
use Domain\Finder;
use Domain\FinderResult;
use Domain\Office\OfficeGroupFinder;
use Domain\Role\RoleScope;
use Illuminate\Support\Arr;
use UseCase\FindInteractorFeature;

/**
 * 事業所グループ検索ユースケース実装.
 */
final class FindOfficeGroupInteractor implements FindOfficeGroupUseCase
{
    use FindInteractorFeature;

    /**
     * Constructor.
     *
     * @param \Domain\Office\OfficeGroupFinder $finder
     */
    public function __construct(OfficeGroupFinder $finder)
    {
        $this->finder = $finder;
    }

    /** {@inheritdoc} */
    public function handle(Context $context, array $filterParams, array $paginationParams): FinderResult
    {
        if (!$context->hasRoleScope(RoleScope::group(), RoleScope::whole())) {
            return FinderResult::from([], Pagination::create([
                'count' => 0,
                'desc' => $paginationParams['desc'] ?? false,
                'itemsPerPage' => $paginationParams['itemsPerPage'] ?? Finder::DEFAULT_ITEMS_PER_PAGE,
                'page' => $paginationParams['page'] ?? 1,
                'pages' => 1,
                'sortBy' => $paginationParams['sortBy'] ?? $this->defaultSortBy(),
            ]));
        }

        $sortBy = Arr::get($paginationParams, 'sortBy', $this->defaultSortBy());
        $filter = $this->defaultFilterParams($context) + $filterParams;
        return $this->finder->find(
            $filter,
            ['sortBy' => $sortBy] + $paginationParams
        );
    }

    /** {@inheritdoc} */
    protected function defaultFilterParams(Context $context): array
    {
        /** @var \Domain\Staff\Staff[]|\ScalikePHP\Option $staffOption */
        $staffOption = $context->staff;
        $idFilter = !$staffOption->isDefined() || $context->hasRoleScope(RoleScope::whole()) ?
            [] :
            ['ids' => $staffOption->head()->officeGroupIds];
        return $idFilter + ['organizationId' => $context->organization->id];
    }

    /** {@inheritdoc} */
    protected function defaultSortBy(): string
    {
        return 'sortOrder';
    }
}

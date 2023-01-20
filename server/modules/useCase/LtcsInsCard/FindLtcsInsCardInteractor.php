<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\LtcsInsCard;

use Domain\Context\Context;
use Domain\LtcsInsCard\LtcsInsCardFinder;
use Domain\Permission\Permission;
use UseCase\FindWithPermissionInteractorFeature;

/**
 * 介護保険被保険者証検索ユースケース実装.
 */
final class FindLtcsInsCardInteractor implements FindLtcsInsCardUseCase
{
    use FindWithPermissionInteractorFeature;

    /**
     * Constructor.
     *
     * @param \Domain\LtcsInsCard\LtcsInsCardFinder $finder
     */
    public function __construct(LtcsInsCardFinder $finder)
    {
        $this->finder = $finder;
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
        return 'date';
    }
}

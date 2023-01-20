<?php
/**
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\User;

use Domain\Context\Context;
use Domain\Permission\Permission;
use Domain\User\UserDwsCalcSpecFinder;
use UseCase\FindWithPermissionInteractorFeature;

/**
 * 障害福祉サービス：利用者別算定情報検索ユースケース実装.
 */
class FindUserDwsCalcSpecInteractor implements FindUserDwsCalcSpecUseCase
{
    use FindWithPermissionInteractorFeature;

    /**
     * Constructor.
     *
     * @param \Domain\User\UserDwsCalcSpecFinder $finder
     */
    public function __construct(UserDwsCalcSpecFinder $finder)
    {
        $this->finder = $finder;
    }

    /** {@inheritdoc} */
    protected function defaultFilterParams(Context $context, Permission $permission): array
    {
        return $this->getOfficeIdFilter($context, $permission);
    }

    /** {@inheritdoc} */
    protected function defaultSortBy(): string
    {
        return 'id';
    }
}

<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\User;

use Domain\User\UserLtcsSubsidyFinder;
use UseCase\FindWithPermissionInteractorFeature;

/**
 * FindUserLtcsSubsidy Interactor.
 */
class FindUserLtcsSubsidyInteractor implements FindUserLtcsSubsidyUseCase
{
    use FindWithPermissionInteractorFeature;

    /**
     * Constructor.
     *
     * @param \Domain\User\UserLtcsSubsidyFinder $finder
     */
    public function __construct(UserLtcsSubsidyFinder $finder)
    {
        $this->finder = $finder;
    }

    /** {@inheritdoc} */
    protected function defaultSortBy(): string
    {
        return 'id';
    }
}

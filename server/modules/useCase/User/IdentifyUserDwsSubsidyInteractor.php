<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\User;

use Domain\Common\Carbon;
use Domain\Context\Context;
use Domain\User\User;
use Domain\User\UserDwsSubsidyFinder;
use ScalikePHP\Option;

/**
 * 利用者：自治体助成情報特定ユースケース実装.
 */
final class IdentifyUserDwsSubsidyInteractor implements IdentifyUserDwsSubsidyUseCase
{
    private UserDwsSubsidyFinder $finder;

    /**
     * {@link \UseCase\User\IdentifyUserDwsSubsidyInteractor} constructor.
     *
     * @param \Domain\User\UserDwsSubsidyFinder $finder
     */
    public function __construct(UserDwsSubsidyFinder $finder)
    {
        $this->finder = $finder;
    }

    /** {@inheritdoc} */
    public function handle(Context $context, User $user, Carbon $targetDate): Option
    {
        $filterParams = [
            'period' => $targetDate,
            'userId' => $user->id,
        ];
        $paginationParams = [
            'all' => true,
            'sortBy' => 'id',
        ];
        return $this->finder->find($filterParams, $paginationParams)->list->headOption();
    }
}

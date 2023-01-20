<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\User;

use Domain\Common\Carbon;
use Domain\Context\Context;
use Domain\User\User;
use Domain\User\UserLtcsCalcSpecFinder;
use ScalikePHP\Option;

/**
 * 介護保険サービス：利用者別算定情報特定ユースケース実装.
 */
final class IdentifyUserLtcsCalcSpecInteractor implements IdentifyUserLtcsCalcSpecUseCase
{
    /**
     * {@link \UseCase\User\IdentifyUserLtcsCalcSpecInteractor} constructor.
     *
     * @param \Domain\User\UserLtcsCalcSpecFinder $finder
     */
    public function __construct(private UserLtcsCalcSpecFinder $finder)
    {
    }

    /** {@inheritdoc} */
    public function handle(Context $context, User $user, Carbon $targetDate): Option
    {
        $filterParams = [
            'userId' => $user->id,
            'effectivatedOnBefore' => $targetDate,
        ];
        $paginationParams = [
            'all' => true,
            'sortBy' => 'effectivatedOn',
            'desc' => true,
        ];
        return $this->finder->find($filterParams, $paginationParams)->list->headOption();
    }
}

<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\User;

use Domain\Common\Carbon;
use Domain\Common\DefrayerCategory;
use Domain\Context\Context;
use Domain\User\User;
use Domain\User\UserLtcsSubsidy;
use Domain\User\UserLtcsSubsidyFinder;
use ScalikePHP\Option;
use ScalikePHP\Seq;

/**
 * 利用者：公費情報特定ユースケース.
 */
final class IdentifyUserLtcsSubsidyInteractor implements IdentifyUserLtcsSubsidyUseCase
{
    private const MAX_SUBSIDIES = 3;

    /**
     * {@link \UseCase\User\IdentifyUserLtcsSubsidyInteractor} constructor.
     *
     * @param \Domain\User\UserLtcsSubsidyFinder $finder
     */
    public function __construct(
        private readonly UserLtcsSubsidyFinder $finder
    ) {
    }

    /** {@inheritdoc} */
    public function handle(Context $context, User $user, Carbon $targetDate): Seq
    {
        $subsidies = $this->find($user, $targetDate);
        return Seq::from(...DefrayerCategory::all())
            ->flatMap(function (DefrayerCategory $category) use ($subsidies): iterable {
                return $subsidies->find(fn (UserLtcsSubsidy $x): bool => $x->defrayerCategory === $category);
            })
            ->map(fn (UserLtcsSubsidy $x): Option => Option::some($x))
            ->append([Option::none(), Option::none(), Option::none()])
            ->take(self::MAX_SUBSIDIES)
            ->computed();
    }

    /**
     * リポジトリから公費情報を取得する.
     *
     * @param \Domain\User\User $user
     * @param \Domain\Common\Carbon $targetDate
     * @return \Domain\User\UserLtcsSubsidy[]&\ScalikePHP\Seq
     */
    private function find(User $user, Carbon $targetDate): Seq
    {
        $filterParams = [
            'period' => $targetDate,
            'userId' => $user->id,
        ];
        $paginationParams = [
            'all' => true,
            'sortBy' => 'id',
        ];
        return $this->finder->find($filterParams, $paginationParams)->list;
    }
}

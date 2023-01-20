<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\LtcsInsCard;

use Domain\Common\Carbon;
use Domain\Context\Context;
use Domain\LtcsInsCard\LtcsInsCardFinder;
use Domain\User\User;
use ScalikePHP\Option;

/**
 * 介護保険被保険者証特定ユースケース実装.
 */
final class IdentifyLtcsInsCardInteractor implements IdentifyLtcsInsCardUseCase
{
    /**
     * {@link \UseCase\LtcsInsCard\IdentifyLtcsInsCardInteractor} constructor.
     *
     * @param \Domain\LtcsInsCard\LtcsInsCardFinder $finder
     */
    public function __construct(private LtcsInsCardFinder $finder)
    {
    }

    /** {@inheritdoc} */
    public function handle(Context $context, User $user, Carbon $targetDate): Option
    {
        $filterParams = [
            'organizationId' => $context->organization->id,
            'userId' => $user->id,
            'effectivatedBefore' => $targetDate,
        ];
        $paginationParams = [
            'itemsPerPage' => 1,
            'sortBy' => 'effectivatedOn',
            'desc' => true,
        ];
        return $this->finder->find($filterParams, $paginationParams)->list->headOption();
    }
}

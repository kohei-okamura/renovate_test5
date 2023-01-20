<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Staff;

use Domain\Context\Context;
use Domain\Staff\InvitationRepository;
use ScalikePHP\Option;

/**
 * 招待取得ユースケース実装.
 */
class LookupInvitationByTokenInteractor implements LookupInvitationByTokenUseCase
{
    private InvitationRepository $repository;

    /**
     * Constructor.
     *
     * @param \Domain\Staff\InvitationRepository $repository
     */
    public function __construct(
        InvitationRepository $repository
    ) {
        $this->repository = $repository;
    }

    /** {@inheritdoc} */
    public function handle(Context $context, string $token): Option
    {
        return $this->repository
            ->lookupOptionByToken($token);
    }
}

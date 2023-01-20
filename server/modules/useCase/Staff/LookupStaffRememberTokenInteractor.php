<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Staff;

use Domain\Context\Context;
use Domain\Staff\StaffRememberTokenRepository;
use ScalikePHP\Seq;

/**
 * スタッフリメンバートークン取得ユースケース実装.
 */
final class LookupStaffRememberTokenInteractor implements LookupStaffRememberTokenUseCase
{
    private StaffRememberTokenRepository $repository;

    /**
     * Constructor.
     *
     * @param \Domain\Staff\StaffRememberTokenRepository $repository
     */
    public function __construct(StaffRememberTokenRepository $repository)
    {
        $this->repository = $repository;
    }

    /** {@inheritdoc} */
    public function handle(Context $context, int ...$id): Seq
    {
        return $this->repository->lookup(...$id);
    }
}

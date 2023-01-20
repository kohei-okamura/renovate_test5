<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\User;

use Domain\Context\Context;
use Domain\Permission\Permission;
use Domain\User\UserDwsCalcSpec;
use Domain\User\UserDwsCalcSpecRepository;
use ScalikePHP\Seq;

/**
 * 障害福祉サービス：利用者別算定情報取得ユースケースの実装.
 */
class LookupUserDwsCalcSpecInteractor implements LookupUserDwsCalcSpecUseCase
{
    /**
     * Constructor.
     *
     * @param \UseCase\User\EnsureUserUseCase $ensureUserUseCase
     * @param \Domain\User\UserDwsCalcSpecRepository $repository
     */
    public function __construct(
        private EnsureUserUseCase $ensureUserUseCase,
        private UserDwsCalcSpecRepository $repository
    ) {
    }

    /** {@inheritdoc} */
    public function handle(Context $context, Permission $permission, int $userId, int ...$ids): Seq
    {
        $this->ensureUserUseCase->handle($context, $permission, $userId);
        $p = fn (UserDwsCalcSpec $x): bool => $x->userId === $userId;
        $xs = $this->repository->lookup(...$ids);
        return $xs->forAll($p) ? $xs : Seq::empty();
    }
}

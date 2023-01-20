<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Calling;

use Domain\Calling\Calling;
use Domain\Calling\CallingRepository;
use Domain\Context\Context;
use Domain\Staff\Staff;
use ScalikePHP\Option;

/**
 * トークンを用いた出勤確認情報取得ユースケース実装.
 */
class LookupCallingByTokenInteractor implements LookupCallingByTokenUseCase
{
    private CallingRepository $repository;

    /**
     * Contructor.
     *
     * @param \Domain\Calling\CallingRepository $repository
     */
    public function __construct(CallingRepository $repository)
    {
        $this->repository = $repository;
    }

    /** {@inheritdoc} */
    public function handle(Context $context, string $token): Option
    {
        $staff = $context->staff->get();
        assert($staff instanceof Staff);
        return $this->repository->lookupOptionByToken($token)
            ->filter(fn (Calling $x): bool => $x->staffId === $staff->id);
    }
}

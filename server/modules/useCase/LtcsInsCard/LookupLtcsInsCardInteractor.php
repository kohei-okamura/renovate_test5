<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\LtcsInsCard;

use Domain\Context\Context;
use Domain\LtcsInsCard\LtcsInsCard;
use Domain\LtcsInsCard\LtcsInsCardRepository;
use Domain\Permission\Permission;
use ScalikePHP\Seq;
use UseCase\User\EnsureUserUseCase;

/**
 * 介護保険被保険者証取得ユースケース実装.
 */
final class LookupLtcsInsCardInteractor implements LookupLtcsInsCardUseCase
{
    private EnsureUserUseCase $ensureUserUseCase;
    private LtcsInsCardRepository $repository;

    /**
     * Constructor.
     *
     * @param \UseCase\User\EnsureUserUseCase $ensureUserUseCase
     * @param \Domain\LtcsInsCard\LtcsInsCardRepository $repository
     */
    public function __construct(EnsureUserUseCase $ensureUserUseCase, LtcsInsCardRepository $repository)
    {
        $this->ensureUserUseCase = $ensureUserUseCase;
        $this->repository = $repository;
    }

    /** {@inheritdoc} */
    public function handle(Context $context, Permission $permission, int $userId, int ...$ids): Seq
    {
        $this->ensureUserUseCase->handle($context, $permission, $userId);
        $p = fn (LtcsInsCard $x): bool => $x->userId === $userId;
        $xs = $this->repository->lookup(...$ids);
        return $xs->forAll($p) ? $xs : Seq::emptySeq();
    }
}

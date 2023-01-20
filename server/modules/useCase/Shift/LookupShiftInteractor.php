<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Shift;

use Domain\Context\Context;
use Domain\Permission\Permission;
use Domain\Shift\Shift;
use Domain\Shift\ShiftRepository;
use ScalikePHP\Seq;

/**
 * 勤務シフト取得ユースケース実装.
 */
final class LookupShiftInteractor implements LookupShiftUseCase
{
    private ShiftRepository $repository;

    /**
     * Constructor.
     *
     * @param \Domain\Shift\ShiftRepository $repository
     */
    public function __construct(ShiftRepository $repository)
    {
        $this->repository = $repository;
    }

    /** {@inheritdoc} */
    public function handle(Context $context, Permission $permission, int ...$ids): Seq
    {
        $xs = $this->repository->lookup(...$ids);
        return $xs->forAll(
            fn (Shift $x): bool => $context->isAccessibleTo($permission, $x->organizationId, [$x->officeId])
        ) ? $xs : Seq::emptySeq();
    }
}

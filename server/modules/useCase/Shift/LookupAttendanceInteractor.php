<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Shift;

use Domain\Context\Context;
use Domain\Permission\Permission;
use Domain\Shift\Attendance;
use Domain\Shift\AttendanceRepository;
use ScalikePHP\Seq;

/**
 * 勤務実績取得ユースケース実装.
 */
final class LookupAttendanceInteractor implements LookupAttendanceUseCase
{
    private AttendanceRepository $repository;

    /**
     * Constructor.
     *
     * @param \Domain\Shift\AttendanceRepository $repository
     */
    public function __construct(AttendanceRepository $repository)
    {
        $this->repository = $repository;
    }

    /** {@inheritdoc} */
    public function handle(Context $context, Permission $permission, int ...$id): Seq
    {
        $xs = $this->repository->lookup(...$id);
        return $xs->forAll(
            fn (Attendance $x): bool => $context->isAccessibleTo($permission, $x->organizationId, [$x->officeId])
        ) ? $xs : Seq::emptySeq();
    }
}

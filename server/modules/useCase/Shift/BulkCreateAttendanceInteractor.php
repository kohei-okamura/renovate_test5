<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Shift;

use Domain\Common\Carbon;
use Domain\Shift\Attendance;
use Domain\Shift\AttendanceRepository;
use Domain\Shift\Shift;
use Domain\Shift\ShiftFinder;
use Domain\TransactionManager;
use Domain\TransactionManagerFactory;

/**
 * 勤務実績一括登録実装.
 */
final class BulkCreateAttendanceInteractor implements BulkCreateAttendanceUseCase
{
    private ShiftFinder $shiftFinder;
    private AttendanceRepository $attendanceRepository;
    private TransactionManager $transaction;

    /**
     * Constructor.
     *
     * @param \Domain\Shift\ShiftFinder $shiftFinder
     * @param \Domain\Shift\AttendanceRepository $attendanceRepository
     * @param \Domain\TransactionManagerFactory $transactionManagerFactory
     */
    public function __construct(
        ShiftFinder $shiftFinder,
        AttendanceRepository $attendanceRepository,
        TransactionManagerFactory $transactionManagerFactory
    ) {
        $this->shiftFinder = $shiftFinder;
        $this->attendanceRepository = $attendanceRepository;
        $this->transaction = $transactionManagerFactory->factory($attendanceRepository);
    }

    /** {@inheritdoc} */
    public function handle(Carbon $targetDate, int ...$organizationIds): int
    {
        $count = 0;
        foreach ($organizationIds as $organizationId) {
            $this->transaction->run(function () use ($organizationId, $targetDate, &$count): void {
                $now = Carbon::now(); // 同一Transactionは同じ時刻として生成する
                $this->shiftFinder
                    ->cursor(
                        [
                            'organizationId' => $organizationId,
                            'isConfirmed' => true,
                            'endDate' => $targetDate,
                        ],
                        [
                            'sortBy' => 'id',
                        ]
                    )
                    ->each(function (Shift $shift) use ($now, &$count): void {
                        $this->attendanceRepository->store(Attendance::createFromShift($shift, $now));
                        ++$count;
                    });
            });
        }
        return $count;
    }
}

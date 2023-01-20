<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Shift;

use Domain\Common\Carbon;
use Domain\Context\Context;
use Domain\Permission\Permission;
use Domain\Shift\Attendance;
use Domain\Shift\AttendanceRepository;
use Domain\TransactionManager;
use Domain\TransactionManagerFactory;
use Lib\Exceptions\NotFoundException;
use Lib\Logging;

/**
 * 勤務実績キャンセル実装.
 */
final class CancelAttendanceInteractor implements CancelAttendanceUseCase
{
    use Logging;

    private LookupAttendanceUseCase $lookupAttendanceUseCase;
    private AttendanceRepository $repository;
    private TransactionManager $transaction;

    /**
     * Constructor.
     *
     * @param \Domain\Shift\AttendanceRepository $attendanceRepository
     * @param \Domain\TransactionManagerFactory $transactionManagerFactory
     * @param LookupAttendanceUseCase $lookupAttendanceUseCase
     */
    public function __construct(
        LookupAttendanceUseCase $lookupAttendanceUseCase,
        AttendanceRepository $attendanceRepository,
        TransactionManagerFactory $transactionManagerFactory
    ) {
        $this->lookupAttendanceUseCase = $lookupAttendanceUseCase;
        $this->repository = $attendanceRepository;
        $this->transaction = $transactionManagerFactory->factory($attendanceRepository);
    }

    /** {@inheritdoc} */
    public function handle(Context $context, string $reason, int ...$ids): void
    {
        $this->transaction->run(function () use ($context, $reason, $ids) {
            $attendances = $this->lookupAttendanceUseCase
                ->handle($context, Permission::updateAttendances(), ...$ids)
                ->filter(fn (Attendance $x): bool => $x->isCanceled === false);
            if (count($attendances) !== count($ids)) {
                $idList = implode(',', $ids);
                throw new NotFoundException("Attendances({$idList}) not found");
            }
            $attendances->each(function (Attendance $shift) use ($reason): void {
                $this->repository->store($shift->copy([
                    'isCanceled' => true,
                    'reason' => $reason,
                    'updatedAt' => Carbon::now(),
                ]));
            });
        });
        $this->logger()->info(
            '勤務実績がキャンセルされました',
            // TODO DEV-1577 IDの複数出力方法は検討中なので暫定的に空文字
            ['id' => ''] + $context->logContext()
        );
    }
}

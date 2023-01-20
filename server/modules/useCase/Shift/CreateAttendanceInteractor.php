<?php
/*
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
use UseCase\Contract\IdentifyContractUseCase;

/**
 * 勤務実績登録実装.
 */
final class CreateAttendanceInteractor implements CreateAttendanceUseCase
{
    use Logging;

    private IdentifyContractUseCase $identifyContractUseCase;
    private AttendanceRepository $repository;
    private TransactionManager $transaction;

    /**
     * Constructor.
     *
     * @param \UseCase\Contract\IdentifyContractUseCase $identifyContractUseCase
     * @param \Domain\Shift\AttendanceRepository $repository
     * @param \Domain\TransactionManagerFactory $transaction
     */
    public function __construct(
        IdentifyContractUseCase $identifyContractUseCase,
        AttendanceRepository $repository,
        TransactionManagerFactory $transaction
    ) {
        $this->identifyContractUseCase = $identifyContractUseCase;
        $this->repository = $repository;
        $this->transaction = $transaction->factory($repository);
    }

    /** {@inheritdoc} */
    public function handle(Context $context, Attendance $attendance): Attendance
    {
        if ($attendance->userId !== null) {
            $serviceSegment = $attendance->task->toServiceSegment()->headOption()->getOrElse(function (): void {
                throw new NotFoundException('ServiceSegment not found');
            });
            /** @var \Domain\Contract\Contract $contract */
            $contract = $this->identifyContractUseCase
                ->handle(
                    $context,
                    Permission::createShifts(),
                    $attendance->officeId,
                    $attendance->userId,
                    $serviceSegment,
                    Carbon::now()
                )
                ->getOrElse(function (): void {
                    throw new NotFoundException('Contract not found');
                });
        }
        $x = $this->transaction->run(fn (): Attendance => $this->repository->store($attendance->copy([
            'organizationId' => $context->organization->id,
            'contractId' => $attendance->userId !== null ? $contract->id : null,
        ])));
        $this->logger()->info(
            '勤務実績が登録されました',
            ['id' => $x->id] + $context->logContext()
        );
        return $x;
    }
}

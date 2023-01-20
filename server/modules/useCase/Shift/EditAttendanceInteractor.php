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
 * 勤務実績編集実装.
 */
final class EditAttendanceInteractor implements EditAttendanceUseCase
{
    use Logging;

    private IdentifyContractUseCase $identifyContractUseCase;
    private LookupAttendanceUseCase $lookupUseCase;
    private AttendanceRepository $repository;
    private TransactionManager $transaction;

    /**
     * Constructor.
     *
     * @param \UseCase\Contract\IdentifyContractUseCase $identifyContractUseCase
     * @param \UseCase\Shift\LookupAttendanceUseCase $lookupUseCase
     * @param \Domain\Shift\AttendanceRepository $repository
     * @param \Domain\TransactionManagerFactory $transactionManagerFactory
     */
    public function __construct(
        IdentifyContractUseCase $identifyContractUseCase,
        LookupAttendanceUseCase $lookupUseCase,
        AttendanceRepository $repository,
        TransactionManagerFactory $transactionManagerFactory
    ) {
        $this->identifyContractUseCase = $identifyContractUseCase;
        $this->lookupUseCase = $lookupUseCase;
        $this->repository = $repository;
        $this->transaction = $transactionManagerFactory->factory($repository);
    }

    /** {@inheritdoc} */
    public function handle(Context $context, int $id, array $values): Attendance
    {
        $entity = $this->lookupUseCase
            ->handle($context, Permission::updateAttendances(), $id)
            ->headOption()
            ->getOrElse(function () use ($id): void {
                throw new NotFoundException("Attendance({$id}) not found");
            });
        /** @var \Domain\Shift\Attendance $storeEntity */
        $storeEntity = $entity->copy($values);
        if ($storeEntity->userId !== null) {
            $serviceSegment = $storeEntity->task->toServiceSegment()->headOption()->getOrElse(function (): void {
                throw new NotFoundException('ServiceSegment not found');
            });
            /** @var \Domain\Contract\Contract $contract */
            $contract = $this->identifyContractUseCase
                ->handle(
                    $context,
                    Permission::updateShifts(),
                    $storeEntity->officeId,
                    $storeEntity->userId,
                    $serviceSegment,
                    Carbon::now()
                )
                ->getOrElse(function (): void {
                    throw new NotFoundException('Contract not found');
                });
        }
        $x = $this->transaction->run(fn (): Attendance => $this->repository->store(
            $storeEntity->copy([
                'contractId' => $storeEntity->userId !== null ? $contract->id : null,
                'updatedAt' => Carbon::now(),
            ])
        ));
        $this->logger()->info(
            '勤務実績が更新されました',
            ['id' => $x->id] + $context->logContext()
        );
        return $x;
    }
}

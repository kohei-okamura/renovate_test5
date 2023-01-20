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
use Domain\TransactionManager;
use Domain\TransactionManagerFactory;
use Lib\Exceptions\NotFoundException;
use Lib\Logging;

/**
 * 勤務実績一括確定実装.
 */
final class ConfirmAttendanceInteractor implements ConfirmAttendanceUseCase
{
    use Logging;

    private LookupAttendanceUseCase $lookupUseCase;
    private AttendanceRepository $repository;
    private TransactionManager $transaction;

    /**
     * Constructor.
     *
     * @param \UseCase\Shift\LookupAttendanceUseCase $lookupUseCase
     * @param \Domain\Shift\AttendanceRepository $repository
     * @param \Domain\TransactionManagerFactory $transactionManagerFactory
     */
    public function __construct(
        LookupAttendanceUseCase $lookupUseCase,
        AttendanceRepository $repository,
        TransactionManagerFactory $transactionManagerFactory
    ) {
        $this->lookupUseCase = $lookupUseCase;
        $this->repository = $repository;
        $this->transaction = $transactionManagerFactory->factory($repository);
    }

    /** {@inheritdoc} */
    public function handle(Context $context, int ...$id): void
    {
        $this->transaction->run(function () use ($context, $id) {
            $entities = $this->lookupUseCase->handle($context, Permission::updateAttendances(), ...$id);
            if (count($entities) !== count($id)) {
                throw new NotFoundException('Specify Entities not found');
            }

            $entities->filter(fn (Attendance $x): bool => !$x->isConfirmed)
                ->each(fn ($e): Attendance => $this->repository->store($e->copy(['isConfirmed' => true])));
        });
        $this->logger()->info(
            '勤務実績が確定されました',
            // TODO: IDの複数出力方法はDEV-1577
            ['id' => ''] + $context->logContext()
        );
    }
}

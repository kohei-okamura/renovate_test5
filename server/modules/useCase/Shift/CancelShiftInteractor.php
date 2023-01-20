<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Shift;

use Domain\Common\Carbon;
use Domain\Context\Context;
use Domain\Event\EventDispatcher;
use Domain\Permission\Permission;
use Domain\Shift\Assignee;
use Domain\Shift\CancelShiftEvent;
use Domain\Shift\Shift;
use Domain\Shift\ShiftRepository;
use Domain\Staff\Staff;
use Domain\TransactionManager;
use Domain\TransactionManagerFactory;
use Lib\Exceptions\NotFoundException;
use Lib\Logging;
use ScalikePHP\Seq;
use UseCase\Staff\LookupStaffUseCase;
use UseCase\User\LookupUserUseCase;

/**
 * 勤務シフトキャンセル実装.
 */
class CancelShiftInteractor implements CancelShiftUseCase
{
    use Logging;

    private EventDispatcher $dispatcher;
    private LookupShiftUseCase $lookupShiftUseCase;
    private LookupStaffUseCase $lookupStaffUseCase;
    private LookupUserUseCase $lookupUserUseCase;
    private ShiftRepository $repository;
    private TransactionManager $transaction;

    /**
     * Constructor.
     *
     * @param \UseCase\Shift\LookupShiftUseCase $lookupShiftUseCase
     * @param \UseCase\Staff\LookupStaffUseCase $lookupStaffUseCase
     * @param \UseCase\User\LookupUserUseCase $lookupUserUseCase
     * @param \Domain\Shift\ShiftRepository $repository
     * @param \Domain\TransactionManagerFactory $transactionManagerFactory
     * @param EventDispatcher $dispatcher
     */
    public function __construct(
        EventDispatcher $dispatcher,
        LookupShiftUseCase $lookupShiftUseCase,
        LookupStaffUseCase $lookupStaffUseCase,
        LookupUserUseCase $lookupUserUseCase,
        ShiftRepository $repository,
        TransactionManagerFactory $transactionManagerFactory
    ) {
        $this->dispatcher = $dispatcher;
        $this->lookupShiftUseCase = $lookupShiftUseCase;
        $this->lookupStaffUseCase = $lookupStaffUseCase;
        $this->lookupUserUseCase = $lookupUserUseCase;
        $this->repository = $repository;
        $this->transaction = $transactionManagerFactory->factory($repository);
    }

    /** {@inheritdoc} */
    public function handle(Context $context, string $reason, int ...$ids): void
    {
        $this->transaction->run(function () use ($context, $reason, $ids) {
            $shifts = $this->lookupShiftUseCase
                ->handle($context, Permission::updateShifts(), ...$ids)
                ->filter(fn (Shift $x): bool => $x->isCanceled === false);
            if (count($shifts) !== count($ids)) {
                $idList = implode(',', $ids);
                throw new NotFoundException("Shifts({$idList}) not found");
            }
            $shifts->each(function (Shift $shift) use ($context, $reason): void {
                $staffs = $this->lookupStaffUseCase
                    ->handle(
                        $context,
                        Permission::updateShifts(),
                        ...Seq::fromArray($shift->assignees)
                            ->map(fn (Assignee $assignee) => $assignee->staffId)
                            ->toArray()
                    );
                if ($staffs->isEmpty()) {
                    throw new NotFoundException('Staff not found');
                }

                $user = $this->lookupUserUseCase
                    ->handle($context, Permission::updateShifts(), $shift->userId)
                    ->headOption()
                    ->orNull();

                $this->repository->store($shift->copy([
                    'isCanceled' => true,
                    'reason' => $reason,
                    'updatedAt' => Carbon::now(),
                ]));

                $staffs->each(function (Staff $staff) use ($context, $shift, $user): void {
                    $this->dispatcher->dispatch(new CancelShiftEvent($context, $shift, $staff, $user));
                });
            });
        });
        $this->logger()->info(
            '勤務シフトがキャンセルされました',
            // TODO DEV-1577 IDの複数出力方法は検討中なので暫定的に空文字
            ['id' => ''] + $context->logContext()
        );
    }
}

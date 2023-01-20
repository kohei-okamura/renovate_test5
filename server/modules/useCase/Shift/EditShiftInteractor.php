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
use Domain\Shift\Shift;
use Domain\Shift\ShiftRepository;
use Domain\Shift\UpdateShiftEvent;
use Domain\Staff\Staff;
use Domain\TransactionManager;
use Domain\TransactionManagerFactory;
use Domain\User\User;
use Lib\Exceptions\NotFoundException;
use Lib\Logging;
use ScalikePHP\Seq;
use UseCase\Contract\IdentifyContractUseCase;
use UseCase\Staff\LookupStaffUseCase;
use UseCase\User\LookupUserUseCase;

/**
 * 勤務シフト編集実装.
 */
final class EditShiftInteractor implements EditShiftUseCase
{
    use Logging;

    private IdentifyContractUseCase $identifyContractUseCase;
    private LookupShiftUseCase $lookupUseCase;
    private LookupStaffUseCase $lookupStaffUseCase;
    private LookupUserUseCase $lookupUserUseCase;
    private ShiftRepository $repository;
    private TransactionManager $transaction;
    private EventDispatcher $eventDispatcher;

    /**
     * Constructor.
     *
     * @param \UseCase\Contract\IdentifyContractUseCase $identifyContractUseCase
     * @param \UseCase\Staff\LookupStaffUseCase $lookupStaffUseCase
     * @param \UseCase\User\LookupUserUseCase $lookupUserUseCase
     * @param \UseCase\Shift\LookupShiftUseCase $lookupUseCase
     * @param \Domain\Shift\ShiftRepository $repository
     * @param \Domain\TransactionManagerFactory $transactionManagerFactory
     * @param \Domain\Event\EventDispatcher $eventDispatcher
     */
    public function __construct(
        IdentifyContractUseCase $identifyContractUseCase,
        LookupStaffUseCase $lookupStaffUseCase,
        LookupUserUseCase $lookupUserUseCase,
        LookupShiftUseCase $lookupUseCase,
        ShiftRepository $repository,
        TransactionManagerFactory $transactionManagerFactory,
        EventDispatcher $eventDispatcher
    ) {
        $this->identifyContractUseCase = $identifyContractUseCase;
        $this->lookupStaffUseCase = $lookupStaffUseCase;
        $this->lookupUserUseCase = $lookupUserUseCase;
        $this->lookupUseCase = $lookupUseCase;
        $this->repository = $repository;
        $this->transaction = $transactionManagerFactory->factory($repository);
        $this->eventDispatcher = $eventDispatcher;
    }

    /** {@inheritdoc} */
    public function handle(Context $context, int $id, array $values): Shift
    {
        $originalShift = $this->lookupShift($context, $id);
        /** @var \Domain\Shift\Shift $updatedShift */
        $updatedShift = $originalShift->copy($values);
        if ($updatedShift->userId !== null) {
            $serviceSegment = $updatedShift->task->toServiceSegment()->headOption()->getOrElse(function (): void {
                throw new NotFoundException('ServiceSegment not found');
            });
            /** @var \Domain\Contract\Contract $contract */
            $contract = $this->identifyContractUseCase
                ->handle(
                    $context,
                    Permission::updateShifts(),
                    $updatedShift->officeId,
                    $updatedShift->userId,
                    $serviceSegment,
                    Carbon::now()
                )
                ->getOrElse(function (): void {
                    throw new NotFoundException('Contract not found');
                });
        }
        if ($originalShift->isConfirmed) {
            $staffs = $this->lookupStaffUseCase
                ->handle(
                    $context,
                    Permission::updateShifts(),
                    ...Seq::fromArray($originalShift->assignees)
                        ->map(fn (Assignee $assignee): int => $assignee->staffId)
                        ->toArray()
                );
            if ($staffs->isEmpty()) {
                throw new NotFoundException('Staff not found');
            }

            $originalUser = $this->lookupUser($context, $originalShift->userId);
            $updatedUser = $this->lookupUser($context, $updatedShift->userId);

            $staffs->each(function (Staff $staff) use ($context, $originalShift, $updatedShift, $originalUser, $updatedUser): void {
                $this->eventDispatcher->dispatch(new UpdateShiftEvent($context, $originalShift, $updatedShift, $originalUser, $updatedUser, $staff));
            });
        }
        $x = $this->transaction->run(fn (): Shift => $this->repository->store(
            $updatedShift->copy([
                'contractId' => $updatedShift->userId !== null ? $contract->id : null,
                'updatedAt' => Carbon::now(),
            ])
        ));
        $this->logger()->info(
            '勤務シフトが更新されました',
            ['id' => $x->id] + $context->logContext()
        );
        return $x;
    }

    /**
     * 勤務シフトを取得する.
     *
     * @param \Domain\Context\Context $context
     * @param int $id
     * @return \Domain\Shift\Shift
     */
    private function lookupShift(Context $context, int $id): Shift
    {
        return $this->lookupUseCase
            ->handle($context, Permission::updateShifts(), $id)
            ->headOption()
            ->getOrElse(function () use ($id): void {
                throw new NotFoundException("Shift({$id}) not found");
            });
    }

    /**
     * 利用者を取得する.
     *
     * @param \Domain\Context\Context $context
     * @param int $id
     * @return \Domain\User\User
     */
    private function lookupUser(Context $context, int $id): User
    {
        return $this->lookupUserUseCase
            ->handle($context, Permission::updateShifts(), $id)
            ->headOption()
            ->getOrElse(function () use ($id): void {
                throw new NotFoundException("User({$id}) not found");
            });
    }
}

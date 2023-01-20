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
use Domain\Shift\Shift;
use Domain\Shift\ShiftRepository;
use Domain\TransactionManager;
use Domain\TransactionManagerFactory;
use Lib\Exceptions\NotFoundException;
use Lib\Logging;
use UseCase\Contract\IdentifyContractUseCase;

/**
 * 勤務シフト登録実装.
 */
final class CreateShiftInteractor implements CreateShiftUseCase
{
    use Logging;

    private IdentifyContractUseCase $identifyContractUseCase;
    private ShiftRepository $repository;
    private TransactionManager $transaction;

    /**
     * Constructor.
     *
     * @param \UseCase\Contract\IdentifyContractUseCase $identifyContractUseCase
     * @param \Domain\Shift\ShiftRepository $repository
     * @param \Domain\TransactionManagerFactory $transaction
     */
    public function __construct(
        IdentifyContractUseCase $identifyContractUseCase,
        ShiftRepository $repository,
        TransactionManagerFactory $transaction
    ) {
        $this->identifyContractUseCase = $identifyContractUseCase;
        $this->repository = $repository;
        $this->transaction = $transaction->factory($repository);
    }

    /** {@inheritdoc} */
    public function handle(Context $context, Shift $shift): Shift
    {
        if ($shift->userId !== null) {
            $serviceSegment = $shift->task->toServiceSegment()->headOption()->getOrElse(function (): void {
                throw new NotFoundException('ServiceSegment not found');
            });
            /** @var \Domain\Contract\Contract $contract */
            $contract = $this->identifyContractUseCase
                ->handle(
                    $context,
                    Permission::createShifts(),
                    $shift->officeId,
                    $shift->userId,
                    $serviceSegment,
                    Carbon::now()
                )
                ->getOrElse(function (): void {
                    throw new NotFoundException('Contract not found');
                });
        }
        $x = $this->transaction->run(fn (): Shift => $this->repository->store(
            $shift->copy([
                'organizationId' => $context->organization->id,
                'contractId' => $shift->userId !== null ? $contract->id : null,
            ])
        ));
        $this->logger()->info(
            '勤務シフトが登録されました',
            ['id' => $x->id] + $context->logContext()
        );
        return $x;
    }
}

<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Shift;

use Domain\Context\Context;
use Domain\Permission\Permission;
use Domain\Shift\ShiftRepository;
use Domain\TransactionManager;
use Domain\TransactionManagerFactory;
use Domain\Validator\ConfirmShiftAsyncValidator;
use Lib\Exceptions\NotFoundException;
use Lib\Logging;

/**
 * 勤務シフト確定実装.
 */
final class ConfirmShiftInteractor implements ConfirmShiftUseCase
{
    use Logging;

    private LookupShiftUseCase $lookupUseCase;
    private ShiftRepository $repository;
    private TransactionManager $transaction;
    private ConfirmShiftAsyncValidator $validator;

    /**
     * Constructor.
     *
     * @param \UseCase\Shift\LookupShiftUseCase $lookupUseCase
     * @param \Domain\Shift\ShiftRepository $repository
     * @param \Domain\TransactionManagerFactory $transactionManagerFactory
     * @param \Domain\Validator\ConfirmShiftAsyncValidator $validator
     */
    public function __construct(
        LookupShiftUseCase $lookupUseCase,
        ShiftRepository $repository,
        TransactionManagerFactory $transactionManagerFactory,
        ConfirmShiftAsyncValidator $validator
    ) {
        $this->lookupUseCase = $lookupUseCase;
        $this->repository = $repository;
        $this->transaction = $transactionManagerFactory->factory($repository);
        $this->validator = $validator;
    }

    /** {@inheritdoc} */
    public function handle(Context $context, int ...$ids): void
    {
        $this->validator->validate($context, compact('ids'));
        $this->transaction->run(function () use ($context, $ids) {
            $entities = $this->lookupUseCase->handle($context, Permission::updateShifts(), ...$ids);
            if (count($entities) !== count($ids)) {
                $id = implode(',', $ids);
                throw new NotFoundException("Entity({$id}) not found");
            }
            foreach ($entities as $entity) {
                $this->repository->store($entity->copy(['isConfirmed' => true]));
            }
        });
        $this->logger()->info(
            '勤務シフトが確定されました',
            // TODO DEV-1577 IDの複数出力方法は検討中なので暫定的に空文字
            ['id' => ''] + $context->logContext()
        );
    }
}

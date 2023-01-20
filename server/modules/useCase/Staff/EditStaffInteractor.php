<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Staff;

use Domain\Common\Carbon;
use Domain\Context\Context;
use Domain\Permission\Permission;
use Domain\Staff\Staff;
use Domain\Staff\StaffRepository;
use Domain\TransactionManager;
use Domain\TransactionManagerFactory;
use Lib\Exceptions\NotFoundException;
use Lib\Logging;

/**
 * スタッフ編集実装.
 */
final class EditStaffInteractor implements EditStaffUseCase
{
    use Logging;

    private GetStaffInfoUseCase $getStaffInfoUseCase;
    private LookupStaffUseCase $lookupUseCase;
    private StaffRepository $staffRepository;
    private TransactionManager $transaction;

    /**
     * Constructor.
     *
     * @param \UseCase\Staff\GetStaffInfoUseCase $getStaffInfoUseCase
     * @param \UseCase\Staff\LookupStaffUseCase $lookupUseCase
     * @param \Domain\Staff\StaffRepository $staffRepository
     * @param \Domain\TransactionManagerFactory $transactionManagerFactory
     */
    public function __construct(
        GetStaffInfoUseCase $getStaffInfoUseCase,
        LookupStaffUseCase $lookupUseCase,
        StaffRepository $staffRepository,
        TransactionManagerFactory $transactionManagerFactory
    ) {
        $this->getStaffInfoUseCase = $getStaffInfoUseCase;
        $this->lookupUseCase = $lookupUseCase;
        $this->staffRepository = $staffRepository;
        $this->transaction = $transactionManagerFactory->factory($staffRepository);
    }

    /** {@inheritdoc} */
    public function handle(Context $context, int $id, array $values): array
    {
        $entity = $this->lookupUseCase->handle($context, Permission::updateStaffs(), $id)->headOption()->getOrElse(function () use ($id): void {
            throw new NotFoundException("Staff({$id}) not found");
        });

        $x = $this->transaction->run(fn (): Staff => $this->staffRepository->store(
            $entity->copy($values + ['version' => $entity->version + 1, 'updatedAt' => Carbon::now()])
        ));
        $this->logger()->info(
            'スタッフ情報が更新されました',
            ['id' => $x->id] + $context->logContext()
        );

        return $this->getStaffInfoUseCase->handle($context, $id);
    }
}

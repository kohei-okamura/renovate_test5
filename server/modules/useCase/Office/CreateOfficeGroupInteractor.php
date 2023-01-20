<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Office;

use Domain\Common\Carbon;
use Domain\Context\Context;
use Domain\Office\OfficeGroup;
use Domain\Office\OfficeGroupRepository;
use Domain\TransactionManager;
use Domain\TransactionManagerFactory;
use Lib\Logging;

/**
 * 事業所グループ登録実装.
 */
final class CreateOfficeGroupInteractor implements CreateOfficeGroupUseCase
{
    use Logging;

    private OfficeGroupRepository $officeGroupRepository;
    private TransactionManager $transaction;

    /**
     * Constructor.
     *
     * @param \Domain\Office\OfficeGroupRepository $officeGroupRepository
     * @param \Domain\TransactionManagerFactory $transactionManagerFactory
     */
    public function __construct(
        OfficeGroupRepository $officeGroupRepository,
        TransactionManagerFactory $transactionManagerFactory
    ) {
        $this->officeGroupRepository = $officeGroupRepository;
        $this->transaction = $transactionManagerFactory->factory($officeGroupRepository);
    }

    /** {@inheritdoc} */
    public function handle(Context $context, OfficeGroup $officeGroup): OfficeGroup
    {
        $x = $this->transaction->run(fn (): OfficeGroup => $this->officeGroupRepository->store($officeGroup->copy([
            'organizationId' => $context->organization->id,
            'sortOrder' => Carbon::now()->unix(),
        ])));
        $this->logger()->info(
            '事業所グループが登録されました',
            ['id' => $x->id] + $context->logContext()
        );
        return $x;
    }
}

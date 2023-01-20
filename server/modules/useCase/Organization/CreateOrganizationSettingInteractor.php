<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Organization;

use Domain\Common\Carbon;
use Domain\Context\Context;
use Domain\Organization\OrganizationSetting;
use Domain\Organization\OrganizationSettingRepository;
use Domain\TransactionManager;
use Domain\TransactionManagerFactory;
use Lib\Logging;

/**
 * 事業者別設定登録ユースケース.
 */
class CreateOrganizationSettingInteractor implements CreateOrganizationSettingUseCase
{
    use Logging;

    private OrganizationSettingRepository $repository;
    private TransactionManager $transaction;

    /**
     * {@link \UseCase\Organization\CreateOrganizationSettingInteractor} Constructor.
     *
     * @param \Domain\Organization\OrganizationSettingRepository $repository
     * @param \Domain\TransactionManagerFactory $transactionManagerFactory
     */
    public function __construct(
        OrganizationSettingRepository $repository,
        TransactionManagerFactory $transactionManagerFactory
    ) {
        $this->repository = $repository;
        $this->transaction = $transactionManagerFactory->factory($repository);
    }

    /** {@inheritdoc} */
    public function handle(Context $context, OrganizationSetting $organizationSetting): OrganizationSetting
    {
        $entity = $this->transaction->run(function () use ($context, $organizationSetting) {
            return $this->repository->store($organizationSetting->copy([
                'organizationId' => $context->organization->id,
                'createdAt' => Carbon::now(),
                'updatedAt' => Carbon::now(),
            ]));
        });

        $this->logger()->info(
            '事業者別設定が登録されました',
            ['id' => $entity->id] + $context->logContext()
        );

        return $entity;
    }
}

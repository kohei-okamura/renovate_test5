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
use Domain\Permission\Permission;
use Domain\TransactionManager;
use Domain\TransactionManagerFactory;
use Lib\Exceptions\NotFoundException;
use Lib\Logging;

/**
 * 事業者別設定更新ユースケース実装.
 */
class EditOrganizationSettingInteractor implements EditOrganizationSettingUseCase
{
    use Logging;

    private OrganizationSettingRepository $repository;
    private LookupOrganizationSettingUseCase $lookupUseCase;
    private TransactionManager $transaction;

    /**
     * {@link \UseCase\Organization\CreateOrganizationSettingInteractor} Constructor.
     *
     * @param \Domain\Organization\OrganizationSettingRepository $repository
     * @param \Domain\TransactionManagerFactory $transactionManagerFactory
     * @param \UseCase\Organization\LookupOrganizationSettingUseCase $lookupUseCase
     */
    public function __construct(
        OrganizationSettingRepository $repository,
        TransactionManagerFactory $transactionManagerFactory,
        LookupOrganizationSettingUseCase $lookupUseCase
    ) {
        $this->repository = $repository;
        $this->transaction = $transactionManagerFactory->factory($repository);
        $this->lookupUseCase = $lookupUseCase;
    }

    /** {@inheritdoc} */
    public function handle(Context $context, array $values): OrganizationSetting
    {
        $x = $this->transaction->run(function () use ($context, $values) {
            $entity = $this->lookupUseCase->handle($context, Permission::updateOrganizationSettings())->getOrElse(function (): void {
                throw new NotFoundException('OrganizationSetting not found');
            });
            return $this->repository->store($entity->copy($values + [
                'updatedAt' => Carbon::now(),
            ]));
        });

        $this->logger()->info(
            '事業者別設定が更新されました',
            ['id' => $x->id] + $context->logContext()
        );

        return $x;
    }
}

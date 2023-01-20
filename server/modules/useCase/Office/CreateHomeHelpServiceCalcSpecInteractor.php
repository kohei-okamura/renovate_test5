<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Office;

use Domain\Context\Context;
use Domain\Office\HomeHelpServiceCalcSpec;
use Domain\Office\HomeHelpServiceCalcSpecRepository;
use Domain\Permission\Permission;
use Domain\TransactionManager;
use Domain\TransactionManagerFactory;
use Lib\Logging;

/**
 * 事業所算定情報（障害・居宅介護）登録実装.
 */
final class CreateHomeHelpServiceCalcSpecInteractor implements CreateHomeHelpServiceCalcSpecUseCase
{
    use Logging;

    private EnsureOfficeUseCase $ensureOfficeUseCase;
    private HomeHelpServiceCalcSpecRepository $repository;
    private TransactionManager $transaction;

    /**
     * Constructor.
     *
     * @param \UseCase\Office\EnsureOfficeUseCase $ensureOfficeUseCase
     * @param \Domain\Office\HomeHelpServiceCalcSpecRepository $repository
     * @param \Domain\TransactionManagerFactory $transactionManagerFactory
     */
    public function __construct(
        EnsureOfficeUseCase $ensureOfficeUseCase,
        HomeHelpServiceCalcSpecRepository $repository,
        TransactionManagerFactory $transactionManagerFactory
    ) {
        $this->ensureOfficeUseCase = $ensureOfficeUseCase;
        $this->repository = $repository;
        $this->transaction = $transactionManagerFactory->factory($repository);
    }

    /** {@inheritdoc} */
    public function handle(Context $context, int $officeId, HomeHelpServiceCalcSpec $homeHelpServiceCalcSpec): HomeHelpServiceCalcSpec
    {
        $x = $this->transaction->run(function () use ($context, $officeId, $homeHelpServiceCalcSpec): HomeHelpServiceCalcSpec {
            $this->ensureOfficeUseCase->handle($context, [Permission::createInternalOffices()], $officeId);
            return $this->repository->store($homeHelpServiceCalcSpec);
        });
        $this->logger()->info(
            '事業所算定情報（障害・居宅介護）が登録されました',
            ['id' => $x->id] + $context->logContext()
        );
        return $x;
    }
}

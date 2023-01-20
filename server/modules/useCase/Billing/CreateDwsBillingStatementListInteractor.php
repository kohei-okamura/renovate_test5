<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Billing;

use Domain\Billing\DwsBillingBundle;
use Domain\Billing\DwsBillingStatementRepository;
use Domain\Context\Context;
use Domain\Office\HomeHelpServiceCalcSpec;
use Domain\Office\Office;
use Domain\Office\VisitingCareForPwsdCalcSpec;
use Domain\Permission\Permission;
use Domain\TransactionManager;
use Domain\TransactionManagerFactory;
use Domain\User\User;
use Lib\Exceptions\NotFoundException;
use ScalikePHP\Option;
use ScalikePHP\Seq;
use UseCase\Office\IdentifyHomeHelpServiceCalcSpecUseCase;
use UseCase\Office\IdentifyVisitingCareForPwsdCalcSpecUseCase;
use UseCase\User\LookupUserUseCase;

/**
 * 障害福祉サービス：明細書一覧生成ユースケース実装.
 */
final class CreateDwsBillingStatementListInteractor implements CreateDwsBillingStatementListUseCase
{
    private TransactionManager $transaction;

    /**
     * {@link \UseCase\Billing\CreateDwsBillingStatementListInteractor} constructor.
     *
     * @param \UseCase\Billing\BuildDwsBillingStatementUseCase $buildStatementUseCase
     * @param \UseCase\Office\IdentifyHomeHelpServiceCalcSpecUseCase $identifyHomeHelpServiceCalcSpecUseCase
     * @param \UseCase\Office\IdentifyVisitingCareForPwsdCalcSpecUseCase $identifyVisitingCareForPwsdCalcSpecUseCase
     * @param \UseCase\User\LookupUserUseCase $lookupUserUseCase
     * @param \Domain\Billing\DwsBillingStatementRepository $repository
     * @param \Domain\TransactionManagerFactory $factory
     */
    public function __construct(
        private BuildDwsBillingStatementUseCase $buildStatementUseCase,
        private IdentifyHomeHelpServiceCalcSpecUseCase $identifyHomeHelpServiceCalcSpecUseCase,
        private IdentifyVisitingCareForPwsdCalcSpecUseCase $identifyVisitingCareForPwsdCalcSpecUseCase,
        private LookupUserUseCase $lookupUserUseCase,
        private DwsBillingStatementRepository $repository,
        TransactionManagerFactory $factory
    ) {
        $this->transaction = $factory->factory($repository);
    }

    /** {@inheritdoc} */
    public function handle(Context $context, Office $office, DwsBillingBundle $bundle): Seq
    {
        return $this->transaction->run(function () use ($context, $office, $bundle): Seq {
            $homeHelpServiceCalcSpec = $this->identifyHomeHelpServiceCalcSpec($context, $office, $bundle);
            $visitingCareForPwsdCalcSpec = $this->identifyVisitingCareForPwsdCalcSpec($context, $office, $bundle);
            $statements = $this->generate(
                $context,
                $office,
                $bundle,
                $homeHelpServiceCalcSpec,
                $visitingCareForPwsdCalcSpec
            );
            return Seq::from(...$statements);
        });
    }

    /**
     * 障害福祉サービス：明細書の一覧を生成する.
     *
     * @param \Domain\Context\Context $context
     * @param \Domain\Office\Office $office
     * @param \Domain\Billing\DwsBillingBundle $bundle
     * @param null|\Domain\Office\HomeHelpServiceCalcSpec $homeHelpServiceCalcSpec
     * @param null|\Domain\Office\VisitingCareForPwsdCalcSpec $visitingCareForPwsdCalcSpec
     * @return iterable
     */
    private function generate(
        Context $context,
        Office $office,
        DwsBillingBundle $bundle,
        ?HomeHelpServiceCalcSpec $homeHelpServiceCalcSpec,
        ?VisitingCareForPwsdCalcSpec $visitingCareForPwsdCalcSpec
    ): iterable {
        foreach (Seq::from(...$bundle->details)->groupBy('userId') as $userId => $details) {
            $statement = $this->buildStatementUseCase->handle(
                $context,
                $office,
                $bundle,
                $homeHelpServiceCalcSpec,
                $visitingCareForPwsdCalcSpec,
                $this->lookupUser($context, $userId),
                $details,
                Option::none(),
                Option::none()
            );
            yield $this->repository->store($statement);
        }
    }

    /**
     * 障害福祉サービス：居宅介護：算定情報を取得（特定）する.
     *
     * @param \Domain\Context\Context $context
     * @param \Domain\Office\Office $office
     * @param \Domain\Billing\DwsBillingBundle $bundle
     * @return \Domain\Office\HomeHelpServiceCalcSpec
     */
    private function identifyHomeHelpServiceCalcSpec(
        Context $context,
        Office $office,
        DwsBillingBundle $bundle
    ): ?HomeHelpServiceCalcSpec {
        return $this->identifyHomeHelpServiceCalcSpecUseCase
            ->handle($context, $office, $bundle->providedIn)
            ->orNull();
    }

    /**
     * 障害福祉サービス：重度訪問介護：算定情報を取得（特定）する.
     *
     * @param \Domain\Context\Context $context
     * @param \Domain\Office\Office $office
     * @param \Domain\Billing\DwsBillingBundle $bundle
     * @return \Domain\Office\VisitingCareForPwsdCalcSpec
     */
    private function identifyVisitingCareForPwsdCalcSpec(
        Context $context,
        Office $office,
        DwsBillingBundle $bundle
    ): ?VisitingCareForPwsdCalcSpec {
        return $this->identifyVisitingCareForPwsdCalcSpecUseCase
            ->handle($context, $office, $bundle->providedIn)
            ->orNull();
    }

    /**
     * 利用者を取得する.
     *
     * @param \Domain\Context\Context $context
     * @param int $userId
     * @throws \Lib\Exceptions\NotFoundException
     * @return \Domain\User\User
     */
    private function lookupUser(Context $context, int $userId): User
    {
        return $this->lookupUserUseCase
            ->handle($context, Permission::createBillings(), $userId)
            ->headOption()
            ->getOrElse(function () use ($userId): void {
                throw new NotFoundException("User({$userId}) not found");
            });
    }
}

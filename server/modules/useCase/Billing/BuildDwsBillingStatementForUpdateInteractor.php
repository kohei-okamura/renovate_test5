<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Billing;

use Domain\Billing\DwsBilling;
use Domain\Billing\DwsBillingBundle;
use Domain\Billing\DwsBillingBundleRepository;
use Domain\Billing\DwsBillingCopayCoordinationFinder;
use Domain\Billing\DwsBillingRepository;
use Domain\Billing\DwsBillingServiceDetail;
use Domain\Billing\DwsBillingStatement;
use Domain\Context\Context;
use Domain\Office\HomeHelpServiceCalcSpec;
use Domain\Office\Office;
use Domain\Office\OfficeRepository;
use Domain\Office\VisitingCareForPwsdCalcSpec;
use Domain\User\User;
use Domain\User\UserRepository;
use Lib\Exceptions\NotFoundException;
use Lib\Exceptions\RuntimeException;
use ScalikePHP\Option;
use ScalikePHP\Seq;
use UseCase\Office\IdentifyHomeHelpServiceCalcSpecUseCase;
use UseCase\Office\IdentifyVisitingCareForPwsdCalcSpecUseCase;

/**
 * 介護保険サービス：請求：明細書 更新用生成ユースケース実装.
 */
class BuildDwsBillingStatementForUpdateInteractor implements BuildDwsBillingStatementForUpdateUseCase
{
    /**
     * constructor.
     *
     * @param \UseCase\Billing\BuildDwsBillingStatementUseCase $buildStatementUseCase
     * @param \Domain\Billing\DwsBillingBundleRepository $bundleRepository
     * @param \Domain\Billing\DwsBillingCopayCoordinationFinder $billingCopayCoordinationFinder
     * @param \Domain\Billing\DwsBillingRepository $billingRepository
     * @param \UseCase\Office\IdentifyHomeHelpServiceCalcSpecUseCase $identifyHomeHelpServiceCalcSpecUseCase
     * @param \UseCase\Office\IdentifyVisitingCareForPwsdCalcSpecUseCase $identifyVisitingCareForPwsdCalcSpecUseCase
     * @param \Domain\Office\OfficeRepository $officeRepository
     * @param \Domain\User\UserRepository $userRepository
     */
    public function __construct(
        private BuildDwsBillingStatementUseCase $buildStatementUseCase,
        private DwsBillingBundleRepository $bundleRepository,
        private DwsBillingCopayCoordinationFinder $billingCopayCoordinationFinder,
        private DwsBillingRepository $billingRepository,
        private IdentifyHomeHelpServiceCalcSpecUseCase $identifyHomeHelpServiceCalcSpecUseCase,
        private IdentifyVisitingCareForPwsdCalcSpecUseCase $identifyVisitingCareForPwsdCalcSpecUseCase,
        private OfficeRepository $officeRepository,
        private UserRepository $userRepository
    ) {
    }

    /** {@inheritdoc} */
    public function handle(Context $context, DwsBillingStatement $entityForUpdate): DwsBillingStatement
    {
        $bundle = $this->getBundle($entityForUpdate->dwsBillingBundleId);
        $office = $this->getOffice($entityForUpdate);
        $details = Seq::fromArray($bundle->details)
            ->filter(fn (DwsBillingServiceDetail $x) => $x->userId === $entityForUpdate->user->userId);
        return $this->buildStatementUseCase->handle(
            $context,
            $office,
            $bundle,
            $this->identifyHomeHelpServiceCalcSpec($context, $office, $bundle),
            $this->identifyVisitingCareForPwsdCalcSpec($context, $office, $bundle),
            $this->getUser($entityForUpdate->user->userId),
            $details,
            $this->getDwsBillingCopayCoordination($bundle, $entityForUpdate->user->userId),
            Option::from($entityForUpdate)
        );
    }

    /**
     * 請求 を取得する.
     *
     * @param int $billingId
     * @return \Domain\Billing\DwsBilling
     */
    private function getBilling(int $billingId): DwsBilling
    {
        return $this->billingRepository
            ->lookup($billingId)
            ->headOption()
            ->getOrElse(function () use ($billingId): void {
                throw new NotFoundException("DwsBilling({$billingId}) not found.");
            });
    }

    /**
     * 請求単位 を取得する.
     *
     * @param int $bundleId
     * @return \Domain\Billing\DwsBillingBundle
     */
    private function getBundle(int $bundleId): DwsBillingBundle
    {
        return $this->bundleRepository
            ->lookup($bundleId)
            ->headOption()
            ->getOrElse(function () use ($bundleId): void {
                throw new NotFoundException("DwsBillingBundle({$bundleId}) not found");
            });
    }

    /**
     * 事業所 を取得する.
     *
     * @param \Domain\Billing\DwsBillingStatement $statement
     * @return \Domain\Office\Office
     */
    private function getOffice(DwsBillingStatement $statement): Office
    {
        $billing = $this->getBilling($statement->dwsBillingId);

        $officeId = $billing->office->officeId;
        return $this->officeRepository
            ->lookup($officeId)
            ->headOption()
            ->getOrElse(function () use ($officeId): void {
                throw new RuntimeException("Office({$officeId}) not found.");
            });
    }

    /**
     * 利用者を取得する.
     *
     * @param int $userId
     * @return \Domain\User\User
     */
    private function getUser(int $userId): User
    {
        return $this->userRepository
            ->lookup($userId)
            ->headOption()
            ->getOrElse(function () use ($userId): void {
                throw new RuntimeException("User({$userId}) not found.");
            });
    }

    /**
     * 上限額管理結果票を取得する.
     *
     * @param \Domain\Billing\DwsBillingBundle $bundle
     * @param int $userId
     * @return \Domain\Billing\DwsBillingCopayCoordination[]&\ScalikePHP\Option
     */
    private function getDwsBillingCopayCoordination(DwsBillingBundle $bundle, int $userId): Option
    {
        $filterParams = [
            'dwsBillingId' => $bundle->dwsBillingId,
            'dwsBillingBundleId' => $bundle->id,
            'userIds' => [$userId],
        ];

        return $this->billingCopayCoordinationFinder
            ->find($filterParams, ['all' => true, 'sortBy' => 'id'])
            ->list
            ->headOption();
    }

    /**
     * 障害福祉サービス：居宅介護：算定情報を取得（特定）する.
     *
     * @param \Domain\Context\Context $context
     * @param \Domain\Office\Office $office
     * @param \Domain\Billing\DwsBillingBundle $bundle
     * @return null|\Domain\Office\HomeHelpServiceCalcSpec
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
     * @return null|\Domain\Office\VisitingCareForPwsdCalcSpec
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
}

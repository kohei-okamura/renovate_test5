<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Billing;

use Domain\Billing\LtcsBillingStatus;
use Domain\Context\Context;
use Domain\Job\Job as DomainJob;
use Domain\Permission\Permission;
use Lib\Exceptions\LogicException;
use UseCase\Job\RunJobUseCase;

/**
 * 介護保険サービス：明細書状態一括更新ジョブ実行ユースケース実装.
 */
final class RunBulkUpdateLtcsBillingStatementStatusJobInteractor implements RunBulkUpdateLtcsBillingStatementStatusJobUseCase
{
    private RunJobUseCase $runJobUseCase;
    private BulkUpdateLtcsBillingStatementStatusUseCase $useCase;
    private ConfirmLtcsBillingStatementStatusUseCase $confirmStatementStatusUseCase;
    private LookupLtcsBillingUseCase $lookupLtcsBillingUseCase;

    /**
     * Constructor.
     *
     * @param \UseCase\Job\RunJobUseCase $runJobUseCase
     * @param \UseCase\Billing\BulkUpdateLtcsBillingStatementStatusUseCase $useCase
     * @param ConfirmLtcsBillingStatementStatusUseCase $confirmStatementStatusUseCase
     * @param LookupLtcsBillingUseCase $lookupLtcsBillingUseCase
     */
    public function __construct(
        RunJobUseCase $runJobUseCase,
        BulkUpdateLtcsBillingStatementStatusUseCase $useCase,
        ConfirmLtcsBillingStatementStatusUseCase $confirmStatementStatusUseCase,
        LookupLtcsBillingUseCase $lookupLtcsBillingUseCase
    ) {
        $this->runJobUseCase = $runJobUseCase;
        $this->useCase = $useCase;
        $this->confirmStatementStatusUseCase = $confirmStatementStatusUseCase;
        $this->lookupLtcsBillingUseCase = $lookupLtcsBillingUseCase;
    }

    /** {@inheritdoc} */
    public function handle(
        Context $context,
        DomainJob $domainJob,
        int $billingId,
        int $bundleId,
        array $ids,
        LtcsBillingStatus $status
    ): void {
        $this->runJobUseCase->handle(
            $context,
            $domainJob,
            function () use ($context, $bundleId, $billingId, $ids, $status): void {
                $this->useCase->handle($context, $billingId, $bundleId, $ids, $status);
            }
        );
        $billing = $this->lookupLtcsBillingUseCase
            ->handle($context, Permission::updateBillings(), $billingId)
            ->headOption()
            ->getOrElse(function (): void {
                throw new LogicException('LtcsBillings cannot be empty');
            });
        $this->confirmStatementStatusUseCase->handle($context, $billing);
    }
}

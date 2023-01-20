<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Billing;

use Domain\Billing\DwsBillingStatus;
use Domain\Context\Context;
use Domain\Job\Job as DomainJob;
use Domain\Permission\Permission;
use Lib\Exceptions\LogicException;
use UseCase\Job\RunJobUseCase;

/**
 * 障害福祉サービス：サービス提供実績記録票状態一括更新ジョブ実行ユースケース実装.
 */
final class RunBulkUpdateDwsBillingServiceReportStatusJobInteractor implements RunBulkUpdateDwsBillingServiceReportStatusJobUseCase
{
    private RunJobUseCase $runJobUseCase;
    private BulkUpdateDwsBillingServiceReportStatusUseCase $useCase;
    private ConfirmDwsBillingStatusUseCase $confirmBillingStatusUseCase;
    private LookupDwsBillingUseCase $lookupDwsBillingUseCase;

    /**
     * Constructor.
     *
     * @param \UseCase\Job\RunJobUseCase $runJobUseCase
     * @param \UseCase\Billing\BulkUpdateDwsBillingServiceReportStatusUseCase $useCase
     * @param \UseCase\Billing\ConfirmDwsBillingStatusUseCase $confirmBillingStatusUseCase
     * @param LookupDwsBillingUseCase $lookupDwsBillingUseCase
     */
    public function __construct(
        RunJobUseCase $runJobUseCase,
        BulkUpdateDwsBillingServiceReportStatusUseCase $useCase,
        ConfirmDwsBillingStatusUseCase $confirmBillingStatusUseCase,
        LookupDwsBillingUseCase $lookupDwsBillingUseCase
    ) {
        $this->runJobUseCase = $runJobUseCase;
        $this->useCase = $useCase;
        $this->confirmBillingStatusUseCase = $confirmBillingStatusUseCase;
        $this->lookupDwsBillingUseCase = $lookupDwsBillingUseCase;
    }

    /** {@inheritdoc} */
    public function handle(
        Context $context,
        DomainJob $domainJob,
        int $billingId,
        array $ids,
        DwsBillingStatus $status
    ): void {
        $this->runJobUseCase->handle(
            $context,
            $domainJob,
            function () use ($context, $billingId, $ids, $status): void {
                $this->useCase->handle($context, $billingId, $ids, $status);
                $billing = $this->lookupDwsBillingUseCase
                    ->handle($context, Permission::updateBillings(), $billingId)
                    ->headOption()
                    ->getOrElse(function (): void {
                        throw new LogicException('DwsBillings cannot be empty');
                    });
                $this->confirmBillingStatusUseCase->handle($context, $billing);
            }
        );
    }
}

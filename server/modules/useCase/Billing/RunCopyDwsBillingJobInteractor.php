<?php
/**
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Billing;

use Domain\Context\Context;
use Domain\Job\Job;
use UseCase\Job\RunJobUseCase;

/**
 * 障害福祉サービス：請求コピージョブ実行ユースケース実装.
 */
final class RunCopyDwsBillingJobInteractor implements RunCopyDwsBillingJobUseCase
{
    private CopyDwsBillingUseCase $copyUseCase;
    private RunJobUseCase $runJobUseCase;

    /**
     * constructor.
     *
     * @param \UseCase\Billing\CopyDwsBillingUseCase $copyUseCase
     * @param \UseCase\Job\RunJobUseCase $runJobUseCase
     */
    public function __construct(CopyDwsBillingUseCase $copyUseCase, RunJobUseCase $runJobUseCase)
    {
        $this->copyUseCase = $copyUseCase;
        $this->runJobUseCase = $runJobUseCase;
    }

    /** {@inheritdoc} */
    public function handle(Context $context, Job $domainJob, int $billingId): void
    {
        $this->runJobUseCase->handle(
            $context,
            $domainJob,
            function () use ($context, $billingId): array {
                $billing = $this->copyUseCase->handle($context, $billingId);
                return ['billing' => $billing->toAssoc()];
            }
        );
    }
}

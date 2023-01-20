<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Billing;

use Domain\Common\Carbon;
use Domain\Common\CarbonRange;
use Domain\Context\Context;
use Domain\Job\Job;
use UseCase\Job\RunJobUseCase;

/**
 * 介護保険サービス：請求生成ジョブ実行ユースケース実装.
 */
final class RunCreateLtcsBillingJobInteractor implements RunCreateLtcsBillingJobUseCase
{
    private CreateLtcsBillingUseCase $createUseCase;
    private RunJobUseCase $runJobUseCase;

    /**
     * constructor.
     *
     * @param \UseCase\Billing\CreateLtcsBillingUseCase $createUseCase
     * @param \UseCase\Job\RunJobUseCase $runJobUseCase
     */
    public function __construct(CreateLtcsBillingUseCase $createUseCase, RunJobUseCase $runJobUseCase)
    {
        $this->createUseCase = $createUseCase;
        $this->runJobUseCase = $runJobUseCase;
    }

    /** {@inheritdoc} */
    public function handle(
        Context $context,
        Job $domainJob,
        int $officeId,
        Carbon $transactedIn,
        CarbonRange $fixedAt
    ): void {
        $this->runJobUseCase->handle($context, $domainJob, function () use ($context, $officeId, $transactedIn, $fixedAt): array {
            $entity = $this->createUseCase->handle($context, $officeId, $transactedIn, $fixedAt);
            return ['id' => $entity->id];
        });
    }
}

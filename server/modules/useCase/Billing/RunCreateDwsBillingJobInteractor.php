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
 * 障害福祉サービス：請求生成ジョブ実行ユースケース実装.
 */
final class RunCreateDwsBillingJobInteractor implements RunCreateDwsBillingJobUseCase
{
    private CreateDwsBillingUseCase $createUseCase;
    private RunJobUseCase $runJobUseCase;

    /**
     * constructor.
     *
     * @param \UseCase\Billing\CreateDwsBillingUseCase $createUseCase
     * @param \UseCase\Job\RunJobUseCase $runJobUseCase
     */
    public function __construct(CreateDwsBillingUseCase $createUseCase, RunJobUseCase $runJobUseCase)
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
        $this->runJobUseCase->handle(
            $context,
            $domainJob,
            function () use ($context, $officeId, $transactedIn, $fixedAt): array {
                $entity = $this->createUseCase->handle($context, $officeId, $transactedIn, $fixedAt);
                return ['id' => $entity->id];
            }
        );
    }
}

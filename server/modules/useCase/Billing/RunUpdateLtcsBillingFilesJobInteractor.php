<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Billing;

use Domain\Context\Context;
use Domain\Job\Job as DomainJob;
use UseCase\Job\RunJobUseCase;

/**
 * 介護保険サービス：請求：ファイル生成ジョブ実行ユースケース実装.
 */
final class RunUpdateLtcsBillingFilesJobInteractor implements RunUpdateLtcsBillingFilesJobUseCase
{
    private UpdateLtcsBillingFilesUseCase $updateUseCase;
    private RunJobUseCase $runJobUseCase;

    /**
     * constructor.
     *
     * @param \UseCase\Billing\UpdateLtcsBillingFilesUseCase $updateUseCase
     * @param \UseCase\Job\RunJobUseCase $runJobUseCase
     */
    public function __construct(UpdateLtcsBillingFilesUseCase $updateUseCase, RunJobUseCase $runJobUseCase)
    {
        $this->updateUseCase = $updateUseCase;
        $this->runJobUseCase = $runJobUseCase;
    }

    /** {@inheritdoc} */
    public function handle(Context $context, DomainJob $job, int $billingId): void
    {
        $this->runJobUseCase->handle($context, $job, function () use ($context, $billingId): array {
            $entity = $this->updateUseCase->handle($context, $billingId);
            return ['id' => $entity->id];
        });
    }
}

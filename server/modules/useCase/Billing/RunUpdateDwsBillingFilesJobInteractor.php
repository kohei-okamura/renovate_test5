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
 * 障害福祉サービス：請求：ファイル生成ジョブ実行ユースケース実装.
 */
final class RunUpdateDwsBillingFilesJobInteractor implements RunUpdateDwsBillingFilesJobUseCase
{
    private UpdateDwsBillingFilesUseCase $updateUseCase;
    private RunJobUseCase $runJobUseCase;

    /**
     * constructor.
     *
     * @param \UseCase\Billing\UpdateDwsBillingFilesUseCase $updateUseCase
     * @param \UseCase\Job\RunJobUseCase $runJobUseCase
     */
    public function __construct(UpdateDwsBillingFilesUseCase $updateUseCase, RunJobUseCase $runJobUseCase)
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

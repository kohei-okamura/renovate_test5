<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Jobs;

use Domain\Context\Context;
use Domain\Job\Job as DomainJob;
use UseCase\Billing\RunCreateCopayListJobUseCase;

/**
 * 利用者負担額一覧表作成ジョブ.
 */
final class CreateCopayListJob extends Job
{
    /**
     * Constructor.
     *
     * @param \Domain\Context\Context $context
     * @param \Domain\Job\Job $domainJob
     * @param int $billingId
     * @param array $ids
     * @param bool $isDivided
     */
    public function __construct(
        private Context $context,
        private DomainJob $domainJob,
        private int $billingId,
        private array $ids,
        private bool $isDivided
    ) {
    }

    /**
     * 利用者負担額一覧表ジョブを実行する.
     *
     * @param \UseCase\Billing\RunCreateCopayListJobUseCase $useCase
     * @return void
     */
    public function handle(RunCreateCopayListJobUseCase $useCase): void
    {
        $useCase->handle(
            $this->context,
            $this->domainJob,
            $this->billingId,
            $this->ids,
            $this->isDivided
        );
    }
}

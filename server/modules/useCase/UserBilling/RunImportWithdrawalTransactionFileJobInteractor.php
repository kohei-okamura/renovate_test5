<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\UserBilling;

use Domain\Context\Context;
use Domain\Job\Job as DomainJob;
use UseCase\Job\RunJobUseCase;

/**
 * {@link \UseCase\UserBilling\RunImportWithdrawalTransactionFileJobUseCase} の実装.
 */
class RunImportWithdrawalTransactionFileJobInteractor implements RunImportWithdrawalTransactionFileJobUseCase
{
    private RunJobUseCase $runJobUseCase;
    private ImportWithdrawalTransactionFileUseCase $importFileUseCase;

    /**
     * Constructor.
     *
     * @param \UseCase\Job\RunJobUseCase $runJobUseCase
     * @param \UseCase\UserBilling\ImportWithdrawalTransactionFileUseCase $importFileUseCase
     */
    public function __construct(RunJobUseCase $runJobUseCase, ImportWithdrawalTransactionFileUseCase $importFileUseCase)
    {
        $this->runJobUseCase = $runJobUseCase;
        $this->importFileUseCase = $importFileUseCase;
    }

    /** {@inheritdoc} */
    public function handle(Context $context, string $path, DomainJob $domainJob): void
    {
        $this->runJobUseCase->handle(
            $context,
            $domainJob,
            function () use ($context, $path): void {
                $this->importFileUseCase->handle($context, $path);
            }
        );
    }
}

<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\UserBilling;

use Domain\Config\Config;
use Domain\Context\Context;
use Domain\Job\Job as DomainJob;
use UseCase\Job\RunJobUseCase;

/**
 * {@link \UseCase\UserBilling\RunCreateWithdrawalTransactionFileJobUseCase} の実装.
 */
final class RunCreateWithdrawalTransactionFileJobInteractor implements RunCreateWithdrawalTransactionFileJobUseCase
{
    private RunJobUseCase $runJobUseCase;
    private GenerateWithdrawalTransactionFileUseCase $generateWithdrawalTransactionFileUseCase;
    private Config $config;

    /**
     * Constructor.
     *
     * @param \UseCase\Job\RunJobUseCase $runJobUseCase
     * @param \UseCase\UserBilling\GenerateWithdrawalTransactionFileUseCase $generateWithdrawalTransactionFileUseCase
     * @param \Domain\Config\Config $config
     */
    public function __construct(
        RunJobUseCase $runJobUseCase,
        GenerateWithdrawalTransactionFileUseCase $generateWithdrawalTransactionFileUseCase,
        Config $config
    ) {
        $this->runJobUseCase = $runJobUseCase;
        $this->generateWithdrawalTransactionFileUseCase = $generateWithdrawalTransactionFileUseCase;
        $this->config = $config;
    }

    /** {@inheritdoc} */
    public function handle(Context $context, DomainJob $domainJob, int $id): void
    {
        $this->runJobUseCase->handle(
            $context,
            $domainJob,
            function () use ($context, $id): array {
                $path = $this->generateWithdrawalTransactionFileUseCase->handle($context, $id);
                $filename = $this->config->filename('zinger.filename.withdrawal_transaction_file');
                return [
                    'uri' => $context->uri("user-billings/download/{$path}"),
                    'filename' => $filename,
                ];
            }
        );
    }
}

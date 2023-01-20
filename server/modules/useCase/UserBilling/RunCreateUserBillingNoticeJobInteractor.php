<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\UserBilling;

use Domain\Common\Carbon;
use Domain\Config\Config;
use Domain\Context\Context;
use Domain\Job\Job as DomainJob;
use Lib\Logging;
use UseCase\Job\RunJobUseCase;

/**
 * 代理受領額通知書生成ジョブ実行ユースケース実装.
 */
class RunCreateUserBillingNoticeJobInteractor implements RunCreateUserBillingNoticeJobUseCase
{
    use Logging;

    private RunJobUseCase $runJobUseCase;
    private GenerateUserBillingNoticePdfUseCase $generateUseCase;
    private Config $config;

    /**
     * @param \UseCase\Job\RunJobUseCase $runJobUseCase
     * @param \UseCase\UserBilling\GenerateUserBillingNoticePdfUseCase $generateUseCase
     * @param \Domain\Config\Config $config
     */
    public function __construct(RunJobUseCase $runJobUseCase, GenerateUserBillingNoticePdfUseCase $generateUseCase, Config $config)
    {
        $this->runJobUseCase = $runJobUseCase;
        $this->generateUseCase = $generateUseCase;
        $this->config = $config;
    }

    /** {@inheritdoc} */
    public function handle(Context $context, DomainJob $domainJob, array $ids, Carbon $issuedOn): void
    {
        $this->runJobUseCase->handle(
            $context,
            $domainJob,
            function () use ($context, $ids, $issuedOn): array {
                $path = $this->generateUseCase->handle($context, $ids, $issuedOn);
                $filename = $this->config->filename('zinger.filename.user_billing_notice_pdf');
                $this->logger()->info(
                    '利用者請求：代理受領額通知書生成ジョブ終了',
                    ['filename' => $filename] + $context->logContext()
                );
                return [
                    'uri' => $context->uri("user-billings/download/{$path}"),
                    'filename' => $filename,
                ];
            }
        );
    }
}

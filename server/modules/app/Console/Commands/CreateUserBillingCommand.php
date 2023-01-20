<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Console\Commands;

use App\Console\OrganizationIterator;
use Domain\Common\Carbon;
use Domain\Context\Context;
use Exception;
use Illuminate\Console\Command;
use Lib\Exceptions\InvalidArgumentException;
use Lib\Logging;
use UseCase\UserBilling\CreateUserBillingListUseCase;

/**
 * 利用者請求生成コマンド.
 */
class CreateUserBillingCommand extends Command
{
    use Logging;

    /**
     * コンソールコマンドの名前と引数、オプション.
     *
     * @var string
     */
    protected $signature = <<<'SIGNATURE'
        user-billing:create
            {--batch : バッチ処理(前月のサービス提供年月を対象として処理する)}
            {--providedIn= : サービス提供年月(YYYY-MM). batch指定時は無視.}
    SIGNATURE;

    /**
     * コンソールコマンドの説明.
     *
     * @var string
     */
    protected $description = '利用者請求生成';

    /**
     * コンソールコマンドの実行.
     *
     * @param \App\Console\OrganizationIterator $iterator
     * @param \UseCase\UserBilling\CreateUserBillingListUseCase $createUserBillingListUseCase
     * @return int
     */
    public function handle(OrganizationIterator $iterator, CreateUserBillingListUseCase $createUserBillingListUseCase)
    {
        try {
            $providedIn = $this->getProvidedIn();
            $iterator->iterate(function (Context $context) use ($createUserBillingListUseCase, $providedIn): void {
                $createUserBillingListUseCase->handle($context, $providedIn);
            });
            return self::SUCCESS;
        } catch (Exception $e) {
            $this->logger()->error($e);
            return self::FAILURE;
        }
    }

    /**
     * コマンドオプションの条件から、サービス提供年月を算出する.
     * @return \Domain\Common\Carbon
     */
    private function getProvidedIn(): Carbon
    {
        $carbon = $this->option('batch')
            ? Carbon::now()->subMonth()->firstOfMonth()
            : $this->getProvidedInFromCommandOptions();
        assert($carbon instanceof Carbon);
        return $carbon;
    }

    /**
     * コマンドオプションからサービス提供年月を取得する.
     *
     * @return \Domain\Common\Carbon
     */
    private function getProvidedInFromCommandOptions(): Carbon
    {
        $providedIn = $this->option('providedIn');
        if ($providedIn === null || preg_match('/\A[0-9]{4}-(?:0[1-9]|1[0-2])\z/', $providedIn) !== 1) {
            throw new InvalidArgumentException();
        }
        $carbon = Carbon::parse($providedIn)->firstOfMonth();
        assert($carbon instanceof Carbon);
        return $carbon;
    }
}

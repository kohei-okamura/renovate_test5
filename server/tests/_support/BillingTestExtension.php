<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

use Codeception\Event\TestEvent;
use Codeception\Extension;
use Domain\Common\Carbon;
use Tests\Unit\Mixins\DatabaseMixinSupport;

/**
 * BillingテストSuite 共通処理クラス.
 */
class BillingTestExtension extends Extension
{
    public static array $events = [
        'test.before' => 'beforeTest',
        'test.after' => 'afterTest',
    ];

    private bool $onceFlag = false;

    /**
     * テスト 事前処理
     *
     * @param Codeception\Event\TestEvent $e
     * @throws \Codeception\Exception\ModuleRequireException
     */
    public function beforeTest(TestEvent $e)
    {
        Carbon::setTestNow(Carbon::create(date('c'))->startOfDay()->addDay()); // 実際の明日の日付を指定

        if (!$this->onceFlag) {
            // NOTE test.suite がgetApplication() できないので、flagで再実行されないよう制御する
            $app = $this->getModule('Lumen')->getApplication();
            // Database migrate & fixture (1度だけ)
            DatabaseMixinSupport::migrateOnce($app);
            DatabaseMixinSupport::fixtureOnce();

            $this->onceFlag = true;
        }
    }

    /**
     * テスト 事後処理
     *
     * @param Codeception\Event\TestEvent $e
     */
    public function afterTest(TestEvent $e)
    {
        Carbon::clearTestNow();
    }
}

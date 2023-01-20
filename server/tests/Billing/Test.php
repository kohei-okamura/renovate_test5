<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Billing;

use BillingTester;
use Lib\Json;
use ReflectionClass;
use ReflectionMethod;
use Tests\Unit\Fixtures;

/**
 * Billing Test Suite 基底クラス.
 */
abstract class Test implements Fixtures
{
    protected const COMMAND_SUCCESS = 0;
    protected const COMMAND_FAILURE = 1;

    /**
     * 共通セットアップ処理
     *
     * @param \BillingTester $I
     * @throws \ReflectionException
     */
    public function _before(BillingTester $I)
    {
        $I->resetAssertLog();

        $I->haveHttpHeader('Host', 'eustylelab1.zinger-e2e.test');

        $I->setUpSnapshotIncrementor();
        $I->setTargetClass($this);
        app('db')->beginTransaction();

        // Mixin処理実行
        $reflection = new ReflectionClass($this);
        $methods = $reflection->getMethods(ReflectionMethod::IS_PUBLIC);
        foreach ($methods as $method) {
            if (strpos($method->getName(), '_beforeMixin') === 0) {
                $method->invoke($this, $I);
            }
        }
    }

    /**
     * 共通終了処理
     *
     * @param \ApiTester $I
     */
    public function _after(BillingTester $I)
    {
        // Mixin処理実行
        $reflection = new ReflectionClass($this);
        $methods = $reflection->getMethods(ReflectionMethod::IS_PUBLIC);
        foreach ($methods as $method) {
            if (strpos($method->getName(), '_afterMixin') === 0) {
                $method->invoke($this, $I);
            }
        }
        app('db')->rollback();
    }

    /**
     * テスト成功時終了処理.
     *
     * @param \BillingTester $I
     */
    public function _passed(BillingTester $I)
    {
        $I->checkAssertLog();
    }

    /**
     * DomainモデルからArrayを生成する（リクエストパラメータ用 または レスポンスコンテント検証用）.
     *
     * @param array|\Domain\Model|\ScalikePHP\Seq $entity
     * @throws \JsonException
     * @return array
     */
    protected function domainToArray($entity): array
    {
        return Json::decode(Json::encode($entity), true);
    }
}

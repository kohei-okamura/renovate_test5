<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Domain\UserBilling;

use Domain\Common\Addr;
use Domain\Common\Prefecture;
use Domain\UserBilling\UserBillingDwsItem;
use Domain\UserBilling\UserBillingReceiptPdfBillingDestination;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Test;

/**
 * {@link \Domain\UserBilling\UserBillingReceiptPdfBillingDestination} のテスト
 */
final class UserBillingReceiptPdfBillingDestinationTest extends Test
{
    use ExamplesConsumer;
    use MatchesSnapshots;
    use UnitSupport;

    protected UserBillingDwsItem $userBillingDwsItem;

    protected array $values = [];

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
    }

    /**
     * @test
     * @return void
     */
    public function describe_from(): void
    {
        $this->specify('インスタンスを生成する', function (): void {
            $actual = UserBillingReceiptPdfBillingDestination::from(
                $this->examples()->users[0]
            );
            $this->assertMatchesModelSnapshot($actual);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_json(): void
    {
        $this->specify('JSON エンコードした結果', function (): void {
            $actual = $this->createInstance()->toJson();
            $this->assertMatchesJsonSnapshot($actual);
        });
    }

    /**
     * テスト対象のインスタンスを生成する.
     *
     * @param array $attrs
     * @return \Domain\UserBilling\UserBillingReceiptPdfBillingDestination
     */
    private function createInstance(array $attrs = []): UserBillingReceiptPdfBillingDestination
    {
        $x = new UserBillingReceiptPdfBillingDestination(
            addr: new Addr(
                postcode: '164-0011',
                prefecture: Prefecture::tokyo(),
                city: '中野区',
                street: '中央1-35-6',
                apartment: 'レッチフィールド中野坂上ビル6F',
            ),
            corporationName: '会社名',
            agentName: '名前'
        );
        return $x->copy($attrs);
    }
}

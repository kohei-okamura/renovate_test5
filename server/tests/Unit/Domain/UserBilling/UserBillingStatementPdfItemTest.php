<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Domain\UserBilling;

use Domain\UserBilling\UserBillingDwsItem;
use Domain\UserBilling\UserBillingStatementPdfItem;
use ScalikePHP\Map;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Test;

/**
 * {@link \Domain\UserBilling\UserBillingStatementPdfItem} のテスト
 */
final class UserBillingStatementPdfItemTest extends Test
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
    public function describe_fromDws(): void
    {
        $this->should('return an instance', function (): void {
            $actual = UserBillingStatementPdfItem::fromDws(
                $this->examples->dwsBillingStatements[0]->items[0],
                Map::from(['111111' => '身体介護'])
            );
            $this->assertMatchesModelSnapshot($actual);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_fromLtcs(): void
    {
        $this->should('return an instance', function (): void {
            $actual = UserBillingStatementPdfItem::fromLtcs(
                $this->examples->ltcsBillingStatements[9]->items[0],
                Map::from(['112145' => '身体4・Ⅰ'])
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
        $this->should('changes to JSON encode results', function (): void {
            $actual = $this->createInstance()->toJson();
            $this->assertMatchesJsonSnapshot($actual);
        });
    }

    /**
     * テスト対象のインスタンスを生成する.
     *
     * @param array $attrs
     * @return \Domain\UserBilling\UserBillingStatementPdfItem
     */
    private function createInstance(array $attrs = []): UserBillingStatementPdfItem
    {
        $x = new UserBillingStatementPdfItem(
            serviceCode: '111111',
            serviceName: '身体介護1',
            unitScore: number_format(100),
            count: number_format(10),
            totalScore: number_format(1000),
        );
        return $x->copy($attrs);
    }
}

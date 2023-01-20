<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Domain\UserBilling;

use Domain\UserBilling\UserBillingDwsItem;
use Domain\UserBilling\UserBillingStatementPdfAmount;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Test;

/**
 * {@link \Domain\UserBilling\UserBillingStatementPdfAmount} のテスト.
 */
final class UserBillingStatementPdfAmountTest extends Test
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
            $actual = UserBillingStatementPdfAmount::fromDws(
                $this->examples->userBillings[0]->dwsItem
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
            $actual = UserBillingStatementPdfAmount::fromLtcs(
                $this->examples->userBillings[0]->ltcsItem
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
            $this->assertMatchesJsonSnapshot($this->createInstance()->toJson());
        });
    }

    /**
     * テスト対象のインスタンスを生成する.
     *
     * @param array $attrs
     * @return \Domain\UserBilling\UserBillingStatementPdfAmount
     */
    private function createInstance(array $attrs = []): UserBillingStatementPdfAmount
    {
        $x = new UserBillingStatementPdfAmount(
            score: number_format(1000),
            unitCost: sprintf('%.2f', 11.4),
            subtotalCost: number_format(11400),
            benefitAmount: number_format(10000),
            copayWithTax: number_format(1400)
        );
        return $x->copy($attrs);
    }
}

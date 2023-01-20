<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Domain\Billing;

use Domain\Billing\DwsBillingStatementAggregate;
use Domain\Billing\DwsServiceDivisionCode;
use Domain\Common\Carbon;
use Domain\Common\Decimal;
use ScalikePHP\Option;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Test;
use Tests\Unit\UseCase\Billing\DwsBillingTestSupport;

/**
 * {@link \Domain\Billing\DwsBillingStatementAggregate} のテスト.
 */
final class DwsBillingStatementAggregateTest extends Test
{
    use CarbonMixin;
    use DwsBillingTestSupport;
    use MatchesSnapshots;
    use UnitSupport;

    /**
     * 初期化処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachTest(function (self $self): void {
            $self->setupTestData();
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_instance(): void
    {
        $this->should('return an instance', function (): void {
            $x = $this->createInstance();
            $this->assertMatchesModelSnapshot($x);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_json(): void
    {
        $this->should('be able to encode to json', function (): void {
            $x = $this->createInstance();
            $this->assertMatchesJsonSnapshot($x->toJson());
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_from(): void
    {
        $examples = [
            'general case' => [
                $this->contract,
                $this->dwsCertification->copy(['copayLimit' => 9300]),
                Option::none(),
                DwsServiceDivisionCode::visitingCareForPwsd(),
                1,
                Decimal::fromInt(10_3600),
                14,
                52330,
                0,
                Option::none(),
                Option::none(),
                Option::none(),
            ],
            'copay coordinated case' => [
                $this->contract,
                $this->dwsCertification->copy(['copayLimit' => 37200]),
                Option::none(),
                DwsServiceDivisionCode::visitingCareForPwsd(),
                1,
                Decimal::fromInt(11_2000),
                22,
                68546,
                0,
                Option::none(),
                Option::some(10086),
                Option::none(),
            ],
            'UserDwsSubsidy specified case' => [
                $this->contract,
                $this->dwsCertification->copy(['copayLimit' => 4600]),
                Option::some($this->userDwsSubsidy),
                DwsServiceDivisionCode::homeHelpService(),
                1,
                Decimal::fromInt(11_2000),
                17,
                12744,
                0,
                Option::none(),
                Option::none(),
                Option::none(),
            ],
            'subtotalSubsidy specified case' => [
                $this->contract,
                $this->dwsCertification->copy(['copayLimit' => 37200]),
                Option::none(),
                DwsServiceDivisionCode::visitingCareForPwsd(),
                1,
                Decimal::fromInt(11_2000),
                26,
                47025,
                0,
                Option::none(),
                Option::some(37200),
                Option::some(21400),
            ],
        ];
        $this->should(
            'return a DwsBillingStatementAggregate',
            function (...$args): void {
                $actual = DwsBillingStatementAggregate::from(...$args);
                $this->assertMatchesModelSnapshot($actual);
            },
            ['examples' => $examples]
        );
    }

    /**
     * テスト対象のインスタンスを生成する.
     *
     * @param array $attrs
     * @return \Domain\Billing\DwsBillingStatementAggregate
     */
    private function createInstance(array $attrs = []): DwsBillingStatementAggregate
    {
        $x = new DwsBillingStatementAggregate(
            serviceDivisionCode: DwsServiceDivisionCode::visitingCareForPwsd(),
            startedOn: Carbon::today()->subDay(),
            terminatedOn: Carbon::today(),
            serviceDays: 3,
            subtotalScore: 1000,
            unitCost: Decimal::fromInt(10_0000),
            subtotalFee: 1000,
            unmanagedCopay: 1000,
            managedCopay: 1000,
            cappedCopay: 1000,
            adjustedCopay: 1000,
            coordinatedCopay: 1000,
            subtotalCopay: 1000,
            subtotalBenefit: 1000,
            subtotalSubsidy: 1000,
        );
        return $x->copy($attrs);
    }
}

<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\Billing;

use Domain\Office\LtcsOfficeLocationAddition;
use ScalikePHP\Option;
use ScalikePHP\Seq;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\DummyContextMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;
use UseCase\Billing\ComputeLtcsBillingLocationAdditionInteractor;

/**
 * {@link \UseCase\Billing\ComputeLtcsBillingLocationAdditionInteractor} のテスト.
 */
final class ComputeLtcsBillingLocationAdditionInteractorTest extends Test
{
    use CarbonMixin;
    use ComputeLtcsBillingAdditionTestSupport;
    use DummyContextMixin;
    use MatchesSnapshots;
    use MockeryMixin;
    use UnitSupport;

    private Seq $entries;
    private ComputeLtcsBillingLocationAdditionInteractor $interactor;

    /**
     * 初期化処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachTest(function (self $self): void {
            $self->entries = self::dictionaryEntries();
        });
        self::beforeEachSpec(function (ComputeLtcsBillingLocationAdditionInteractorTest $self): void {
            $self->interactor = app(ComputeLtcsBillingLocationAdditionInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should(
            'return a Option of LtcsBillingServiceDetail contains LocationAddition when the report contains specifiedArea',
            function (): void {
                $actual = $this->interactor->handle(
                    $this->context,
                    self::provisionReport(['locationAddition' => LtcsOfficeLocationAddition::specifiedArea()]),
                    $this->entries,
                    1000
                );
                $this->assertMatchesModelSnapshot($actual);
            }
        );
        $this->should(
            'return a Option of LtcsBillingServiceDetail contains specifiedAreaAddition when the report contains mountainousArea',
            function (): void {
                $actual = $this->interactor->handle(
                    $this->context,
                    self::provisionReport(['locationAddition' => LtcsOfficeLocationAddition::mountainousArea()]),
                    $this->entries,
                    2000
                );
                $this->assertMatchesModelSnapshot($actual);
            }
        );
        $this->should(
            'return a Option None when the report only LtcsOfficeLocationAddition is None',
            function (): void {
                $expected = Option::none();
                $actual = $this->interactor->handle($this->context, self::provisionReport(), $this->entries, 3000);
                $this->assertSame($expected, $actual);
            }
        );
    }
}

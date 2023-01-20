<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\Billing;

use ScalikePHP\Option;
use ScalikePHP\Seq;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;
use UseCase\Billing\ComputeLtcsBillingUserLocationAdditionInteractor;

/**
 * {@link \UseCase\Billing\ComputeLtcsBillingUserLocationAdditionInteractor} のテスト.
 */
final class ComputeLtcsBillingUserLocationAdditionInteractorTest extends Test
{
    use CarbonMixin;
    use ComputeLtcsBillingAdditionTestSupport;
    use ContextMixin;
    use ExamplesConsumer;
    use MatchesSnapshots;
    use MockeryMixin;
    use UnitSupport;

    private Seq $entries;
    private ComputeLtcsBillingUserLocationAdditionInteractor $interactor;

    /**
     * 初期化処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachTest(function (self $self): void {
            $self->entries = self::dictionaryEntries();
        });
        self::beforeEachSpec(function (ComputeLtcsBillingUserLocationAdditionInteractorTest $self): void {
            $self->interactor = app(ComputeLtcsBillingUserLocationAdditionInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should(
            'return a Option of LtcsBillingServiceDetail contains mountainousAreaAddition when userLtcsCalcSpec is mountainousArea',
            function (): void {
                $actual = $this->interactor->handle(
                    $this->context,
                    self::provisionReport(),
                    Option::from($this->examples->userLtcsCalcSpecs[1]),
                    $this->entries,
                    1000
                );
                $this->assertMatchesModelSnapshot($actual);
            }
        );
        $this->should(
            'return a Option None when userLtcsCalcSpec is none',
            function (): void {
                $actual = $this->interactor->handle(
                    $this->context,
                    self::provisionReport(),
                    Option::from($this->examples->userLtcsCalcSpecs[0]),
                    $this->entries,
                    1000
                );
                $this->assertNone($actual);
            }
        );
        $this->should(
            'return a Option None when userCalcSpec is Option None',
            function (): void {
                $actual = $this->interactor->handle(
                    $this->context,
                    self::provisionReport(),
                    Option::none(),
                    $this->entries,
                    1000
                );
                $this->assertNone($actual);
            }
        );
    }
}

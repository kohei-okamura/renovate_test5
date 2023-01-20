<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\Billing;

use Domain\Office\LtcsTreatmentImprovementAddition;
use ScalikePHP\Option;
use ScalikePHP\Seq;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\DummyContextMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;
use UseCase\Billing\ComputeLtcsBillingTreatmentImprovementAdditionInteractor;

/**
 * {@link \UseCase\Billing\ComputeLtcsBillingTreatmentImprovementAdditionInteractor} のテスト.
 */
final class ComputeLtcsBillingTreatmentImprovementAdditionInteractorTest extends Test
{
    use CarbonMixin;
    use ComputeLtcsBillingAdditionTestSupport;
    use DummyContextMixin;
    use MatchesSnapshots;
    use MockeryMixin;
    use UnitSupport;

    private Seq $entries;
    private ComputeLtcsBillingTreatmentImprovementAdditionInteractor $interactor;

    /**
     * 初期化処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachTest(function (self $self): void {
            $self->entries = self::dictionaryEntries();
        });
        self::beforeEachSpec(function (self $self): void {
            $self->interactor = app(ComputeLtcsBillingTreatmentImprovementAdditionInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should(
            'return a Some of a LtcsBillingServiceDetail containing TreatmentImprovementAddition when the report contains addition1',
            function (): void {
                $actual = $this->interactor->handle(
                    $this->context,
                    self::provisionReport([
                        'treatmentImprovementAddition' => LtcsTreatmentImprovementAddition::addition1(),
                    ]),
                    $this->entries,
                    1000
                );
                $this->assertMatchesModelSnapshot($actual);
            }
        );
        $this->should(
            'return a Some of a LtcsBillingServiceDetail containing TreatmentImprovementAddition when the report contains addition2',
            function (): void {
                $actual = $this->interactor->handle(
                    $this->context,
                    self::provisionReport([
                        'treatmentImprovementAddition' => LtcsTreatmentImprovementAddition::addition2(),
                    ]),
                    $this->entries,
                    2000
                );
                $this->assertMatchesModelSnapshot($actual);
            }
        );
        $this->should(
            'return a Some of a LtcsBillingServiceDetail containing TreatmentImprovementAddition when the report contains addition3',
            function (): void {
                $actual = $this->interactor->handle(
                    $this->context,
                    self::provisionReport([
                        'treatmentImprovementAddition' => LtcsTreatmentImprovementAddition::addition3(),
                    ]),
                    $this->entries,
                    3000
                );
                $this->assertMatchesModelSnapshot($actual);
            }
        );
        $this->should(
            'return a Some of a LtcsBillingServiceDetail containing TreatmentImprovementAddition when the report contains addition4',
            function (): void {
                $actual = $this->interactor->handle(
                    $this->context,
                    self::provisionReport([
                        'treatmentImprovementAddition' => LtcsTreatmentImprovementAddition::addition4(),
                    ]),
                    $this->entries,
                    4000
                );
                $this->assertMatchesModelSnapshot($actual);
            }
        );
        $this->should(
            'return a Some of a LtcsBillingServiceDetail containing TreatmentImprovementAddition when the report contains addition5',
            function (): void {
                $actual = $this->interactor->handle(
                    $this->context,
                    self::provisionReport([
                        'treatmentImprovementAddition' => LtcsTreatmentImprovementAddition::addition5(),
                    ]),
                    $this->entries,
                    5000
                );
                $this->assertMatchesModelSnapshot($actual);
            }
        );
        $this->should(
            'return a None when when the report contains only none',
            function (): void {
                $actual = $this->interactor->handle(
                    $this->context,
                    self::provisionReport([
                        'treatmentImprovementAddition' => LtcsTreatmentImprovementAddition::none(),
                    ]),
                    $this->entries,
                    6000
                );
                $this->assertSame(Option::none(), $actual);
            }
        );
        $this->should(
            'return a None when the report does not contain TreatmentImprovementAddition',
            function (): void {
                $actual = $this->interactor->handle(
                    $this->context,
                    self::provisionReport(),
                    $this->entries,
                    7000
                );
                $this->assertSame(Option::none(), $actual);
            }
        );
    }
}

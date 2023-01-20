<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\Billing;

use Domain\Office\LtcsSpecifiedTreatmentImprovementAddition;
use ScalikePHP\Option;
use ScalikePHP\Seq;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\DummyContextMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;
use UseCase\Billing\ComputeLtcsBillingSpecifiedTreatmentImprovementAdditionInteractor;

/**
 * {@link \UseCase\Billing\ComputeLtcsBillingSpecifiedTreatmentImprovementAdditionInteractor} のテスト.
 */
final class ComputeLtcsBillingSpecifiedTreatmentImprovementAdditionInteractorTest extends Test
{
    use CarbonMixin;
    use ComputeLtcsBillingAdditionTestSupport;
    use DummyContextMixin;
    use MatchesSnapshots;
    use MockeryMixin;
    use UnitSupport;

    private Seq $entries;
    private ComputeLtcsBillingSpecifiedTreatmentImprovementAdditionInteractor $interactor;

    /**
     * 初期化処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachTest(function (self $self): void {
            $self->entries = self::dictionaryEntries();
        });
        self::beforeEachSpec(function (self $self): void {
            $self->interactor = app(ComputeLtcsBillingSpecifiedTreatmentImprovementAdditionInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should(
            'return a Some of a LtcsBillingServiceDetail containing SpecifiedTreatmentImprovementAddition when the report contains addition1',
            function (): void {
                $actual = $this->interactor->handle(
                    $this->context,
                    self::provisionReport([
                        'specifiedTreatmentImprovementAddition' => LtcsSpecifiedTreatmentImprovementAddition::addition1(),
                    ]),
                    $this->entries,
                    1000
                );
                $this->assertMatchesModelSnapshot($actual);
            }
        );
        $this->should(
            'return a Some of a LtcsBillingServiceDetail containing SpecifiedTreatmentImprovementAddition when the report contains addition2',
            function (): void {
                $actual = $this->interactor->handle(
                    $this->context,
                    self::provisionReport([
                        'specifiedTreatmentImprovementAddition' => LtcsSpecifiedTreatmentImprovementAddition::addition2(),
                    ]),
                    $this->entries,
                    2000
                );
                $this->assertMatchesModelSnapshot($actual);
            }
        );
        $this->should(
            'return a None when the report contains only none',
            function (): void {
                $actual = $this->interactor->handle(
                    $this->context,
                    self::provisionReport([
                        'specifiedTreatmentImprovementAddition' => LtcsSpecifiedTreatmentImprovementAddition::none(),
                    ]),
                    $this->entries,
                    3000
                );
                $this->assertSame(Option::none(), $actual);
            }
        );
        $this->should(
            'return a None when the report does not contain SpecifiedTreatmentImprovementAddition',
            function (): void {
                $actual = $this->interactor->handle(
                    $this->context,
                    self::provisionReport(),
                    $this->entries,
                    4000
                );
                $this->assertSame(Option::none(), $actual);
            }
        );
    }
}

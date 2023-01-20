<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\Billing;

use Domain\Shift\ServiceOption;
use ScalikePHP\Seq;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\DummyContextMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;
use UseCase\Billing\ComputeLtcsBillingVitalFunctionsImprovementAdditionInteractor;

/**
 * {@link \UseCase\Billing\ComputeLtcsBillingVitalFunctionsImprovementAdditionInteractor} のテスト.
 */
final class ComputeLtcsBillingVitalFunctionsImprovementAdditionInteractorTest extends Test
{
    use CarbonMixin;
    use ComputeLtcsBillingAdditionTestSupport;
    use DummyContextMixin;
    use MatchesSnapshots;
    use MockeryMixin;
    use UnitSupport;

    private Seq $entries;
    private ComputeLtcsBillingVitalFunctionsImprovementAdditionInteractor $interactor;

    /**
     * 初期化処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachTest(function (self $self): void {
            $self->entries = self::dictionaryEntries();
        });
        self::beforeEachSpec(function (self $self): void {
            $self->interactor = app(ComputeLtcsBillingVitalFunctionsImprovementAdditionInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should(
            'return a Seq of a LtcsBillingServiceDetail containing vitalFunctionsImprovementAddition1',
            function (): void {
                $actual = $this->interactor->handle(
                    $this->context,
                    self::provisionReport([
                        'entries' => [self::provisionReportEntry(['options' => [ServiceOption::vitalFunctionsImprovement1()]])],
                    ]),
                    $this->entries
                );
                $this->assertMatchesModelSnapshot($actual);
            }
        );
        $this->should(
            'return a Seq of a LtcsBillingServiceDetail containing vitalFunctionsImprovementAddition2',
            function (): void {
                $actual = $this->interactor->handle(
                    $this->context,
                    self::provisionReport([
                        'entries' => [
                            self::provisionReportEntry(['options' => [ServiceOption::vitalFunctionsImprovement2()]]),
                        ],
                    ]),
                    $this->entries
                );
                $this->assertMatchesModelSnapshot($actual);
            }
        );
        $this->should(
            'return a Seq of a LtcsBillingServiceDetail containing vitalFunctionsImprovementAddition1 and vitalFunctionsImprovementAddition2',
            function (): void {
                $actual = $this->interactor->handle(
                    $this->context,
                    self::provisionReport([
                        'entries' => [
                            self::provisionReportEntry([
                                'options' => [
                                    ServiceOption::vitalFunctionsImprovement1(),
                                    ServiceOption::vitalFunctionsImprovement2(),
                                ],
                            ]),
                        ],
                    ]),
                    $this->entries
                );
                $this->assertMatchesModelSnapshot($actual);
            }
        );
        $this->should(
            'return a empty Seq when the report does not contain vitalFunctionsImprovementAddition',
            function (): void {
                $actual = $this->interactor->handle(
                    $this->context,
                    self::provisionReport([
                        'entries' => [self::provisionReportEntry(['options' => []])],
                    ]),
                    $this->entries
                );
                $this->assertTrue($actual->isEmpty());
            }
        );
    }
}

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
use UseCase\Billing\ComputeLtcsBillingEmergencyAdditionInteractor;

/**
 * {@link \UseCase\Billing\ComputeLtcsBillingEmergencyAdditionInteractor} のテスト.
 */
final class ComputeLtcsBillingEmergencyAdditionInteractorTest extends Test
{
    use CarbonMixin;
    use ComputeLtcsBillingAdditionTestSupport;
    use DummyContextMixin;
    use MatchesSnapshots;
    use MockeryMixin;
    use UnitSupport;

    private Seq $entries;
    private ComputeLtcsBillingEmergencyAdditionInteractor $interactor;

    /**
     * 初期化処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachTest(function (self $self): void {
            $self->entries = self::dictionaryEntries();
        });
        self::beforeEachSpec(function (self $self): void {
            $self->interactor = app(ComputeLtcsBillingEmergencyAdditionInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should(
            'return a Seq of a LtcsBillingServiceDetail containing EmergencyAddition when the report contains emergency',
            function (): void {
                $actual = $this->interactor->handle(
                    $this->context,
                    self::provisionReport([
                        'entries' => [
                            self::provisionReportEntry(['options' => [ServiceOption::emergency()]]),
                            self::provisionReportEntry(['options' => [ServiceOption::emergency()]]),
                        ],
                    ]),
                    $this->entries
                );
                $this->assertMatchesModelSnapshot($actual);
            }
        );
        $this->should(
            'return a Seq of a LtcsBillingServiceDetail containing EmergencyAddition when the report for plan contains emergency',
            function (): void {
                $actual = $this->interactor->handle(
                    $this->context,
                    self::provisionReport([
                        'entries' => [
                            self::provisionReportEntry(['options' => [ServiceOption::emergency()]]),
                            self::provisionReportEntry(['options' => [ServiceOption::emergency()]]),
                        ],
                    ]),
                    $this->entries,
                    true,
                );
                $this->assertMatchesModelSnapshot($actual);
            }
        );
        $this->should(
            'return an empty Seq when the report does not contain emergency',
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

<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\Billing;

use Domain\Common\Carbon;
use Domain\Shift\ServiceOption;
use ScalikePHP\Option;
use ScalikePHP\Seq;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\DummyContextMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;
use UseCase\Billing\ComputeLtcsBillingFirstTimeAdditionInteractor;

/**
 * {@link \UseCase\Billing\ComputeLtcsBillingFirstTimeAdditionInteractor} のテスト.
 */
final class ComputeLtcsBillingFirstTimeAdditionInteractorTest extends Test
{
    use CarbonMixin;
    use ComputeLtcsBillingAdditionTestSupport;
    use DummyContextMixin;
    use MatchesSnapshots;
    use MockeryMixin;
    use UnitSupport;

    private Seq $entries;
    private ComputeLtcsBillingFirstTimeAdditionInteractor $interactor;

    /**
     * 初期化処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachTest(function (self $self): void {
            $self->entries = self::dictionaryEntries();
        });
        self::beforeEachSpec(function (self $self): void {
            $self->interactor = app(ComputeLtcsBillingFirstTimeAdditionInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should(
            'return a Some of a LtcsBillingServiceDetail containing FirstTimeAddition when the report contains firstTime',
            function (): void {
                $actual = $this->interactor->handle(
                    context: $this->context,
                    report: self::provisionReport([
                        'entries' => [
                            self::provisionReportEntry([
                                'results' => [
                                    Carbon::create(2021, 1, 3),
                                    Carbon::create(2021, 1, 7),
                                ],
                            ]),
                            self::provisionReportEntry(['results' => []]),
                            self::provisionReportEntry([
                                'options' => [ServiceOption::firstTime()],
                                'results' => [
                                    Carbon::create(2021, 1, 2),
                                    Carbon::create(2021, 1, 5),
                                    Carbon::create(2021, 1, 8),
                                ],
                            ]),
                        ],
                    ]),
                    dictionaryEntries: $this->entries,
                    usePlan: false
                );
                $this->assertMatchesModelSnapshot($actual);
            }
        );
        $this->should(
            'return a Some of a LtcsBillingServiceDetail containing FirstTimeAddition when the report for plan contains firstTime',
            function (): void {
                $actual = $this->interactor->handle(
                    context: $this->context,
                    report: self::provisionReport([
                        'entries' => [
                            self::provisionReportEntry([
                                'results' => [
                                    Carbon::create(2021, 1, 3),
                                    Carbon::create(2021, 1, 7),
                                ],

                            ]),
                            self::provisionReportEntry(['results' => []]),
                            self::provisionReportEntry([
                                'options' => [ServiceOption::firstTime()],
                                'results' => [
                                    Carbon::create(2021, 1, 2),
                                    Carbon::create(2021, 1, 5),
                                    Carbon::create(2021, 1, 8),
                                ],
                            ]),
                        ],
                    ]),
                    dictionaryEntries: $this->entries,
                    usePlan: true
                );
                $this->assertMatchesModelSnapshot($actual);
            }
        );
        $this->should(
            'return a None when the report does not contain firstTime',
            function (): void {
                $actual = $this->interactor->handle(
                    $this->context,
                    self::provisionReport(),
                    $this->entries
                );
                $this->assertSame(Option::none(), $actual);
            }
        );
        $this->should(
            'return a None when the report contains firstTime but does not have results',
            function (): void {
                $actual = $this->interactor->handle(
                    $this->context,
                    self::provisionReport([
                        'entries' => [
                            self::provisionReportEntry(['results' => []]),
                            self::provisionReportEntry(['results' => []]),
                        ],
                        'options' => [ServiceOption::firstTime()],
                    ]),
                    $this->entries
                );
                $this->assertSame(Option::none(), $actual);
            }
        );
    }
}

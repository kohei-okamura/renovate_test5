<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\Billing;

use Domain\Billing\DwsBillingServiceDetail;
use Domain\Common\Carbon;
use Domain\Common\IntRange;
use Domain\ServiceCode\ServiceCode;
use Domain\ServiceCodeDictionary\DwsServiceCodeCategory;
use Domain\ServiceCodeDictionary\DwsVisitingCareForPwsdDictionaryEntry;
use Domain\ServiceCodeDictionary\Timeframe;
use ScalikePHP\Option;
use ScalikePHP\Seq;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\DummyContextMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;
use UseCase\Billing\ComputeDwsVisitingCareForPwsdServiceDetailCovid19PandemicSpecialAdditionInteractor;

/**
 * {@link \UseCase\Billing\ComputeDwsVisitingCareForPwsdServiceDetailCovid19PandemicSpecialAdditionInteractor} のテスト.
 */
final class ComputeDwsVisitingCareForPwsdServiceDetailCovid19PandemicSpecialAdditionInteractorTest extends Test
{
    use CarbonMixin;
    use DummyContextMixin;
    use DwsBillingTestSupport;
    use MockeryMixin;
    use UnitSupport;

    /** @var \Domain\ServiceCodeDictionary\DwsVisitingCareForPwsdDictionaryEntry[]|\ScalikePHP\Option */
    private Option $entryOption;

    private ComputeDwsVisitingCareForPwsdServiceDetailCovid19PandemicSpecialAdditionInteractor $interactor;

    /**
     * 初期化処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachTest(function (self $self): void {
            $self->setupTestData();
            $self->entryOption = Option::some(DwsVisitingCareForPwsdDictionaryEntry::create([
                'id' => 1,
                'dwsVisitingCareForPwsdDictionaryId' => 1,
                'serviceCode' => ServiceCode::fromString('12ZZ01'),
                'name' => '令和3年9月30日までの上乗せ分（重訪）',
                'category' => DwsServiceCodeCategory::covid19PandemicSpecialAddition(),
                'isSecondary' => false,
                'isCoaching' => false,
                'isHospitalized' => false,
                'isLongHospitalized' => false,
                'score' => 0,
                'timeframe' => Timeframe::unknown(),
                'duration' => IntRange::create(['start' => 0, 'end' => 0]),
                'unit' => 0,
                'createdAt' => Carbon::now(),
                'updatedAt' => Carbon::now(),
            ]));
        });
        self::beforeEachSpec(function (self $self): void {
            $self->interactor = app(ComputeDwsVisitingCareForPwsdServiceDetailCovid19PandemicSpecialAdditionInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('return an empty Seq before 2021-04-01', function (): void {
            $actual = $this->interactor->handle($this->context, $this->report, 2000, Option::none());

            $this->assertEquals(Seq::empty(), $actual);
        });
        $this->should(
            'return a Seq between 2021-04-01 and 2021-09-30',
            function (): void {
                $report = $this->report(['providedIn' => Carbon::create(2021, 4, 1)]);
                $expected = DwsBillingServiceDetail::create([
                    'userId' => $report->userId,
                    'providedOn' => $report->providedIn->endOfMonth(),
                    'serviceCode' => ServiceCode::fromString('12ZZ01'),
                    'serviceCodeCategory' => DwsServiceCodeCategory::covid19PandemicSpecialAddition(),
                    'unitScore' => 2,
                    'isAddition' => true,
                    'count' => 1,
                    'totalScore' => 2,
                ]);

                $actual = $this->interactor->handle($this->context, $report, 2000, $this->entryOption);

                $this->assertCount(1, $actual);
                $this->assertModelStrictEquals($expected, $actual->head());
            },
            [
                'examples' => [
                    [0, 1],
                    [499, 1],
                    [500, 1],
                    [999, 1],
                    [1000, 1],
                    [1499, 1],
                    [1500, 2],
                    [1999, 2],
                    [2000, 2],
                ],
            ]
        );
        $this->should('return an empty Seq after 2021-09-30', function (): void {
            $report = $this->report(['providedIn' => Carbon::create(2021, 10, 1)]);

            $actual = $this->interactor->handle($this->context, $report, 2000, $this->entryOption);

            $this->assertEquals(Seq::empty(), $actual);
        });
        $this->should('return an empty Seq when the dictionary entry does not exist', function (): void {
            $report = $this->report(['providedIn' => Carbon::create(2021, 10, 1)]);

            $actual = $this->interactor->handle($this->context, $report, 2000, Option::none());

            $this->assertEquals(Seq::empty(), $actual);
        });
    }
}

<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\Billing;

use Domain\Billing\LtcsBillingServiceDetail;
use Domain\Billing\LtcsBillingServiceDetailDisposition;
use Domain\Common\Carbon;
use Domain\ProvisionReport\LtcsBuildingSubtraction;
use Domain\ProvisionReport\LtcsProvisionReport;
use Domain\ServiceCodeDictionary\LtcsHomeVisitLongTermCareDictionaryEntry;
use Domain\ServiceCodeDictionary\LtcsNoteRequirement;
use Domain\ServiceCodeDictionary\LtcsServiceCodeCategory;
use Lib\Exceptions\SetupException;
use ScalikePHP\Seq;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;
use UseCase\Billing\ComputeLtcsServiceDetailCovid19PandemicSpecialAdditionInteractor;

/**
 * {@link \UseCase\Billing\ComputeLtcsServiceDetailCovid19PandemicSpecialAdditionInteractor} のテスト.
 */
final class ComputeLtcsServiceDetailCovid19PandemicSpecialAdditionInteractorTest extends Test
{
    use ExamplesConsumer;
    use MatchesSnapshots;
    use MockeryMixin;
    use UnitSupport;

    private LtcsProvisionReport $report;
    /** @var \Domain\ServiceCodeDictionary\LtcsHomeVisitLongTermCareDictionaryEntry[]|\ScalikePHP\Seq */
    private Seq $dictionaryEntries;
    private LtcsHomeVisitLongTermCareDictionaryEntry $entry;

    private ComputeLtcsServiceDetailCovid19PandemicSpecialAdditionInteractor $interactor;

    /**
     * 初期化処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachSpec(function (self $self): void {
            $self->report = $self->examples->ltcsProvisionReports[0];
            $self->dictionaryEntries = Seq::fromArray($self->examples()->ltcsHomeVisitLongTermCareDictionaryEntries);
            $self->entry = $self->dictionaryEntries
                ->filter(function (LtcsHomeVisitLongTermCareDictionaryEntry $x): bool {
                    return $x->category === LtcsServiceCodeCategory::covid19PandemicSpecialAddition();
                })
                ->head();

            $self->interactor = app(ComputeLtcsServiceDetailCovid19PandemicSpecialAdditionInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('before 2021-04-01', function (Carbon $date): void {
            $actual = $this->interactor->handle(
                $this->report->copy(['providedIn' => $date]),
                $this->dictionaryEntries,
                2000
            );

            $this->assertEquals(Seq::emptySeq(), $actual);
        }, [
            'examples' => [
                'before 2021-04-01' => [Carbon::create(2021, 3)->endOfMonth()->startOfDay()],
                'after 2021-10-01' => [Carbon::create(2021, 10)->startOfMonth()->startOfDay()],
            ],
        ]);
        $this->should('return seq of service detail when in the target date', function (Carbon $date): void {
            $report = $this->report->copy(['providedIn' => $date]);

            $actual = $this->interactor->handle(
                $report,
                $this->dictionaryEntries,
                2000,
            );

            $this->assertCount(1, $actual);
            $this->assertModelStrictEquals(
                new LtcsBillingServiceDetail(
                    userId: $report->userId,
                    disposition: LtcsBillingServiceDetailDisposition::result(),
                    providedOn: $report->providedIn->endOfMonth(),
                    serviceCode: $this->entry->serviceCode,
                    serviceCodeCategory: $this->entry->category,
                    buildingSubtraction: LtcsBuildingSubtraction::none(),
                    noteRequirement: LtcsNoteRequirement::none(),
                    isAddition: true,
                    isLimited: false,
                    durationMinutes: 0,
                    unitScore: 2,
                    count: 1,
                    wholeScore: 2,
                    maxBenefitQuotaExcessScore: 0,
                    maxBenefitExcessScore: 0,
                    totalScore: 2,
                ),
                $actual->head()
            );
        }, [
            'examples' => [
                'after 2021-04-01' => [Carbon::create(2021, 4)->startOfMonth()->startOfDay()],
                'before 2021-10-01' => [Carbon::create(2021, 9)->endOfMonth()->startOfDay()],
            ],
        ]);
        $this->should('set 1 on unitScore and totalScore properties when score under 500', function (): void {
            $report = $this->report->copy(['providedIn' => Carbon::create(2021, 4)->endOfMonth()]);

            $actual = $this->interactor->handle(
                $report,
                $this->dictionaryEntries,
                500,
            );

            $this->assertCount(1, $actual);
            $this->assertModelStrictEquals(
                new LtcsBillingServiceDetail(
                    userId: $report->userId,
                    disposition: LtcsBillingServiceDetailDisposition::result(),
                    providedOn: $report->providedIn,
                    serviceCode: $this->entry->serviceCode,
                    serviceCodeCategory: $this->entry->category,
                    buildingSubtraction: LtcsBuildingSubtraction::none(),
                    noteRequirement: LtcsNoteRequirement::none(),
                    isAddition: true,
                    isLimited: false,
                    durationMinutes: 0,
                    unitScore: 1,
                    count: 1,
                    wholeScore: 1,
                    maxBenefitQuotaExcessScore: 0,
                    maxBenefitExcessScore: 0,
                    totalScore: 1,
                ),
                $actual->head()
            );
        });
        $this->should('return empty when score is 0', function (): void {
            $report = $this->report->copy(['providedIn' => Carbon::create(2021, 4)->endOfMonth()]);

            $actual = $this->interactor->handle(
                $report,
                $this->dictionaryEntries,
                0,
            );

            $this->assertCount(0, $actual);
        });
        $this->should(
            'throw SetupException when Entry of Covid19PandemicSpecialAddition is not found',
            function (): void {
                $report = $this->report->copy(['providedIn' => Carbon::create(2021, 4)->endOfMonth()]);
                $this->assertThrows(SetupException::class, function () use ($report): void {
                    $this->interactor->handle($report, Seq::empty(), 500);
                });
            }
        );
    }
}

<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Domain\ProvisionReport;

use Domain\Billing\LtcsBillingServiceDetail;
use Domain\Billing\LtcsBillingServiceDetailDisposition;
use Domain\Common\Carbon;
use Domain\Common\Decimal;
use Domain\Office\Office;
use Domain\Office\OfficeLtcsHomeVisitLongTermCareService;
use Domain\ProvisionReport\LtcsBuildingSubtraction;
use Domain\ProvisionReport\LtcsProvisionReport;
use Domain\ProvisionReport\LtcsProvisionReportOverScore;
use Domain\ProvisionReport\LtcsProvisionReportSheetAppendixEntry;
use Domain\ServiceCode\ServiceCode;
use Domain\ServiceCodeDictionary\LtcsHomeVisitLongTermCareDictionaryEntry;
use Domain\ServiceCodeDictionary\LtcsNoteRequirement;
use Domain\ServiceCodeDictionary\LtcsServiceCodeCategory;
use Domain\User\User;
use ScalikePHP\Seq;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Test;

/**
 * {@link \Domain\ProvisionReport\LtcsProvisionReportSheetAppendixEntry} のテスト.
 */
final class LtcsProvisionReportSheetAppendixEntryTest extends Test
{
    use ExamplesConsumer;
    use MatchesSnapshots;
    use UnitSupport;

    private User $user;
    private Office $office;
    private LtcsProvisionReport $report;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachSpec(function (LtcsProvisionReportSheetAppendixEntryTest $self): void {
            $self->user = $self->examples->users[16];
            $self->office = $self->examples->offices[0]->copy([
                'ltcsHomeVisitLongTermCareService' => OfficeLtcsHomeVisitLongTermCareService::create([
                    'ltcsAreaGradeId' => $self->examples->ltcsAreaGrades[4]->id,
                    'code' => '1370406140',
                    'openedOn' => Carbon::now()->startOfDay(),
                    'designationExpiredOn' => Carbon::now()->endOfDay(),
                ]),
            ]);
            $self->report = $self->examples->ltcsProvisionReports[0]->copy(
                [
                    'plan' => new LtcsProvisionReportOverScore(
                        maxBenefitExcessScore: 0,
                        maxBenefitQuotaExcessScore: 0,
                    ),
                    'result' => new LtcsProvisionReportOverScore(
                        maxBenefitExcessScore: 0,
                        maxBenefitQuotaExcessScore: 0,
                    ),
                ]
            );
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
        $this->should('return an instance', function (): void {
            $serviceCodeMap = Seq::fromArray($this->examples->ltcsHomeVisitLongTermCareDictionaryEntries)
                ->toMap(fn (LtcsHomeVisitLongTermCareDictionaryEntry $x): string => $x->serviceCode->toString())
                ->mapValues(fn (LtcsHomeVisitLongTermCareDictionaryEntry $x): string => $x->name);
            $serviceDetail = new LtcsBillingServiceDetail(
                userId: $this->user->id,
                disposition: LtcsBillingServiceDetailDisposition::result(),
                providedOn: Carbon::now()->startOfDay(),
                serviceCode: ServiceCode::fromString('112444'),
                serviceCodeCategory: LtcsServiceCodeCategory::housework(),
                buildingSubtraction: LtcsBuildingSubtraction::none(),
                noteRequirement: LtcsNoteRequirement::none(),
                isAddition: false,
                isLimited: true,
                durationMinutes: 310,
                unitScore: 1284,
                count: 1,
                wholeScore: 1284,
                maxBenefitQuotaExcessScore: 0,
                maxBenefitExcessScore: 0,
                totalScore: 1284,
            );
            $actual = LtcsProvisionReportSheetAppendixEntry::from(
                80,
                Decimal::fromInt(11_400),
                $this->office,
                $serviceCodeMap,
                Seq::from($serviceDetail),
                $this->report->result->maxBenefitQuotaExcessScore,
                $this->report->result->maxBenefitExcessScore,
            );
            $this->assertMatchesModelSnapshot($actual);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_computeTotal(): void
    {
        $this->should('return an instance', function (): void {
            $actual = LtcsProvisionReportSheetAppendixEntry::computeTotal(
                Seq::from($this->examples->ltcsProvisionReportSheetAppendixEntry[0])
            );
            $this->assertMatchesModelSnapshot($actual);
        });
    }

    /**
     * テスト対象のインスタンスを生成する.
     *
     * @param array $attrs
     * @return \Domain\ProvisionReport\LtcsProvisionReportSheetAppendixEntry
     */
    private function createInstance(array $attrs = []): LtcsProvisionReportSheetAppendixEntry
    {
        $x = new LtcsProvisionReportSheetAppendixEntry(
            officeName: '事業所名',
            officeCode: '0123456789',
            serviceName: '身体介護1',
            serviceCode: '111111',
            unitScore: 250,
            count: 2,
            wholeScore: 500,
            maxBenefitQuotaExcessScore: 20,
            maxBenefitExcessScore: 40,
            unitCost: Decimal::fromInt(11_400),
            benefitRate: 80,
        );
        return $x->copy($attrs);
    }
}

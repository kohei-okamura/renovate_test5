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
use Domain\LtcsInsCard\LtcsInsCard;
use Domain\LtcsInsCard\LtcsLevel;
use Domain\Office\Office;
use Domain\Office\OfficeLtcsHomeVisitLongTermCareService;
use Domain\ProvisionReport\LtcsBuildingSubtraction;
use Domain\ProvisionReport\LtcsProvisionReport;
use Domain\ProvisionReport\LtcsProvisionReportOverScore;
use Domain\ProvisionReport\LtcsProvisionReportSheetAppendix;
use Domain\ServiceCode\ServiceCode;
use Domain\ServiceCodeDictionary\LtcsHomeVisitLongTermCareDictionaryEntry;
use Domain\ServiceCodeDictionary\LtcsNoteRequirement;
use Domain\ServiceCodeDictionary\LtcsServiceCodeCategory;
use Domain\User\User;
use ScalikePHP\Map;
use ScalikePHP\Option;
use ScalikePHP\Seq;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Test;

/**
 * {@link \Domain\ProvisionReport\LtcsProvisionReportSheetAppendix} のテスト.
 */
final class LtcsProvisionReportSheetAppendixTest extends Test
{
    use CarbonMixin;
    use ExamplesConsumer;
    use MatchesSnapshots;
    use UnitSupport;

    private LtcsInsCard $ltcsInsCard;
    private User $user;
    private Office $office;
    private LtcsProvisionReport $report;
    private Map $serviceCodeMap;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachTest(function (self $self): void {
            $self->ltcsInsCard = $self->examples->ltcsInsCards[0]->copy([
                'ltcsLevel' => LtcsLevel::careLevel3(),
                'copayRate' => 10,
            ]);
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
            $self->serviceCodeMap = Seq::fromArray($self->examples->ltcsHomeVisitLongTermCareDictionaryEntries)
                ->toMap(fn (LtcsHomeVisitLongTermCareDictionaryEntry $x): string => $x->serviceCode->toString())
                ->mapValues(fn (LtcsHomeVisitLongTermCareDictionaryEntry $x): string => $x->name);
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
            $serviceDetails = Seq::from(
                $this->ltcsBillingServiceDetail(),
                // 身9生3・2人・深・Ⅰ
                $this->ltcsBillingServiceDetail([
                    'serviceCode' => ServiceCode::fromString('112444'),
                    'durationMinutes' => 310,
                    'unitScore' => 1284,
                    'wholeScore' => 1284,
                ]),
                // 身9生3・2人・深・Ⅰ
                $this->ltcsBillingServiceDetail([
                    'serviceCode' => ServiceCode::fromString('112444'),
                    'durationMinutes' => 310,
                    'unitScore' => 1443,
                    'wholeScore' => 1443,
                ]),
                // 身9生3・2人・深・Ⅰ
                $this->ltcsBillingServiceDetail([
                    'serviceCode' => ServiceCode::fromString('112444'),
                    'durationMinutes' => 310,
                    'unitScore' => 1284,
                    'wholeScore' => 1284,
                ]),
                // 訪問介護処遇改善加算Ⅰ
                $this->ltcsBillingServiceDetail([
                    'serviceCode' => ServiceCode::fromString('116275'),
                    'isAddition' => true,
                    'isLimited' => false,
                    'durationMinutes' => 0,
                    'unitScore' => 584,
                    'wholeScore' => 584,
                ]),
            );

            $actual = LtcsProvisionReportSheetAppendix::from(
                report: $this->report,
                insCardAtFirstOfMonth: Option::from($this->ltcsInsCard),
                insCardAtLastOfMonth: $this->ltcsInsCard,
                office: $this->office,
                user: $this->user,
                serviceDetails: $serviceDetails,
                insuranceClaimAmount: 0,
                subsidyClaimAmount: 0,
                copayAmount: 0,
                unitCost: $this->examples->ltcsAreaGradeFees[0]->fee,
                serviceCodeMap: $this->serviceCodeMap
            );

            $this->assertMatchesModelSnapshot($actual);
        });

        $this->should('return an instance when ltcs ins card is updated in the month', function (): void {
            $insCardAtLastOfMonth = $this->examples->ltcsInsCards[1]->copy([
                'ltcsLevel' => LtcsLevel::careLevel4(),
                'copayRate' => 20,
            ]);
            $serviceDetails = Seq::from(
                $this->ltcsBillingServiceDetail(),
                // 身9生3・2人・深・Ⅰ
                $this->ltcsBillingServiceDetail([
                    'serviceCode' => ServiceCode::fromString('112444'),
                    'durationMinutes' => 310,
                    'unitScore' => 1284,
                    'wholeScore' => 1284,
                ]),
                // 身9生3・2人・深・Ⅰ
                $this->ltcsBillingServiceDetail([
                    'serviceCode' => ServiceCode::fromString('112444'),
                    'durationMinutes' => 310,
                    'unitScore' => 1443,
                    'wholeScore' => 1443,
                ]),
                // 身9生3・2人・深・Ⅰ
                $this->ltcsBillingServiceDetail([
                    'serviceCode' => ServiceCode::fromString('112444'),
                    'durationMinutes' => 310,
                    'unitScore' => 1284,
                    'wholeScore' => 1284,
                ]),
                // 訪問介護処遇改善加算Ⅰ
                $this->ltcsBillingServiceDetail([
                    'serviceCode' => ServiceCode::fromString('116275'),
                    'isAddition' => true,
                    'isLimited' => false,
                    'durationMinutes' => 0,
                    'unitScore' => 584,
                    'wholeScore' => 584,
                ]),
            );

            $actual = LtcsProvisionReportSheetAppendix::from(
                report: $this->report,
                insCardAtFirstOfMonth: Option::from($this->ltcsInsCard),
                insCardAtLastOfMonth: $insCardAtLastOfMonth,
                office: $this->office,
                user: $this->user,
                serviceDetails: $serviceDetails,
                insuranceClaimAmount: 0,
                subsidyClaimAmount: 0,
                copayAmount: 0,
                unitCost: $this->examples->ltcsAreaGradeFees[0]->fee,
                serviceCodeMap: $this->serviceCodeMap
            );

            $this->assertMatchesModelSnapshot($actual);
        });

        $this->should('return an instance not include total', function (): void {
            $serviceDetails = Seq::from(
                $this->ltcsBillingServiceDetail()
            );

            $actual = LtcsProvisionReportSheetAppendix::from(
                report: $this->report,
                insCardAtFirstOfMonth: Option::from($this->ltcsInsCard),
                insCardAtLastOfMonth: $this->ltcsInsCard,
                office: $this->office,
                user: $this->user,
                serviceDetails: $serviceDetails,
                insuranceClaimAmount: 0,
                subsidyClaimAmount: 0,
                copayAmount: 0,
                unitCost: $this->examples->ltcsAreaGradeFees[0]->fee,
                serviceCodeMap: $this->serviceCodeMap
            );

            $this->assertMatchesModelSnapshot($actual);
        });

        $this->should('return an instance with mask', function (): void {
            $serviceDetails = Seq::from(
                $this->ltcsBillingServiceDetail(),
                // 訪問介護処遇改善加算Ⅰ
                $this->ltcsBillingServiceDetail([
                    'serviceCode' => ServiceCode::fromString('116275'),
                    'isAddition' => true,
                    'isLimited' => false,
                    'durationMinutes' => 0,
                    'unitScore' => 210,
                    'wholeScore' => 210,
                    'totalScore' => 210,
                ]),
                // 身9生3・2人・深・Ⅰ
                $this->ltcsBillingServiceDetail([
                    'serviceCode' => ServiceCode::fromString('112444'),
                    'durationMinutes' => 310,
                    'unitScore' => 1284,
                    'wholeScore' => 1284,
                    'totalScore' => 1284,
                ]),
            );

            $actual = LtcsProvisionReportSheetAppendix::from(
                report: $this->report,
                insCardAtFirstOfMonth: Option::from($this->ltcsInsCard),
                insCardAtLastOfMonth: $this->ltcsInsCard,
                office: $this->office,
                user: $this->user,
                serviceDetails: $serviceDetails,
                insuranceClaimAmount: 0,
                subsidyClaimAmount: 0,
                copayAmount: 0,
                unitCost: $this->examples->ltcsAreaGradeFees[0]->fee,
                serviceCodeMap: $this->serviceCodeMap
            );

            $this->assertMatchesModelSnapshot($actual);
        });

        $this->should('return an instance when result is greater than or equal to 0', function (): void {
            $report = $this->examples->ltcsProvisionReports[0]->copy([
                'plan' => new LtcsProvisionReportOverScore(
                    maxBenefitExcessScore: 0,
                    maxBenefitQuotaExcessScore: 0,
                ),
                'result' => new LtcsProvisionReportOverScore(
                    maxBenefitExcessScore: 100,
                    maxBenefitQuotaExcessScore: 300,
                ),
            ]);
            $serviceDetails = Seq::from(
                $this->ltcsBillingServiceDetail(),
                // 身9生3・2人・深・Ⅰ
                $this->ltcsBillingServiceDetail([
                    'serviceCode' => ServiceCode::fromString('112444'),
                    'durationMinutes' => 310,
                    'totalScore' => 1284,
                    'wholeScore' => 1284,
                    'unitScore' => 1284,
                ]),
                // 身9生3・2人・深・Ⅰ
                $this->ltcsBillingServiceDetail([
                    'serviceCode' => ServiceCode::fromString('112444'),
                    'durationMinutes' => 310,
                    'totalScore' => 1443,
                    'wholeScore' => 1443,
                    'unitScore' => 1443,
                ]),
                // 身9生3・2人・深・Ⅰ
                $this->ltcsBillingServiceDetail([
                    'serviceCode' => ServiceCode::fromString('112444'),
                    'durationMinutes' => 310,
                    'totalScore' => 1284,
                    'wholeScore' => 1284,
                    'unitScore' => 1284,
                ]),
                // 訪問介護処遇改善加算Ⅰ
                $this->ltcsBillingServiceDetail([
                    'serviceCode' => ServiceCode::fromString('116275'),
                    'isAddition' => true,
                    'isLimited' => false,
                    'durationMinutes' => 0,
                    'totalScore' => 495,
                    'wholeScore' => 550,
                    'unitScore' => 495,
                ]),
            );

            $actual = LtcsProvisionReportSheetAppendix::from(
                report: $report,
                insCardAtFirstOfMonth: Option::from($this->ltcsInsCard),
                insCardAtLastOfMonth: $this->ltcsInsCard,
                office: $this->office,
                user: $this->user,
                serviceDetails: $serviceDetails,
                insuranceClaimAmount: 0,
                subsidyClaimAmount: 0,
                copayAmount: 0,
                unitCost: $this->examples->ltcsAreaGradeFees[0]->fee,
                serviceCodeMap: $this->serviceCodeMap
            );

            $this->assertMatchesModelSnapshot($actual);
        });
    }

    /**
     * テスト用のサービス詳細を生成する.
     *
     * @param array $overwrites
     * @return \Domain\Billing\LtcsBillingServiceDetail
     */
    private function ltcsBillingServiceDetail(array $overwrites = []): LtcsBillingServiceDetail
    {
        $x = new LtcsBillingServiceDetail(
            userId: $this->user->id,
            disposition: LtcsBillingServiceDetailDisposition::result(),
            providedOn: Carbon::now()->startOfDay(),
            serviceCode: ServiceCode::fromString('111111'),
            serviceCodeCategory: LtcsServiceCodeCategory::housework(),
            buildingSubtraction: LtcsBuildingSubtraction::none(),
            noteRequirement: LtcsNoteRequirement::none(),
            isAddition: false,
            isLimited: true,
            durationMinutes: 30,
            unitScore: 250,
            count: 1,
            wholeScore: 250,
            maxBenefitQuotaExcessScore: 0,
            maxBenefitExcessScore: 0,
            totalScore: 250,
        );
        return $x->copy($overwrites);
    }

    /**
     * テスト対象のインスタンスを生成する.
     *
     * @param array $attrs
     * @return \Domain\ProvisionReport\LtcsProvisionReportSheetAppendix
     */
    private function createInstance(array $attrs = []): LtcsProvisionReportSheetAppendix
    {
        $x = new LtcsProvisionReportSheetAppendix(
            providedIn: Carbon::now(),
            insNumber: '123456789',
            userName: '名前',
            unmanagedEntries: Seq::from($this->examples->ltcsProvisionReportSheetAppendixEntry[1]),
            managedEntries: Seq::from($this->examples->ltcsProvisionReportSheetAppendixEntry[0]),
            maxBenefit: 36217,
            insuranceClaimAmount: 29373,
            subsidyClaimAmount: 0,
            copayAmount: 10000,
            unitCost: Decimal::fromInt(11_400),
        );
        return $x->copy($attrs);
    }
}

<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Domain\ProvisionReport;

use Domain\Billing\LtcsBillingServiceDetail;
use Domain\Billing\LtcsBillingServiceDetailDisposition;
use Domain\Common\Carbon;
use Domain\Common\Sex;
use Domain\Common\StructuredName;
use Domain\Common\TimeRange;
use Domain\LtcsInsCard\LtcsInsCard;
use Domain\LtcsInsCard\LtcsInsCardStatus;
use Domain\LtcsInsCard\LtcsLevel;
use Domain\Office\Office;
use Domain\Office\OfficeLtcsHomeVisitLongTermCareService;
use Domain\ProvisionReport\LtcsBuildingSubtraction;
use Domain\ProvisionReport\LtcsProvisionReportEntry;
use Domain\ProvisionReport\LtcsProvisionReportSheetPdf;
use Domain\ServiceCode\ServiceCode;
use Domain\ServiceCodeDictionary\LtcsHomeVisitLongTermCareDictionaryEntry;
use Domain\ServiceCodeDictionary\LtcsNoteRequirement;
use Domain\ServiceCodeDictionary\LtcsServiceCodeCategory;
use Domain\User\User;
use ScalikePHP\Option;
use ScalikePHP\Seq;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;

/**
 * {@link \Domain\ProvisionReport\LtcsProvisionReportSheetPdf} のテスト.
 */
final class LtcsProvisionReportSheetPdfTest extends Test
{
    use CarbonMixin;
    use ExamplesConsumer;
    use MatchesSnapshots;
    use MockeryMixin;
    use UnitSupport;

    private LtcsInsCard $ltcsInsCard;
    private User $user;
    private Office $office;
    private Office $carePlanAuthorOffice;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachSpec(function (self $self): void {
            $self->ltcsInsCard = $self->examples->ltcsInsCards[0]->copy([
                'status' => LtcsInsCardStatus::approved(),
                'ltcsLevel' => LtcsLevel::careLevel3(),
                'insNumber' => '465879',
                'insurerNumber' => '789455',
                'insurerName' => '保険者証名前',
                'careManagerName' => 'ケアマネの名前',
                'copayRate' => 10,
                'activatedOn' => Carbon::now(),
                'deactivatedOn' => Carbon::now()->addMonth(),
            ]);
            $self->user = $self->examples->users[16]->copy([
                'name' => new StructuredName(
                    familyName: '成歩堂',
                    givenName: '龍ノ介',
                    phoneticFamilyName: 'ナルホドウ',
                    phoneticGivenName: 'リュウノスケ',
                ),
            ]);
            $self->office = $self->examples->offices[0]->copy([
                'ltcsHomeVisitLongTermCareService' => OfficeLtcsHomeVisitLongTermCareService::create([
                    'ltcsAreaGradeId' => $self->examples->ltcsAreaGrades[4]->id,
                    'code' => '1370406140',
                    'openedOn' => Carbon::now()->startOfDay(),
                    'designationExpiredOn' => Carbon::now()->endOfDay(),
                ]),
            ]);
            $self->carePlanAuthorOffice = $self->examples->offices[22]->copy([
                'name' => 'ケアプラン作った事業所',
            ]);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_create(): void
    {
        $this->should('return an instance', function () {
            $actual = $this->createInstance();
            $this->assertMatchesModelSnapshot($actual);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_json(): void
    {
        $this->should('changes to JSON encode results', function (): void {
            $actual = $this->createInstance();
            $this->assertMatchesJsonSnapshot($actual);
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
                $this->ltcsBillingServiceDetail([
                    'serviceCode' => ServiceCode::fromString('116275'),
                    'isAddition' => true,
                    'isLimited' => false,
                    'durationMinutes' => 0,
                    'unitScore' => 34,
                ]),
                $this->ltcsBillingServiceDetail([
                    'serviceCode' => ServiceCode::fromString('114001'),
                    'serviceCodeCategory' => LtcsServiceCodeCategory::firstTimeAddition(),
                    'isAddition' => true,
                    'isLimited' => true,
                    'durationMinutes' => 0,
                    'unitScore' => 200,
                ]),
                $this->ltcsBillingServiceDetail([
                    'serviceCode' => ServiceCode::fromString('112444'),
                    'durationMinutes' => 310,
                    'unitScore' => 1284,
                ]),
            );
            $serviceCodeMap = Seq::fromArray($this->examples->ltcsHomeVisitLongTermCareDictionaryEntries)
                ->toMap(fn (LtcsHomeVisitLongTermCareDictionaryEntry $x): string => $x->serviceCode->toString());
            $actual = LtcsProvisionReportSheetPdf::from(
                Option::from($this->ltcsInsCard),
                $this->ltcsInsCard,
                Seq::empty(),
                $serviceDetails,
                $this->user,
                Carbon::now(),
                $this->examples->ltcsProvisionReports[0]->copy([
                    'entries' => Seq::fromArray($this->examples->ltcsProvisionReports[0]->entries)
                        ->map(fn (LtcsProvisionReportEntry $x) => $x->copy([
                            'plans' => [
                                Carbon::parse('2020-10-09'),
                                Carbon::parse('2020-10-17'),
                                Carbon::parse('2020-10-26'),
                            ],
                            'results' => [
                                Carbon::parse('2020-10-09'),
                                Carbon::parse('2020-10-17'),
                                Carbon::parse('2020-10-26'),
                            ],
                        ]))
                        ->toArray(),
                ]),
                $serviceCodeMap,
                $this->office,
                Option::from($this->carePlanAuthorOffice)
            );
            $this->assertMatchesModelSnapshot($actual);
        });
        $this->should('return an instance when ltcs ins card is updated in the month', function (): void {
            $serviceDetails = Seq::from(
                $this->ltcsBillingServiceDetail([
                    'serviceCode' => ServiceCode::fromString('116275'),
                    'isAddition' => true,
                    'isLimited' => false,
                    'durationMinutes' => 0,
                    'unitScore' => 34,
                ]),
                $this->ltcsBillingServiceDetail([
                    'serviceCode' => ServiceCode::fromString('114001'),
                    'serviceCodeCategory' => LtcsServiceCodeCategory::firstTimeAddition(),
                    'isAddition' => true,
                    'isLimited' => true,
                    'durationMinutes' => 0,
                    'unitScore' => 200,
                ]),
                $this->ltcsBillingServiceDetail([
                    'serviceCode' => ServiceCode::fromString('112444'),
                    'durationMinutes' => 310,
                    'unitScore' => 1284,
                ]),
            );
            $serviceCodeMap = Seq::fromArray($this->examples->ltcsHomeVisitLongTermCareDictionaryEntries)
                ->toMap(fn (LtcsHomeVisitLongTermCareDictionaryEntry $x): string => $x->serviceCode->toString());
            $actual = LtcsProvisionReportSheetPdf::from(
                Option::from($this->ltcsInsCard),
                $this->examples->ltcsInsCards[1]->copy([
                    'status' => LtcsInsCardStatus::applied(),
                    'ltcsLevel' => LtcsLevel::careLevel2(),
                    'insNumber' => '222222',
                    'insurerNumber' => '222222',
                    'insurerName' => '変更後の後保険者証名前',
                    'careManagerName' => '変更後のケアマネの名前',
                    'copayRate' => 20,
                    'activatedOn' => Carbon::now()->addYear(),
                    'deactivatedOn' => Carbon::now()->addYears(2),
                    'effectivatedOn' => Carbon::now()->addMonths(11),
                ]),
                Seq::empty(),
                $serviceDetails,
                $this->user,
                Carbon::now(),
                $this->examples->ltcsProvisionReports[0]->copy([
                    'entries' => Seq::fromArray($this->examples->ltcsProvisionReports[0]->entries)
                        ->map(fn (LtcsProvisionReportEntry $x) => $x->copy([
                            'plans' => [
                                Carbon::parse('2020-10-09'),
                                Carbon::parse('2020-10-17'),
                                Carbon::parse('2020-10-26'),
                            ],
                            'results' => [
                                Carbon::parse('2020-10-09'),
                                Carbon::parse('2020-10-17'),
                                Carbon::parse('2020-10-26'),
                            ],
                        ]))
                        ->toArray(),
                ]),
                $serviceCodeMap,
                $this->office,
                Option::none()
            );
            $this->assertMatchesModelSnapshot($actual);
        });
        $this->should('return an instance with mask', function (): void {
            $serviceDetails = Seq::from(
                $this->ltcsBillingServiceDetail([
                    'serviceCode' => ServiceCode::fromString('116275'),
                    'isAddition' => true,
                    'isLimited' => false,
                    'durationMinutes' => 0,
                    'unitScore' => 34,
                ]),
                $this->ltcsBillingServiceDetail([
                    'serviceCode' => ServiceCode::fromString('114001'),
                    'serviceCodeCategory' => LtcsServiceCodeCategory::firstTimeAddition(),
                    'isAddition' => true,
                    'isLimited' => true,
                    'durationMinutes' => 0,
                    'unitScore' => 200,
                ]),
                $this->ltcsBillingServiceDetail([
                    'serviceCode' => ServiceCode::fromString('112444'),
                    'durationMinutes' => 310,
                    'unitScore' => 1284,
                ]),
            );
            $serviceCodeMap = Seq::fromArray($this->examples->ltcsHomeVisitLongTermCareDictionaryEntries)
                ->toMap(fn (LtcsHomeVisitLongTermCareDictionaryEntry $x): string => $x->serviceCode->toString());
            $actual = LtcsProvisionReportSheetPdf::from(
                Option::from($this->ltcsInsCard),
                $this->ltcsInsCard,
                Seq::empty(),
                $serviceDetails,
                $this->user,
                Carbon::now(),
                $this->examples->ltcsProvisionReports[0]->copy([
                    'entries' => Seq::fromArray($this->examples->ltcsProvisionReports[0]->entries)
                        ->map(fn (LtcsProvisionReportEntry $x) => $x->copy([
                            'plans' => [
                                Carbon::parse('2020-10-09'),
                                Carbon::parse('2020-10-17'),
                                Carbon::parse('2020-10-26'),
                            ],
                            'results' => [
                                Carbon::parse('2020-10-09'),
                                Carbon::parse('2020-10-17'),
                                Carbon::parse('2020-10-26'),
                            ],
                        ]))
                        ->toArray(),
                ]),
                $serviceCodeMap,
                $this->office,
                Option::none(),
                true,
                true
            );
            $this->assertMatchesModelSnapshot($actual);
        });
        $this->should('return an instance when ltcs ins card is updated in the month with mask', function (): void {
            $serviceDetails = Seq::from(
                $this->ltcsBillingServiceDetail([
                    'serviceCode' => ServiceCode::fromString('116275'),
                    'isAddition' => true,
                    'isLimited' => false,
                    'durationMinutes' => 0,
                    'unitScore' => 34,
                ]),
                $this->ltcsBillingServiceDetail([
                    'serviceCode' => ServiceCode::fromString('114001'),
                    'serviceCodeCategory' => LtcsServiceCodeCategory::firstTimeAddition(),
                    'isAddition' => true,
                    'isLimited' => true,
                    'durationMinutes' => 0,
                    'unitScore' => 200,
                ]),
                $this->ltcsBillingServiceDetail([
                    'serviceCode' => ServiceCode::fromString('112444'),
                    'durationMinutes' => 310,
                    'unitScore' => 1284,
                ]),
            );
            $serviceCodeMap = Seq::fromArray($this->examples->ltcsHomeVisitLongTermCareDictionaryEntries)
                ->toMap(fn (LtcsHomeVisitLongTermCareDictionaryEntry $x): string => $x->serviceCode->toString());
            $actual = LtcsProvisionReportSheetPdf::from(
                Option::from($this->ltcsInsCard),
                $this->examples->ltcsInsCards[1]->copy([
                    'status' => LtcsInsCardStatus::applied(),
                    'ltcsLevel' => LtcsLevel::careLevel2(),
                    'insNumber' => '222222',
                    'insurerNumber' => '222222',
                    'insurerName' => '変更後の後保険者証名前',
                    'careManagerName' => '変更後のケアマネの名前',
                    'copayRate' => 20,
                    'activatedOn' => Carbon::now()->addYear(),
                    'deactivatedOn' => Carbon::now()->addYears(2),
                    'effectivatedOn' => Carbon::now()->addMonths(11),
                ]),
                Seq::empty(),
                $serviceDetails,
                $this->user,
                Carbon::now(),
                $this->examples->ltcsProvisionReports[0]->copy([
                    'entries' => Seq::fromArray($this->examples->ltcsProvisionReports[0]->entries)
                        ->map(fn (LtcsProvisionReportEntry $x) => $x->copy([
                            'plans' => [
                                Carbon::parse('2020-10-09'),
                                Carbon::parse('2020-10-17'),
                                Carbon::parse('2020-10-26'),
                            ],
                            'results' => [
                                Carbon::parse('2020-10-09'),
                                Carbon::parse('2020-10-17'),
                                Carbon::parse('2020-10-26'),
                            ],
                        ]))
                        ->toArray(),
                ]),
                $serviceCodeMap,
                $this->office,
                Option::from($this->carePlanAuthorOffice),
                true,
                true,
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
            providedOn: Carbon::now(),
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
     * @return \Domain\ProvisionReport\LtcsProvisionReportSheetPdf
     */
    private function createInstance(array $attrs = []): LtcsProvisionReportSheetPdf
    {
        $values = [
            'status' => LtcsInsCardStatus::applied(),
            'providedIn' => Carbon::now(),
            'insurerNumber' => '123456',
            'insurerName' => '新宿区',
            'carePlanAuthorOfficeName' => 'テストケア',
            'careManagerName' => 'テスト太郎',
            'carePlanAuthorOfficeTel' => '03-1234-5678',
            'createdOn' => Carbon::now()->toJapaneseDate(),
            'insNumber' => '0123456789',
            'phoneticDisplayName' => 'テストナマエ',
            'displayName' => 'テスト名前',
            'birthday' => Carbon::parse('2001-10-11'),
            'sex' => Sex::female(),
            'ltcsLevel' => LtcsLevel::resolve(LtcsLevel::careLevel2()),
            'updatedLtcsLevel' => '',
            'ltcsLevelUpdatedOn' => '',
            'maxBenefit' => 36127,
            'activatedOn' => Carbon::now()->toJapaneseYearMonth(),
            'deactivatedOn' => Carbon::now()->toJapaneseYearMonth(),
            'entries' => [
                [
                    'slot' => TimeRange::create([
                        'start' => '08:00',
                        'end' => '16:00',
                    ]),
                    'serviceName' => '仮のサービス内容',
                    'officeName' => '土屋訪問介護事業所',
                    'plans' => [0, 1, 0, 1],
                    'results' => [0, 1, 0, 1],
                    'plansCount' => 2,
                    'resultsCount' => 2,
                ],
            ],
            'currentPageCount' => 1,
            'maxPageCount' => 3,
        ];
        return LtcsProvisionReportSheetPdf::create($attrs + $values);
    }
}

<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Billing\LtcsBilling;

use BillingTester;
use Codeception\Util\HttpCode;
use Domain\Billing\LtcsBilling;
use Domain\Billing\LtcsBillingBundle;
use Domain\Billing\LtcsBillingInvoice;
use Domain\Billing\LtcsBillingOffice;
use Domain\Billing\LtcsBillingServiceDetail;
use Domain\Billing\LtcsBillingServiceDetailDisposition;
use Domain\Billing\LtcsBillingStatement;
use Domain\Billing\LtcsBillingStatementAggregate;
use Domain\Billing\LtcsBillingStatementAggregateInsurance;
use Domain\Billing\LtcsBillingStatementAggregateSubsidy;
use Domain\Billing\LtcsBillingStatementInsurance;
use Domain\Billing\LtcsBillingStatementItem;
use Domain\Billing\LtcsBillingStatementItemSubsidy;
use Domain\Billing\LtcsBillingStatementSubsidy;
use Domain\Billing\LtcsBillingStatus;
use Domain\Billing\LtcsBillingUser;
use Domain\Billing\LtcsCarePlanAuthor;
use Domain\Billing\LtcsExpiredReason;
use Domain\Billing\LtcsServiceDivisionCode;
use Domain\Common\Carbon;
use Domain\Common\Decimal;
use Domain\Common\TimeRange;
use Domain\Office\HomeVisitLongTermCareSpecifiedOfficeAddition;
use Domain\Office\LtcsBaseIncreaseSupportAddition;
use Domain\Office\LtcsOfficeLocationAddition;
use Domain\Office\LtcsSpecifiedTreatmentImprovementAddition;
use Domain\Office\LtcsTreatmentImprovementAddition;
use Domain\Project\LtcsProjectAmount;
use Domain\Project\LtcsProjectAmountCategory;
use Domain\Project\LtcsProjectServiceCategory;
use Domain\ProvisionReport\LtcsBuildingSubtraction;
use Domain\ProvisionReport\LtcsProvisionReport;
use Domain\ProvisionReport\LtcsProvisionReportEntry;
use Domain\ProvisionReport\LtcsProvisionReportOverScore;
use Domain\ProvisionReport\LtcsProvisionReportSheetAppendix;
use Domain\ProvisionReport\LtcsProvisionReportSheetAppendixEntry;
use Domain\ProvisionReport\LtcsProvisionReportStatus;
use Domain\ServiceCode\ServiceCode;
use Domain\ServiceCodeDictionary\LtcsNoteRequirement;
use Domain\ServiceCodeDictionary\LtcsServiceCodeCategory;
use Domain\ServiceCodeDictionary\Timeframe;
use Psr\Log\LogLevel;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;

/**
 * Billing Create のテスト（Ltcs）.
 * POST /ltcs-billings
 */
final class CreateLtcsBillingCest extends CreateLtcsBillingTest
{
    use ExamplesConsumer;

    /**
     * API正常呼び出しテスト.
     *
     * @param BillingTester $I
     * @throws \Codeception\Exception\ModuleException
     */
    public function succeedAPICall(BillingTester $I): void
    {
        $I->wantTo('succeed API call');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);

        $office = $this->examples->offices[0];
        $user = $this->examples->users[0];
        $providedIn = Carbon::create(2021, 2, 1);

        //
        // 予実を準備
        //
        $this->getProvisionReportRepository()->store(LtcsProvisionReport::create([
            'userId' => $user->id,
            'officeId' => $office->id,
            'contractId' => $this->examples->contracts[0]->id,
            'providedIn' => $providedIn,
            'entries' => [
                LtcsProvisionReportEntry::create([
                    'ownExpenseProgramId' => null,
                    'slot' => TimeRange::create(['start' => '08:00', 'end' => '10:00']),
                    'timeframe' => Timeframe::morning(),
                    'category' => LtcsProjectServiceCategory::physicalCare(),
                    'amounts' => [
                        LtcsProjectAmount::create([
                            'category' => LtcsProjectAmountCategory::physicalCare(),
                            'amount' => 120,
                        ]),
                    ],
                    'headcount' => 1,
                    'serviceCode' => ServiceCode::fromString('111412'),
                    'options' => [],
                    'note' => 'ISさん 江東区',
                    'plans' => [
                        Carbon::create(2021, 2, 2),
                        Carbon::create(2021, 2, 9),
                        Carbon::create(2021, 2, 16),
                        Carbon::create(2021, 2, 23),
                    ],
                    'results' => [
                        Carbon::create(2021, 2, 7),
                        Carbon::create(2021, 2, 14),
                        Carbon::create(2021, 2, 21),
                        Carbon::create(2021, 2, 28),
                    ],
                ]),
            ],
            'specifiedOfficeAddition' => HomeVisitLongTermCareSpecifiedOfficeAddition::none(),
            'treatmentImprovementAddition' => LtcsTreatmentImprovementAddition::none(),
            'specifiedTreatmentImprovementAddition' => LtcsSpecifiedTreatmentImprovementAddition::none(),
            'baseIncreaseSupportAddition' => LtcsBaseIncreaseSupportAddition::none(),
            'locationAddition' => LtcsOfficeLocationAddition::none(),
            'plan' => new LtcsProvisionReportOverScore(
                maxBenefitExcessScore: 0,
                maxBenefitQuotaExcessScore: 0,
            ),
            'result' => new LtcsProvisionReportOverScore(
                maxBenefitExcessScore: 0,
                maxBenefitQuotaExcessScore: 0,
            ),
            'status' => LtcsProvisionReportStatus::fixed(),
            'fixedAt' => Carbon::create(2021, 3, 4, 0, 0, 0),
            'createdAt' => Carbon::now(),
            'updatedAt' => Carbon::now(),
        ]));

        $I->sendPOST('ltcs-billings', [
            'officeId' => $office->id,
            'transactedIn' => '2021-03',
        ]);

        $I->seeResponseCodeIs(HttpCode::ACCEPTED);
        $I->seeLogCount(3);
        $I->seeLogMessage(2, LogLevel::INFO, 'ジョブが登録されました', [
            'id' => '*',
            'organizationId' => $staff->organizationId,
            'staffId' => $staff->id,
        ]);
        $I->seeLogMessage(0, LogLevel::INFO, 'ジョブが更新されました', [
            'id' => '*',
            'organizationId' => $staff->organizationId,
            'staffId' => $staff->id,
        ]);
        $I->seeLogMessage(1, LogLevel::INFO, 'ジョブが更新されました', [
            'id' => '*',
            'organizationId' => $staff->organizationId,
            'staffId' => $staff->id,
        ]);

        //
        // 保管された請求を検証
        //

        //
        // 請求（LtcsBilling）
        //
        /** @var \Domain\Billing\LtcsBilling $billing */
        $billing = $this->getBillingFinder()
            ->find([], ['sortBy' => 'id', 'desc' => true, 'itemsPerPage' => 1])
            ->list
            ->head();
        $I->assertModelStrictEquals(
            LtcsBilling::create([
                'id' => $billing->id,
                'organizationId' => $staff->organizationId,
                'office' => LtcsBillingOffice::from($office),
                'transactedIn' => Carbon::create(2021, 3),
                'files' => [],
                'status' => LtcsBillingStatus::checking(),
                'fixedAt' => null,
                'createdAt' => Carbon::now(),
                'updatedAt' => Carbon::now(),
            ]),
            $billing
        );

        //
        // 請求単位（LtcsBillingBundle）
        //
        /** @var \Domain\Billing\LtcsBillingBundle[]|\ScalikePHP\Seq $bundles */
        /** @var \Domain\Billing\LtcsBillingBundle $bundle */
        $bundles = $this->getBundleRepository()->lookupByBillingId($billing->id)->head()[1];
        $bundle = $bundles->head();
        $I->assertCount(1, $bundles);
        $I->assertModelStrictEquals(
            LtcsBillingBundle::create([
                'id' => $bundle->id,
                'billingId' => $billing->id,
                'providedIn' => $providedIn,
                'details' => [
                    $this->serviceDetailForDate(Carbon::create(2021, 2, 7)),
                    $this->serviceDetailForDate(Carbon::create(2021, 2, 14)),
                    $this->serviceDetailForDate(Carbon::create(2021, 2, 21)),
                    $this->serviceDetailForDate(Carbon::create(2021, 2, 28)),
                ],
                'createdAt' => Carbon::now(),
                'updatedAt' => Carbon::now(),
            ], true),
            $bundle
        );

        //
        // 請求書（LtcsBillingInvoice）
        //
        /** @var \Domain\Billing\LtcsBillingInvoice[]|\ScalikePHP\Seq $invoices */
        $invoices = $this->getInvoiceRepository()->lookupByBundleId($bundle->id)->head()[1];
        $invoice = $invoices->head();
        $I->assertCount(1, $invoices);
        $I->assertModelStrictEquals(
            new LtcsBillingInvoice(
                id: $invoice->id,
                billingId: $billing->id,
                bundleId: $bundle->id,
                isSubsidy: false,
                defrayerCategory: null,
                statementCount: 1,
                totalScore: 3300,
                totalFee: 37620,
                insuranceAmount: 26334,
                subsidyAmount: 0,
                copayAmount: 11286,
                createdAt: Carbon::now(),
                updatedAt: Carbon::now(),
            ),
            $invoice
        );

        //
        // 明細書（LtcsBillingStatement）
        //
        $insCard = $this->examples->ltcsInsCards[15];
        /** @var \Domain\Billing\LtcsBillingStatement[]|\ScalikePHP\Seq $statements */
        $statements = $this->getStatementRepository()->lookupByBundleId($bundle->id)->head()[1];
        $statement = $statements->head();
        $I->assertCount(1, $statements);
        $I->assertModelStrictEquals(
            new LtcsBillingStatement(
                id: $statement->id, // ID は実際の値をコピーしておく（差が出ないように）
                billingId: $billing->id,
                bundleId: $bundle->id,
                insurerNumber: $insCard->insurerNumber,
                insurerName: $insCard->insurerName,
                user: new LtcsBillingUser(
                    userId: $user->id,
                    ltcsInsCardId: $insCard->id,
                    insNumber: $insCard->insNumber,
                    name: $user->name,
                    sex: $user->sex,
                    birthday: $user->birthday,
                    ltcsLevel: $insCard->ltcsLevel,
                    activatedOn: $insCard->activatedOn,
                    deactivatedOn: $insCard->deactivatedOn,
                ),
                carePlanAuthor: new LtcsCarePlanAuthor(
                    authorType: $insCard->carePlanAuthorType,
                    officeId: $this->examples->offices[20]->id,
                    code: $this->examples->offices[20]->ltcsCareManagementService->code,
                    name: $this->examples->offices[20]->name,
                ),
                // TODO DEV-4722
                agreedOn: null,
                expiredOn: null,
                expiredReason: LtcsExpiredReason::unspecified(),
                // TODO DEV-4722 ここまで
                insurance: new LtcsBillingStatementInsurance(
                    benefitRate: 100 - $insCard->copayRate,
                    totalScore: 3300,
                    claimAmount: 26334,
                    copayAmount: 11286,
                ),
                subsidies: [
                    LtcsBillingStatementSubsidy::empty(),
                    LtcsBillingStatementSubsidy::empty(),
                    LtcsBillingStatementSubsidy::empty(),
                ],
                items: [
                    new LtcsBillingStatementItem(
                        serviceCode: ServiceCode::fromString('111412'),
                        serviceCodeCategory: LtcsServiceCodeCategory::physicalCare(),
                        unitScore: 825,
                        count: 4,
                        totalScore: 3300,
                        subsidies: [
                            LtcsBillingStatementItemSubsidy::empty(),
                            LtcsBillingStatementItemSubsidy::empty(),
                            LtcsBillingStatementItemSubsidy::empty(),
                        ],
                        note: '',
                    ),
                ],
                aggregates: [
                    new LtcsBillingStatementAggregate(
                        serviceDivisionCode: LtcsServiceDivisionCode::homeVisitLongTermCare(),
                        serviceDays: 4,
                        plannedScore: 3300,
                        managedScore: 3300,
                        unmanagedScore: 0,
                        insurance: new LtcsBillingStatementAggregateInsurance(
                            totalScore: 3300,
                            unitCost: $this->examples->ltcsAreaGradeFees[0]->fee,
                            claimAmount: 26334,
                            copayAmount: 11286,
                        ),
                        subsidies: [
                            LtcsBillingStatementAggregateSubsidy::empty(),
                            LtcsBillingStatementAggregateSubsidy::empty(),
                            LtcsBillingStatementAggregateSubsidy::empty(),
                        ],
                    ),
                ],
                appendix: new LtcsProvisionReportSheetAppendix(
                    providedIn: Carbon::create(2021, 2, 1),
                    insNumber: '0123456789',
                    userName: $user->name->displayName,
                    unmanagedEntries: Seq::empty(),
                    managedEntries: Seq::from(new LtcsProvisionReportSheetAppendixEntry(
                        officeName: $office->name,
                        officeCode: $office->ltcsHomeVisitLongTermCareService->code,
                        serviceName: '身体介護4・夜',
                        serviceCode: '111412',
                        unitScore: 825, // 令和3年2月時点（令和元年4月版）
                        count: 4,
                        wholeScore: 3300,
                        maxBenefitQuotaExcessScore: 0,
                        maxBenefitExcessScore: 0,
                        unitCost: Decimal::fromInt(11_4000),
                        benefitRate: 70,
                    )),
                    maxBenefit: 30938, // 要介護4
                    insuranceClaimAmount: 26334, // 3,300単位 × 11.40円 × 70%
                    subsidyClaimAmount: 0,
                    copayAmount: 11286,
                    unitCost: Decimal::fromInt(11_4000),
                ),
                status: LtcsBillingStatus::ready(),
                fixedAt: null,
                createdAt: Carbon::now(),
                updatedAt: Carbon::now(),
            ),
            $statement
        );
    }

    /**
     * 対象の予実に介護保険サービスの実績が含まれていない場合400が返るテスト.
     *
     * @param BillingTester $I
     * @throws \Codeception\Exception\ModuleException
     * @noinspection PhpUnused
     */
    public function failWithBadRequestWhenProvisionReportsDoNotContainLtcs(BillingTester $I): void
    {
        $I->wantTo('fail with Bad Request when provision reports do not contain ltcs');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);

        $office = $this->examples->offices[0];

        // 予実を準備
        $this->getProvisionReportRepository()->store(LtcsProvisionReport::create([
            'userId' => $this->examples->users[0]->id,
            'officeId' => $office->id,
            'contractId' => $this->examples->contracts[0]->id,
            'providedIn' => Carbon::create(2021, 3),
            'entries' => [
                // 予定のみで実績なし
                LtcsProvisionReportEntry::create([
                    'ownExpenseProgramId' => null,
                    'slot' => TimeRange::create(['start' => '05:00', 'end' => '07:00']),
                    'timeframe' => Timeframe::morning(),
                    'category' => LtcsProjectServiceCategory::physicalCare(),
                    'amounts' => [
                        LtcsProjectAmount::create([
                            'category' => LtcsProjectAmountCategory::physicalCare(),
                            'amount' => 120,
                        ]),
                    ],
                    'headcount' => 1,
                    'serviceCode' => ServiceCode::fromString('111412'),
                    'options' => [],
                    'note' => 'ISさん 江東区',
                    'plans' => [
                        Carbon::create(2021, 3, 2),
                        Carbon::create(2021, 3, 9),
                        Carbon::create(2021, 3, 16),
                        Carbon::create(2021, 3, 23),
                    ],
                    'results' => [],
                ]),
                // 自費
                LtcsProvisionReportEntry::create([
                    'ownExpenseProgramId' => $this->examples->ownExpensePrograms[0]->id,
                    'slot' => TimeRange::create(['start' => '10:00', 'end' => '11:20']),
                    'timeframe' => Timeframe::morning(),
                    'category' => LtcsProjectServiceCategory::ownExpense(),
                    'amounts' => [],
                    'headcount' => 1,
                    'serviceCode' => null,
                    'options' => [],
                    'note' => '備考',
                    'plans' => [
                        Carbon::create(2021, 3, 2),
                        Carbon::create(2021, 3, 9),
                        Carbon::create(2021, 3, 16),
                        Carbon::create(2021, 3, 23),
                    ],
                    'results' => [
                        Carbon::create(2021, 3, 2),
                        Carbon::create(2021, 3, 9),
                        Carbon::create(2021, 3, 16),
                        Carbon::create(2021, 3, 23),
                    ],
                ]),
            ],
            'specifiedOfficeAddition' => HomeVisitLongTermCareSpecifiedOfficeAddition::none(),
            'treatmentImprovementAddition' => LtcsTreatmentImprovementAddition::none(),
            'specifiedTreatmentImprovementAddition' => LtcsSpecifiedTreatmentImprovementAddition::none(),
            'baseIncreaseSupportAddition' => LtcsBaseIncreaseSupportAddition::none(),
            'locationAddition' => LtcsOfficeLocationAddition::none(),
            'plan' => new LtcsProvisionReportOverScore(
                maxBenefitExcessScore: 0,
                maxBenefitQuotaExcessScore: 0,
            ),
            'result' => new LtcsProvisionReportOverScore(
                maxBenefitExcessScore: 0,
                maxBenefitQuotaExcessScore: 0,
            ),
            'status' => LtcsProvisionReportStatus::fixed(),
            'fixedAt' => Carbon::create(2021, 3, 11),
            'createdAt' => Carbon::now(),
            'updatedAt' => Carbon::now(),
        ]));

        $I->sendPOST('ltcs-billings', [
            'officeId' => $this->examples->offices[0]->id,
            'transactedIn' => '2021-04',
        ]);

        $I->seeResponseCodeIs(HttpCode::BAD_REQUEST);

        $I->seeLogCount(0);
        $I->seeResponseContainsJson(['errors' => ['officeId' => ['対象となる予実が存在しません。']]]);
    }

    /**
     * 検証用のサービス詳細を生成する.
     *
     * @param \Domain\Common\Carbon $providedOn
     * @return \Domain\Billing\LtcsBillingServiceDetail
     */
    private function serviceDetailForDate(Carbon $providedOn): LtcsBillingServiceDetail
    {
        return new LtcsBillingServiceDetail(
            userId: $this->examples->users[0]->id,
            disposition: LtcsBillingServiceDetailDisposition::result(),
            providedOn: $providedOn,
            serviceCode: ServiceCode::fromString('111412'),
            serviceCodeCategory: LtcsServiceCodeCategory::physicalCare(),
            buildingSubtraction: LtcsBuildingSubtraction::none(),
            noteRequirement: LtcsNoteRequirement::none(),
            isAddition: false,
            isLimited: true,
            durationMinutes: 120,
            unitScore: 825,
            count: 1,
            wholeScore: 825,
            maxBenefitQuotaExcessScore: 0,
            maxBenefitExcessScore: 0,
            totalScore: 825,
        );
    }
}

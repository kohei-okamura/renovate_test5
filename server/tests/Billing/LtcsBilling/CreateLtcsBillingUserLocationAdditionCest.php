<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
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
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;

/**
 * 介護保険サービス 請求生成テスト.
 * 訪問介護中山間地域等提供加算
 */
final class CreateLtcsBillingUserLocationAdditionCest extends CreateLtcsBillingTest
{
    use ExamplesConsumer;

    /**
     * API正常呼び出しテスト.
     *
     * @param \BillingTester $I
     * @throws \Codeception\Exception\ModuleException
     */
    public function succeedAPICall(BillingTester $I): void
    {
        $I->wantTo('succeed API call');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);

        $office = $this->examples->offices[0];
        $user = $this->examples->users[19];
        $providedIn = Carbon::create(2022, 4, 1);

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
                    'slot' => TimeRange::create(['start' => '06:00', 'end' => '08:00']),
                    'amounts' => [
                        LtcsProjectAmount::create([
                            'category' => LtcsProjectAmountCategory::physicalCare(),
                            'amount' => 120,
                        ]),
                    ],
                    'headcount' => 1,
                    'serviceCode' => ServiceCode::fromString('111412'), // 身体介護4・夜
                    'options' => [],
                    'note' => '',
                    'timeframe' => Timeframe::morning(),
                    'category' => LtcsProjectServiceCategory::physicalCare(),
                    'plans' => [
                        Carbon::create(2022, 4, 1),
                    ],
                    'results' => [
                        Carbon::create(2022, 4, 1),
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
            'fixedAt' => Carbon::create(2022, 5, 1, 0, 0, 0),
            'createdAt' => Carbon::now(),
            'updatedAt' => Carbon::now(),
        ]));

        $I->sendPOST('ltcs-billings', [
            'officeId' => $office->id,
            'transactedIn' => '2022-05',
        ]);

        $I->seeResponseCodeIs(HttpCode::ACCEPTED);
        $I->seeLogCount(3);

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
                'transactedIn' => Carbon::create(2022, 5),
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
                    $this->serviceDetailForDate(Carbon::create(2022, 4, 1)),
                    $this->serviceDetailForDate(Carbon::create(2022, 4, 30))->copy([
                        'serviceCode' => ServiceCode::fromString('118110'), // 訪問介護中山間地域等提供加算
                        'serviceCodeCategory' => LtcsServiceCodeCategory::mountainousAreaAddition(),
                        'durationMinutes' => 0,
                        'unitScore' => 41,
                        'count' => 1,
                        'wholeScore' => 41,
                        'totalScore' => 41,
                        'isAddition' => true,
                        'isLimited' => false,
                        'providedOn' => Carbon::create(2022, 4)->endOfMonth()->startOfDay(),
                    ]),
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
                totalScore: 870,
                totalFee: 9918,
                insuranceAmount: 6942,
                subsidyAmount: 0,
                copayAmount: 2976,
                createdAt: Carbon::now(),
                updatedAt: Carbon::now(),
            ),
            $invoice
        );

        //
        // 明細書（LtcsBillingStatement）
        //
        $insCard = $this->examples->ltcsInsCards[17];
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
                    totalScore: 870,
                    claimAmount: 6942,
                    copayAmount: 2976,
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
                        unitScore: 829,
                        count: 1,
                        totalScore: 829,
                        subsidies: [
                            LtcsBillingStatementItemSubsidy::empty(),
                            LtcsBillingStatementItemSubsidy::empty(),
                            LtcsBillingStatementItemSubsidy::empty(),
                        ],
                        note: '',
                    ),
                    new LtcsBillingStatementItem(
                        serviceCode: ServiceCode::fromString('118110'),
                        serviceCodeCategory: LtcsServiceCodeCategory::mountainousAreaAddition(),
                        unitScore: 41,
                        count: 1,
                        totalScore: 41,
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
                        serviceDays: 1,
                        plannedScore: 829,
                        managedScore: 829,
                        unmanagedScore: 41,
                        insurance: new LtcsBillingStatementAggregateInsurance(
                            totalScore: 870,
                            unitCost: $this->examples->ltcsAreaGradeFees[0]->fee,
                            claimAmount: 6942,
                            copayAmount: 2976,
                        ),
                        subsidies: [
                            LtcsBillingStatementAggregateSubsidy::empty(),
                            LtcsBillingStatementAggregateSubsidy::empty(),
                            LtcsBillingStatementAggregateSubsidy::empty(),
                        ],
                    ),
                ],
                appendix: new LtcsProvisionReportSheetAppendix(
                    providedIn: $providedIn,
                    insNumber: '0123456789',
                    userName: $user->name->displayName,
                    unmanagedEntries: Seq::from(new LtcsProvisionReportSheetAppendixEntry(
                        officeName: $office->name,
                        officeCode: $office->ltcsHomeVisitLongTermCareService->code,
                        serviceName: '訪問介護中山間地域等提供加算',
                        serviceCode: '118110',
                        unitScore: 41, // 829 × 5%（小数点以下四捨五入）
                        count: 1,
                        wholeScore: 41,
                        maxBenefitQuotaExcessScore: 0,
                        maxBenefitExcessScore: 0,
                        unitCost: Decimal::fromInt(11_4000),
                        benefitRate: 70,
                    )),
                    managedEntries: Seq::from(new LtcsProvisionReportSheetAppendixEntry(
                        officeName: $office->name,
                        officeCode: $office->ltcsHomeVisitLongTermCareService->code,
                        serviceName: '身体介護4・夜',
                        serviceCode: '111412',
                        unitScore: 829, // 令和3年4月時点（令和3年4月版）
                        count: 1,
                        wholeScore: 829,
                        maxBenefitQuotaExcessScore: 0,
                        maxBenefitExcessScore: 0,
                        unitCost: Decimal::fromInt(11_4000),
                        benefitRate: 70,
                    )),
                    maxBenefit: 30938, // 要介護4
                    insuranceClaimAmount: 6942, // 870単位 × 11.40円 × 70%
                    subsidyClaimAmount: 0,
                    copayAmount: 2976,
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
     * 検証用のサービス詳細を生成する.
     *
     * @param \Domain\Common\Carbon $providedOn
     * @return \Domain\Billing\LtcsBillingServiceDetail
     */
    private function serviceDetailForDate(Carbon $providedOn): LtcsBillingServiceDetail
    {
        return new LtcsBillingServiceDetail(
            userId: $this->examples->users[19]->id,
            disposition: LtcsBillingServiceDetailDisposition::result(),
            providedOn: $providedOn,
            serviceCode: ServiceCode::fromString('111412'), // 予実の値
            serviceCodeCategory: LtcsServiceCodeCategory::physicalCare(),
            buildingSubtraction: LtcsBuildingSubtraction::none(),
            noteRequirement: LtcsNoteRequirement::none(),
            isAddition: false,
            isLimited: true,
            durationMinutes: 120,
            unitScore: 829,
            count: 1,
            wholeScore: 829,
            maxBenefitQuotaExcessScore: 0,
            maxBenefitExcessScore: 0,
            totalScore: 829,
        );
    }
}

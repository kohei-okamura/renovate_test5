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
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;

/**
 * 介護保険サービス 請求生成テスト.
 * COVID-19 加算
 */
final class CreateLtcsBillingCovid19SimpleCest extends CreateLtcsBillingTest
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
        $user = $this->examples->users[0];
        $providedIn = Carbon::create(2021, 4);

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
                    'serviceCode' => ServiceCode::fromString('111411'), // 身体介護4
                    'options' => [],
                    'note' => '',
                    'plans' => [
                        Carbon::create(2021, 4, 1),
                    ],
                    'results' => [
                        Carbon::create(2021, 4, 1),
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
            'fixedAt' => Carbon::create(2021, 5, 1),
            'createdAt' => Carbon::now(),
            'updatedAt' => Carbon::now(),
        ]));

        $I->sendPOST('ltcs-billings', ['officeId' => $office->id, 'transactedIn' => '2021-05']);

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
                'transactedIn' => Carbon::create(2021, 5),
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
                    $this->serviceDetailForDate(Carbon::create(2021, 4, 1)),
                    $this->serviceDetailForDate(Carbon::create(2021, 4, 30, 0, 0, 0))->copy([
                        'serviceCode' => ServiceCode::fromString('118300'), // COVID19加算
                        'serviceCodeCategory' => LtcsServiceCodeCategory::covid19PandemicSpecialAddition(),
                        'durationMinutes' => 0,
                        'unitScore' => 1,
                        'count' => 1,
                        'totalScore' => 1,
                        'wholeScore' => 1,
                        'isAddition' => true,
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
                totalScore: 664,
                totalFee: 7569,
                insuranceAmount: 5298,
                subsidyAmount: 0,
                copayAmount: 2271,
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
                    totalScore: 664,
                    claimAmount: 5298,
                    copayAmount: 2271,
                ),
                subsidies: [
                    LtcsBillingStatementSubsidy::empty(),
                    LtcsBillingStatementSubsidy::empty(),
                    LtcsBillingStatementSubsidy::empty(),
                ],
                items: [
                    new LtcsBillingStatementItem(
                        serviceCode: ServiceCode::fromString('111411'),
                        serviceCodeCategory: LtcsServiceCodeCategory::physicalCare(),
                        unitScore: 663,
                        count: 1,
                        totalScore: 663,
                        subsidies: [
                            LtcsBillingStatementItemSubsidy::empty(),
                            LtcsBillingStatementItemSubsidy::empty(),
                            LtcsBillingStatementItemSubsidy::empty(),
                        ],
                        note: '',
                    ),
                    new LtcsBillingStatementItem(
                        serviceCode: ServiceCode::fromString('118300'),
                        serviceCodeCategory: LtcsServiceCodeCategory::covid19PandemicSpecialAddition(),
                        unitScore: 1,
                        count: 1,
                        totalScore: 1,
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
                        plannedScore: 664,
                        managedScore: 664,
                        unmanagedScore: 0,
                        insurance: new LtcsBillingStatementAggregateInsurance(
                            totalScore: 664,
                            unitCost: $this->examples->ltcsAreaGradeFees[0]->fee,
                            claimAmount: 5298,
                            copayAmount: 2271,
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
                    unmanagedEntries: Seq::empty(),
                    managedEntries: Seq::from(
                        new LtcsProvisionReportSheetAppendixEntry(
                            officeName: $office->name,
                            officeCode: $office->ltcsHomeVisitLongTermCareService->code,
                            serviceName: '身体介護4',
                            serviceCode: '111411',
                            unitScore: 663, // 令和3年4月時点（令和3年4月版）
                            count: 1,
                            wholeScore: 663,
                            maxBenefitQuotaExcessScore: 0,
                            maxBenefitExcessScore: 0,
                            unitCost: Decimal::fromInt(11_4000),
                            benefitRate: 70,
                        ),
                        new LtcsProvisionReportSheetAppendixEntry(
                            officeName: $office->name,
                            officeCode: $office->ltcsHomeVisitLongTermCareService->code,
                            serviceName: '訪問介護令和3年9月30日までの上乗せ分',
                            serviceCode: '118300',
                            unitScore: 1,
                            count: 1,
                            wholeScore: 1,
                            maxBenefitQuotaExcessScore: 0,
                            maxBenefitExcessScore: 0,
                            unitCost: Decimal::fromInt(11_4000),
                            benefitRate: 70,
                        ),
                    ),
                    maxBenefit: 30938, // 要介護4
                    insuranceClaimAmount: 5298, // 664単位 × 11.40円 × 70%
                    subsidyClaimAmount: 0,
                    copayAmount: 2271,
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
            userId: $this->examples->users[0]->id,
            disposition: LtcsBillingServiceDetailDisposition::result(), // 予実の値
            providedOn: $providedOn,
            serviceCode: ServiceCode::fromString('111411'),
            serviceCodeCategory: LtcsServiceCodeCategory::physicalCare(),
            buildingSubtraction: LtcsBuildingSubtraction::none(),
            noteRequirement: LtcsNoteRequirement::none(),
            isAddition: false,
            isLimited: true,
            durationMinutes: 120,
            unitScore: 663,
            count: 1,
            wholeScore: 663,
            maxBenefitQuotaExcessScore: 0,
            maxBenefitExcessScore: 0,
            totalScore: 663,
        );
    }
}

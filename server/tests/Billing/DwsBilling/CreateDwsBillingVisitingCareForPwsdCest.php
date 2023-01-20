<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Billing\DwsBilling;

use BillingTester;
use Codeception\Util\HttpCode;
use Domain\Billing\DwsBilling;
use Domain\Billing\DwsBillingBundle;
use Domain\Billing\DwsBillingHighCostPayment;
use Domain\Billing\DwsBillingInvoice;
use Domain\Billing\DwsBillingInvoiceItem;
use Domain\Billing\DwsBillingOffice;
use Domain\Billing\DwsBillingPayment;
use Domain\Billing\DwsBillingPaymentCategory;
use Domain\Billing\DwsBillingServiceDetail;
use Domain\Billing\DwsBillingServiceReport;
use Domain\Billing\DwsBillingServiceReportAggregate;
use Domain\Billing\DwsBillingServiceReportAggregateCategory;
use Domain\Billing\DwsBillingServiceReportAggregateGroup;
use Domain\Billing\DwsBillingServiceReportDuration;
use Domain\Billing\DwsBillingServiceReportFormat;
use Domain\Billing\DwsBillingServiceReportItem;
use Domain\Billing\DwsBillingServiceReportProviderType;
use Domain\Billing\DwsBillingServiceReportSituation;
use Domain\Billing\DwsBillingStatement;
use Domain\Billing\DwsBillingStatementAggregate;
use Domain\Billing\DwsBillingStatementContract;
use Domain\Billing\DwsBillingStatementCopayCoordinationStatus;
use Domain\Billing\DwsBillingStatementItem;
use Domain\Billing\DwsBillingStatus;
use Domain\Billing\DwsBillingUser;
use Domain\Billing\DwsGrantedServiceCode;
use Domain\Billing\DwsServiceDivisionCode;
use Domain\Common\Carbon;
use Domain\Common\CarbonRange;
use Domain\Common\Decimal;
use Domain\Common\Schedule;
use Domain\DwsCertification\DwsCertificationAgreement;
use Domain\Project\DwsProjectServiceCategory;
use Domain\ProvisionReport\DwsProvisionReport;
use Domain\ProvisionReport\DwsProvisionReportItem;
use Domain\ProvisionReport\DwsProvisionReportRepository;
use Domain\ProvisionReport\DwsProvisionReportStatus;
use Domain\ServiceCode\ServiceCode;
use Domain\ServiceCodeDictionary\DwsServiceCodeCategory;
use Psr\Log\LogLevel;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;

/**
 * Billing Create のテスト（Dws VisitingCareForPwsd）.
 * POST /dws-billings
 */
final class CreateDwsBillingVisitingCareForPwsdCest extends CreateDwsBillingTest
{
    use ExamplesConsumer;

    private DwsProvisionReportRepository $provisionReportRepository;

    /**
     * 正常応答テスト（設定例１）.
     *
     * @param \BillingTester $I
     */
    public function succeedAPICall(BillingTester $I)
    {
        $I->wantTo('succeed API call');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);

        $office = $this->examples->offices[2];

        // 予実を準備
        $reportResults = [
            DwsProvisionReportItem::create([
                'schedule' => Schedule::create([
                    'date' => Carbon::create(2021, 2, 1),
                    'start' => Carbon::create(2021, 2, 1, 4, 00),
                    'end' => Carbon::create(2021, 2, 1, 7, 00),
                ]),
                'category' => DwsProjectServiceCategory::visitingCareForPwsd(),
                'headcount' => 1,
                'movingDurationMinutes' => 0,
                'options' => [],
                'note' => '設定例No.1: 1日に複数回提供',
            ]),
            DwsProvisionReportItem::create([
                'schedule' => Schedule::create([
                    'date' => Carbon::create(2021, 2, 1),
                    'start' => Carbon::create(2021, 2, 1, 8, 00),
                    'end' => Carbon::create(2021, 2, 1, 11, 00),
                ]),
                'category' => DwsProjectServiceCategory::visitingCareForPwsd(),
                'headcount' => 1,
                'movingDurationMinutes' => 0,
                'options' => [],
                'note' => '設定例No.1: 1日に複数回提供',
            ]),
            DwsProvisionReportItem::create([
                'schedule' => Schedule::create([
                    'date' => Carbon::create(2021, 2, 1),
                    'start' => Carbon::create(2021, 2, 1, 12, 00),
                    'end' => Carbon::create(2021, 2, 1, 15, 00),
                ]),
                'category' => DwsProjectServiceCategory::visitingCareForPwsd(),
                'headcount' => 1,
                'movingDurationMinutes' => 0,
                'options' => [],
                'note' => '設定例No.1: 1日に複数回提供',
            ]),
        ];

        /** @var \Domain\ProvisionReport\DwsProvisionReportRepository $repository */
        $repository = app(DwsProvisionReportRepository::class);
        $repository->store(DwsProvisionReport::create([
            'userId' => $this->examples->users[3]->id,
            'officeId' => $office->id,
            'contractId' => $this->examples->contracts[0]->id,
            'providedIn' => Carbon::create(2021, 2),
            'plans' => [],
            'results' => $reportResults,
            'status' => DwsProvisionReportStatus::fixed(),
            'fixedAt' => Carbon::create(2021, 3, 11),
            'createdAt' => Carbon::now(),
            'updatedAt' => Carbon::now(),
        ]));

        $I->sendPOST('dws-billings', $this->defaultParams(['officeId' => $office->id]));

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
        // パラメータの検証
        //
        // 請求
        $billingFinder = $this->getBillingFinder();
        /** @var \Domain\Billing\DwsBilling $billing */
        $billing = $billingFinder->find([], ['sortBy' => 'id', 'desc' => true, 'itemsPerPage' => 1])->list->head();
        $I->assertModelStrictEquals(DwsBilling::create([
            'organizationId' => $staff->organizationId,
            'office' => DwsBillingOffice::from($office),
            'transactedIn' => Carbon::create(2021, 4),
            'files' => [],
            'status' => DwsBillingStatus::checking(),
            'createdAt' => Carbon::now(),
            'updatedAt' => Carbon::now(),

            'id' => $billing->id,
        ]), $billing);

        // 請求単位
        $certification = $this->examples->dwsCertifications[10];
        $baseServiceDetail = DwsBillingServiceDetail::create([
            'userId' => $this->examples->users[3]->id,
            'providedOn' => Carbon::create(2021, 2, 1),
            'isAddition' => false,
        ]);
        $serviceDetails = [
            $baseServiceDetail->copy([
                'serviceCode' => ServiceCode::fromString('124371'), // 重訪３深夜1.0
                'serviceCodeCategory' => DwsServiceCodeCategory::visitingCareForPwsd3(),
                'unitScore' => 276,
                'count' => 1,
                'totalScore' => 276,
            ]),
            $baseServiceDetail->copy([
                'serviceCode' => ServiceCode::fromString('124381'), // 重訪３深夜1.5
                'serviceCodeCategory' => DwsServiceCodeCategory::visitingCareForPwsd3(),
                'unitScore' => 135,
                'count' => 1,
                'totalScore' => 135,
            ]),
            $baseServiceDetail->copy([
                'serviceCode' => ServiceCode::fromString('124491'), // 重訪３深夜2.0
                'serviceCodeCategory' => DwsServiceCodeCategory::visitingCareForPwsd3(),
                'unitScore' => 138,
                'count' => 1,
                'totalScore' => 138,
            ]),
            $baseServiceDetail->copy([
                'serviceCode' => ServiceCode::fromString('122501'), // 重訪３早朝2.5
                'serviceCodeCategory' => DwsServiceCodeCategory::visitingCareForPwsd3(),
                'unitScore' => 114,
                'count' => 1,
                'totalScore' => 114,
            ]),
            $baseServiceDetail->copy([
                'serviceCode' => ServiceCode::fromString('122511'), // 重訪３早朝3.0
                'serviceCodeCategory' => DwsServiceCodeCategory::visitingCareForPwsd3(),
                'unitScore' => 115,
                'count' => 1,
                'totalScore' => 115,
            ]),
            $baseServiceDetail->copy([
                'serviceCode' => ServiceCode::fromString('121521'), // 重訪３日中3.5
                'serviceCodeCategory' => DwsServiceCodeCategory::visitingCareForPwsd3(),
                'unitScore' => 90,
                'count' => 1,
                'totalScore' => 90,
            ]),
            $baseServiceDetail->copy([
                'serviceCode' => ServiceCode::fromString('121531'), // 重訪３ 日中4.0
                'serviceCodeCategory' => DwsServiceCodeCategory::visitingCareForPwsd3(),
                'unitScore' => 92,
                'count' => 1,
                'totalScore' => 92,
            ]),
            $baseServiceDetail->copy([
                'serviceCode' => ServiceCode::fromString('121321'), // 重訪３ 日中8.0
                'serviceCodeCategory' => DwsServiceCodeCategory::visitingCareForPwsd3(),
                'unitScore' => 85,
                'count' => 8,
                'totalScore' => 680,
            ]),
            $baseServiceDetail->copy([
                'serviceCode' => ServiceCode::fromString('121331'), // 重訪３ 日中12.0
                'serviceCodeCategory' => DwsServiceCodeCategory::visitingCareForPwsd3(),
                'unitScore' => 85,
                'count' => 2, // 14:00〜15:00 1count=30分
                'totalScore' => 170,
            ]),
            $baseServiceDetail->copy([
                'serviceCode' => ServiceCode::fromString('126010'), // 特定事業所加算1
                'serviceCodeCategory' => DwsServiceCodeCategory::specifiedOfficeAddition1(),
                'unitScore' => 362, // 合計1810 * 0.2(20%)
                'count' => 1,
                'totalScore' => 362,
                'isAddition' => true,
                'providedOn' => Carbon::create(2021, 2)->endOfMonth()->startOfDay(),
            ]),
        ];
        $bundleRepository = $this->getBundleRepository();
        /** @var \Domain\Billing\DwsBillingBundle[]|\ScalikePHP\Seq $bundles */
        $bundles = $bundleRepository->lookupByBillingId($billing->id)->head()[1];
        $I->assertCount(1, $bundles);
        /** @var \Domain\Billing\DwsBillingBundle $bundle */
        $bundle = $bundles->head();
        $I->assertModelStrictEquals(DwsBillingBundle::create([
            'providedIn' => Carbon::create(2021, 2),
            'cityCode' => $certification->cityCode,
            'cityName' => $certification->cityName,
            'details' => $serviceDetails,
            'createdAt' => carbon::now(),
            'updatedAt' => Carbon::now(),

            'id' => $bundle->id,
            'dwsBillingId' => $billing->id,
        ], true), $bundle);

        $billingUser = DwsBillingUser::from($this->examples->users[3], $certification); // 実績記録票でも使うので
        // 請求明細書
        $statementRepository = $this->getStatementRepository();
        $statements = $statementRepository->lookupByBundleId($bundle->id)->head()[1];
        $I->assertCount(1, $statements);
        /** @var \Domain\Billing\DwsBillingStatement $statement */
        $statement = $statements->head();
        $I->assertModelStrictEquals(DwsBillingStatement::create([
            'subsidyCityCode' => '',
            'user' => $billingUser,
            'dwsAreaGradeName' => $this->examples->dwsAreaGrades[5]->name,
            'dwsAreaGradeCode' => $this->examples->dwsAreaGrades[5]->code,
            'copayLimit' => $certification->copayLimit,
            'totalScore' => 2474,
            'totalFee' => 27708,
            'totalCappedCopay' => 2770,
            'totalAdjustedCopay' => null,
            'totalCoordinatedCopay' => null,
            'totalCopay' => 2770,
            'totalBenefit' => 24938,
            'totalSubsidy' => null,
            'isProvided' => true, // TODO 自社サービス提供有無 あり？
            'copayCoordination' => null,
            'copayCoordinationStatus' => DwsBillingStatementCopayCoordinationStatus::unfilled(),

            'aggregates' => [
                new DwsBillingStatementAggregate(
                    serviceDivisionCode: DwsServiceDivisionCode::visitingCareForPwsd(),
                    startedOn: $this->examples
                        ->contracts[17]
                        ->dwsPeriods[DwsServiceDivisionCode::visitingCareForPwsd()->value()]
                        ->start,
                    terminatedOn: $this->examples
                        ->contracts[17]
                        ->dwsPeriods[DwsServiceDivisionCode::visitingCareForPwsd()->value()]
                        ->end,
                    serviceDays: 1,
                    subtotalScore: 2474,
                    unitCost: $this->examples->dwsAreaGradeFees[0]->fee,
                    subtotalFee: 27708,
                    unmanagedCopay: 2770,
                    managedCopay: 2770,
                    cappedCopay: 2770,
                    adjustedCopay: null,
                    coordinatedCopay: null,
                    subtotalCopay: 2770,
                    subtotalBenefit: 24938,
                    subtotalSubsidy: null,
                ),
            ],
            'contracts' => Seq::from(...$certification->agreements)
                ->map(function (DwsCertificationAgreement $x): DwsBillingStatementContract {
                    return DwsBillingStatementContract::create([
                        'dwsGrantedServiceCode' => DwsGrantedServiceCode::fromDwsCertificationAgreementType($x->dwsCertificationAgreementType),
                        'grantedAmount' => $x->paymentAmount,
                        'agreedOn' => $x->agreedOn,
                        'expiredOn' => $x->expiredOn,
                        'indexNumber' => $x->indexNumber,
                    ]);
                })->toArray(),
            'items' => [
                new DwsBillingStatementItem(
                    serviceCode: ServiceCode::fromString('124371'),
                    serviceCodeCategory: DwsServiceCodeCategory::visitingCareForPwsd3(),
                    unitScore: 276,
                    count: 1,
                    totalScore: 276,
                ),
                new DwsBillingStatementItem(
                    serviceCode: ServiceCode::fromString('124381'), // 重訪３深夜1.5
                    serviceCodeCategory: DwsServiceCodeCategory::visitingCareForPwsd3(),
                    unitScore: 135,
                    count: 1,
                    totalScore: 135,
                ),
                new DwsBillingStatementItem(
                    serviceCode: ServiceCode::fromString('124491'), // 重訪３深夜2.0
                    serviceCodeCategory: DwsServiceCodeCategory::visitingCareForPwsd3(),
                    unitScore: 138,
                    count: 1,
                    totalScore: 138,
                ),
                new DwsBillingStatementItem(
                    serviceCode: ServiceCode::fromString('122501'), // 重訪３早朝2.5
                    serviceCodeCategory: DwsServiceCodeCategory::visitingCareForPwsd3(),
                    unitScore: 114,
                    count: 1,
                    totalScore: 114,
                ),
                new DwsBillingStatementItem(
                    serviceCode: ServiceCode::fromString('122511'), // 重訪３早朝3.0
                    serviceCodeCategory: DwsServiceCodeCategory::visitingCareForPwsd3(),
                    unitScore: 115,
                    count: 1,
                    totalScore: 115,
                ),
                new DwsBillingStatementItem(
                    serviceCode: ServiceCode::fromString('121521'), // 重訪３日中3.5
                    serviceCodeCategory: DwsServiceCodeCategory::visitingCareForPwsd3(),
                    unitScore: 90,
                    count: 1,
                    totalScore: 90,
                ),
                new DwsBillingStatementItem(
                    serviceCode: ServiceCode::fromString('121531'), // 重訪３ 日中4.0
                    serviceCodeCategory: DwsServiceCodeCategory::visitingCareForPwsd3(),
                    unitScore: 92,
                    count: 1,
                    totalScore: 92,
                ),
                new DwsBillingStatementItem(
                    serviceCode: ServiceCode::fromString('121321'), // 重訪３ 日中8.0
                    serviceCodeCategory: DwsServiceCodeCategory::visitingCareForPwsd3(),
                    unitScore: 85,
                    count: 8,
                    totalScore: 680,
                ),
                new DwsBillingStatementItem(
                    serviceCode: ServiceCode::fromString('121331'), // 重訪３ 日中12.0
                    serviceCodeCategory: DwsServiceCodeCategory::visitingCareForPwsd3(),
                    unitScore: 85,
                    count: 2,
                    totalScore: 170,
                ),
                new DwsBillingStatementItem(
                    serviceCode: ServiceCode::fromString('126010'), // 特定事業所加算1
                    serviceCodeCategory: DwsServiceCodeCategory::specifiedOfficeAddition1(),
                    unitScore: 362, // 合計1810 * 0.2(20%)
                    count: 1,
                    totalScore: 362,
                ),
                new DwsBillingStatementItem(
                    serviceCode: ServiceCode::fromString('126710'), // 処遇改善加算2
                    serviceCodeCategory: DwsServiceCodeCategory::treatmentImprovementAddition2(),
                    unitScore: 302,
                    count: 1,
                    totalScore: 302,
                ),
            ],
            'status' => DwsBillingStatus::checking(),
            'createdAt' => Carbon::now(),
            'updatedAt' => Carbon::now(),

            // コピー
            'id' => $statement->id,
            'dwsBillingId' => $billing->id,
            'dwsBillingBundleId' => $bundle->id,
        ], true), $statement);

        // 請求書
        $invoiceRepository = $this->getInvoiceRepository();
        $invoices = $invoiceRepository->lookupByBundleId($bundle->id)->head()[1];

        $I->assertCount(1, $invoices);
        /** @var \Domain\Billing\DwsBillingInvoice $invoice */
        $invoice = $invoices->head();
        $I->assertModelStrictEquals(DwsBillingInvoice::create([
            'claimAmount' => 24938,
            'dwsPayment' => DwsBillingPayment::create([
                'subtotalDetailCount' => 1,
                'subtotalScore' => 2474,
                'subtotalFee' => 27708,
                'subtotalBenefit' => 24938,
                'subtotalCopay' => 2770,
                'subtotalSubsidy' => 0,
            ]),
            'highCostDwsPayment' => DwsBillingHighCostPayment::create([
                'subtotalDetailCount' => 0,
                'subtotalFee' => 0,
                'subtotalBenefit' => 0,
            ]),
            'totalCount' => 1,
            'totalScore' => 2474,
            'totalFee' => 27708,
            'totalBenefit' => 24938,
            'totalCopay' => 2770,
            'totalSubsidy' => 0,
            'items' => [
                DwsBillingInvoiceItem::create([
                    'paymentCategory' => DwsBillingPaymentCategory::category1(),
                    'serviceDivisionCode' => DwsServiceDivisionCode::visitingCareForPwsd(),
                    'subtotalCount' => 1,
                    'subtotalScore' => 2474,
                    'subtotalFee' => 27708,
                    'subtotalBenefit' => 24938,
                    'subtotalCopay' => 2770,
                    'subtotalSubsidy' => 0,
                ]),
            ],

            'createdAt' => carbon::now(),
            'updatedAt' => Carbon::now(),

            // コピー
            'id' => $invoice->id,
            'dwsBillingBundleId' => $bundle->id,
        ], true), $invoice);

        // サービス実績記録票
        $serviceReportRepository = $this->getServiceReportRepository();
        $serviceReports = $serviceReportRepository->lookupByBundleId($bundle->id)->head()[1];
        $I->assertCount(1, $serviceReports);
        $serviceReport = $serviceReports->head();
        $I->assertModelStrictEquals(DwsBillingServiceReport::create([
            'user' => $billingUser,
            'format' => DwsBillingServiceReportFormat::visitingCareForPwsd(),
            'plan' => DwsBillingServiceReportAggregate::fromAssoc([
                DwsBillingServiceReportAggregateGroup::visitingCareForPwsd()->value() => [
                    DwsBillingServiceReportAggregateCategory::categoryTotal()->value() => Decimal::zero(),
                ],
                DwsBillingServiceReportAggregateGroup::outingSupportForPwsd()->value() => [
                    DwsBillingServiceReportAggregateCategory::categoryTotal()->value() => Decimal::zero(),
                ],
            ]),
            'result' => DwsBillingServiceReportAggregate::fromAssoc([
                DwsBillingServiceReportAggregateGroup::visitingCareForPwsd()->value() => [
                    DwsBillingServiceReportAggregateCategory::categoryTotal()->value() => Decimal::fromInt(9_0000),
                ],
                DwsBillingServiceReportAggregateGroup::outingSupportForPwsd()->value() => [
                    DwsBillingServiceReportAggregateCategory::categoryTotal()->value() => Decimal::zero(),
                ],
            ]),
            'emergencyCount' => 0,
            'firstTimeCount' => 0,
            'welfareSpecialistCooperationCount' => 0,
            'behavioralDisorderSupportCooperationCount' => 0,
            'movingCareSupportCount' => 0,
            'items' => [
                DwsBillingServiceReportItem::create([
                    'serialNumber' => 1,
                    'providedOn' => Carbon::create(2021, 2, 1),
                    'serviceType' => DwsGrantedServiceCode::visitingCareForPwsd3(),
                    'providerType' => DwsBillingServiceReportProviderType::none(),
                    'situation' => DwsBillingServiceReportSituation::none(),
                    'plan' => null,
                    'result' => DwsBillingServiceReportDuration::create([
                        'period' => CarbonRange::create([
                            'start' => Carbon::create(2021, 2, 1, 4, 0),
                            'end' => Carbon::create(2021, 2, 1, 7, 0),
                        ]),
                    ]),
                    'serviceCount' => 0,
                    'headcount' => 1,
                    'isCoaching' => false,
                    'isFirstTime' => false,
                    'isEmergency' => false,
                    'isWelfareSpecialistCooperation' => false,
                    'isBehavioralDisorderSupportCooperation' => false,
                    'isMovingCareSupport' => false,
                    'isDriving' => false,
                    'isPreviousMonth' => false,
                    'note' => '',
                ]),
                DwsBillingServiceReportItem::create([
                    'serialNumber' => 1,
                    'providedOn' => Carbon::create(2021, 2, 1),
                    'serviceType' => DwsGrantedServiceCode::visitingCareForPwsd3(),
                    'providerType' => DwsBillingServiceReportProviderType::none(),
                    'situation' => DwsBillingServiceReportSituation::none(),
                    'plan' => null,
                    'result' => DwsBillingServiceReportDuration::create([
                        'period' => CarbonRange::create([
                            'start' => Carbon::create(2021, 2, 1, 8, 0),
                            'end' => Carbon::create(2021, 2, 1, 11, 0),
                        ]),
                    ]),
                    'serviceCount' => 0, // 1人のときは0
                    'headcount' => 1,
                    'isCoaching' => false,
                    'isFirstTime' => false,
                    'isEmergency' => false,
                    'isWelfareSpecialistCooperation' => false,
                    'isBehavioralDisorderSupportCooperation' => false,
                    'isMovingCareSupport' => false,
                    'isDriving' => false,
                    'isPreviousMonth' => false,
                    'note' => '',
                ]),
                DwsBillingServiceReportItem::create([
                    'serialNumber' => 1,
                    'providedOn' => Carbon::create(2021, 2, 1),
                    'serviceType' => DwsGrantedServiceCode::visitingCareForPwsd3(),
                    'providerType' => DwsBillingServiceReportProviderType::none(),
                    'situation' => DwsBillingServiceReportSituation::none(),
                    'plan' => null,
                    'result' => DwsBillingServiceReportDuration::create([
                        'period' => CarbonRange::create([
                            'start' => Carbon::create(2021, 2, 1, 12, 0),
                            'end' => Carbon::create(2021, 2, 1, 15, 0),
                        ]),
                        'serviceDurationHours' => Decimal::fromInt(9_0000),
                    ]),
                    'serviceCount' => 0,
                    'headcount' => 1,
                    'isCoaching' => false,
                    'isFirstTime' => false,
                    'isEmergency' => false,
                    'isWelfareSpecialistCooperation' => false,
                    'isBehavioralDisorderSupportCooperation' => false,
                    'isMovingCareSupport' => false,
                    'isDriving' => false,
                    'isPreviousMonth' => false,
                    'note' => '',
                ]),
            ],

            'status' => DwsBillingStatus::ready(),
            'createdAt' => Carbon::now(),
            'updatedAt' => Carbon::now(),

            'dwsBillingId' => $billing->id,
            'dwsBillingBundleId' => $bundle->id,
            'id' => $serviceReport->id,
        ], true), $serviceReport);
    }

    /**
     * COVID-19加算テスト.
     *
     * @param \BillingTester $I
     */
    public function succeedCovid19AdditionSimple(BillingTester $I)
    {
        $I->wantTo('succeed API call with COVID-19');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);

        $office = $this->examples->offices[2];

        // 予実を準備
        $reportResults = [
            DwsProvisionReportItem::create([
                'schedule' => Schedule::create([
                    'date' => Carbon::create(2021, 4, 1),
                    'start' => Carbon::create(2021, 4, 1, 8, 00),
                    'end' => Carbon::create(2021, 4, 1, 9, 00),
                ]),
                'category' => DwsProjectServiceCategory::visitingCareForPwsd(),
                'headcount' => 1,
                'movingDurationMinutes' => 0,
                'options' => [],
                'note' => 'COVID19加算テスト用シンプルパターン',
            ], true),
        ];

        /** @var \Domain\ProvisionReport\DwsProvisionReportRepository $repository */
        $repository = app(DwsProvisionReportRepository::class);
        $repository->store(DwsProvisionReport::create([
            'userId' => $this->examples->users[3]->id,
            'officeId' => $office->id,
            'contractId' => $this->examples->contracts[0]->id,
            'providedIn' => Carbon::create(2021, 4),
            'plans' => [],
            'results' => $reportResults,
            'status' => DwsProvisionReportStatus::fixed(),
            'fixedAt' => Carbon::create(2021, 5, 1),
            'createdAt' => Carbon::now(),
            'updatedAt' => Carbon::now(),
        ]));

        $I->sendPOST('dws-billings', $this->defaultParams(['transactedIn' => '2021-05', 'officeId' => $office->id]));

        $I->seeResponseCodeIs(HttpCode::ACCEPTED);
        $I->seeLogCount(3);

        //
        // パラメータの検証
        //
        // 請求
        $billingFinder = $this->getBillingFinder();
        /** @var \Domain\Billing\DwsBilling $billing */
        $billing = $billingFinder->find([], ['sortBy' => 'id', 'desc' => true, 'itemsPerPage' => 1])->list->head();
        $I->assertModelStrictEquals(DwsBilling::create([
            'organizationId' => $staff->organizationId,
            'office' => DwsBillingOffice::from($office),
            'transactedIn' => Carbon::create(2021, 5),
            'files' => [],
            'status' => DwsBillingStatus::checking(),
            'createdAt' => Carbon::now(),
            'updatedAt' => Carbon::now(),

            'id' => $billing->id,
        ]), $billing);

        // 請求単位
        $certification = $this->examples->dwsCertifications[17]; // 2021/4/1以降の受給者証
        $baseServiceDetail = DwsBillingServiceDetail::create([
            'userId' => $this->examples->users[3]->id,
            'providedOn' => Carbon::create(2021, 4, 1),
            'isAddition' => false,
        ]);
        $bundleRepository = $this->getBundleRepository();
        /** @var \Domain\Billing\DwsBillingBundle[]|\ScalikePHP\Seq $bundles */
        $bundles = $bundleRepository->lookupByBillingId($billing->id)->head()[1];
        $I->assertCount(1, $bundles);
        /** @var \Domain\Billing\DwsBillingBundle $bundle */
        $bundle = $bundles->head();
        $I->assertModelStrictEquals(DwsBillingBundle::create([
            'providedIn' => Carbon::create(2021, 4),
            'cityCode' => $certification->cityCode,
            'cityName' => $certification->cityName,
            'details' => [
                $baseServiceDetail->copy([
                    'serviceCode' => ServiceCode::fromString('121371'), // 重訪３日中1.0
                    'serviceCodeCategory' => DwsServiceCodeCategory::visitingCareForPwsd3(),
                    'unitScore' => 185,
                    'count' => 1,
                    'totalScore' => 185,
                ]),
                $baseServiceDetail->copy([
                    'serviceCode' => ServiceCode::fromString('12ZZ01'), // COVID-19
                    'serviceCodeCategory' => DwsServiceCodeCategory::covid19PandemicSpecialAddition(),
                    'unitScore' => 1,
                    'count' => 1,
                    'totalScore' => 1,
                    'isAddition' => true,
                    'providedOn' => Carbon::create(2021, 4)->endOfMonth()->startOfDay(),
                ]),
                $baseServiceDetail->copy([
                    'serviceCode' => ServiceCode::fromString('126010'), // 特定事業所加算1
                    'serviceCodeCategory' => DwsServiceCodeCategory::specifiedOfficeAddition1(),
                    'unitScore' => 37, // 合計186 * 0.2(20%)
                    'count' => 1,
                    'totalScore' => 37,
                    'isAddition' => true,
                    'providedOn' => Carbon::create(2021, 4)->endOfMonth()->startOfDay(),
                ]),
            ],
            'createdAt' => carbon::now(),
            'updatedAt' => Carbon::now(),

            'id' => $bundle->id,
            'dwsBillingId' => $billing->id,
        ]), $bundle);

        $billingUser = DwsBillingUser::from($this->examples->users[3], $certification); // 実績記録票でも使うので
        // 請求明細書
        $statementRepository = $this->getStatementRepository();
        $statements = $statementRepository->lookupByBundleId($bundle->id)->head()[1];
        $I->assertCount(1, $statements);
        /** @var \Domain\Billing\DwsBillingStatement $statement */
        $statement = $statements->head();
        $I->assertModelStrictEquals(DwsBillingStatement::create([
            'subsidyCityCode' => '',
            'user' => $billingUser,
            'dwsAreaGradeName' => $this->examples->dwsAreaGrades[5]->name,
            'dwsAreaGradeCode' => $this->examples->dwsAreaGrades[5]->code,
            'copayLimit' => $certification->copayLimit,
            'totalScore' => 256,
            'totalFee' => 2867, // 11.2円
            'totalCappedCopay' => 286,
            'totalAdjustedCopay' => null,
            'totalCoordinatedCopay' => null,
            'totalCopay' => 286,
            'totalBenefit' => 2581,
            'totalSubsidy' => null,
            'isProvided' => true, // TODO 自社サービス提供有無 あり？
            'copayCoordination' => null,
            'copayCoordinationStatus' => DwsBillingStatementCopayCoordinationStatus::unfilled(),

            'aggregates' => [
                new DwsBillingStatementAggregate(
                    serviceDivisionCode: DwsServiceDivisionCode::visitingCareForPwsd(),
                    startedOn: $this->examples
                        ->contracts[17]
                        ->dwsPeriods[DwsServiceDivisionCode::visitingCareForPwsd()->value()]
                        ->start,
                    terminatedOn: $this->examples
                        ->contracts[17]
                        ->dwsPeriods[DwsServiceDivisionCode::visitingCareForPwsd()->value()]
                        ->end,
                    serviceDays: 1,
                    subtotalScore: 256,
                    unitCost: $this->examples->dwsAreaGradeFees[0]->fee,
                    subtotalFee: 2867,
                    unmanagedCopay: 286,
                    managedCopay: 286,
                    cappedCopay: 286,
                    adjustedCopay: null,
                    coordinatedCopay: null,
                    subtotalCopay: 286,
                    subtotalBenefit: 2581,
                    subtotalSubsidy: null,
                ),
            ],
            'contracts' => Seq::from(...$certification->agreements)
                ->map(function (DwsCertificationAgreement $x): DwsBillingStatementContract {
                    return DwsBillingStatementContract::create([
                        'dwsGrantedServiceCode' => DwsGrantedServiceCode::fromDwsCertificationAgreementType($x->dwsCertificationAgreementType),
                        'grantedAmount' => $x->paymentAmount,
                        'agreedOn' => $x->agreedOn,
                        'expiredOn' => $x->expiredOn,
                        'indexNumber' => $x->indexNumber,
                    ]);
                })->toArray(),
            'items' => [
                new DwsBillingStatementItem(
                    serviceCode: ServiceCode::fromString('121371'), // 重訪３日中1.0
                    serviceCodeCategory: DwsServiceCodeCategory::visitingCareForPwsd3(),
                    unitScore: 185,
                    count: 1,
                    totalScore: 185,
                ),
                new DwsBillingStatementItem(
                    serviceCode: ServiceCode::fromString('12ZZ01'), // 重訪 COVID-19 加算
                    serviceCodeCategory: DwsServiceCodeCategory::covid19PandemicSpecialAddition(),
                    unitScore: 1, // これまでの合計185 * 0.1% = 1 (1以下はすべて1)
                    count: 1,
                    totalScore: 1,
                ),
                new DwsBillingStatementItem(
                    serviceCode: ServiceCode::fromString('126010'), // 特定事業所加算1
                    serviceCodeCategory: DwsServiceCodeCategory::specifiedOfficeAddition1(),
                    unitScore: 37, // 合計186 * 0.2(20%)
                    count: 1,
                    totalScore: 37,
                ),
                new DwsBillingStatementItem(
                    serviceCode: ServiceCode::fromString('126710'), // 処遇改善加算2
                    serviceCodeCategory: DwsServiceCodeCategory::treatmentImprovementAddition2(),
                    unitScore: 33, // 合計 223 * 14.6%
                    count: 1,
                    totalScore: 33,
                ),
            ],
            'status' => DwsBillingStatus::checking(),
            'createdAt' => Carbon::now(),
            'updatedAt' => Carbon::now(),

            // コピー
            'id' => $statement->id,
            'dwsBillingId' => $billing->id,
            'dwsBillingBundleId' => $bundle->id,
        ], true), $statement);
    }

    /**
     * リクエストパラメータ組み立て.
     *
     * @param array $overwrites
     * @return array
     */
    private function defaultParams(array $overwrites = []): array
    {
        return $overwrites + [
            'officeId' => $this->examples->offices[0]->id,
            'transactedIn' => '2021-04',
        ];
    }
}

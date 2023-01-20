<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
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
final class CreateDwsBillingVisitingCareForPwsdLocationAdditionCest extends CreateDwsBillingTest
{
    use ExamplesConsumer;

    private DwsProvisionReportRepository $provisionReportRepository;

    /**
     * API正常呼び出しテスト(地域加算がある場合).
     *
     * @param \BillingTester $I
     */
    public function succeedAPICall(BillingTester $I)
    {
        $I->wantTo('succeed API call');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);

        $office = $this->examples->offices[0];

        // 予実を準備
        $reportResults = [
            DwsProvisionReportItem::create([
                'schedule' => Schedule::create([
                    'date' => Carbon::create(2022, 2, 1),
                    'start' => Carbon::create(2022, 2, 1, 4, 00),
                    'end' => Carbon::create(2022, 2, 1, 7, 00),
                ]),
                'category' => DwsProjectServiceCategory::visitingCareForPwsd(),
                'headcount' => 1,
                'movingDurationMinutes' => 0,
                'options' => [],
                'note' => '設定例No.1: 1日に複数回提供',
            ]),
            DwsProvisionReportItem::create([
                'schedule' => Schedule::create([
                    'date' => Carbon::create(2022, 2, 1),
                    'start' => Carbon::create(2022, 2, 1, 8, 00),
                    'end' => Carbon::create(2022, 2, 1, 11, 00),
                ]),
                'category' => DwsProjectServiceCategory::visitingCareForPwsd(),
                'headcount' => 1,
                'movingDurationMinutes' => 0,
                'options' => [],
                'note' => '設定例No.1: 1日に複数回提供',
            ]),
            DwsProvisionReportItem::create([
                'schedule' => Schedule::create([
                    'date' => Carbon::create(2022, 2, 1),
                    'start' => Carbon::create(2022, 2, 1, 12, 00),
                    'end' => Carbon::create(2022, 2, 1, 15, 00),
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
            'userId' => $this->examples->users[19]->id,
            'officeId' => $office->id,
            'contractId' => $this->examples->contracts[36]->id,
            'providedIn' => Carbon::create(2022, 2),
            'plans' => [],
            'results' => $reportResults,
            'status' => DwsProvisionReportStatus::fixed(),
            'fixedAt' => Carbon::create(2022, 3, 11),
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
            'transactedIn' => Carbon::create(2022, 4),
            'files' => [],
            'status' => DwsBillingStatus::checking(),
            'createdAt' => Carbon::now(),
            'updatedAt' => Carbon::now(),

            'id' => $billing->id,
        ]), $billing);

        // 請求単位
        $certification = $this->examples->dwsCertifications[25];
        $baseServiceDetail = DwsBillingServiceDetail::create([
            'userId' => $this->examples->users[19]->id,
            'providedOn' => Carbon::create(2022, 2, 1),
            'isAddition' => false,
        ]);
        $serviceDetails = [
            $baseServiceDetail->copy([
                'serviceCode' => ServiceCode::fromString('124171'), // 重訪1深夜1.0
                'serviceCodeCategory' => DwsServiceCodeCategory::visitingCareForPwsd1(),
                'unitScore' => 320,
                'count' => 1,
                'totalScore' => 320,
            ]),
            $baseServiceDetail->copy([
                'serviceCode' => ServiceCode::fromString('124181'), // 重訪1深夜1.5
                'serviceCodeCategory' => DwsServiceCodeCategory::visitingCareForPwsd1(),
                'unitScore' => 156,
                'count' => 1,
                'totalScore' => 156,
            ]),
            $baseServiceDetail->copy([
                'serviceCode' => ServiceCode::fromString('124391'), // 重訪1深夜2.0
                'serviceCodeCategory' => DwsServiceCodeCategory::visitingCareForPwsd1(),
                'unitScore' => 159,
                'count' => 1,
                'totalScore' => 159,
            ]),
            $baseServiceDetail->copy([
                'serviceCode' => ServiceCode::fromString('122401'), // 重訪1早朝2.5
                'serviceCodeCategory' => DwsServiceCodeCategory::visitingCareForPwsd1(),
                'unitScore' => 131,
                'count' => 1,
                'totalScore' => 131,
            ]),
            $baseServiceDetail->copy([
                'serviceCode' => ServiceCode::fromString('122411'), // 重訪1早朝3.0
                'serviceCodeCategory' => DwsServiceCodeCategory::visitingCareForPwsd1(),
                'unitScore' => 133,
                'count' => 1,
                'totalScore' => 133,
            ]),
            $baseServiceDetail->copy([
                'serviceCode' => ServiceCode::fromString('121421'), // 重訪1日中3.5
                'serviceCodeCategory' => DwsServiceCodeCategory::visitingCareForPwsd1(),
                'unitScore' => 104,
                'count' => 1,
                'totalScore' => 104,
            ]),
            $baseServiceDetail->copy([
                'serviceCode' => ServiceCode::fromString('121431'), // 重訪1 日中4.0
                'serviceCodeCategory' => DwsServiceCodeCategory::visitingCareForPwsd1(),
                'unitScore' => 106,
                'count' => 1,
                'totalScore' => 106,
            ]),
            $baseServiceDetail->copy([
                'serviceCode' => ServiceCode::fromString('121121'), // 重訪1 日中8.0
                'serviceCodeCategory' => DwsServiceCodeCategory::visitingCareForPwsd1(),
                'unitScore' => 98,
                'count' => 8,
                'totalScore' => 784,
            ]),
            $baseServiceDetail->copy([
                'serviceCode' => ServiceCode::fromString('121131'), // 重訪1 日中12.0
                'serviceCodeCategory' => DwsServiceCodeCategory::visitingCareForPwsd1(),
                'unitScore' => 98,
                'count' => 2, // 14:00〜15:00 1count=30分
                'totalScore' => 196,
            ]),
            $baseServiceDetail->copy([
                'serviceCode' => ServiceCode::fromString('126015'), // 特別地域加算
                'serviceCodeCategory' => DwsServiceCodeCategory::specifiedAreaAddition(),
                'unitScore' => 313, // 合計2089 * 0.15(15%)
                'count' => 1,
                'totalScore' => 313,
                'isAddition' => true,
                'providedOn' => Carbon::create(2022, 2)->endOfMonth()->startOfDay(),
            ]),
        ];
        $bundleRepository = $this->getBundleRepository();
        /** @var \Domain\Billing\DwsBillingBundle[]|\ScalikePHP\Seq $bundles */
        $bundles = $bundleRepository->lookupByBillingId($billing->id)->head()[1];
        $I->assertCount(1, $bundles);
        /** @var \Domain\Billing\DwsBillingBundle $bundle */
        $bundle = $bundles->head();
        $I->assertModelStrictEquals(DwsBillingBundle::create([
            'providedIn' => Carbon::create(2022, 2),
            'cityCode' => $certification->cityCode,
            'cityName' => $certification->cityName,
            'details' => $serviceDetails,
            'createdAt' => carbon::now(),
            'updatedAt' => Carbon::now(),

            'id' => $bundle->id,
            'dwsBillingId' => $billing->id,
        ]), $bundle);

        $billingUser = DwsBillingUser::from($this->examples->users[19], $certification); // 実績記録票でも使うので
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
            'totalScore' => 2402,
            'totalFee' => 26902,
            'totalCappedCopay' => 2690,
            'totalAdjustedCopay' => null,
            'totalCoordinatedCopay' => null,
            'totalCopay' => 2690,
            'totalBenefit' => 24212,
            'totalSubsidy' => null,
            'isProvided' => true, // TODO 自社サービス提供有無 あり？
            'copayCoordination' => null,
            'copayCoordinationStatus' => DwsBillingStatementCopayCoordinationStatus::unapplicable(),

            'aggregates' => [
                new DwsBillingStatementAggregate(
                    serviceDivisionCode: DwsServiceDivisionCode::visitingCareForPwsd(),
                    startedOn: $this->examples
                        ->contracts[36]
                        ->dwsPeriods[DwsServiceDivisionCode::visitingCareForPwsd()->value()]
                        ->start,
                    terminatedOn: $this->examples
                        ->contracts[36]
                        ->dwsPeriods[DwsServiceDivisionCode::visitingCareForPwsd()->value()]
                        ->end,
                    serviceDays: 1,
                    subtotalScore: 2402,
                    unitCost: $this->examples->dwsAreaGradeFees[0]->fee,
                    subtotalFee: 26902,
                    unmanagedCopay: 2690,
                    managedCopay: 2690,
                    cappedCopay: 2690,
                    adjustedCopay: null,
                    coordinatedCopay: null,
                    subtotalCopay: 2690,
                    subtotalBenefit: 24212,
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
                    serviceCode: ServiceCode::fromString('124171'),
                    serviceCodeCategory: DwsServiceCodeCategory::visitingCareForPwsd1(),
                    unitScore: 320,
                    count: 1,
                    totalScore: 320,
                ),
                new DwsBillingStatementItem(
                    serviceCode: ServiceCode::fromString('124181'), // 重訪1深夜1.5
                    serviceCodeCategory: DwsServiceCodeCategory::visitingCareForPwsd1(),
                    unitScore: 156,
                    count: 1,
                    totalScore: 156,
                ),
                new DwsBillingStatementItem(
                    serviceCode: ServiceCode::fromString('124391'), // 重訪1深夜2.0
                    serviceCodeCategory: DwsServiceCodeCategory::visitingCareForPwsd1(),
                    unitScore: 159,
                    count: 1,
                    totalScore: 159,
                ),
                new DwsBillingStatementItem(
                    serviceCode: ServiceCode::fromString('122401'), // 重訪1早朝2.5
                    serviceCodeCategory: DwsServiceCodeCategory::visitingCareForPwsd1(),
                    unitScore: 131,
                    count: 1,
                    totalScore: 131,
                ),
                new DwsBillingStatementItem(
                    serviceCode: ServiceCode::fromString('122411'), // 重訪1早朝3.0
                    serviceCodeCategory: DwsServiceCodeCategory::visitingCareForPwsd1(),
                    unitScore: 133,
                    count: 1,
                    totalScore: 133,
                ),
                new DwsBillingStatementItem(
                    serviceCode: ServiceCode::fromString('121421'), // 重訪1日中3.5
                    serviceCodeCategory: DwsServiceCodeCategory::visitingCareForPwsd1(),
                    unitScore: 104,
                    count: 1,
                    totalScore: 104,
                ),
                new DwsBillingStatementItem(
                    serviceCode: ServiceCode::fromString('121431'), // 重訪1 日中4.0
                    serviceCodeCategory: DwsServiceCodeCategory::visitingCareForPwsd1(),
                    unitScore: 106,
                    count: 1,
                    totalScore: 106,
                ),
                new DwsBillingStatementItem(
                    serviceCode: ServiceCode::fromString('121121'), // 重訪1 日中8.0
                    serviceCodeCategory: DwsServiceCodeCategory::visitingCareForPwsd1(),
                    unitScore: 98,
                    count: 8,
                    totalScore: 784,
                ),
                new DwsBillingStatementItem(
                    serviceCode: ServiceCode::fromString('121131'), // 重訪1 日中12.0
                    serviceCodeCategory: DwsServiceCodeCategory::visitingCareForPwsd1(),
                    unitScore: 98,
                    count: 2,
                    totalScore: 196,
                ),
                new DwsBillingStatementItem(
                    serviceCode: ServiceCode::fromString('126015'), // 特別地域加算
                    serviceCodeCategory: DwsServiceCodeCategory::specifiedAreaAddition(),
                    unitScore: 313, // 合計2089 * 0.15(15%)
                    count: 1,
                    totalScore: 313,
                ),
            ],
            'status' => DwsBillingStatus::ready(),
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
            'claimAmount' => 24212,
            'dwsPayment' => DwsBillingPayment::create([
                'subtotalDetailCount' => 1,
                'subtotalScore' => 2402,
                'subtotalFee' => 26902,
                'subtotalBenefit' => 24212,
                'subtotalCopay' => 2690,
                'subtotalSubsidy' => 0,
            ]),
            'highCostDwsPayment' => DwsBillingHighCostPayment::create([
                'subtotalDetailCount' => 0,
                'subtotalFee' => 0,
                'subtotalBenefit' => 0,
            ]),
            'totalCount' => 1,
            'totalScore' => 2402,
            'totalFee' => 26902,
            'totalBenefit' => 24212,
            'totalCopay' => 2690,
            'totalSubsidy' => 0,
            'items' => [
                DwsBillingInvoiceItem::create([
                    'paymentCategory' => DwsBillingPaymentCategory::category1(),
                    'serviceDivisionCode' => DwsServiceDivisionCode::visitingCareForPwsd(),
                    'subtotalCount' => 1,
                    'subtotalScore' => 2402,
                    'subtotalFee' => 26902,
                    'subtotalBenefit' => 24212,
                    'subtotalCopay' => 2690,
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
                    'providedOn' => Carbon::create(2022, 2, 1),
                    'serviceType' => DwsGrantedServiceCode::visitingCareForPwsd1(),
                    'providerType' => DwsBillingServiceReportProviderType::none(),
                    'situation' => DwsBillingServiceReportSituation::none(),
                    'plan' => null,
                    'result' => DwsBillingServiceReportDuration::create([
                        'period' => CarbonRange::create([
                            'start' => Carbon::create(2022, 2, 1, 4, 0),
                            'end' => Carbon::create(2022, 2, 1, 7, 0),
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
                    'providedOn' => Carbon::create(2022, 2, 1),
                    'serviceType' => DwsGrantedServiceCode::visitingCareForPwsd1(),
                    'providerType' => DwsBillingServiceReportProviderType::none(),
                    'situation' => DwsBillingServiceReportSituation::none(),
                    'plan' => null,
                    'result' => DwsBillingServiceReportDuration::create([
                        'period' => CarbonRange::create([
                            'start' => Carbon::create(2022, 2, 1, 8, 0),
                            'end' => Carbon::create(2022, 2, 1, 11, 0),
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
                    'providedOn' => Carbon::create(2022, 2, 1),
                    'serviceType' => DwsGrantedServiceCode::visitingCareForPwsd1(),
                    'providerType' => DwsBillingServiceReportProviderType::none(),
                    'situation' => DwsBillingServiceReportSituation::none(),
                    'plan' => null,
                    'result' => DwsBillingServiceReportDuration::create([
                        'period' => CarbonRange::create([
                            'start' => Carbon::create(2022, 2, 1, 12, 0),
                            'end' => Carbon::create(2022, 2, 1, 15, 0),
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
     * リクエストパラメータ組み立て.
     *
     * @param array $overwrites
     * @return array
     */
    private function defaultParams(array $overwrites = []): array
    {
        return $overwrites + [
            'officeId' => $this->examples->offices[0]->id,
            'transactedIn' => '2022-04',
        ];
    }
}

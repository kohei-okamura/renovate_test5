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
 * Billing Create のテスト（福祉・介護職員等ベースアップ加算がある場合）.
 * POST /dws-billings
 */
class CreateDwsBillingHomeHelpServiceBaseIncreaseAdditionCest extends CreateDwsBillingTest
{
    use ExamplesConsumer;

    /**
     * API正常呼び出しテスト(設定例1).
     *
     * @param BillingTester $I
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
                    'date' => Carbon::create(2022, 10, 1),
                    'start' => Carbon::create(2022, 10, 1, 10, 00),
                    'end' => Carbon::create(2022, 10, 1, 11, 30),
                ]),
                'category' => DwsProjectServiceCategory::physicalCare(),
                'headcount' => 1,
                'movingDurationMinutes' => 0,
                'options' => [],
                'note' => '設定例No.1',
            ]),
        ];

        /** @var \Domain\ProvisionReport\DwsProvisionReportRepository $repository */
        $repository = app(DwsProvisionReportRepository::class);
        $repository->store(DwsProvisionReport::create([
            'userId' => $this->examples->users[0]->id,
            'officeId' => $office->id,
            'contractId' => $this->examples->contracts[0]->id,
            'providedIn' => Carbon::create(2022, 10),
            'plans' => [],
            'results' => $reportResults,
            'status' => DwsProvisionReportStatus::fixed(),
            'fixedAt' => Carbon::create(2022, 11, 10),
            'createdAt' => Carbon::now(),
            'updatedAt' => Carbon::now(),
        ]));

        $I->sendPOST('dws-billings', $this->defaultParams());

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

        // データ検証
        $certification = $this->examples->dwsCertifications[27];
        // Billing
        $billingFinder = $this->getBillingFinder();
        /** @var \Domain\Billing\DwsBilling $billing */
        $billing = $billingFinder->find([], ['sortBy' => 'id', 'desc' => true, 'itemsPerPage' => 1])->list->head();
        $I->assertModelStrictEquals(DwsBilling::create([
            'organizationId' => $staff->organizationId,
            'office' => DwsBillingOffice::from($office),
            'transactedIn' => Carbon::create(2022, 11),
            'files' => [],
            'status' => DwsBillingStatus::checking(),
            'createdAt' => Carbon::now(),
            'updatedAt' => Carbon::now(),

            // コピーする
            'id' => $billing->id,
        ]), $billing);

        // Bundle
        $bundleRepository = $this->getBundleRepository();
        $bundles = $bundleRepository->lookupByBillingId($billing->id)->head()[1];
        $I->assertCount(1, $bundles);
        $actualBundle = $bundles->head();
        $expectedServiceDetails = [
            DwsBillingServiceDetail::create([
                'userId' => $this->examples->users[0]->id,
                'providedOn' => Carbon::create(2022, 10, 1),
                'serviceCode' => ServiceCode::fromString('111119'), // 身体日中1.5
                'serviceCodeCategory' => DwsServiceCodeCategory::physicalCare(),
                'isAddition' => false,
                'unitScore' => 584,
                'count' => 1,
                'totalScore' => 584,
            ]),
        ];
        $I->assertModelStrictEquals(DwsBillingBundle::create([
            'dwsBillingId' => $billing->id,
            'providedIn' => Carbon::create(2022, 10),
            'cityCode' => $certification->cityCode,
            'cityName' => $certification->cityName,
            'details' => $expectedServiceDetails,
            'createdAt' => Carbon::now(),
            'updatedAt' => Carbon::now(),

            // コピーする
            'id' => $actualBundle->id,
        ]), $actualBundle);

        // Statement
        $statementRepository = $this->getStatementRepository();
        /** @var \Domain\Billing\DwsBillingStatement[]|\ScalikePHP\Seq $statements */
        $statements = $statementRepository->lookupByBundleId($actualBundle->id)->head()[1];
        $I->assertCount(1, $statements);
        /** @var \Domain\Billing\DwsBillingStatement $actualStatement */
        $actualStatement = $statements->head();
        $statementContracts = Seq::from(...$certification->agreements)
            ->map(function (DwsCertificationAgreement $x): DwsBillingStatementContract {
                return DwsBillingStatementContract::create([
                    'officeId' => $this->examples->offices[0]->id,
                    'dwsGrantedServiceCode' => DwsGrantedServiceCode::fromDwsCertificationAgreementType($x->dwsCertificationAgreementType),
                    'grantedAmount' => $x->paymentAmount,
                    'agreedOn' => $x->agreedOn,
                    'expiredOn' => $x->expiredOn,
                    'indexNumber' => $x->indexNumber,
                ]);
            })->toArray();

        $I->assertModelStrictEquals(DwsBillingStatement::create([
            'dwsBillingId' => $billing->id,
            'dwsBillingBundleId' => $actualBundle->id,
            'subsidyCityCode' => '',
            'user' => DwsBillingUser::from($this->examples->users[0], $certification),
            'dwsAreaGradeName' => $this->examples->dwsAreaGrades[5]->name,
            'dwsAreaGradeCode' => $this->examples->dwsAreaGrades[5]->code,
            'copayLimit' => $certification->copayLimit,
            'totalScore' => 610,
            'totalFee' => 6832, // 11.2
            'totalCappedCopay' => 683, // 10%
            'totalCopay' => 683,
            'totalBenefit' => 6149,
            'totalSubsidy' => null,
            'isProvided' => true,
            'copayCoordinationStatus' => DwsBillingStatementCopayCoordinationStatus::unapplicable(), // 不要
            'aggregates' => [
                new DwsBillingStatementAggregate(
                    serviceDivisionCode: DwsServiceDivisionCode::homeHelpService(),
                    startedOn: $this->examples
                        ->contracts[16]
                        ->dwsPeriods[DwsServiceDivisionCode::homeHelpService()->value()]
                        ->start,
                    terminatedOn: $this->examples
                        ->contracts[16]
                        ->dwsPeriods[DwsServiceDivisionCode::homeHelpService()->value()]
                        ->end,
                    serviceDays: 1,
                    subtotalScore: 610,
                    unitCost: $this->examples->dwsAreaGradeFees[0]->fee,
                    subtotalFee: 6832,
                    unmanagedCopay: 683,
                    managedCopay: 683,
                    cappedCopay: 683,
                    adjustedCopay: null,
                    coordinatedCopay: null,
                    subtotalCopay: 683,
                    subtotalBenefit: 6149,
                    subtotalSubsidy: null,
                ),
            ],
            'contracts' => $statementContracts,

            'items' => [
                new DwsBillingStatementItem(
                    serviceCode: ServiceCode::fromString('111119'), // 身体日中1.5
                    serviceCodeCategory: DwsServiceCodeCategory::physicalCare(),
                    unitScore: 584,
                    count: 1,
                    totalScore: 584,
                ),
                new DwsBillingStatementItem(
                    serviceCode: ServiceCode::fromString('116766'), // 福祉・介護職員等ベースアップ等支援加算（4.5%)
                    serviceCodeCategory: DwsServiceCodeCategory::baseIncreaseSupportAddition(),
                    unitScore: 26, // これまでの合計 584 の 4.5%
                    count: 1,
                    totalScore: 26,
                ),
            ],
            'status' => DwsBillingStatus::ready(), // 上限管理なしなので ready() へ
            'createdAt' => Carbon::now(),
            'updatedAt' => Carbon::now(),

            // コピー
            'id' => $actualStatement->id,
        ]), $actualStatement);

        // Invoice
        $invoiceRepository = $this->getInvoiceRepository();
        /** @var \Domain\Billing\DwsBillingInvoice[]|\ScalikePHP\Seq $invoices */
        $invoices = $invoiceRepository->lookupByBundleId($actualBundle->id)->head()[1];
        $I->assertCount(1, $invoices);
        /** @var \Domain\Billing\DwsBillingInvoice $actualInvoice */
        $actualInvoice = $invoices->head();
        $I->assertModelStrictEquals(DwsBillingInvoice::create([
            'dwsBillingBundleId' => $actualBundle->id,
            'claimAmount' => 6149,
            'dwsPayment' => DwsBillingPayment::create([
                'subtotalDetailCount' => 1,
                'subtotalScore' => 610,
                'subtotalFee' => 6832,
                'subtotalBenefit' => 6149,
                'subtotalCopay' => 683,
                'subtotalSubsidy' => 0,
            ]),
            'highCostDwsPayment' => DwsBillingHighCostPayment::create([
                'subtotalDetailCount' => 0,
                'subtotalFee' => 0,
                'subtotalBenefit' => 0,
            ]),
            'totalCount' => 1,
            'totalScore' => 610,
            'totalFee' => 6832,
            'totalBenefit' => 6149,
            'totalCopay' => 683,
            'totalSubsidy' => 0,
            'items' => [
                DwsBillingInvoiceItem::create([
                    'paymentCategory' => DwsBillingPaymentCategory::category1(),
                    'serviceDivisionCode' => DwsServiceDivisionCode::homeHelpService(),
                    'subtotalCount' => 1,
                    'subtotalScore' => 610,
                    'subtotalFee' => 6832,
                    'subtotalBenefit' => 6149,
                    'subtotalCopay' => 683,
                    'subtotalSubsidy' => 0,
                ]),
            ],

            'createdAt' => carbon::now(),
            'updatedAt' => Carbon::now(),
            // コピー
            'id' => $actualInvoice->id,
        ]), $actualInvoice);

        // ServiceReport
        $serviceReportRepository = $this->getServiceReportRepository();
        $serviceReports = $serviceReportRepository->lookupByBundleId($actualBundle->id)->values()->flatten();
        $I->assertCount(1, $serviceReports);
        $serviceReport = $serviceReports->head();
        $I->assertModelStrictEquals(DwsBillingServiceReport::create([
            'user' => DwsBillingUser::from($this->examples->users[0], $certification),
            'format' => DwsBillingServiceReportFormat::homeHelpService(),
            'plan' => DwsBillingServiceReportAggregate::fromAssoc([
                DwsBillingServiceReportAggregateGroup::physicalCare()->value() => [
                    DwsBillingServiceReportAggregateCategory::category100()->value() => Decimal::zero(),
                    DwsBillingServiceReportAggregateCategory::category70()->value() => Decimal::zero(),
                    DwsBillingServiceReportAggregateCategory::categoryPwsd()->value() => Decimal::zero(),
                    DwsBillingServiceReportAggregateCategory::categoryTotal()->value() => Decimal::zero(),
                ],
                DwsBillingServiceReportAggregateGroup::accompanyWithPhysicalCare()->value() => [
                    DwsBillingServiceReportAggregateCategory::category100()->value() => Decimal::zero(),
                    DwsBillingServiceReportAggregateCategory::category70()->value() => Decimal::zero(),
                    DwsBillingServiceReportAggregateCategory::categoryPwsd()->value() => Decimal::zero(),
                    DwsBillingServiceReportAggregateCategory::categoryTotal()->value() => Decimal::zero(),
                ],
                DwsBillingServiceReportAggregateGroup::housework()->value() => [
                    DwsBillingServiceReportAggregateCategory::category100()->value() => Decimal::zero(),
                    DwsBillingServiceReportAggregateCategory::category90()->value() => Decimal::zero(),
                    DwsBillingServiceReportAggregateCategory::categoryTotal()->value() => Decimal::zero(),
                ],
                DwsBillingServiceReportAggregateGroup::accompany()->value() => [
                    DwsBillingServiceReportAggregateCategory::category100()->value() => Decimal::zero(),
                    DwsBillingServiceReportAggregateCategory::category90()->value() => Decimal::zero(),
                    DwsBillingServiceReportAggregateCategory::categoryTotal()->value() => Decimal::zero(),
                ],
                DwsBillingServiceReportAggregateGroup::accessibleTaxi()->value() => [
                    DwsBillingServiceReportAggregateCategory::category100()->value() => Decimal::zero(),
                    DwsBillingServiceReportAggregateCategory::category90()->value() => Decimal::zero(),
                    DwsBillingServiceReportAggregateCategory::categoryTotal()->value() => Decimal::zero(),
                ],
            ]),
            'result' => DwsBillingServiceReportAggregate::fromAssoc([
                DwsBillingServiceReportAggregateGroup::physicalCare()->value() => [
                    DwsBillingServiceReportAggregateCategory::category100()->value() => Decimal::fromInt(1_5000),
                    DwsBillingServiceReportAggregateCategory::category70()->value() => Decimal::zero(),
                    DwsBillingServiceReportAggregateCategory::categoryPwsd()->value() => Decimal::zero(),
                    DwsBillingServiceReportAggregateCategory::categoryTotal()->value() => Decimal::fromInt(1_5000),
                ],
                DwsBillingServiceReportAggregateGroup::accompanyWithPhysicalCare()->value() => [
                    DwsBillingServiceReportAggregateCategory::category100()->value() => Decimal::zero(),
                    DwsBillingServiceReportAggregateCategory::category70()->value() => Decimal::zero(),
                    DwsBillingServiceReportAggregateCategory::categoryPwsd()->value() => Decimal::zero(),
                    DwsBillingServiceReportAggregateCategory::categoryTotal()->value() => Decimal::zero(),
                ],
                DwsBillingServiceReportAggregateGroup::housework()->value() => [
                    DwsBillingServiceReportAggregateCategory::category100()->value() => Decimal::zero(),
                    DwsBillingServiceReportAggregateCategory::category90()->value() => Decimal::zero(),
                    DwsBillingServiceReportAggregateCategory::categoryTotal()->value() => Decimal::zero(),
                ],
                DwsBillingServiceReportAggregateGroup::accompany()->value() => [
                    DwsBillingServiceReportAggregateCategory::category100()->value() => Decimal::zero(),
                    DwsBillingServiceReportAggregateCategory::category90()->value() => Decimal::zero(),
                    DwsBillingServiceReportAggregateCategory::categoryTotal()->value() => Decimal::zero(),
                ],
                DwsBillingServiceReportAggregateGroup::accessibleTaxi()->value() => [
                    DwsBillingServiceReportAggregateCategory::category100()->value() => Decimal::zero(),
                    DwsBillingServiceReportAggregateCategory::category90()->value() => Decimal::zero(),
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
                    'providedOn' => Carbon::create(2022, 10, 1),
                    'serviceType' => DwsGrantedServiceCode::physicalCare(),
                    'providerType' => DwsBillingServiceReportProviderType::novice(),
                    'situation' => DwsBillingServiceReportSituation::none(),
                    'plan' => null,
                    'result' => DwsBillingServiceReportDuration::create([
                        'period' => CarbonRange::create([
                            'start' => Carbon::create(2022, 10, 1, 10, 0),
                            'end' => Carbon::create(2022, 10, 1, 11, 30),
                        ]),
                        'serviceDurationHours' => Decimal::fromInt(1_5000),
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
            'dwsBillingBundleId' => $actualBundle->id,
            'id' => $serviceReport->id,
        ], true), $serviceReport);
    }

    /**
     * リクエストパラメータの組み立て.
     *
     * @param array $overwrites
     * @return array
     */
    private function defaultParams(array $overwrites = []): array
    {
        return $overwrites + [
            'officeId' => $this->examples->offices[0]->id,
            'transactedIn' => '2022-11',
        ];
    }
}

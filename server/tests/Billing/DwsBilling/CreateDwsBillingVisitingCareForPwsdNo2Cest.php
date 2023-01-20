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
use Domain\Billing\DwsBillingOffice;
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
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;

class CreateDwsBillingVisitingCareForPwsdNo2Cest extends CreateDwsBillingTest
{
    use ExamplesConsumer;

    /**
     * COVID-19 加算 ＆ 移動加算 テスト.
     *
     * @param \BillingTester $I
     * @throws \Codeception\Exception\ModuleException
     * @noinspection PhpUnused
     */
    public function succeedCovid19AdditionWithMovingSupport(BillingTester $I): void
    {
        $I->wantTo('succeed API call with COVID-19 with Moving support');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);

        $office = $this->examples->offices[2];

        // 予実を準備
        $reportResults = [
            DwsProvisionReportItem::create([
                'schedule' => Schedule::create([
                    'date' => Carbon::create(2021, 4, 1),
                    'start' => Carbon::create(2021, 4, 1, 4, 00),
                    'end' => Carbon::create(2021, 4, 1, 7, 00),
                ]),
                'category' => DwsProjectServiceCategory::visitingCareForPwsd(),
                'headcount' => 1,
                'movingDurationMinutes' => 180,
                'options' => [],
                'note' => '設定例No.2: 移動あり',
            ], true),
            DwsProvisionReportItem::create([
                'schedule' => Schedule::create([
                    'date' => Carbon::create(2021, 4, 1),
                    'start' => Carbon::create(2021, 4, 1, 7, 30),
                    'end' => Carbon::create(2021, 4, 1, 11, 00),
                ]),
                'category' => DwsProjectServiceCategory::visitingCareForPwsd(),
                'headcount' => 1,
                'movingDurationMinutes' => 0,
                'options' => [],
                'note' => '設定例No.2: 移動あり',
            ]),
            DwsProvisionReportItem::create([
                'schedule' => Schedule::create([
                    'date' => Carbon::create(2021, 4, 1),
                    'start' => Carbon::create(2021, 4, 1, 13, 00),
                    'end' => Carbon::create(2021, 4, 1, 16, 30),
                ]),
                'category' => DwsProjectServiceCategory::visitingCareForPwsd(),
                'headcount' => 1,
                'movingDurationMinutes' => 0,
                'options' => [],
                'note' => '設定例No.2: 移動あり',
            ]),
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

        $I->sendPOST('dws-billings', ['transactedIn' => '2021-05', 'officeId' => $office->id]);

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
        $bundleRepository = $this->getBundleRepository();
        /** @var \Domain\Billing\DwsBillingBundle[]|\ScalikePHP\Seq $bundles */
        $bundles = $bundleRepository->lookupByBillingId($billing->id)->head()[1];
        $I->assertCount(1, $bundles);
        /** @var \Domain\Billing\DwsBillingBundle $bundle */
        $bundle = $bundles->head();

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
            'totalScore' => 2989,
            'totalFee' => 33476, // 合計単位数 2,989 単位 × 単価 11.20 円（端数切捨）
            'totalCappedCopay' => 3347,
            'totalAdjustedCopay' => null,
            'totalCoordinatedCopay' => null,
            'totalCopay' => 3347,
            'totalBenefit' => 30129,
            'totalSubsidy' => null,
            'isProvided' => true,
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
                    subtotalScore: 2989,
                    unitCost: $this->examples->dwsAreaGradeFees[0]->fee,
                    subtotalFee: 33476,
                    unmanagedCopay: 3347,
                    managedCopay: 3347,
                    cappedCopay: 3347,
                    adjustedCopay: null,
                    coordinatedCopay: null,
                    subtotalCopay: 3347,
                    subtotalBenefit: 30129,
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
                    serviceCode: ServiceCode::fromString('124371'), // 重訪Ⅲ深夜1.0
                    serviceCodeCategory: DwsServiceCodeCategory::visitingCareForPwsd3(),
                    unitScore: 278,
                    count: 1,
                    totalScore: 278,
                ),
                new DwsBillingStatementItem(
                    serviceCode: ServiceCode::fromString('124381'), // 重訪Ⅲ深夜1.5
                    serviceCodeCategory: DwsServiceCodeCategory::visitingCareForPwsd3(),
                    unitScore: 135,
                    count: 1,
                    totalScore: 135,
                ),
                new DwsBillingStatementItem(
                    serviceCode: ServiceCode::fromString('124491'), // 重訪Ⅲ深夜2.0
                    serviceCodeCategory: DwsServiceCodeCategory::visitingCareForPwsd3(),
                    unitScore: 138,
                    count: 1,
                    totalScore: 138,
                ),
                new DwsBillingStatementItem(
                    serviceCode: ServiceCode::fromString('122501'), // 重訪Ⅲ早朝2.5
                    serviceCodeCategory: DwsServiceCodeCategory::visitingCareForPwsd3(),
                    unitScore: 114,
                    count: 1,
                    totalScore: 114,
                ),
                new DwsBillingStatementItem(
                    serviceCode: ServiceCode::fromString('122511'), // 重訪Ⅲ早朝3.0
                    serviceCodeCategory: DwsServiceCodeCategory::visitingCareForPwsd3(),
                    unitScore: 115,
                    count: 1,
                    totalScore: 115,
                ),
                new DwsBillingStatementItem(
                    serviceCode: ServiceCode::fromString('122521'), // 重訪Ⅲ早朝3.5
                    serviceCodeCategory: DwsServiceCodeCategory::visitingCareForPwsd3(),
                    unitScore: 113,
                    count: 1,
                    totalScore: 113,
                ),
                new DwsBillingStatementItem(
                    serviceCode: ServiceCode::fromString('121531'), // 重訪Ⅲ日中4.0
                    serviceCodeCategory: DwsServiceCodeCategory::visitingCareForPwsd3(),
                    unitScore: 92,
                    count: 1,
                    totalScore: 92,
                ),
                new DwsBillingStatementItem(
                    serviceCode: ServiceCode::fromString('121321'), // 重訪Ⅲ日中8.0
                    serviceCodeCategory: DwsServiceCodeCategory::visitingCareForPwsd3(),
                    unitScore: 85,
                    count: 8,
                    totalScore: 680,
                ),
                new DwsBillingStatementItem(
                    serviceCode: ServiceCode::fromString('121331'), // 重訪Ⅲ日中12.0
                    serviceCodeCategory: DwsServiceCodeCategory::visitingCareForPwsd3(),
                    unitScore: 85,
                    count: 4,
                    totalScore: 340,
                ),
                new DwsBillingStatementItem(
                    serviceCode: ServiceCode::fromString('128453'), // 移動加算 1.0
                    serviceCodeCategory: DwsServiceCodeCategory::outingSupportForPwsd(),
                    unitScore: 100,
                    count: 1,
                    totalScore: 100,
                ),
                new DwsBillingStatementItem(
                    serviceCode: ServiceCode::fromString('128457'), // 移動加算 1.5
                    serviceCodeCategory: DwsServiceCodeCategory::outingSupportForPwsd(),
                    unitScore: 25,
                    count: 1,
                    totalScore: 25,
                ),
                new DwsBillingStatementItem(
                    serviceCode: ServiceCode::fromString('128461'), // 移動加算 2.0
                    serviceCodeCategory: DwsServiceCodeCategory::outingSupportForPwsd(),
                    unitScore: 25,
                    count: 1,
                    totalScore: 25,
                ),
                new DwsBillingStatementItem(
                    serviceCode: ServiceCode::fromString('128465'), // 移動加算 2.5
                    serviceCodeCategory: DwsServiceCodeCategory::outingSupportForPwsd(),
                    unitScore: 25,
                    count: 1,
                    totalScore: 25,
                ),
                new DwsBillingStatementItem(
                    serviceCode: ServiceCode::fromString('128469'), // 移動加算 3.0
                    serviceCodeCategory: DwsServiceCodeCategory::outingSupportForPwsd(),
                    unitScore: 25,
                    count: 1,
                    totalScore: 25,
                ),
                new DwsBillingStatementItem(
                    serviceCode: ServiceCode::fromString('12ZZ01'), // 令和3年9月30日までの上乗せ分（重訪）
                    serviceCodeCategory: DwsServiceCodeCategory::covid19PandemicSpecialAddition(),
                    unitScore: 2, // 移動介護加算を除く 2,005 単位 × 0.1% = 2.005 を四捨五入
                    count: 1,
                    totalScore: 2,
                ),
                new DwsBillingStatementItem(
                    serviceCode: ServiceCode::fromString('126010'), // 特定事業所加算1
                    serviceCodeCategory: DwsServiceCodeCategory::specifiedOfficeAddition1(),
                    unitScore: 401, // 移動介護加算を除く (2,005 + 2) = 2,007 単位 × 20% = 401.4 を四捨五入
                    count: 1,
                    totalScore: 401,
                ),
                new DwsBillingStatementItem(
                    serviceCode: ServiceCode::fromString('126710'), // 処遇改善加算2
                    serviceCodeCategory: DwsServiceCodeCategory::treatmentImprovementAddition2(),
                    unitScore: 381, // これまでの合計 2,608 単位 × 14.6% = 380.768 を四捨五入
                    count: 1,
                    totalScore: 381,
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
                    DwsBillingServiceReportAggregateCategory::categoryTotal()->value() => Decimal::fromInt(10_0000),
                ],
                DwsBillingServiceReportAggregateGroup::outingSupportForPwsd()->value() => [
                    DwsBillingServiceReportAggregateCategory::categoryTotal()->value() => Decimal::fromInt(3_0000),
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
                    'providedOn' => Carbon::create(2021, 4, 1),
                    'serviceType' => DwsGrantedServiceCode::visitingCareForPwsd3(),
                    'providerType' => DwsBillingServiceReportProviderType::none(),
                    'situation' => DwsBillingServiceReportSituation::none(),
                    'plan' => null,
                    'result' => DwsBillingServiceReportDuration::create([
                        'period' => CarbonRange::create([
                            'start' => Carbon::create(2021, 4, 1, 4, 0),
                            'end' => Carbon::create(2021, 4, 1, 7, 0),
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
                    'providedOn' => Carbon::create(2021, 4, 1),
                    'serviceType' => DwsGrantedServiceCode::visitingCareForPwsd3(),
                    'providerType' => DwsBillingServiceReportProviderType::none(),
                    'situation' => DwsBillingServiceReportSituation::none(),
                    'plan' => null,
                    'result' => DwsBillingServiceReportDuration::create([
                        'period' => CarbonRange::create([
                            'start' => Carbon::create(2021, 4, 1, 7, 30),
                            'end' => Carbon::create(2021, 4, 1, 11, 0),
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
                    'providedOn' => Carbon::create(2021, 4, 1),
                    'serviceType' => DwsGrantedServiceCode::visitingCareForPwsd3(),
                    'providerType' => DwsBillingServiceReportProviderType::none(),
                    'situation' => DwsBillingServiceReportSituation::none(),
                    'plan' => null,
                    'result' => DwsBillingServiceReportDuration::create([
                        'period' => CarbonRange::create([
                            'start' => Carbon::create(2021, 4, 1, 13, 0),
                            'end' => Carbon::create(2021, 4, 1, 16, 30),
                        ]),
                        'serviceDurationHours' => Decimal::fromInt(10_0000),
                        'movingDurationHours' => Decimal::fromInt(3_0000),
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
}

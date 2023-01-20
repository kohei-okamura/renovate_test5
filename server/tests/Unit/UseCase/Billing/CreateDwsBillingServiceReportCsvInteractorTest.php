<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\Billing;

use Domain\Billing\DwsBilling;
use Domain\Billing\DwsBillingBundle;
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
use Domain\Billing\DwsBillingStatus;
use Domain\Billing\DwsBillingUser;
use Domain\Billing\DwsGrantedServiceCode;
use Domain\Common\Addr;
use Domain\Common\Carbon;
use Domain\Common\CarbonRange;
use Domain\Common\Decimal;
use Domain\Common\Prefecture;
use Domain\Exchange\DwsBillingServiceReportItemRecord;
use Domain\Exchange\DwsBillingServiceReportSummaryRecord;
use Domain\Exchange\DwsControlRecord;
use Domain\Exchange\EndRecord;
use Lib\Csv;
use Lib\Exceptions\FileIOException;
use Lib\Exceptions\LogicException;
use Mockery;
use ScalikePHP\Seq;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\BuildDwsBillingServiceReportRecordListUseCaseMixin;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\ConfigMixin;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\GenerateFileNameUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\StoreCsvUseCaseMixin;
use Tests\Unit\Mixins\TokenMakerMixin;
use Tests\Unit\Test;
use UseCase\Billing\CreateDwsBillingServiceReportCsvInteractor;

/**
 * {@link \UseCase\Billing\CreateDwsBillingServiceReportCsvInteractor} のテスト.
 */
final class CreateDwsBillingServiceReportCsvInteractorTest extends Test
{
    use BuildDwsBillingServiceReportRecordListUseCaseMixin;
    use CarbonMixin;
    use ConfigMixin;
    use ContextMixin;
    use ExamplesConsumer;
    use GenerateFileNameUseCaseMixin;
    use MatchesSnapshots;
    use MockeryMixin;
    use StoreCsvUseCaseMixin;
    use TokenMakerMixin;
    use UnitSupport;

    private const FILENAME = 'dummy.csv';

    private DwsBilling $dwsBilling;
    /** @var \Domain\Billing\DwsBillingBundle[]&\ScalikePHP\Seq */
    private Seq $dwsBillingBundles;
    private CreateDwsBillingServiceReportCsvInteractor $interactor;

    /**
     * 初期化処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachSpec(function (CreateDwsBillingServiceReportCsvInteractorTest $self): void {
            $self->dwsBilling = $self->createDwsBillingStub();
            $self->dwsBillingBundles = Seq::from($self->createDwsBillingBundleStub());

            $self->buildDwsBillingServiceReportRecordListUseCase
                ->allows('handle')
                ->andReturn([])
                ->byDefault();

            $self->storeCsvUseCase
                ->allows('handle')
                ->andReturn('path/to/stored-file.csv')
                ->byDefault();

            $self->config
                ->allows('get')
                ->with('zinger.filename.dws_service_report_csv')
                ->andReturn('サービス提供実績記録票_#{office}_#{transactedIn}.csv')
                ->byDefault();

            $self->tokenMaker
                ->allows('make')
                ->andReturn(str_repeat('x', 60))
                ->byDefault();

            $self->interactor = app(CreateDwsBillingServiceReportCsvInteractor::class);
            $self->generateFileNameUseCase
                ->allows('handle')
                ->andReturn(self::FILENAME)
                ->byDefault();
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('create records using BuildDwsBillingServiceReportRecordListUseCase', function (): void {
            $this->buildDwsBillingServiceReportRecordListUseCase
                ->expects('handle')
                ->with($this->context, $this->dwsBilling, $this->dwsBillingBundles)
                ->andReturn([]);

            $this->interactor->handle($this->context, $this->dwsBilling, $this->dwsBillingBundles);
        });
        $this->should('throw LogicException if bundles have multiple providedIn', function (): void {
            $multipleProvideInDwsBillingBundles = Seq::from(
                $this->examples->dwsBillingBundles[0],
                $this->examples->dwsBillingBundles[1]->copy([
                    'providedIn' => Carbon::parse('2022-11-11'),
                ]),
            );
            $this->assertThrows(
                LogicException::class,
                function () use ($multipleProvideInDwsBillingBundles): void {
                    $this->interactor->handle($this->context, $this->dwsBilling, $multipleProvideInDwsBillingBundles);
                }
            );
        });
        $this->should('store the csv', function (): void {
            $this->buildDwsBillingServiceReportRecordListUseCase
                ->expects('handle')
                ->andReturnUsing(fn (): array => $this->createExchangeRecordsStub());
            $this->storeCsvUseCase
                ->expects('handle')
                ->with($this->context, 'artifacts', 'dws-billing-', Mockery::capture($actual))
                ->andReturn('path/to/stored-file.csv');

            $this->interactor->handle($this->context, $this->dwsBilling, $this->dwsBillingBundles);

            $this->assertEquals(
                Csv::read(__DIR__ . '/CreateDwsBillingServiceReportCsvInteractorTest.csv')->toArray(),
                is_array($actual) ? $actual : iterator_to_array($actual),
            );
        });
        $this->should('throw FileIOException when StoreCsvUseCase throws it', function (): void {
            $this->storeCsvUseCase
                ->expects('handle')
                ->andThrow(new FileIOException('Failed to store file'));

            $this->assertThrows(FileIOException::class, function (): void {
                $this->interactor->handle($this->context, $this->dwsBilling, $this->dwsBillingBundles);
            });
        });
        $this->should('return an instance of DwsBillingFile', function (): void {
            // TODO: DEV-4532 バックエンドのスナップショットテスト対応
            $this->markTestSkipped();
        });
    }

    /**
     * テスト用の伝送レコードを生成する.
     */
    private function createExchangeRecordsStub(): array
    {
        $billing = $this->dwsBilling;
        $bundle = $this->dwsBillingBundles->head();
        $serviceReports = $this->createDwsBillingServiceReportStub();
        $records = Seq::fromArray($serviceReports)
            ->flatMap(fn (DwsBillingServiceReport $x): iterable => [
                DwsBillingServiceReportSummaryRecord::from($billing, $bundle, $x),
                ...DwsBillingServiceReportItemRecord::from($billing, $bundle, $x),
            ]);
        return [
            DwsControlRecord::forServiceReport($billing, count($records)),
            ...$records,
            EndRecord::instance(),
        ];
    }

    /**
     * テスト用の請求を生成する.
     *
     * @return \Domain\Billing\DwsBilling
     */
    private function createDwsBillingStub(): DwsBilling
    {
        return DwsBilling::create([
            'id' => 1,
            'organizationId' => $this->examples->organizations[0]->id,
            'office' => DwsBillingOffice::create([
                'officeId' => 1,
                'code' => '1210103949',
                'name' => 'ﾃｽﾄﾀﾞｶﾗﾅﾝﾃﾞﾓｲｲﾔ',
                'abbr' => '事業1',
                'addr' => new Addr(
                    postcode: '739-0604',
                    prefecture: Prefecture::hiroshima(),
                    city: '大竹市',
                    street: '北栄1-13-11',
                    apartment: '北栄荘411',
                ),
                'tel' => '090-3169-6661',
            ]),
            'transactedIn' => Carbon::create(2020, 12),
            'files' => [],
            'status' => DwsBillingStatus::fixed(),
            'fixedAt' => Carbon::now(),
            'createdAt' => Carbon::now(),
            'updatedAt' => Carbon::now(),
        ]);
    }

    /**
     * テスト用の請求単位を生成する.
     *
     * @return \Domain\Billing\DwsBillingBundle
     */
    private function createDwsBillingBundleStub(): DwsBillingBundle
    {
        return DwsBillingBundle::create([
            'id' => 1,
            'dwsBillingId' => 1,
            'providedIn' => Carbon::create(2020, 9),
            'cityCode' => '122127',
            'cityName' => 'ﾓﾘｵｳﾁｮｳ',
            'details' => [],
            'createdAt' => Carbon::now(),
            'updatedAt' => Carbon::now(),
        ]);
    }

    /**
     * テスト用のサービス提供実績記録票を生成する.
     *
     * @return array
     */
    private function createDwsBillingServiceReportStub(): array
    {
        // CSVにおいてplanは不要なのでこのテストではnullにしておく。
        return [
            DwsBillingServiceReport::create([
                'id' => 1,
                'dwsBillingId' => 1,
                'dwsBillingBundleId' => 1,
                'user' => DwsBillingUser::create([
                    'userId' => 1,
                    'dwsCertificationId' => 1,
                    'dwsNumber' => '1221200064',
                    'name' => 'ﾀﾞﾚﾃﾞﾓｲｲ',
                    'childName' => '',
                    'copayLimit' => 10000,
                ]),
                'format' => DwsBillingServiceReportFormat::visitingCareForPwsd(),
                'emergencyCount' => 0,
                'firstTimeCount' => 0,
                'welfareSpecialistCooperationCount' => 0,
                'behavioralDisorderSupportCooperationCount' => 0,
                'movingCareSupportCount' => 0,
                'plan' => null,
                'result' => DwsBillingServiceReportAggregate::fromAssoc([
                    DwsBillingServiceReportAggregateGroup::visitingCareForPwsd()->value() => [
                        DwsBillingServiceReportAggregateCategory::categoryTotal()->value() => Decimal::fromInt(268_5000),
                    ],
                ]),
                'items' => [
                    DwsBillingServiceReportItem::create([
                        'serialNumber' => 1,
                        'providedOn' => Carbon::create(2020, 9, 8),
                        'serviceType' => DwsGrantedServiceCode::none(),
                        'providerType' => DwsBillingServiceReportProviderType::none(),
                        'situation' => DwsBillingServiceReportSituation::none(),
                        'plan' => null,
                        'result' => DwsBillingServiceReportDuration::create([
                            'period' => CarbonRange::create([
                                'start' => Carbon::create(2020, 9, 8, 21, 00),
                                'end' => Carbon::create(2020, 9, 9, 00, 00),
                            ]),
                            'serviceDurationHours' => Decimal::fromInt(3_0000),
                            'movingDurationHours' => null,
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
                        'serialNumber' => 2,
                        'providedOn' => Carbon::create(2020, 9, 9),
                        'serviceType' => DwsGrantedServiceCode::none(),
                        'providerType' => DwsBillingServiceReportProviderType::none(),
                        'situation' => DwsBillingServiceReportSituation::none(),
                        'plan' => null,
                        'result' => DwsBillingServiceReportDuration::create([
                            'period' => CarbonRange::create([
                                'start' => Carbon::create(2020, 9, 9, 00, 00),
                                'end' => Carbon::create(2020, 9, 9, 9, 00),
                            ]),
                            'serviceDurationHours' => null,
                            'movingDurationHours' => null,
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
                        'serialNumber' => 2,
                        'providedOn' => Carbon::create(2020, 9, 9),
                        'serviceType' => DwsGrantedServiceCode::none(),
                        'providerType' => DwsBillingServiceReportProviderType::none(),
                        'situation' => DwsBillingServiceReportSituation::none(),
                        'plan' => null,
                        'result' => DwsBillingServiceReportDuration::create([
                            'period' => CarbonRange::create([
                                'start' => Carbon::create(2020, 9, 9, 21, 00),
                                'end' => Carbon::create(2020, 9, 10, 00, 00),
                            ]),
                            'serviceDurationHours' => Decimal::fromInt(12_0000),
                            'movingDurationHours' => null,
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
                        'serialNumber' => 3,
                        'providedOn' => Carbon::create(2020, 9, 10),
                        'serviceType' => DwsGrantedServiceCode::none(),
                        'providerType' => DwsBillingServiceReportProviderType::none(),
                        'situation' => DwsBillingServiceReportSituation::none(),
                        'plan' => null,
                        'result' => DwsBillingServiceReportDuration::create([
                            'period' => CarbonRange::create([
                                'start' => Carbon::create(2020, 9, 10, 00, 00),
                                'end' => Carbon::create(2020, 9, 10, 9, 30),
                            ]),
                            'serviceDurationHours' => null,
                            'movingDurationHours' => null,
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
                        'serialNumber' => 3,
                        'providedOn' => Carbon::create(2020, 9, 10),
                        'serviceType' => DwsGrantedServiceCode::none(),
                        'providerType' => DwsBillingServiceReportProviderType::none(),
                        'situation' => DwsBillingServiceReportSituation::none(),
                        'plan' => null,
                        'result' => DwsBillingServiceReportDuration::create([
                            'period' => CarbonRange::create([
                                'start' => Carbon::create(2020, 9, 10, 21, 00),
                                'end' => Carbon::create(2020, 9, 11, 00, 00),
                            ]),
                            'serviceDurationHours' => Decimal::fromInt(12_5000),
                            'movingDurationHours' => null,
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
                        'serialNumber' => 4,
                        'providedOn' => Carbon::create(2020, 9, 11),
                        'serviceType' => DwsGrantedServiceCode::none(),
                        'providerType' => DwsBillingServiceReportProviderType::none(),
                        'situation' => DwsBillingServiceReportSituation::none(),
                        'plan' => null,
                        'result' => DwsBillingServiceReportDuration::create([
                            'period' => CarbonRange::create([
                                'start' => Carbon::create(2020, 9, 11, 00, 00),
                                'end' => Carbon::create(2020, 9, 11, 9, 30),
                            ]),
                            'serviceDurationHours' => null,
                            'movingDurationHours' => null,
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
                        'serialNumber' => 4,
                        'providedOn' => Carbon::create(2020, 9, 11),
                        'serviceType' => DwsGrantedServiceCode::none(),
                        'providerType' => DwsBillingServiceReportProviderType::none(),
                        'situation' => DwsBillingServiceReportSituation::none(),
                        'plan' => null,
                        'result' => DwsBillingServiceReportDuration::create([
                            'period' => CarbonRange::create([
                                'start' => Carbon::create(2020, 9, 11, 21, 00),
                                'end' => Carbon::create(2020, 9, 12, 00, 00),
                            ]),
                            'serviceDurationHours' => Decimal::fromInt(12_5000),
                            'movingDurationHours' => null,
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
                        'serialNumber' => 5,
                        'providedOn' => Carbon::create(2020, 9, 12),
                        'serviceType' => DwsGrantedServiceCode::none(),
                        'providerType' => DwsBillingServiceReportProviderType::none(),
                        'situation' => DwsBillingServiceReportSituation::none(),
                        'plan' => null,
                        'result' => DwsBillingServiceReportDuration::create([
                            'period' => CarbonRange::create([
                                'start' => Carbon::create(2020, 9, 12, 00, 00),
                                'end' => Carbon::create(2020, 9, 12, 9, 30),
                            ]),
                            'serviceDurationHours' => null,
                            'movingDurationHours' => null,
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
                        'serialNumber' => 5,
                        'providedOn' => Carbon::create(2020, 9, 12),
                        'serviceType' => DwsGrantedServiceCode::none(),
                        'providerType' => DwsBillingServiceReportProviderType::none(),
                        'situation' => DwsBillingServiceReportSituation::none(),
                        'plan' => null,
                        'result' => DwsBillingServiceReportDuration::create([
                            'period' => CarbonRange::create([
                                'start' => Carbon::create(2020, 9, 12, 21, 00),
                                'end' => Carbon::create(2020, 9, 12, 00, 00),
                            ]),
                            'serviceDurationHours' => Decimal::fromInt(12_5000),
                            'movingDurationHours' => null,
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
                        'serialNumber' => 6,
                        'providedOn' => Carbon::create(2020, 9, 13),
                        'serviceType' => DwsGrantedServiceCode::none(),
                        'providerType' => DwsBillingServiceReportProviderType::none(),
                        'situation' => DwsBillingServiceReportSituation::none(),
                        'plan' => null,
                        'result' => DwsBillingServiceReportDuration::create([
                            'period' => CarbonRange::create([
                                'start' => Carbon::create(2020, 9, 13, 00, 00),
                                'end' => Carbon::create(2020, 9, 13, 9, 30),
                            ]),
                            'serviceDurationHours' => null,
                            'movingDurationHours' => null,
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
                        'serialNumber' => 6,
                        'providedOn' => Carbon::create(2020, 9, 13),
                        'serviceType' => DwsGrantedServiceCode::none(),
                        'providerType' => DwsBillingServiceReportProviderType::none(),
                        'situation' => DwsBillingServiceReportSituation::none(),
                        'plan' => null,
                        'result' => DwsBillingServiceReportDuration::create([
                            'period' => CarbonRange::create([
                                'start' => Carbon::create(2020, 9, 13, 21, 00),
                                'end' => Carbon::create(2020, 9, 14, 00, 00),
                            ]),
                            'serviceDurationHours' => Decimal::fromInt(12_5000),
                            'movingDurationHours' => null,
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
                        'serialNumber' => 7,
                        'providedOn' => Carbon::create(2020, 9, 14),
                        'serviceType' => DwsGrantedServiceCode::none(),
                        'providerType' => DwsBillingServiceReportProviderType::none(),
                        'situation' => DwsBillingServiceReportSituation::none(),
                        'plan' => null,
                        'result' => DwsBillingServiceReportDuration::create([
                            'period' => CarbonRange::create([
                                'start' => Carbon::create(2020, 9, 14, 00, 00),
                                'end' => Carbon::create(2020, 9, 15, 9, 00),
                            ]),
                            'serviceDurationHours' => null,
                            'movingDurationHours' => null,
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
                        'serialNumber' => 7,
                        'providedOn' => Carbon::create(2020, 9, 14),
                        'serviceType' => DwsGrantedServiceCode::none(),
                        'providerType' => DwsBillingServiceReportProviderType::none(),
                        'situation' => DwsBillingServiceReportSituation::none(),
                        'plan' => null,
                        'result' => DwsBillingServiceReportDuration::create([
                            'period' => CarbonRange::create([
                                'start' => Carbon::create(2020, 9, 14, 21, 00),
                                'end' => Carbon::create(2020, 9, 15, 00, 00),
                            ]),
                            'serviceDurationHours' => Decimal::fromInt(12_0000),
                            'movingDurationHours' => null,
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
                        'serialNumber' => 8,
                        'providedOn' => Carbon::create(2020, 9, 15),
                        'serviceType' => DwsGrantedServiceCode::none(),
                        'providerType' => DwsBillingServiceReportProviderType::none(),
                        'situation' => DwsBillingServiceReportSituation::none(),
                        'plan' => null,
                        'result' => DwsBillingServiceReportDuration::create([
                            'period' => CarbonRange::create([
                                'start' => Carbon::create(2020, 9, 15, 00, 00),
                                'end' => Carbon::create(2020, 9, 15, 9, 30),
                            ]),
                            'serviceDurationHours' => null,
                            'movingDurationHours' => null,
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
                        'serialNumber' => 8,
                        'providedOn' => Carbon::create(2020, 9, 15),
                        'serviceType' => DwsGrantedServiceCode::none(),
                        'providerType' => DwsBillingServiceReportProviderType::none(),
                        'situation' => DwsBillingServiceReportSituation::none(),
                        'plan' => null,
                        'result' => DwsBillingServiceReportDuration::create([
                            'period' => CarbonRange::create([
                                'start' => Carbon::create(2020, 9, 15, 21, 00),
                                'end' => Carbon::create(2020, 9, 16, 00, 00),
                            ]),
                            'serviceDurationHours' => Decimal::fromInt(12_5000),
                            'movingDurationHours' => null,
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
                        'serialNumber' => 9,
                        'providedOn' => Carbon::create(2020, 9, 16),
                        'serviceType' => DwsGrantedServiceCode::none(),
                        'providerType' => DwsBillingServiceReportProviderType::none(),
                        'situation' => DwsBillingServiceReportSituation::none(),
                        'plan' => null,
                        'result' => DwsBillingServiceReportDuration::create([
                            'period' => CarbonRange::create([
                                'start' => Carbon::create(2020, 9, 16, 00, 00),
                                'end' => Carbon::create(2020, 9, 16, 9, 30),
                            ]),
                            'serviceDurationHours' => null,
                            'movingDurationHours' => null,
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
                        'serialNumber' => 9,
                        'providedOn' => Carbon::create(2020, 9, 16),
                        'serviceType' => DwsGrantedServiceCode::none(),
                        'providerType' => DwsBillingServiceReportProviderType::none(),
                        'situation' => DwsBillingServiceReportSituation::none(),
                        'plan' => null,
                        'result' => DwsBillingServiceReportDuration::create([
                            'period' => CarbonRange::create([
                                'start' => Carbon::create(2020, 9, 16, 21, 00),
                                'end' => Carbon::create(2020, 9, 17, 00, 00),
                            ]),
                            'serviceDurationHours' => Decimal::fromInt(12_5000),
                            'movingDurationHours' => null,
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
                        'serialNumber' => 10,
                        'providedOn' => Carbon::create(2020, 9, 17),
                        'serviceType' => DwsGrantedServiceCode::none(),
                        'providerType' => DwsBillingServiceReportProviderType::none(),
                        'situation' => DwsBillingServiceReportSituation::none(),
                        'plan' => null,
                        'result' => DwsBillingServiceReportDuration::create([
                            'period' => CarbonRange::create([
                                'start' => Carbon::create(2020, 9, 17, 00, 00),
                                'end' => Carbon::create(2020, 9, 17, 9, 00),
                            ]),
                            'serviceDurationHours' => null,
                            'movingDurationHours' => null,
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
                        'serialNumber' => 10,
                        'providedOn' => Carbon::create(2020, 9, 17),
                        'serviceType' => DwsGrantedServiceCode::none(),
                        'providerType' => DwsBillingServiceReportProviderType::none(),
                        'situation' => DwsBillingServiceReportSituation::none(),
                        'plan' => null,
                        'result' => DwsBillingServiceReportDuration::create([
                            'period' => CarbonRange::create([
                                'start' => Carbon::create(2020, 9, 17, 21, 00),
                                'end' => Carbon::create(2020, 9, 17, 00, 00),
                            ]),
                            'serviceDurationHours' => Decimal::fromInt(12_0000),
                            'movingDurationHours' => null,
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
                        'serialNumber' => 11,
                        'providedOn' => Carbon::create(2020, 9, 18),
                        'serviceType' => DwsGrantedServiceCode::none(),
                        'providerType' => DwsBillingServiceReportProviderType::none(),
                        'situation' => DwsBillingServiceReportSituation::none(),
                        'plan' => null,
                        'result' => DwsBillingServiceReportDuration::create([
                            'period' => CarbonRange::create([
                                'start' => Carbon::create(2020, 9, 18, 00, 00),
                                'end' => Carbon::create(2020, 9, 18, 9, 30),
                            ]),
                            'serviceDurationHours' => null,
                            'movingDurationHours' => null,
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
                        'serialNumber' => 11,
                        'providedOn' => Carbon::create(2020, 9, 18),
                        'serviceType' => DwsGrantedServiceCode::none(),
                        'providerType' => DwsBillingServiceReportProviderType::none(),
                        'situation' => DwsBillingServiceReportSituation::none(),
                        'plan' => null,
                        'result' => DwsBillingServiceReportDuration::create([
                            'period' => CarbonRange::create([
                                'start' => Carbon::create(2020, 9, 18, 21, 00),
                                'end' => Carbon::create(2020, 9, 18, 00, 00),
                            ]),
                            'serviceDurationHours' => Decimal::fromInt(12_5000),
                            'movingDurationHours' => null,
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
                        'serialNumber' => 12,
                        'providedOn' => Carbon::create(2020, 9, 19),
                        'serviceType' => DwsGrantedServiceCode::none(),
                        'providerType' => DwsBillingServiceReportProviderType::none(),
                        'situation' => DwsBillingServiceReportSituation::none(),
                        'plan' => null,
                        'result' => DwsBillingServiceReportDuration::create([
                            'period' => CarbonRange::create([
                                'start' => Carbon::create(2020, 9, 19, 00, 00),
                                'end' => Carbon::create(2020, 9, 19, 9, 45),
                            ]),
                            'serviceDurationHours' => null,
                            'movingDurationHours' => null,
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
                        'serialNumber' => 12,
                        'providedOn' => Carbon::create(2020, 9, 19),
                        'serviceType' => DwsGrantedServiceCode::none(),
                        'providerType' => DwsBillingServiceReportProviderType::none(),
                        'situation' => DwsBillingServiceReportSituation::none(),
                        'plan' => null,
                        'result' => DwsBillingServiceReportDuration::create([
                            'period' => CarbonRange::create([
                                'start' => Carbon::create(2020, 9, 19, 21, 00),
                                'end' => Carbon::create(2020, 9, 20, 00, 00),
                            ]),
                            'serviceDurationHours' => Decimal::fromInt(13_0000),
                            'movingDurationHours' => null,
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
                        'serialNumber' => 13,
                        'providedOn' => Carbon::create(2020, 9, 20),
                        'serviceType' => DwsGrantedServiceCode::none(),
                        'providerType' => DwsBillingServiceReportProviderType::none(),
                        'situation' => DwsBillingServiceReportSituation::none(),
                        'plan' => null,
                        'result' => DwsBillingServiceReportDuration::create([
                            'period' => CarbonRange::create([
                                'start' => Carbon::create(2020, 9, 20, 00, 00),
                                'end' => Carbon::create(2020, 9, 20, 9, 30),
                            ]),
                            'serviceDurationHours' => null,
                            'movingDurationHours' => null,
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
                        'serialNumber' => 13,
                        'providedOn' => Carbon::create(2020, 9, 20),
                        'serviceType' => DwsGrantedServiceCode::none(),
                        'providerType' => DwsBillingServiceReportProviderType::none(),
                        'situation' => DwsBillingServiceReportSituation::none(),
                        'plan' => null,
                        'result' => DwsBillingServiceReportDuration::create([
                            'period' => CarbonRange::create([
                                'start' => Carbon::create(2020, 9, 20, 21, 00),
                                'end' => Carbon::create(2020, 9, 20, 00, 00),
                            ]),
                            'serviceDurationHours' => Decimal::fromInt(12_5000),
                            'movingDurationHours' => null,
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
                        'serialNumber' => 14,
                        'providedOn' => Carbon::create(2020, 9, 21),
                        'serviceType' => DwsGrantedServiceCode::none(),
                        'providerType' => DwsBillingServiceReportProviderType::none(),
                        'situation' => DwsBillingServiceReportSituation::none(),
                        'plan' => null,
                        'result' => DwsBillingServiceReportDuration::create([
                            'period' => CarbonRange::create([
                                'start' => Carbon::create(2020, 9, 21, 00, 00),
                                'end' => Carbon::create(2020, 9, 21, 9, 10),
                            ]),
                            'serviceDurationHours' => null,
                            'movingDurationHours' => null,
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
                        'serialNumber' => 14,
                        'providedOn' => Carbon::create(2020, 9, 21),
                        'serviceType' => DwsGrantedServiceCode::none(),
                        'providerType' => DwsBillingServiceReportProviderType::none(),
                        'situation' => DwsBillingServiceReportSituation::none(),
                        'plan' => null,
                        'result' => DwsBillingServiceReportDuration::create([
                            'period' => CarbonRange::create([
                                'start' => Carbon::create(2020, 9, 21, 21, 00),
                                'end' => Carbon::create(2020, 9, 21, 00, 00),
                            ]),
                            'serviceDurationHours' => Decimal::fromInt(12_5000),
                            'movingDurationHours' => null,
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
                        'serialNumber' => 15,
                        'providedOn' => Carbon::create(2020, 9, 22),
                        'serviceType' => DwsGrantedServiceCode::none(),
                        'providerType' => DwsBillingServiceReportProviderType::none(),
                        'situation' => DwsBillingServiceReportSituation::none(),
                        'plan' => null,
                        'result' => DwsBillingServiceReportDuration::create([
                            'period' => CarbonRange::create([
                                'start' => Carbon::create(2020, 9, 22, 00, 00),
                                'end' => Carbon::create(2020, 9, 22, 9, 00),
                            ]),
                            'serviceDurationHours' => null,
                            'movingDurationHours' => null,
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
                        'serialNumber' => 15,
                        'providedOn' => Carbon::create(2020, 9, 22),
                        'serviceType' => DwsGrantedServiceCode::none(),
                        'providerType' => DwsBillingServiceReportProviderType::none(),
                        'situation' => DwsBillingServiceReportSituation::none(),
                        'plan' => null,
                        'result' => DwsBillingServiceReportDuration::create([
                            'period' => CarbonRange::create([
                                'start' => Carbon::create(2020, 9, 22, 21, 00),
                                'end' => Carbon::create(2020, 9, 23, 00, 00),
                            ]),
                            'serviceDurationHours' => Decimal::fromInt(12_0000),
                            'movingDurationHours' => null,
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
                        'serialNumber' => 16,
                        'providedOn' => Carbon::create(2020, 9, 23),
                        'serviceType' => DwsGrantedServiceCode::none(),
                        'providerType' => DwsBillingServiceReportProviderType::none(),
                        'situation' => DwsBillingServiceReportSituation::none(),
                        'plan' => null,
                        'result' => DwsBillingServiceReportDuration::create([
                            'period' => CarbonRange::create([
                                'start' => Carbon::create(2020, 9, 23, 00, 00),
                                'end' => Carbon::create(2020, 9, 23, 9, 30),
                            ]),
                            'serviceDurationHours' => null,
                            'movingDurationHours' => null,
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
                        'serialNumber' => 16,
                        'providedOn' => Carbon::create(2020, 9, 23),
                        'serviceType' => DwsGrantedServiceCode::none(),
                        'providerType' => DwsBillingServiceReportProviderType::none(),
                        'situation' => DwsBillingServiceReportSituation::none(),
                        'plan' => null,
                        'result' => DwsBillingServiceReportDuration::create([
                            'period' => CarbonRange::create([
                                'start' => Carbon::create(2020, 9, 23, 21, 00),
                                'end' => Carbon::create(2020, 9, 23, 00, 00),
                            ]),
                            'serviceDurationHours' => Decimal::fromInt(12_5000),
                            'movingDurationHours' => null,
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
                        'serialNumber' => 17,
                        'providedOn' => Carbon::create(2020, 9, 24),
                        'serviceType' => DwsGrantedServiceCode::none(),
                        'providerType' => DwsBillingServiceReportProviderType::none(),
                        'situation' => DwsBillingServiceReportSituation::none(),
                        'plan' => null,
                        'result' => DwsBillingServiceReportDuration::create([
                            'period' => CarbonRange::create([
                                'start' => Carbon::create(2020, 9, 24, 00, 00),
                                'end' => Carbon::create(2020, 9, 24, 9, 30),
                            ]),
                            'serviceDurationHours' => null,
                            'movingDurationHours' => null,
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
                        'serialNumber' => 17,
                        'providedOn' => Carbon::create(2020, 9, 24),
                        'serviceType' => DwsGrantedServiceCode::none(),
                        'providerType' => DwsBillingServiceReportProviderType::none(),
                        'situation' => DwsBillingServiceReportSituation::none(),
                        'plan' => null,
                        'result' => DwsBillingServiceReportDuration::create([
                            'period' => CarbonRange::create([
                                'start' => Carbon::create(2020, 9, 24, 21, 00),
                                'end' => Carbon::create(2020, 9, 24, 00, 00),
                            ]),
                            'serviceDurationHours' => Decimal::fromInt(12_5000),
                            'movingDurationHours' => null,
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
                        'serialNumber' => 18,
                        'providedOn' => Carbon::create(2020, 9, 25),
                        'serviceType' => DwsGrantedServiceCode::none(),
                        'providerType' => DwsBillingServiceReportProviderType::none(),
                        'situation' => DwsBillingServiceReportSituation::none(),
                        'plan' => null,
                        'result' => DwsBillingServiceReportDuration::create([
                            'period' => CarbonRange::create([
                                'start' => Carbon::create(2020, 9, 25, 00, 00),
                                'end' => Carbon::create(2020, 9, 25, 9, 45),
                            ]),
                            'serviceDurationHours' => null,
                            'movingDurationHours' => null,
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
                        'serialNumber' => 18,
                        'providedOn' => Carbon::create(2020, 9, 25),
                        'serviceType' => DwsGrantedServiceCode::none(),
                        'providerType' => DwsBillingServiceReportProviderType::none(),
                        'situation' => DwsBillingServiceReportSituation::none(),
                        'plan' => null,
                        'result' => DwsBillingServiceReportDuration::create([
                            'period' => CarbonRange::create([
                                'start' => Carbon::create(2020, 9, 25, 21, 00),
                                'end' => Carbon::create(2020, 9, 25, 00, 00),
                            ]),
                            'serviceDurationHours' => Decimal::fromInt(13_0000),
                            'movingDurationHours' => null,
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
                        'serialNumber' => 19,
                        'providedOn' => Carbon::create(2020, 9, 26),
                        'serviceType' => DwsGrantedServiceCode::none(),
                        'providerType' => DwsBillingServiceReportProviderType::none(),
                        'situation' => DwsBillingServiceReportSituation::none(),
                        'plan' => null,
                        'result' => DwsBillingServiceReportDuration::create([
                            'period' => CarbonRange::create([
                                'start' => Carbon::create(2020, 9, 26, 9, 00),
                                'end' => Carbon::create(2020, 9, 26, 9, 45),
                            ]),
                            'serviceDurationHours' => null,
                            'movingDurationHours' => null,
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
                        'serialNumber' => 19,
                        'providedOn' => Carbon::create(2020, 9, 26),
                        'serviceType' => DwsGrantedServiceCode::none(),
                        'providerType' => DwsBillingServiceReportProviderType::none(),
                        'situation' => DwsBillingServiceReportSituation::none(),
                        'plan' => null,
                        'result' => DwsBillingServiceReportDuration::create([
                            'period' => CarbonRange::create([
                                'start' => Carbon::create(2020, 9, 26, 21, 00),
                                'end' => Carbon::create(2020, 9, 27, 00, 00),
                            ]),
                            'serviceDurationHours' => Decimal::fromInt(4_0000),
                            'movingDurationHours' => null,
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
                        'serialNumber' => 20,
                        'providedOn' => Carbon::create(2020, 9, 27),
                        'serviceType' => DwsGrantedServiceCode::none(),
                        'providerType' => DwsBillingServiceReportProviderType::none(),
                        'situation' => DwsBillingServiceReportSituation::none(),
                        'plan' => null,
                        'result' => DwsBillingServiceReportDuration::create([
                            'period' => CarbonRange::create([
                                'start' => Carbon::create(2020, 9, 27, 00, 00),
                                'end' => Carbon::create(2020, 9, 27, 9, 30),
                            ]),
                            'serviceDurationHours' => null,
                            'movingDurationHours' => null,
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
                        'serialNumber' => 20,
                        'providedOn' => Carbon::create(2020, 9, 27),
                        'serviceType' => DwsGrantedServiceCode::none(),
                        'providerType' => DwsBillingServiceReportProviderType::none(),
                        'situation' => DwsBillingServiceReportSituation::none(),
                        'plan' => null,
                        'result' => DwsBillingServiceReportDuration::create([
                            'period' => CarbonRange::create([
                                'start' => Carbon::create(2020, 9, 27, 21, 00),
                                'end' => Carbon::create(2020, 9, 28, 00, 00),
                            ]),
                            'serviceDurationHours' => Decimal::fromInt(12_5000),
                            'movingDurationHours' => null,
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
                        'serialNumber' => 21,
                        'providedOn' => Carbon::create(2020, 9, 28),
                        'serviceType' => DwsGrantedServiceCode::none(),
                        'providerType' => DwsBillingServiceReportProviderType::none(),
                        'situation' => DwsBillingServiceReportSituation::none(),
                        'plan' => null,
                        'result' => DwsBillingServiceReportDuration::create([
                            'period' => CarbonRange::create([
                                'start' => Carbon::create(2020, 9, 28, 00, 00),
                                'end' => Carbon::create(2020, 9, 28, 9, 10),
                            ]),
                            'serviceDurationHours' => null,
                            'movingDurationHours' => null,
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
                        'serialNumber' => 21,
                        'providedOn' => Carbon::create(2020, 9, 28),
                        'serviceType' => DwsGrantedServiceCode::none(),
                        'providerType' => DwsBillingServiceReportProviderType::none(),
                        'situation' => DwsBillingServiceReportSituation::none(),
                        'plan' => null,
                        'result' => DwsBillingServiceReportDuration::create([
                            'period' => CarbonRange::create([
                                'start' => Carbon::create(2020, 9, 28, 21, 00),
                                'end' => Carbon::create(2020, 9, 29, 00, 00),
                            ]),
                            'serviceDurationHours' => Decimal::fromInt(12_5000),
                            'movingDurationHours' => null,
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
                        'serialNumber' => 22,
                        'providedOn' => Carbon::create(2020, 9, 29),
                        'serviceType' => DwsGrantedServiceCode::none(),
                        'providerType' => DwsBillingServiceReportProviderType::none(),
                        'situation' => DwsBillingServiceReportSituation::none(),
                        'plan' => null,
                        'result' => DwsBillingServiceReportDuration::create([
                            'period' => CarbonRange::create([
                                'start' => Carbon::create(2020, 9, 29, 00, 00),
                                'end' => Carbon::create(2020, 9, 29, 9, 30),
                            ]),
                            'serviceDurationHours' => null,
                            'movingDurationHours' => null,
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
                        'serialNumber' => 22,
                        'providedOn' => Carbon::create(2020, 9, 29),
                        'serviceType' => DwsGrantedServiceCode::none(),
                        'providerType' => DwsBillingServiceReportProviderType::none(),
                        'situation' => DwsBillingServiceReportSituation::none(),
                        'plan' => null,
                        'result' => DwsBillingServiceReportDuration::create([
                            'period' => CarbonRange::create([
                                'start' => Carbon::create(2020, 9, 29, 21, 00),
                                'end' => Carbon::create(2020, 9, 30, 00, 00),
                            ]),
                            'serviceDurationHours' => Decimal::fromInt(12_5000),
                            'movingDurationHours' => null,
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
                        'serialNumber' => 23,
                        'providedOn' => Carbon::create(2020, 9, 30),
                        'serviceType' => DwsGrantedServiceCode::none(),
                        'providerType' => DwsBillingServiceReportProviderType::none(),
                        'situation' => DwsBillingServiceReportSituation::none(),
                        'plan' => null,
                        'result' => DwsBillingServiceReportDuration::create([
                            'period' => CarbonRange::create([
                                'start' => Carbon::create(2020, 9, 30, 00, 00),
                                'end' => Carbon::create(2020, 9, 30, 9, 30),
                            ]),
                            'serviceDurationHours' => null,
                            'movingDurationHours' => null,
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
                        'serialNumber' => 23,
                        'providedOn' => Carbon::create(2020, 9, 30),
                        'serviceType' => DwsGrantedServiceCode::none(),
                        'providerType' => DwsBillingServiceReportProviderType::none(),
                        'situation' => DwsBillingServiceReportSituation::none(),
                        'plan' => null,
                        'result' => DwsBillingServiceReportDuration::create([
                            'period' => CarbonRange::create([
                                'start' => Carbon::create(2020, 9, 30, 21, 00),
                                'end' => Carbon::create(2020, 9, 31, 00, 00),
                            ]),
                            'serviceDurationHours' => Decimal::fromInt(12_5000),
                            'movingDurationHours' => null,
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
                'status' => DwsBillingStatus::fixed(),
                'fixedAt' => Carbon::now(),
                'createdAt' => Carbon::now(),
                'updatedAt' => Carbon::now(),
            ]),
        ];
    }
}

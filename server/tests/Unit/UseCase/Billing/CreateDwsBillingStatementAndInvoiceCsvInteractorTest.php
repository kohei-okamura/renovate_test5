<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\Billing;

use Domain\Billing\DwsBilling;
use Domain\Billing\DwsBillingBundle;
use Domain\Billing\DwsBillingHighCostPayment;
use Domain\Billing\DwsBillingInvoice;
use Domain\Billing\DwsBillingInvoiceItem;
use Domain\Billing\DwsBillingOffice;
use Domain\Billing\DwsBillingPayment;
use Domain\Billing\DwsBillingPaymentCategory;
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
use Domain\Common\Decimal;
use Domain\Common\StructuredName;
use Domain\Exchange\DwsBillingInvoiceItemRecord;
use Domain\Exchange\DwsBillingInvoiceSummaryRecord;
use Domain\Exchange\DwsBillingStatementAggregateRecord;
use Domain\Exchange\DwsBillingStatementContractRecord;
use Domain\Exchange\DwsBillingStatementDaysRecord;
use Domain\Exchange\DwsBillingStatementItemRecord;
use Domain\Exchange\DwsBillingStatementSummaryRecord;
use Domain\Exchange\DwsControlRecord;
use Domain\Exchange\EndRecord;
use Domain\ServiceCode\ServiceCode;
use Domain\ServiceCodeDictionary\DwsServiceCodeCategory;
use Lib\Csv;
use Lib\Exceptions\FileIOException;
use Lib\Exceptions\LogicException;
use Mockery;
use ScalikePHP\Seq;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\BuildDwsBillingStatementAndInvoiceRecordListUseCaseMixin;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\ConfigMixin;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\GenerateFileNameUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\StoreCsvUseCaseMixin;
use Tests\Unit\Mixins\TokenMakerMixin;
use Tests\Unit\Test;
use UseCase\Billing\CreateDwsBillingStatementAndInvoiceCsvInteractor;

/**
 * {@link \UseCase\Billing\CreateDwsBillingStatementAndInvoiceCsvInteractor} のテスト.
 */
class CreateDwsBillingStatementAndInvoiceCsvInteractorTest extends Test
{
    use BuildDwsBillingStatementAndInvoiceRecordListUseCaseMixin;
    use GenerateFileNameUseCaseMixin;
    use CarbonMixin;
    use ConfigMixin;
    use ContextMixin;
    use ExamplesConsumer;
    use MatchesSnapshots;
    use MockeryMixin;
    use StoreCsvUseCaseMixin;
    use TokenMakerMixin;
    use UnitSupport;

    private const FILENAME = 'dummy.csv';

    private DwsBilling $dwsBilling;
    /** @var \Domain\Billing\DwsBillingBundle[]&\ScalikePHP\Seq */
    private Seq $dwsBillingBundles;

    private CreateDwsBillingStatementAndInvoiceCsvInteractor $interactor;

    /**
     * 初期化処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachSpec(function (CreateDwsBillingStatementAndInvoiceCsvInteractorTest $self): void {
            $self->dwsBilling = $self->examples->dwsBillings[0];
            $self->dwsBillingBundles = Seq::fromArray($self->examples->dwsBillingBundles)->take(5);

            $self->buildDwsBillingStatementAndInvoiceRecordListUseCase
                ->allows('handle')
                ->andReturn([])
                ->byDefault();

            $self->storeCsvUseCase
                ->allows('handle')
                ->andReturn('path/to/stored-file.csv')
                ->byDefault();

            $self->config
                ->allows('get')
                ->with('zinger.filename.dws_invoice_csv')
                ->andReturn('介護給付費等請求書・明細書_#{office}_#{providedIn}.csv')
                ->byDefault();

            $self->tokenMaker
                ->allows('make')
                ->andReturn(str_repeat('x', 60))
                ->byDefault();

            $self->generateFileNameUseCase
                ->allows('handle')
                ->andReturn(self::FILENAME)
                ->byDefault();

            $self->interactor = app(CreateDwsBillingStatementAndInvoiceCsvInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('create records using BuildDwsBillingStatementAndInvoiceRecordListUseCase', function (): void {
            $this->buildDwsBillingStatementAndInvoiceRecordListUseCase
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
            $this->buildDwsBillingStatementAndInvoiceRecordListUseCase
                ->expects('handle')
                ->andReturnUsing(fn (): array => $this->createExchangeRecordsStub());
            $this->storeCsvUseCase
                ->expects('handle')
                ->with($this->context, 'artifacts', 'dws-billing-', Mockery::capture($actual))
                ->andReturn('path/to/stored-file.csv');

            $this->interactor->handle($this->context, $this->dwsBilling, $this->dwsBillingBundles);

            $this->assertEquals(
                Csv::read(__DIR__ . '/CreateDwsBillingStatementAndInvoiceCsvInteractorTest.csv')->toArray(),
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
     *
     * @return array
     */
    protected function createExchangeRecordsStub(): array
    {
        $billing = $this->createDwsBillingStub();
        $bundle = $this->createDwsBillingBundleStub();
        $invoice = $this->createDwsBillingInvoiceStub();
        $statements = $this->createDwsBillingStatementStub();
        $invoiceItems = Seq::fromArray($invoice->items)
            ->map(fn (DwsBillingInvoiceItem $item) => DwsBillingInvoiceItemRecord::from($billing, $bundle, $item));
        $statementDates = Seq::fromArray($statements->aggregates)
            ->map(fn (DwsBillingStatementAggregate $aggregate) => DwsBillingStatementDaysRecord::from(
                $billing,
                $bundle,
                $statements->user,
                $aggregate
            ));
        $statementItems = Seq::fromArray($statements->items)
            ->map(fn (DwsBillingStatementItem $item) => DwsBillingStatementItemRecord::from(
                $billing,
                $bundle,
                $statements->user,
                $item
            ));
        $statementAggregates = Seq::fromArray($statements->aggregates)
            ->map(fn (DwsBillingStatementAggregate $aggregate) => DwsBillingStatementAggregateRecord::from(
                $billing,
                $bundle,
                $statements->user,
                $aggregate
            ));
        $statementContracts = Seq::fromArray($statements->contracts)
            ->map(fn (DwsBillingStatementContract $aggregate) => DwsBillingStatementContractRecord::from(
                $billing,
                $bundle,
                $statements->user,
                $aggregate
            ));
        $records = [
            DwsBillingInvoiceSummaryRecord::from($billing, $bundle, $invoice),
            ...$invoiceItems,
            DwsBillingStatementSummaryRecord::from($billing, $bundle, $statements),
            ...$statementDates,
            ...$statementItems,
            ...$statementAggregates,
            ...$statementContracts,
        ];
        return [
            DwsControlRecord::forInvoice($billing, count($records)),
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
                'code' => '1312404278',
                'name' => 'ﾃｽﾄﾀﾞｶﾗﾅﾝﾃﾞﾓｲｲﾔ',
            ]),
            'transactedIn' => Carbon::create(2020, 11),
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
            'cityCode' => '133051',
            'cityName' => 'ﾓﾘｵｳﾁｮｳ',
            'details' => [],
            'createdAt' => Carbon::now(),
            'updatedAt' => Carbon::now(),
        ]);
    }

    /**
     * テスト用の請求書を生成する.
     *
     * @return \Domain\Billing\DwsBillingInvoice
     */
    private function createDwsBillingInvoiceStub(): DwsBillingInvoice
    {
        return DwsBillingInvoice::create([
            'id' => 1,
            'dwsBillingBundleId' => 1,
            'claimAmount' => 185746,
            'dwsPayment' => DwsBillingPayment::create([
                'subtotalDetailCount' => 1,
                'subtotalScore' => 17041,
                'subtotalFee' => 185746,
                'subtotalBenefit' => 185746,
                'subtotalCopay' => 0,
                'subtotalSubsidy' => 0,
            ]),
            'highCostDwsPayment' => DwsBillingHighCostPayment::create([
                'subtotalDetailCount' => 0,
                'subtotalFee' => 0,
                'subtotalBenefit' => 0,
            ]),
            'totalCount' => 1,
            'totalScore' => 17041,
            'totalFee' => 185746,
            'totalBenefit' => 185746,
            'totalCopay' => 0,
            'totalSubsidy' => 0,
            'items' => [
                DwsBillingInvoiceItem::create([
                    'paymentCategory' => DwsBillingPaymentCategory::category1(),
                    'serviceDivisionCode' => DwsServiceDivisionCode::homeHelpService(),
                    'subtotalCount' => 1,
                    'subtotalScore' => 17041,
                    'subtotalFee' => 185746,
                    'subtotalBenefit' => 185746,
                    'subtotalCopay' => 0,
                    'subtotalSubsidy' => 0,
                ]),
            ],
            'createdAt' => Carbon::now(),
            'updatedAt' => Carbon::now(),
        ]);
    }

    /**
     * テスト用の明細書を生成する.
     *
     * @return \Domain\Billing\DwsBillingStatement
     */
    private function createDwsBillingStatementStub(): DwsBillingStatement
    {
        return DwsBillingStatement::create([
            'dwsBillingId' => 1,
            'dwsBillingBundleId' => 1,
            'subsidyCityCode' => null,
            'user' => DwsBillingUser::create([
                'userId' => 1,
                'dwsCertificationId' => 1,
                'dwsNumber' => '1330510494',
                'name' => new StructuredName(
                    familyName: 'テスト',
                    givenName: 'タロウ',
                    phoneticFamilyName: 'ﾃｽﾄ',
                    phoneticGivenName: 'ﾀﾛｳ',
                ),
                'childName' => StructuredName::empty(),
                'copayLimit' => 0,
            ]),
            'dwsAreaGradeName' => 'ﾄﾞｺﾀﾞｺｺ',
            'dwsAreaGradeCode' => '03',
            'copayLimit' => 0,
            'totalScore' => 17041,
            'totalFee' => 185746,
            'totalCappedCopay' => 0,
            'totalAdjustedCopay' => null,
            'totalCoordinatedCopay' => null,
            'totalCopay' => 0,
            'totalBenefit' => 185746,
            'totalSubsidy' => 0,
            'isProvided' => false,
            'copayCoordination' => null,
            'copayCoordinationStatus' => DwsBillingStatementCopayCoordinationStatus::unapplicable(),
            'aggregates' => [
                new DwsBillingStatementAggregate(
                    serviceDivisionCode: DwsServiceDivisionCode::homeHelpService(),
                    startedOn: Carbon::create(2020, 8, 26),
                    terminatedOn: null,
                    serviceDays: 11,
                    subtotalScore: 17041,
                    unitCost: Decimal::fromInt(10_9000),
                    subtotalFee: 185746,
                    unmanagedCopay: 18574,
                    managedCopay: 18574,
                    cappedCopay: 0,
                    adjustedCopay: null,
                    coordinatedCopay: null,
                    subtotalCopay: 0,
                    subtotalBenefit: 185746,
                    subtotalSubsidy: null,
                ),
            ],
            'contracts' => [
                DwsBillingStatementContract::create([
                    'dwsGrantedServiceCode' => DwsGrantedServiceCode::physicalCare(),
                    'grantedAmount' => 3120,
                    'agreedOn' => Carbon::create(2020, 8, 17),
                    'expiredOn' => null,
                    'indexNumber' => 5,
                ]),
                DwsBillingStatementContract::create([
                    'dwsGrantedServiceCode' => DwsGrantedServiceCode::housework(),
                    'grantedAmount' => 1170,
                    'agreedOn' => Carbon::create(2020, 8, 17),
                    'expiredOn' => null,
                    'indexNumber' => 6,
                ]),
            ],
            'items' => [
                // 身体夜1.0
                new DwsBillingStatementItem(
                    serviceCode: ServiceCode::fromString('111219'),
                    serviceCodeCategory: DwsServiceCodeCategory::physicalCare(),
                    unitScore: 491,
                    count: 4,
                    totalScore: 1964,
                ),
                // 身体日1.0・夜2.0
                new DwsBillingStatementItem(
                    serviceCode: ServiceCode::fromString('111455'),
                    serviceCodeCategory: DwsServiceCodeCategory::physicalCare(),
                    unitScore: 921,
                    count: 2,
                    totalScore: 1842,
                ),
                // 身体日1.5・夜1.5
                new DwsBillingStatementItem(
                    serviceCode: ServiceCode::fromString('111467'),
                    serviceCodeCategory: DwsServiceCodeCategory::physicalCare(),
                    unitScore: 876,
                    count: 5,
                    totalScore: 4380,
                ),
                // 身体夜増0.5
                new DwsBillingStatementItem(
                    serviceCode: ServiceCode::fromString('111931'),
                    serviceCodeCategory: DwsServiceCodeCategory::physicalCare(),
                    unitScore: 101,
                    count: 2,
                    totalScore: 202,
                ),
                // 身体夜増1.0
                new DwsBillingStatementItem(
                    serviceCode: ServiceCode::fromString('111935'),
                    serviceCodeCategory: DwsServiceCodeCategory::physicalCare(),
                    unitScore: 203,
                    count: 5,
                    totalScore: 1015,
                ),
                // 家事夜1.5
                new DwsBillingStatementItem(
                    serviceCode: ServiceCode::fromString('116223'),
                    serviceCodeCategory: DwsServiceCodeCategory::housework(),
                    unitScore: 335,
                    count: 11,
                    totalScore: 3685,
                ),
                // 居介処遇改善加算Ⅰ
                new DwsBillingStatementItem(
                    serviceCode: ServiceCode::fromString('116715'),
                    serviceCodeCategory: DwsServiceCodeCategory::treatmentImprovementAddition1(),
                    unitScore: 3953,
                    count: 1,
                    totalScore: 3953,
                ),
            ],
            'status' => DwsBillingStatus::fixed(),
            'fixedAt' => Carbon::now(),
            'createdAt' => Carbon::now(),
            'updatedAt' => Carbon::now(),
        ]);
    }
}

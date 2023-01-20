<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\Billing;

use Domain\Billing\LtcsBilling;
use Domain\Billing\LtcsBillingBundle;
use Domain\Billing\LtcsBillingFile;
use Domain\Billing\LtcsBillingInvoice;
use Domain\Billing\LtcsBillingOffice;
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
use Domain\Common\MimeType;
use Domain\Common\Sex;
use Domain\Exchange\EndRecord;
use Domain\Exchange\LtcsBillingInvoiceRecord;
use Domain\Exchange\LtcsBillingStatementAggregateRecord;
use Domain\Exchange\LtcsBillingStatementItemRecord;
use Domain\Exchange\LtcsBillingStatementSummaryRecord;
use Domain\Exchange\LtcsControlRecord;
use Domain\LtcsInsCard\LtcsCarePlanAuthorType;
use Domain\LtcsInsCard\LtcsLevel;
use Domain\ServiceCode\ServiceCode;
use Domain\ServiceCodeDictionary\LtcsServiceCodeCategory;
use Lib\Csv;
use Lib\Exceptions\FileIOException;
use Mockery;
use ScalikePHP\Seq;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\BuildLtcsBillingInvoiceRecordListUseCaseMixin;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\GenerateFileNameUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\StoreCsvUseCaseMixin;
use Tests\Unit\Mixins\TokenMakerMixin;
use Tests\Unit\Test;
use UseCase\Billing\CreateLtcsBillingInvoiceCsvInteractor;

/**
 * {@link \UseCase\Billing\CreateLtcsBillingInvoiceCsvInteractor} のテスト.
 */
final class CreateLtcsBillingInvoiceCsvInteractorTest extends Test
{
    use BuildLtcsBillingInvoiceRecordListUseCaseMixin;
    use CarbonMixin;
    use ContextMixin;
    use ExamplesConsumer;
    use GenerateFileNameUseCaseMixin;
    use MatchesSnapshots;
    use MockeryMixin;
    use StoreCsvUseCaseMixin;
    use TokenMakerMixin;
    use UnitSupport;

    private const FILENAME = 'dummy.pdf';

    private LtcsBilling $ltcsBilling;
    private LtcsBillingBundle $ltcsBillingBundle;
    private CreateLtcsBillingInvoiceCsvInteractor $interactor;

    /**
     * 初期化処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachSpec(function (CreateLtcsBillingInvoiceCsvInteractorTest $self): void {
            $self->buildLtcsBillingInvoiceRecordListUseCase
                ->allows('handle')
                ->andReturnUsing(fn (): array => [])
                ->byDefault();
            $self->generateFileNameUseCase
                ->allows('handle')
                ->andReturn(self::FILENAME)
                ->byDefault();
            $self->storeCsvUseCase
                ->allows('handle')
                ->andReturn('path/to/stored-file.csv')
                ->byDefault();
            $self->tokenMaker
                ->allows('make')
                ->andReturn(str_repeat('x', 60))
                ->byDefault();

            $self->ltcsBilling = $self->createBillingStub();
            $self->ltcsBillingBundle = $self->createBundleStub($self->ltcsBilling);
            $self->interactor = app(CreateLtcsBillingInvoiceCsvInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('create records using CreateLtcsBillingInvoiceRecordsUseCase', function (): void {
            $this->buildLtcsBillingInvoiceRecordListUseCase
                ->expects('handle')
                ->with($this->context, $this->ltcsBilling, $this->ltcsBillingBundle)
                ->andReturnUsing(fn (): array => []);

            $this->interactor->handle($this->context, $this->ltcsBilling, $this->ltcsBillingBundle);
        });
        $this->should('generate file name using GenerateFileNameUseCase', function (): void {
            $placeholders = [
                'office' => $this->ltcsBilling->office->abbr,
                'transactedIn' => $this->ltcsBilling->transactedIn,
                'providedIn' => $this->ltcsBillingBundle->providedIn,
            ];
            $this->generateFileNameUseCase
                ->expects('handle')
                ->with('ltcs_invoice_csv', $placeholders)
                ->andReturn(self::FILENAME);

            $this->interactor->handle($this->context, $this->ltcsBilling, $this->ltcsBillingBundle);
        });
        $this->should('store the csv', function (): void {
            $this->buildLtcsBillingInvoiceRecordListUseCase
                ->expects('handle')
                ->andReturnUsing(fn (): array => $this->createExchangeRecordsStub());
            $this->storeCsvUseCase
                ->expects('handle')
                ->with($this->context, 'artifacts', 'ltcs-billing-', Mockery::capture($actual))
                ->andReturn('path/to/stored-file.csv');

            $this->interactor->handle($this->context, $this->ltcsBilling, $this->ltcsBillingBundle);

            $this->assertEquals(
                Csv::read(__DIR__ . '/CreateLtcsBillingInvoiceCsvInteractorTest.csv')->toArray(),
                is_array($actual) ? $actual : iterator_to_array($actual),
            );
        });
        $this->should('throw FileIOException when StoreCsvUseCase throws it', function (): void {
            $this->storeCsvUseCase
                ->expects('handle')
                ->andThrow(new FileIOException('Failed to store file'));

            $this->assertThrows(FileIOException::class, function (): void {
                $this->interactor->handle($this->context, $this->ltcsBilling, $this->ltcsBillingBundle);
            });
        });
        $this->should('return an instance of LtcsBillingFile', function (): void {
            $this->assertModelStrictEquals(
                new LtcsBillingFile(
                    name: self::FILENAME,
                    path: 'path/to/stored-file.csv',
                    token: str_repeat('x', 60),
                    mimeType: MimeType::csv(),
                    createdAt: Carbon::now(),
                    downloadedAt: null,
                ),
                $this->interactor->handle($this->context, $this->ltcsBilling, $this->ltcsBillingBundle)
            );
        });
    }

    /**
     * テスト用の伝送レコードを生成する.
     */
    private function createExchangeRecordsStub(): array
    {
        $billing = $this->ltcsBilling;
        $bundle = $this->ltcsBillingBundle;
        $invoice = $this->createInvoiceStub($bundle);
        $statements = $this->createStatementStubs($bundle);
        $records = [
            LtcsBillingInvoiceRecord::from($billing, $bundle, $invoice, count($statements)),
            ...Seq::fromArray($statements)->flatMap(fn (LtcsBillingStatement $statement): iterable => [
                LtcsBillingStatementSummaryRecord::from($billing, $bundle, $statement),
                ...LtcsBillingStatementItemRecord::from($billing, $bundle, $statement),
                ...LtcsBillingStatementAggregateRecord::from($billing, $bundle, $statement),
            ]),
        ];
        return [
            LtcsControlRecord::from($billing, count($records)),
            ...$records,
            EndRecord::instance(),
        ];
    }

    /**
     * テスト用の請求を生成する.
     */
    private function createBillingStub(): LtcsBilling
    {
        $office = $this->examples->offices[0];
        return LtcsBilling::create([
            'id' => 1,
            'organizationId' => $this->examples->organizations[0]->id,
            'office' => new LtcsBillingOffice(
                officeId: $office->id,
                code: '1371405083',
                name: $office->name,
                abbr: $office->abbr,
                addr: $office->addr,
                tel: $office->tel,
            ),
            'transactedIn' => Carbon::create(2019, 11),
            'files' => [],
            'status' => LtcsBillingStatus::checking(),
            'fixedAt' => null,
            'createdAt' => Carbon::now(),
            'updatedAt' => Carbon::now(),
        ]);
    }

    /**
     * テスト用の請求単位を生成する.
     *
     * @param \Domain\Billing\LtcsBilling $billing
     * @return \Domain\Billing\LtcsBillingBundle
     */
    private function createBundleStub(LtcsBilling $billing): LtcsBillingBundle
    {
        return LtcsBillingBundle::create([
            'id' => 1,
            'billingId' => $billing->id,
            'providedIn' => Carbon::create(2019, 10),
            'details' => [],
            'createdAt' => Carbon::now(),
            'updatedAt' => Carbon::now(),
        ]);
    }

    /**
     * テスト用の請求書を生成する.
     *
     * @param \Domain\Billing\LtcsBillingBundle $bundle
     * @return \Domain\Billing\LtcsBillingInvoice
     */
    private function createInvoiceStub(LtcsBillingBundle $bundle): LtcsBillingInvoice
    {
        return new LtcsBillingInvoice(
            id: 1,
            billingId: $bundle->billingId,
            bundleId: $bundle->id,
            isSubsidy: false,
            defrayerCategory: null,
            statementCount: 8,
            totalScore: 62050,
            totalFee: 707368,
            insuranceAmount: 586289,
            subsidyAmount: 0,
            copayAmount: 121079,
            createdAt: Carbon::now(),
            updatedAt: Carbon::now(),
        );
    }

    /**
     * テスト用の明細書を生成する.
     *
     * @param \Domain\Billing\LtcsBillingBundle $bundle
     * @return array
     */
    private function createStatementStubs(LtcsBillingBundle $bundle): array
    {
        return [
            new LtcsBillingStatement(
                id: 1,
                billingId: $bundle->billingId,
                bundleId: $bundle->id,
                insurerNumber: '131086',
                insurerName: '江東区',
                user: new LtcsBillingUser(
                    userId: $this->examples->users[0]->id,
                    ltcsInsCardId: $this->examples->ltcsInsCards[0]->id,
                    insNumber: '0001260082',
                    name: $this->examples->users[0]->name,
                    sex: Sex::male(),
                    birthday: Carbon::create(1944, 12, 11),
                    ltcsLevel: LtcsLevel::careLevel5(),
                    activatedOn: Carbon::create(2019, 2, 18),
                    deactivatedOn: Carbon::create(2020, 2, 29),
                ),
                carePlanAuthor: new LtcsCarePlanAuthor(
                    authorType: LtcsCarePlanAuthorType::careManagerOffice(),
                    officeId: $this->examples->offices[0]->id,
                    code: '1370802488',
                    name: $this->examples->offices[0]->name,
                ),
                agreedOn: null,
                expiredOn: null,
                expiredReason: LtcsExpiredReason::unspecified(),
                insurance: new LtcsBillingStatementInsurance(
                    benefitRate: 70,
                    totalScore: 20096,
                    claimAmount: 160365,
                    copayAmount: 68729,
                ),
                subsidies: [
                    LtcsBillingStatementSubsidy::empty(),
                    LtcsBillingStatementSubsidy::empty(),
                    LtcsBillingStatementSubsidy::empty(),
                ],
                items: [
                    new LtcsBillingStatementItem(
                        serviceCode: ServiceCode::fromString('111213'),
                        serviceCodeCategory: LtcsServiceCodeCategory::physicalCare(),
                        unitScore: 593,
                        count: 14,
                        totalScore: 8302,
                        subsidies: [
                            LtcsBillingStatementItemSubsidy::empty(),
                            LtcsBillingStatementItemSubsidy::empty(),
                            LtcsBillingStatementItemSubsidy::empty(),
                        ],
                        note: '',
                    ),
                    new LtcsBillingStatementItem(
                        serviceCode: ServiceCode::fromString('111312'),
                        serviceCodeCategory: LtcsServiceCodeCategory::physicalCare(),
                        unitScore: 721,
                        count: 13,
                        totalScore: 9373,
                        subsidies: [
                            LtcsBillingStatementItemSubsidy::empty(),
                            LtcsBillingStatementItemSubsidy::empty(),
                            LtcsBillingStatementItemSubsidy::empty(),
                        ],
                        note: '',
                    ),
                    new LtcsBillingStatementItem(
                        serviceCode: ServiceCode::fromString('116275'),
                        serviceCodeCategory: LtcsServiceCodeCategory::treatmentImprovementAddition1(),
                        unitScore: 2421,
                        count: 1,
                        totalScore: 2421,
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
                        serviceDays: 19,
                        plannedScore: 17675,
                        managedScore: 17675,
                        unmanagedScore: 2421,
                        insurance: new LtcsBillingStatementAggregateInsurance(
                            totalScore: 20096,
                            unitCost: Decimal::fromInt(11_4000),
                            claimAmount: 160365,
                            copayAmount: 68729,
                        ),
                        subsidies: [
                            LtcsBillingStatementAggregateSubsidy::empty(),
                            LtcsBillingStatementAggregateSubsidy::empty(),
                            LtcsBillingStatementAggregateSubsidy::empty(),
                        ],
                    ),
                ],
                appendix: null,
                status: LtcsBillingStatus::checking(),
                fixedAt: null,
                createdAt: Carbon::now(),
                updatedAt: Carbon::now(),
            ),
            new LtcsBillingStatement(
                id: 2,
                billingId: $bundle->billingId,
                bundleId: $bundle->id,
                insurerNumber: '131177',
                insurerName: '北区',
                user: new LtcsBillingUser(
                    userId: $this->examples->users[1]->id,
                    ltcsInsCardId: $this->examples->ltcsInsCards[1]->id,
                    insNumber: '0000183814',
                    name: $this->examples->users[1]->name,
                    sex: Sex::female(),
                    birthday: Carbon::create(1934, 9, 19),
                    ltcsLevel: LtcsLevel::careLevel5(),
                    activatedOn: Carbon::create(2019, 9, 1),
                    deactivatedOn: Carbon::create(2022, 8, 31),
                ),
                carePlanAuthor: new LtcsCarePlanAuthor(
                    authorType: LtcsCarePlanAuthorType::self(),
                    officeId: null,
                    code: '',
                    name: '',
                ),
                agreedOn: null,
                expiredOn: null,
                expiredReason: LtcsExpiredReason::unspecified(),
                insurance: new LtcsBillingStatementInsurance(
                    benefitRate: 80,
                    totalScore: 3965,
                    claimAmount: 36160,
                    copayAmount: 9041,
                ),
                subsidies: [
                    LtcsBillingStatementSubsidy::empty(),
                    LtcsBillingStatementSubsidy::empty(),
                    LtcsBillingStatementSubsidy::empty(),
                ],
                items: [
                    new LtcsBillingStatementItem(
                        serviceCode: ServiceCode::fromString('111111'),
                        serviceCodeCategory: LtcsServiceCodeCategory::physicalCare(),
                        unitScore: 249,
                        count: 3,
                        totalScore: 747,
                        subsidies: [
                            LtcsBillingStatementItemSubsidy::empty(),
                            LtcsBillingStatementItemSubsidy::empty(),
                            LtcsBillingStatementItemSubsidy::empty(),
                        ],
                        note: '',
                    ),
                    new LtcsBillingStatementItem(
                        serviceCode: ServiceCode::fromString('111112'),
                        serviceCodeCategory: LtcsServiceCodeCategory::physicalCare(),
                        unitScore: 311,
                        count: 4,
                        totalScore: 1244,
                        subsidies: [
                            LtcsBillingStatementItemSubsidy::empty(),
                            LtcsBillingStatementItemSubsidy::empty(),
                            LtcsBillingStatementItemSubsidy::empty(),
                        ],
                        note: '',
                    ),
                    new LtcsBillingStatementItem(
                        serviceCode: ServiceCode::fromString('111113'),
                        serviceCodeCategory: LtcsServiceCodeCategory::physicalCare(),
                        unitScore: 374,
                        count: 4,
                        totalScore: 1496,
                        subsidies: [
                            LtcsBillingStatementItemSubsidy::empty(),
                            LtcsBillingStatementItemSubsidy::empty(),
                            LtcsBillingStatementItemSubsidy::empty(),
                        ],
                        note: '',
                    ),
                    new LtcsBillingStatementItem(
                        serviceCode: ServiceCode::fromString('116275'),
                        serviceCodeCategory: LtcsServiceCodeCategory::treatmentImprovementAddition1(),
                        unitScore: 478,
                        count: 1,
                        totalScore: 478,
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
                        plannedScore: 3487,
                        managedScore: 3487,
                        unmanagedScore: 478,
                        insurance: new LtcsBillingStatementAggregateInsurance(
                            totalScore: 3965,
                            unitCost: Decimal::fromInt(11_4000),
                            claimAmount: 36160,
                            copayAmount: 9041,
                        ),
                        subsidies: [
                            LtcsBillingStatementAggregateSubsidy::empty(),
                            LtcsBillingStatementAggregateSubsidy::empty(),
                            LtcsBillingStatementAggregateSubsidy::empty(),
                        ],
                    ),
                ],
                appendix: null,
                status: LtcsBillingStatus::checking(),
                fixedAt: null,
                createdAt: Carbon::now(),
                updatedAt: Carbon::now(),
            ),
            new LtcsBillingStatement(
                id: 3,
                billingId: $bundle->billingId,
                bundleId: $bundle->id,
                insurerNumber: '132019',
                insurerName: '八王子市',
                user: new LtcsBillingUser(
                    userId: $this->examples->users[2]->id,
                    ltcsInsCardId: $this->examples->ltcsInsCards[2]->id,
                    insNumber: '1000535110',
                    name: $this->examples->users[2]->name,
                    sex: Sex::female(),
                    birthday: Carbon::create(1956, 9, 16),
                    ltcsLevel: LtcsLevel::careLevel5(),
                    activatedOn: Carbon::create(2018, 5, 1),
                    deactivatedOn: Carbon::create(2020, 4, 30),
                ),
                carePlanAuthor: new LtcsCarePlanAuthor(
                    authorType: LtcsCarePlanAuthorType::careManagerOffice(),
                    officeId: $this->examples->offices[2]->id,
                    code: '1372900249',
                    name: $this->examples->offices[2]->name,
                ),
                agreedOn: null,
                expiredOn: null,
                expiredReason: LtcsExpiredReason::unspecified(),
                insurance: new LtcsBillingStatementInsurance(
                    benefitRate: 90,
                    totalScore: 7862,
                    claimAmount: 80663,
                    copayAmount: 8963,
                ),
                subsidies: [
                    LtcsBillingStatementSubsidy::empty(),
                    LtcsBillingStatementSubsidy::empty(),
                    LtcsBillingStatementSubsidy::empty(),
                ],
                items: [
                    new LtcsBillingStatementItem(
                        serviceCode: ServiceCode::fromString('111211'),
                        serviceCodeCategory: LtcsServiceCodeCategory::physicalCare(),
                        unitScore: 395,
                        count: 5,
                        totalScore: 1975,
                        subsidies: [
                            LtcsBillingStatementItemSubsidy::empty(),
                            LtcsBillingStatementItemSubsidy::empty(),
                            LtcsBillingStatementItemSubsidy::empty(),
                        ],
                        note: '',
                    ),
                    new LtcsBillingStatementItem(
                        serviceCode: ServiceCode::fromString('111212'),
                        serviceCodeCategory: LtcsServiceCodeCategory::physicalCare(),
                        unitScore: 494,
                        count: 10,
                        totalScore: 4940,
                        subsidies: [
                            LtcsBillingStatementItemSubsidy::empty(),
                            LtcsBillingStatementItemSubsidy::empty(),
                            LtcsBillingStatementItemSubsidy::empty(),
                        ],
                        note: '',
                    ),
                    new LtcsBillingStatementItem(
                        serviceCode: ServiceCode::fromString('116275'),
                        serviceCodeCategory: LtcsServiceCodeCategory::treatmentImprovementAddition1(),
                        unitScore: 947,
                        count: 1,
                        totalScore: 947,
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
                        serviceDays: 12,
                        plannedScore: 6915,
                        managedScore: 6915,
                        unmanagedScore: 947,
                        insurance: new LtcsBillingStatementAggregateInsurance(
                            totalScore: 7862,
                            unitCost: Decimal::fromInt(11_4000),
                            claimAmount: 80663,
                            copayAmount: 8963,
                        ),
                        subsidies: [
                            LtcsBillingStatementAggregateSubsidy::empty(),
                            LtcsBillingStatementAggregateSubsidy::empty(),
                            LtcsBillingStatementAggregateSubsidy::empty(),
                        ],
                    ),
                ],
                appendix: null,
                status: LtcsBillingStatus::checking(),
                fixedAt: null,
                createdAt: Carbon::now(),
                updatedAt: Carbon::now(),
            ),
            new LtcsBillingStatement(
                id: 4,
                billingId: $bundle->billingId,
                bundleId: $bundle->id,
                insurerNumber: '132027',
                insurerName: '立川市',
                user: new LtcsBillingUser(
                    userId: $this->examples->users[3]->id,
                    ltcsInsCardId: $this->examples->ltcsInsCards[3]->id,
                    insNumber: '0000494278',
                    name: $this->examples->users[3]->name,
                    sex: Sex::male(),
                    birthday: Carbon::create(1944, 11, 22),
                    ltcsLevel: LtcsLevel::careLevel5(),
                    activatedOn: Carbon::create(2018, 6, 1),
                    deactivatedOn: Carbon::create(2021, 5, 31),
                ),
                carePlanAuthor: new LtcsCarePlanAuthor(
                    authorType: LtcsCarePlanAuthorType::careManagerOffice(),
                    officeId: $this->examples->offices[3]->id,
                    code: '1373002821',
                    name: $this->examples->offices[3]->name,
                ),
                agreedOn: null,
                expiredOn: null,
                expiredReason: LtcsExpiredReason::unspecified(),
                insurance: new LtcsBillingStatementInsurance(
                    benefitRate: 90,
                    totalScore: 2695,
                    claimAmount: 27650,
                    copayAmount: 3073,
                ),
                subsidies: [
                    LtcsBillingStatementSubsidy::empty(),
                    LtcsBillingStatementSubsidy::empty(),
                    LtcsBillingStatementSubsidy::empty(),
                ],
                items: [
                    new LtcsBillingStatementItem(
                        serviceCode: ServiceCode::fromString('111211'),
                        serviceCodeCategory: LtcsServiceCodeCategory::physicalCare(),
                        unitScore: 395,
                        count: 6,
                        totalScore: 2370,
                        subsidies: [
                            LtcsBillingStatementItemSubsidy::empty(),
                            LtcsBillingStatementItemSubsidy::empty(),
                            LtcsBillingStatementItemSubsidy::empty(),
                        ],
                        note: '',
                    ),
                    new LtcsBillingStatementItem(
                        serviceCode: ServiceCode::fromString('116275'),
                        serviceCodeCategory: LtcsServiceCodeCategory::treatmentImprovementAddition1(),
                        unitScore: 325,
                        count: 1,
                        totalScore: 325,
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
                        serviceDays: 6,
                        plannedScore: 2370,
                        managedScore: 2370,
                        unmanagedScore: 325,
                        insurance: new LtcsBillingStatementAggregateInsurance(
                            totalScore: 2695,
                            unitCost: Decimal::fromInt(11_4000),
                            claimAmount: 27650,
                            copayAmount: 3073,
                        ),
                        subsidies: [
                            LtcsBillingStatementAggregateSubsidy::empty(),
                            LtcsBillingStatementAggregateSubsidy::empty(),
                            LtcsBillingStatementAggregateSubsidy::empty(),
                        ],
                    ),
                ],
                appendix: null,
                status: LtcsBillingStatus::checking(),
                fixedAt: null,
                createdAt: Carbon::now(),
                updatedAt: Carbon::now(),
            ),
            new LtcsBillingStatement(
                id: 5,
                billingId: $bundle->billingId,
                bundleId: $bundle->id,
                insurerNumber: '132043',
                insurerName: '三鷹市',
                user: new LtcsBillingUser(
                    userId: $this->examples->users[4]->id,
                    ltcsInsCardId: $this->examples->ltcsInsCards[4]->id,
                    insNumber: '0000059620',
                    name: $this->examples->users[4]->name,
                    sex: Sex::male(),
                    birthday: Carbon::create(1956, 6, 15),
                    ltcsLevel: LtcsLevel::careLevel5(),
                    activatedOn: Carbon::create(2018, 11, 1),
                    deactivatedOn: Carbon::create(2021, 10, 31),
                ),
                carePlanAuthor: new LtcsCarePlanAuthor(
                    authorType: LtcsCarePlanAuthorType::careManagerOffice(),
                    officeId: $this->examples->offices[4]->id,
                    code: '1373602810',
                    name: $this->examples->offices[4]->name,
                ),
                agreedOn: null,
                expiredOn: null,
                expiredReason: LtcsExpiredReason::unspecified(),
                insurance: new LtcsBillingStatementInsurance(
                    benefitRate: 90,
                    totalScore: 3593,
                    claimAmount: 36864,
                    copayAmount: 4096,
                ),
                subsidies: [
                    LtcsBillingStatementSubsidy::empty(),
                    LtcsBillingStatementSubsidy::empty(),
                    LtcsBillingStatementSubsidy::empty(),
                ],
                items: [
                    new LtcsBillingStatementItem(
                        serviceCode: ServiceCode::fromString('111211'),
                        serviceCodeCategory: LtcsServiceCodeCategory::physicalCare(),
                        unitScore: 395,
                        count: 8,
                        totalScore: 3160,
                        subsidies: [
                            LtcsBillingStatementItemSubsidy::empty(),
                            LtcsBillingStatementItemSubsidy::empty(),
                            LtcsBillingStatementItemSubsidy::empty(),
                        ],
                        note: '',
                    ),
                    new LtcsBillingStatementItem(
                        serviceCode: ServiceCode::fromString('116275'),
                        serviceCodeCategory: LtcsServiceCodeCategory::treatmentImprovementAddition1(),
                        unitScore: 433,
                        count: 1,
                        totalScore: 433,
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
                        serviceDays: 8,
                        plannedScore: 3160,
                        managedScore: 3160,
                        unmanagedScore: 433,
                        insurance: new LtcsBillingStatementAggregateInsurance(
                            totalScore: 3593,
                            unitCost: Decimal::fromInt(11_4000),
                            claimAmount: 36864,
                            copayAmount: 4096,
                        ),
                        subsidies: [
                            LtcsBillingStatementAggregateSubsidy::empty(),
                            LtcsBillingStatementAggregateSubsidy::empty(),
                            LtcsBillingStatementAggregateSubsidy::empty(),
                        ],
                    ),
                ],
                appendix: null,
                status: LtcsBillingStatus::checking(),
                fixedAt: null,
                createdAt: Carbon::now(),
                updatedAt: Carbon::now(),
            ),
            new LtcsBillingStatement(
                id: 6,
                billingId: $bundle->billingId,
                bundleId: $bundle->id,
                insurerNumber: '132118',
                insurerName: '小平市',
                user: new LtcsBillingUser(
                    userId: $this->examples->users[5]->id,
                    ltcsInsCardId: $this->examples->ltcsInsCards[5]->id,
                    insNumber: '1300696687',
                    name: $this->examples->users[5]->name,
                    sex: Sex::male(),
                    birthday: Carbon::create(1951, 7, 9),
                    ltcsLevel: LtcsLevel::careLevel5(),
                    activatedOn: Carbon::create(2019, 8, 1),
                    deactivatedOn: Carbon::create(2022, 7, 31),
                ),
                carePlanAuthor: new LtcsCarePlanAuthor(
                    authorType: LtcsCarePlanAuthorType::careManagerOffice(),
                    officeId: $this->examples->offices[5]->id,
                    code: '1374302196',
                    name: $this->examples->offices[5]->name,
                ),
                agreedOn: null,
                expiredOn: null,
                expiredReason: LtcsExpiredReason::unspecified(),
                insurance: new LtcsBillingStatementInsurance(
                    benefitRate: 90,
                    totalScore: 6636,
                    claimAmount: 68085,
                    copayAmount: 7565,
                ),
                subsidies: [
                    LtcsBillingStatementSubsidy::empty(),
                    LtcsBillingStatementSubsidy::empty(),
                    LtcsBillingStatementSubsidy::empty(),
                ],
                items: [
                    new LtcsBillingStatementItem(
                        serviceCode: ServiceCode::fromString('111112'),
                        serviceCodeCategory: LtcsServiceCodeCategory::physicalCare(),
                        unitScore: 311,
                        count: 5,
                        totalScore: 1555,
                        subsidies: [
                            LtcsBillingStatementItemSubsidy::empty(),
                            LtcsBillingStatementItemSubsidy::empty(),
                            LtcsBillingStatementItemSubsidy::empty(),
                        ],
                        note: '',
                    ),
                    new LtcsBillingStatementItem(
                        serviceCode: ServiceCode::fromString('111212'),
                        serviceCodeCategory: LtcsServiceCodeCategory::physicalCare(),
                        unitScore: 494,
                        count: 4,
                        totalScore: 1976,
                        subsidies: [
                            LtcsBillingStatementItemSubsidy::empty(),
                            LtcsBillingStatementItemSubsidy::empty(),
                            LtcsBillingStatementItemSubsidy::empty(),
                        ],
                        note: '',
                    ),
                    new LtcsBillingStatementItem(
                        serviceCode: ServiceCode::fromString('115111'),
                        serviceCodeCategory: LtcsServiceCodeCategory::physicalCareAndHousework(),
                        unitScore: 461,
                        count: 5,
                        totalScore: 2305,
                        subsidies: [
                            LtcsBillingStatementItemSubsidy::empty(),
                            LtcsBillingStatementItemSubsidy::empty(),
                            LtcsBillingStatementItemSubsidy::empty(),
                        ],
                        note: '',
                    ),
                    new LtcsBillingStatementItem(
                        serviceCode: ServiceCode::fromString('116275'),
                        serviceCodeCategory: LtcsServiceCodeCategory::treatmentImprovementAddition1(),
                        unitScore: 800,
                        count: 1,
                        totalScore: 800,
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
                        serviceDays: 9,
                        plannedScore: 5836,
                        managedScore: 5836,
                        unmanagedScore: 800,
                        insurance: new LtcsBillingStatementAggregateInsurance(
                            totalScore: 6636,
                            unitCost: Decimal::fromInt(11_4000),
                            claimAmount: 68085,
                            copayAmount: 7565,
                        ),
                        subsidies: [
                            LtcsBillingStatementAggregateSubsidy::empty(),
                            LtcsBillingStatementAggregateSubsidy::empty(),
                            LtcsBillingStatementAggregateSubsidy::empty(),
                        ],
                    ),
                ],
                appendix: null,
                status: LtcsBillingStatus::checking(),
                fixedAt: null,
                createdAt: Carbon::now(),
                updatedAt: Carbon::now(),
            ),
            new LtcsBillingStatement(
                id: 7,
                billingId: $bundle->billingId,
                bundleId: $bundle->id,
                insurerNumber: '132225',
                insurerName: '東久留米市',
                user: new LtcsBillingUser(
                    userId: $this->examples->users[6]->id,
                    ltcsInsCardId: $this->examples->ltcsInsCards[6]->id,
                    insNumber: '0000398842',
                    name: $this->examples->users[6]->name,
                    sex: Sex::female(),
                    birthday: Carbon::create(1960, 9, 25),
                    ltcsLevel: LtcsLevel::careLevel5(),
                    activatedOn: Carbon::create(2019, 7, 1),
                    deactivatedOn: Carbon::create(2019, 12, 31),
                ),
                carePlanAuthor: new LtcsCarePlanAuthor(
                    authorType: LtcsCarePlanAuthorType::careManagerOffice(),
                    officeId: $this->examples->offices[6]->id,
                    code: '1374800108',
                    name: $this->examples->offices[6]->name,
                ),
                agreedOn: null,
                expiredOn: null,
                expiredReason: LtcsExpiredReason::unspecified(),
                insurance: new LtcsBillingStatementInsurance(
                    benefitRate: 90,
                    totalScore: 8198,
                    claimAmount: 84111,
                    copayAmount: 9346,
                ),
                subsidies: [
                    LtcsBillingStatementSubsidy::empty(),
                    LtcsBillingStatementSubsidy::empty(),
                    LtcsBillingStatementSubsidy::empty(),
                ],
                items: [
                    new LtcsBillingStatementItem(
                        serviceCode: ServiceCode::fromString('111312'),
                        serviceCodeCategory: LtcsServiceCodeCategory::physicalCare(),
                        unitScore: 721,
                        count: 10,
                        totalScore: 7210,
                        subsidies: [
                            LtcsBillingStatementItemSubsidy::empty(),
                            LtcsBillingStatementItemSubsidy::empty(),
                            LtcsBillingStatementItemSubsidy::empty(),
                        ],
                        note: '',
                    ),
                    new LtcsBillingStatementItem(
                        serviceCode: ServiceCode::fromString('116275'),
                        serviceCodeCategory: LtcsServiceCodeCategory::treatmentImprovementAddition1(),
                        unitScore: 988,
                        count: 1,
                        totalScore: 988,
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
                        serviceDays: 10,
                        plannedScore: 7210,
                        managedScore: 7210,
                        unmanagedScore: 988,
                        insurance: new LtcsBillingStatementAggregateInsurance(
                            totalScore: 8198,
                            unitCost: Decimal::fromInt(11_4000),
                            claimAmount: 84111,
                            copayAmount: 9346,
                        ),
                        subsidies: [
                            LtcsBillingStatementAggregateSubsidy::empty(),
                            LtcsBillingStatementAggregateSubsidy::empty(),
                            LtcsBillingStatementAggregateSubsidy::empty(),
                        ],
                    ),
                ],
                appendix: null,
                status: LtcsBillingStatus::checking(),
                fixedAt: null,
                createdAt: Carbon::now(),
                updatedAt: Carbon::now(),
            ),
            new LtcsBillingStatement(
                id: 8,
                billingId: $bundle->billingId,
                bundleId: $bundle->id,
                insurerNumber: '132290',
                insurerName: '西東京市',
                user: new LtcsBillingUser(
                    userId: $this->examples->users[7]->id,
                    ltcsInsCardId: $this->examples->ltcsInsCards[7]->id,
                    insNumber: '3000411191',
                    name: $this->examples->users[7]->name,
                    sex: Sex::female(),
                    birthday: Carbon::create(1972, 3, 19),
                    ltcsLevel: LtcsLevel::careLevel5(),
                    activatedOn: Carbon::create(2019, 3, 1),
                    deactivatedOn: Carbon::create(2021, 2, 28),
                ),
                carePlanAuthor: new LtcsCarePlanAuthor(
                    authorType: LtcsCarePlanAuthorType::careManagerOffice(),
                    officeId: $this->examples->offices[7]->id,
                    code: '1373700036',
                    name: $this->examples->offices[7]->name,
                ),
                agreedOn: null,
                expiredOn: null,
                expiredReason: LtcsExpiredReason::unspecified(),
                insurance: new LtcsBillingStatementInsurance(
                    benefitRate: 90,
                    totalScore: 9005,
                    claimAmount: 92391,
                    copayAmount: 10266,
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
                        unitScore: 660,
                        count: 12,
                        totalScore: 7920,
                        subsidies: [
                            LtcsBillingStatementItemSubsidy::empty(),
                            LtcsBillingStatementItemSubsidy::empty(),
                            LtcsBillingStatementItemSubsidy::empty(),
                        ],
                        note: '',
                    ),
                    new LtcsBillingStatementItem(
                        serviceCode: ServiceCode::fromString('116275'),
                        serviceCodeCategory: LtcsServiceCodeCategory::treatmentImprovementAddition1(),
                        unitScore: 1085,
                        count: 1,
                        totalScore: 1085,
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
                        serviceDays: 8,
                        plannedScore: 7920,
                        managedScore: 7920,
                        unmanagedScore: 1085,
                        insurance: new LtcsBillingStatementAggregateInsurance(
                            totalScore: 9005,
                            unitCost: Decimal::fromInt(11_4000),
                            claimAmount: 92391,
                            copayAmount: 10266,
                        ),
                        subsidies: [
                            LtcsBillingStatementAggregateSubsidy::empty(),
                            LtcsBillingStatementAggregateSubsidy::empty(),
                            LtcsBillingStatementAggregateSubsidy::empty(),
                        ],
                    ),
                ],
                appendix: null,
                status: LtcsBillingStatus::checking(),
                fixedAt: null,
                createdAt: Carbon::now(),
                updatedAt: Carbon::now(),
            ),
        ];
    }
}

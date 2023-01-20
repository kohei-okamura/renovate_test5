<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\Billing;

use Domain\Billing\CopayCoordinationResult;
use Domain\Billing\DwsBilling;
use Domain\Billing\DwsBillingBundle;
use Domain\Billing\DwsBillingCopayCoordination;
use Domain\Billing\DwsBillingCopayCoordinationExchangeAim;
use Domain\Billing\DwsBillingCopayCoordinationItem;
use Domain\Billing\DwsBillingCopayCoordinationPayment;
use Domain\Billing\DwsBillingOffice;
use Domain\Billing\DwsBillingStatus;
use Domain\Billing\DwsBillingUser;
use Domain\Common\Addr;
use Domain\Common\Carbon;
use Domain\Common\Prefecture;
use Domain\Common\StructuredName;
use Domain\Exchange\DwsBillingCopayCoordinationItemRecord;
use Domain\Exchange\DwsBillingCopayCoordinationSummaryRecord;
use Domain\Exchange\DwsControlRecord;
use Domain\Exchange\EndRecord;
use Lib\Csv;
use Lib\Exceptions\FileIOException;
use Lib\Exceptions\LogicException;
use Mockery;
use ScalikePHP\Option;
use ScalikePHP\Seq;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\BuildDwsBillingCopayCoordinationRecordListUseCaseMixin;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\ConfigMixin;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\GenerateFileNameUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\StoreCsvUseCaseMixin;
use Tests\Unit\Mixins\TokenMakerMixin;
use Tests\Unit\Test;
use UseCase\Billing\CreateDwsBillingCopayCoordinationCsvInteractor;

/**
 * {@link \UseCase\Billing\CreateDwsBillingCopayCoordinationCsvInteractor} のテスト.
 */
final class CreateDwsBillingCopayCoordinationCsvInteractorTest extends Test
{
    use BuildDwsBillingCopayCoordinationRecordListUseCaseMixin;
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
    private array $records;
    private CreateDwsBillingCopayCoordinationCsvInteractor $interactor;

    /**
     * 初期化処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachSpec(function (CreateDwsBillingCopayCoordinationCsvInteractorTest $self): void {
            $self->dwsBilling = $self->createDwsBillingStub();
            $self->dwsBillingBundles = Seq::fromArray($self->createDwsBillingBundleStub($self->dwsBilling));
            $self->records = [
                DwsControlRecord::forCopayCoordination($self->dwsBilling, 0),
                EndRecord::instance(),
            ];

            $self->buildDwsBillingCopayCoordinationRecordListUseCase
                ->allows('handle')
                ->andReturn($self->records)
                ->byDefault();

            $self->storeCsvUseCase
                ->allows('handle')
                ->andReturn('path/to/stored-file.csv')
                ->byDefault();

            $self->config
                ->allows('get')
                ->with('zinger.filename.dws_copay_coordination_csv')
                ->andReturn('利用者負担上限額管理結果票_#{office}_#{transactedIn}.csv')
                ->byDefault();

            $self->tokenMaker
                ->allows('make')
                ->andReturn(str_repeat('x', 60))
                ->byDefault();
            $self->interactor = app(CreateDwsBillingCopayCoordinationCsvInteractor::class);
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
        $this->should('create records using CreateDwsCopayCoordinationRecordUseCase', function (): void {
            $this->buildDwsBillingCopayCoordinationRecordListUseCase
                ->expects('handle')
                ->with($this->context, $this->dwsBilling, $this->dwsBillingBundles)
                ->andReturn($this->records);

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
            $this->buildDwsBillingCopayCoordinationRecordListUseCase
                ->expects('handle')
                ->andReturnUsing(fn (): array => $this->createExchangeRecordsStub());
            $this->storeCsvUseCase
                ->expects('handle')
                ->with($this->context, 'artifacts', 'dws-billing-', Mockery::capture($actual))
                ->andReturn('path/to/stored-file.csv');

            $this->interactor->handle($this->context, $this->dwsBilling, $this->dwsBillingBundles);

            $this->assertEquals(
                Csv::read(__DIR__ . '/CreateDwsBillingCopayCoordinationCsvInteractorTest.csv')->toArray(),
                is_array($actual) ? $actual : iterator_to_array($actual),
            );
        });
        $this->should('not store the csv when record size is 0', function (): void {
            $this->buildDwsBillingCopayCoordinationRecordListUseCase
                ->expects('handle')
                ->with($this->context, $this->dwsBilling, $this->dwsBillingBundles)
                ->andReturn($this->records);

            $this->assertSame(
                Option::none(),
                $this->interactor->handle($this->context, $this->dwsBilling, $this->dwsBillingBundles)
            );
        });
        $this->should('throw FileIOException when StoreCsvUseCase throws it', function (): void {
            $this->buildDwsBillingCopayCoordinationRecordListUseCase
                ->expects('handle')
                ->andReturnUsing(fn (): array => $this->createExchangeRecordsStub());
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
        $bundle = $this->dwsBillingBundles;
        $copayCoordinations = Seq::fromArray($this->createDwsBillingCopayCoordinationStub());
        $records = Seq::fromArray($bundle)
            ->flatMap(function (DwsBillingBundle $bundle) use ($copayCoordinations): iterable {
                $copayCoordination = $copayCoordinations
                    ->find(fn (DwsBillingCopayCoordination $x) => $x->dwsBillingBundleId === $bundle->id)
                    ->head();
                assert($copayCoordination instanceof DwsBillingCopayCoordination);
                return [
                    DwsBillingCopayCoordinationSummaryRecord::from($bundle, $copayCoordination),
                    ...DwsBillingCopayCoordinationItemRecord::from($bundle, $copayCoordination),
                ];
            });

        return [
            DwsControlRecord::forCopayCoordination($billing, count($records)),
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
                'code' => '1116507326',
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
            'transactedIn' => Carbon::create(2020, 05),
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
     * @param \Domain\Billing\DwsBilling $billing
     * @return \Domain\Billing\DwsBillingBundle[]
     */
    private function createDwsBillingBundleStub(DwsBilling $billing): array
    {
        return [
            DwsBillingBundle::create([
                'id' => 1,
                'dwsBillingId' => $billing->id,
                'providedIn' => Carbon::create(2020, 3),
                'cityCode' => '112318',
                'cityName' => 'ﾓﾘｵｳﾁｮｳ',
                'details' => [],
                'createdAt' => Carbon::now(),
                'updatedAt' => Carbon::now(),
            ]),
            DwsBillingBundle::create([
                'id' => 2,
                'dwsBillingId' => $billing->id,
                'providedIn' => Carbon::create(2020, 3),
                'cityCode' => '111096',
                'cityName' => 'ﾄｳｷｮｳ',
                'details' => [],
                'createdAt' => Carbon::now(),
                'updatedAt' => Carbon::now(),
            ]),
        ];
    }

    /**
     * テスト用の利用者負担上限額管理結果票を生成する.
     *
     * @return \Domain\Billing\DwsBillingCopayCoordination[]
     */
    private function createDwsBillingCopayCoordinationStub(): array
    {
        return [
            DwsBillingCopayCoordination::create([
                'dwsBillingId' => 1,
                'dwsBillingBundleId' => 1,
                'office' => DwsBillingOffice::create([
                    'officeId' => 1,
                    'code' => '1116507326',
                    'name' => 'ﾅﾝﾃﾞﾓｲｲﾅﾏｴ',
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
                'user' => DwsBillingUser::create([
                    'userId' => self::examples()->users[0]->id,
                    'dwsCertificationId' => self::examples()->dwsCertifications[0]->id,
                    'dwsNumber' => '3100006729',
                    'name' => new StructuredName(
                        familyName: 'ﾃｽﾄ',
                        givenName: 'ﾀﾛｳ',
                        phoneticFamilyName: 'ﾃｽﾄ',
                        phoneticGivenName: 'ﾀﾛｳ',
                    ),
                    'childName' => StructuredName::empty(),
                    'copayLimit' => 9300,
                ]),
                'result' => CopayCoordinationResult::coordinated(),
                'exchangeAim' => DwsBillingCopayCoordinationExchangeAim::declaration(),
                'items' => [
                    DwsBillingCopayCoordinationItem::create(
                        [
                            'itemNumber' => 1,
                            'office' => DwsBillingOffice::create([
                                'officeId' => 1,
                                'code' => '1116507326',
                                'name' => 'ﾅﾝﾃﾞﾓｲｲﾅﾏｴ',
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
                            'subtotal' => DwsBillingCopayCoordinationPayment::create([
                                'fee' => 59252,
                                'copay' => 5925,
                                'coordinatedCopay' => 5925,
                            ]),
                        ]
                    ),
                    DwsBillingCopayCoordinationItem::create(
                        [
                            'itemNumber' => 2,
                            'office' => DwsBillingOffice::create([
                                'officeId' => 2,
                                'code' => '1111600258',
                                'name' => 'ﾅﾝﾃﾞﾓｲｲﾅﾏｴ',
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
                            'subtotal' => DwsBillingCopayCoordinationPayment::create([
                                'fee' => 77264,
                                'copay' => 7726,
                                'coordinatedCopay' => 3375,
                            ]),
                        ]
                    ),
                    DwsBillingCopayCoordinationItem::create(
                        [
                            'itemNumber' => 3,
                            'office' => DwsBillingOffice::create([
                                'officeId' => 3,
                                'code' => '1110801055',
                                'name' => 'ﾅﾝﾃﾞﾓｲｲﾅﾏｴ',
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
                            'subtotal' => DwsBillingCopayCoordinationPayment::create([
                                'fee' => 208857,
                                'copay' => 0,
                                'coordinatedCopay' => 0,
                            ]),
                        ]
                    ),
                ],
                'total' => DwsBillingCopayCoordinationPayment::create([
                    'fee' => 345373,
                    'copay' => 13651,
                    'coordinatedCopay' => 9300,
                ]),
                'createdAt' => Carbon::now(),
                'updatedAt' => Carbon::now(),
            ]),
            DwsBillingCopayCoordination::create([
                'dwsBillingId' => 1,
                'dwsBillingBundleId' => 2,
                'office' => DwsBillingOffice::create([
                    'officeId' => self::examples()->offices[0]->id,
                    'code' => '1116507326',
                    'name' => 'ﾅﾝﾃﾞﾓｲｲﾅﾏｴ',
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
                'user' => DwsBillingUser::create([
                    'userId' => self::examples()->users[0]->id,
                    'dwsCertificationId' => self::examples()->dwsCertifications[0]->id,
                    'dwsNumber' => '3000041941',
                    'name' => new StructuredName(
                        familyName: 'ﾃｽﾄ',
                        givenName: 'ﾌﾀﾘﾒ',
                        phoneticFamilyName: 'ﾃｽﾄ',
                        phoneticGivenName: 'ﾌﾀﾘﾒ',
                    ),
                    'childName' => StructuredName::empty(),
                    'copayLimit' => 9300,
                ]),
                'result' => CopayCoordinationResult::appropriated(),
                'exchangeAim' => DwsBillingCopayCoordinationExchangeAim::declaration(),
                'items' => [
                    DwsBillingCopayCoordinationItem::create(
                        [
                            'itemNumber' => 1,
                            'office' => DwsBillingOffice::create([
                                'officeId' => self::examples()->offices[0]->id,
                                'code' => '1116507326',
                                'name' => 'ﾅﾝﾃﾞﾓｲｲﾅﾏｴ',
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
                            'subtotal' => DwsBillingCopayCoordinationPayment::create([
                                'fee' => 454508,
                                'copay' => 9300,
                                'coordinatedCopay' => 9300,
                            ]),
                        ]
                    ),
                    DwsBillingCopayCoordinationItem::create(
                        [
                            'itemNumber' => 2,
                            'office' => DwsBillingOffice::create([
                                'officeId' => 4,
                                'code' => '1111400063',
                                'name' => 'ﾅﾝﾃﾞﾓｲｲﾅﾏｴ',
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
                            'subtotal' => DwsBillingCopayCoordinationPayment::create([
                                'fee' => 625267,
                                'copay' => 9300,
                                'coordinatedCopay' => 0,
                            ]),
                        ]
                    ),
                ],
                'total' => DwsBillingCopayCoordinationPayment::create([
                    'fee' => 1079775,
                    'copay' => 18600,
                    'coordinatedCopay' => 9300,
                ]),
                'createdAt' => Carbon::now(),
                'updatedAt' => Carbon::now(),
            ]),
        ];
    }
}

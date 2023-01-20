<?php
/*
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
use Domain\Billing\DwsBillingStatement;
use Domain\Billing\DwsBillingStatementCopayCoordinationStatus;
use Domain\Billing\DwsBillingStatus;
use Domain\Billing\DwsBillingUser;
use Domain\Common\Carbon;
use Domain\Common\Pagination;
use Domain\DwsCertification\CopayCoordination;
use Domain\DwsCertification\CopayCoordinationType;
use Domain\DwsCertification\DwsCertification;
use Domain\FinderResult;
use Domain\Office\Office;
use Domain\Permission\Permission;
use Domain\User\User;
use Lib\Exceptions\NotFoundException;
use Mockery;
use ScalikePHP\Option;
use ScalikePHP\Seq;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\DwsBillingCopayCoordinationRepositoryMixin;
use Tests\Unit\Mixins\DwsBillingStatementFinderMixin;
use Tests\Unit\Mixins\EditDwsBillingStatementUseCaseMixin;
use Tests\Unit\Mixins\GetOfficeListUseCaseMixin;
use Tests\Unit\Mixins\IdentifyDwsCertificationUseCaseMixin;
use Tests\Unit\Mixins\LoggerMixin;
use Tests\Unit\Mixins\LookupDwsBillingBundleUseCaseMixin;
use Tests\Unit\Mixins\LookupDwsBillingUseCaseMixin;
use Tests\Unit\Mixins\LookupUserUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\TransactionManagerMixin;
use Tests\Unit\Test;
use UseCase\Billing\CreateDwsBillingCopayCoordinationInteractor;

/**
 * {@link \UseCase\Billing\CreateDwsBillingCopayCoordinationInteractor} のテスト.
 */
final class CreateDwsBillingCopayCoordinationInteractorTest extends Test
{
    use CarbonMixin;
    use ContextMixin;
    use DwsBillingCopayCoordinationRepositoryMixin;
    use DwsBillingStatementFinderMixin;
    use EditDwsBillingStatementUseCaseMixin;
    use ExamplesConsumer;
    use GetOfficeListUseCaseMixin;
    use IdentifyDwsCertificationUseCaseMixin;
    use LoggerMixin;
    use LookupDwsBillingBundleUseCaseMixin;
    use LookupDwsBillingUseCaseMixin;
    use LookupUserUseCaseMixin;
    use MatchesSnapshots;
    use MockeryMixin;
    use TransactionManagerMixin;
    use UnitSupport;

    private User $user;
    private DwsCertification $certification;
    private DwsBilling $billing;
    private DwsBillingBundle $bundle;
    private DwsBillingStatement $statement;
    private DwsBillingCopayCoordination $copayCoordination;
    private Office $office;
    private Office $serviceOffice;

    private CreateDwsBillingCopayCoordinationInteractor $interactor;

    /**
     * 初期化処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachTest(function (self $self): void {
            $self->user = $self->examples->users[0]->copy([
                'id' => 22360679,
            ]);
            $self->certification = $self->examples->dwsCertifications[0]->copy([
                'userId' => 22360679,
                'dwsNumber' => '0000390229',
                'copayCoordination' => CopayCoordination::create([
                    'copayCoordinationType' => CopayCoordinationType::internal(),
                    'officeId' => 24494897,
                ]),
            ]);
            $self->office = $self->examples->offices[2]->copy([
                'id' => 24494897,
            ]);
            $self->serviceOffice = $self->examples->offices[3]->copy([
                'id' => 26457513,
            ]);
            $self->billing = $self->examples->dwsBillings[0]->copy([
                'id' => 14141356,
                'office' => DwsBillingOffice::from($self->office),
                'transactedIn' => Carbon::create(2022, 5),
            ]);
            $self->bundle = $self->examples->dwsBillingBundles[0]->copy([
                'id' => 17320508,
                'dwsBillingId' => 14141356,
                'providedIn' => Carbon::create(2022, 4),
            ]);
            $self->statement = $self->examples->dwsBillingStatements[0]->copy([
                'id' => 28284271,
            ]);
            $self->copayCoordination = DwsBillingCopayCoordination::create([
                'id' => 12345678,
                'dwsBillingId' => 14141356,
                'dwsBillingBundleId' => 17320508,
                'office' => DwsBillingOffice::from($self->office),
                'user' => DwsBillingUser::from($self->user, $self->certification),
                'result' => CopayCoordinationResult::appropriated(),
                'exchangeAim' => DwsBillingCopayCoordinationExchangeAim::declaration(),
                'items' => [
                    DwsBillingCopayCoordinationItem::create([
                        'itemNumber' => 1,
                        'office' => DwsBillingOffice::from($self->office),
                        'subtotal' => DwsBillingCopayCoordinationPayment::create([
                            'fee' => 198000,
                            'copay' => 9300,
                            'coordinatedCopay' => 9300,
                        ]),
                    ]),
                    DwsBillingCopayCoordinationItem::create([
                        'itemNumber' => 2,
                        'office' => DwsBillingOffice::from($self->serviceOffice),
                        'subtotal' => DwsBillingCopayCoordinationPayment::create([
                            'fee' => 123000,
                            'copay' => 9300,
                            'coordinatedCopay' => 0,
                        ]),
                    ]),
                ],
                'total' => DwsBillingCopayCoordinationPayment::create([
                    'fee' => 321000,
                    'copay' => 18600,
                    'coordinatedCopay' => 9300,
                ]),
                'status' => DwsBillingStatus::ready(),
                'createdAt' => Carbon::now(),
                'updatedAt' => Carbon::now(),
            ]);
        });
        self::beforeEachSpec(function (self $self): void {
            $self->lookupUserUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->user))
                ->byDefault();
            $self->lookupDwsBillingUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->billing))
                ->byDefault();
            $self->lookupDwsBillingBundleUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->bundle))
                ->byDefault();
            $self->getOfficeListUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->office, $self->serviceOffice))
                ->byDefault();
            $self->identifyDwsCertificationUseCase
                ->allows('handle')
                ->andReturn(Option::from($self->certification))
                ->byDefault();
            $self->dwsBillingCopayCoordinationRepository
                ->allows('store')
                ->andReturnUsing(fn (DwsBillingCopayCoordination $x): DwsBillingCopayCoordination => $x->copy([
                    'id' => 12345678,
                ]))
                ->byDefault();
            $self->dwsBillingStatementFinder
                ->allows('find')
                ->andReturn(FinderResult::from(Seq::from($self->statement), Pagination::create([
                    'count' => 1,
                    'desc' => false,
                    'itemsPerPage' => 1,
                    'page' => 1,
                    'pages' => 1,
                    'sortBy' => 'id',
                ])))
                ->byDefault();
            $self->editDwsBillingStatementUseCase
                ->allows('handle')
                ->andReturn($self->statement)
                ->byDefault();
            $self->logger
                ->allows('info')
                ->andReturnNull()
                ->byDefault();

            $self->interactor = app(CreateDwsBillingCopayCoordinationInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->specify(
            '指定された User を取得する',
            function (): void {
                $this->lookupUserUseCase
                    ->expects('handle')
                    ->with($this->context, Permission::createBillings(), 22360679)
                    ->andReturn(Seq::from($this->user));

                $this->interactor->handle(
                    context: $this->context,
                    billingId: 14141356,
                    bundleId: 17320508,
                    userId: 22360679,
                    result: CopayCoordinationResult::appropriated(),
                    exchangeAim: DwsBillingCopayCoordinationExchangeAim::declaration(),
                    items: [
                        [
                            'itemNumber' => 3,
                            'officeId' => 26457513,
                            'subtotal' => DwsBillingCopayCoordinationPayment::create([
                                'fee' => 20000,
                                'copay' => 10000,
                                'coordinatedCopay' => 9000,
                            ]),
                        ],
                    ],
                );
            }
        );
        $this->specify(
            '指定された User が見つからない場合は NotFoundException を投げる',
            function (): void {
                $this->lookupUserUseCase
                    ->expects('handle')
                    ->andReturn(Seq::empty());

                $this->assertThrows(NotFoundException::class, function (): void {
                    $this->interactor->handle(
                        context: $this->context,
                        billingId: 14141356,
                        bundleId: 17320508,
                        userId: 22360679,
                        result: CopayCoordinationResult::appropriated(),
                        exchangeAim: DwsBillingCopayCoordinationExchangeAim::declaration(),
                        items: [
                            [
                                'itemNumber' => 3,
                                'officeId' => 26457513,
                                'subtotal' => DwsBillingCopayCoordinationPayment::create([
                                    'fee' => 20000,
                                    'copay' => 10000,
                                    'coordinatedCopay' => 9000,
                                ]),
                            ],
                        ],
                    );
                });
            }
        );
        $this->specify(
            '指定された DwsBilling をリポジトリから取得する',
            function (): void {
                $this->lookupDwsBillingUseCase
                    ->expects('handle')
                    ->with($this->context, Permission::createBillings(), 14141356)
                    ->andReturn(Seq::from($this->billing));

                $this->interactor->handle(
                    context: $this->context,
                    billingId: 14141356,
                    bundleId: 17320508,
                    userId: 22360679,
                    result: CopayCoordinationResult::appropriated(),
                    exchangeAim: DwsBillingCopayCoordinationExchangeAim::declaration(),
                    items: [
                        [
                            'itemNumber' => 3,
                            'officeId' => 26457513,
                            'subtotal' => DwsBillingCopayCoordinationPayment::create([
                                'fee' => 20000,
                                'copay' => 10000,
                                'coordinatedCopay' => 9000,
                            ]),
                        ],
                    ],
                );
            }
        );
        $this->specify(
            '指定された DwsBilling が見つからない場合は NotFoundException を投げる',
            function (): void {
                $this->lookupDwsBillingUseCase
                    ->expects('handle')
                    ->andReturn(Seq::empty());

                $this->assertThrows(NotFoundException::class, function (): void {
                    $this->interactor->handle(
                        context: $this->context,
                        billingId: 14141356,
                        bundleId: 17320508,
                        userId: 22360679,
                        result: CopayCoordinationResult::appropriated(),
                        exchangeAim: DwsBillingCopayCoordinationExchangeAim::declaration(),
                        items: [
                            [
                                'itemNumber' => 3,
                                'officeId' => 26457513,
                                'subtotal' => DwsBillingCopayCoordinationPayment::create([
                                    'fee' => 20000,
                                    'copay' => 10000,
                                    'coordinatedCopay' => 9000,
                                ]),
                            ],
                        ],
                    );
                });
            }
        );
        $this->specify(
            '指定された DwsBillingBundle をリポジトリから取得する',
            function (): void {
                $this->lookupDwsBillingBundleUseCase
                    ->expects('handle')
                    ->with($this->context, Permission::createBillings(), 14141356, 17320508)
                    ->andReturn(Seq::from($this->bundle));

                $this->interactor->handle(
                    context: $this->context,
                    billingId: 14141356,
                    bundleId: 17320508,
                    userId: 22360679,
                    result: CopayCoordinationResult::appropriated(),
                    exchangeAim: DwsBillingCopayCoordinationExchangeAim::declaration(),
                    items: [
                        [
                            'itemNumber' => 3,
                            'officeId' => 26457513,
                            'subtotal' => DwsBillingCopayCoordinationPayment::create([
                                'fee' => 20000,
                                'copay' => 10000,
                                'coordinatedCopay' => 9000,
                            ]),
                        ],
                    ],
                );
            }
        );
        $this->specify(
            '指定された DwsBillingBundle が見つからない場合は NotFoundException を投げる',
            function (): void {
                $this->lookupDwsBillingBundleUseCase
                    ->expects('handle')
                    ->andReturn(Seq::empty());

                $this->assertThrows(NotFoundException::class, function (): void {
                    $this->interactor->handle(
                        context: $this->context,
                        billingId: 14141356,
                        bundleId: 17320508,
                        userId: 22360679,
                        result: CopayCoordinationResult::appropriated(),
                        exchangeAim: DwsBillingCopayCoordinationExchangeAim::declaration(),
                        items: [
                            [
                                'itemNumber' => 3,
                                'officeId' => 26457513,
                                'subtotal' => DwsBillingCopayCoordinationPayment::create([
                                    'fee' => 20000,
                                    'copay' => 10000,
                                    'coordinatedCopay' => 9000,
                                ]),
                            ],
                        ],
                    );
                });
            }
        );
        $this->specify(
            '利用者とサービス提供年月に対応する DwsCertification を特定する',
            function (): void {
                $this->identifyDwsCertificationUseCase
                    ->expects('handle')
                    ->with($this->context, 22360679, equalTo(Carbon::create(2022, 4)))
                    ->andReturn(Option::some($this->certification));

                $this->interactor->handle(
                    context: $this->context,
                    billingId: 14141356,
                    bundleId: 17320508,
                    userId: 22360679,
                    result: CopayCoordinationResult::appropriated(),
                    exchangeAim: DwsBillingCopayCoordinationExchangeAim::declaration(),
                    items: [
                        [
                            'itemNumber' => 3,
                            'officeId' => 26457513,
                            'subtotal' => DwsBillingCopayCoordinationPayment::create([
                                'fee' => 20000,
                                'copay' => 10000,
                                'coordinatedCopay' => 9000,
                            ]),
                        ],
                    ],
                );
            }
        );
        $this->specify(
            'DwsCertification が特定できない場合は NotFoundException を投げる',
            function (): void {
                $this->identifyDwsCertificationUseCase
                    ->expects('handle')
                    ->andReturn(Option::none());

                $this->assertThrows(NotFoundException::class, function (): void {
                    $this->interactor->handle(
                        context: $this->context,
                        billingId: 14141356,
                        bundleId: 17320508,
                        userId: 22360679,
                        result: CopayCoordinationResult::appropriated(),
                        exchangeAim: DwsBillingCopayCoordinationExchangeAim::declaration(),
                        items: [
                            [
                                'itemNumber' => 3,
                                'officeId' => 26457513,
                                'subtotal' => DwsBillingCopayCoordinationPayment::create([
                                    'fee' => 20000,
                                    'copay' => 10000,
                                    'coordinatedCopay' => 9000,
                                ]),
                            ],
                        ],
                    );
                });
            }
        );
        $this->specify(
            '利用者負担上限額管理結果票に含まれる Office の一覧を取得する',
            function (): void {
                $this->getOfficeListUseCase
                    ->expects('handle')
                    ->with($this->context, 24494897, 26457513)
                    ->andReturn(Seq::from(
                        $this->office,
                        $this->serviceOffice
                    ));

                $this->interactor->handle(
                    context: $this->context,
                    billingId: 14141356,
                    bundleId: 17320508,
                    userId: 22360679,
                    result: CopayCoordinationResult::appropriated(),
                    exchangeAim: DwsBillingCopayCoordinationExchangeAim::declaration(),
                    items: [
                        [
                            'itemNumber' => 3,
                            'officeId' => 26457513,
                            'subtotal' => DwsBillingCopayCoordinationPayment::create([
                                'fee' => 20000,
                                'copay' => 10000,
                                'coordinatedCopay' => 9000,
                            ]),
                        ],
                    ],
                );
            }
        );
        $this->specify(
            '請求単位と利用者に対応する DwsBillingStatement を取得する',
            function (): void {
                $paginationParams = [
                    'all' => true,
                    'sortBy' => 'id',
                ];
                $expected = [
                    'dwsBillingBundleId' => 17320508,
                    'userId' => 22360679,
                ];
                $this->dwsBillingStatementFinder
                    ->expects('find')
                    ->with(Mockery::capture($actual), $paginationParams)
                    ->andReturn(FinderResult::from(Seq::from($this->statement), Pagination::create([
                        'count' => 1,
                        'desc' => false,
                        'itemsPerPage' => 1,
                        'page' => 1,
                        'pages' => 1,
                        'sortBy' => 'id',
                    ])));

                $this->interactor->handle(
                    context: $this->context,
                    billingId: 14141356,
                    bundleId: 17320508,
                    userId: 22360679,
                    result: CopayCoordinationResult::appropriated(),
                    exchangeAim: DwsBillingCopayCoordinationExchangeAim::declaration(),
                    items: [
                        [
                            'itemNumber' => 3,
                            'officeId' => 26457513,
                            'subtotal' => DwsBillingCopayCoordinationPayment::create([
                                'fee' => 20000,
                                'copay' => 10000,
                                'coordinatedCopay' => 9000,
                            ]),
                        ],
                    ],
                );

                $this->assertSame($expected, $actual);
            }
        );
        $this->specify(
            'DwsBillingStatement の上限管理区分を書き換える',
            function (): void {
                $expected = [
                    'copayCoordinationStatus' => DwsBillingStatementCopayCoordinationStatus::checking(),
                ];
                $this->editDwsBillingStatementUseCase
                    ->expects('handle')
                    ->with(
                        $this->context,
                        14141356,
                        17320508,
                        28284271,
                        Mockery::capture($actual)
                    )
                    ->andReturn($this->statement);

                $this->interactor->handle(
                    context: $this->context,
                    billingId: 14141356,
                    bundleId: 17320508,
                    userId: 22360679,
                    result: CopayCoordinationResult::appropriated(),
                    exchangeAim: DwsBillingCopayCoordinationExchangeAim::declaration(),
                    items: [
                        [
                            'itemNumber' => 3,
                            'officeId' => 26457513,
                            'subtotal' => DwsBillingCopayCoordinationPayment::create([
                                'fee' => 20000,
                                'copay' => 10000,
                                'coordinatedCopay' => 9000,
                            ]),
                        ],
                    ],
                );

                $this->assertSame($expected, $actual);
            }
        );
        $this->specify(
            '組み立てた DwsBillingCopayCoordination をリポジトリに格納する',
            function (): void {
                $expected = $this->copayCoordination->copy(['id' => null]);
                $this->dwsBillingCopayCoordinationRepository
                    ->expects('store')
                    ->with(Mockery::capture($actual))
                    ->andReturnUsing(fn (DwsBillingCopayCoordination $x): DwsBillingCopayCoordination => $x->copy([
                        'id' => 31415926,
                    ]));

                $this->interactor->handle(
                    context: $this->context,
                    billingId: 14141356,
                    bundleId: 17320508,
                    userId: 22360679,
                    result: CopayCoordinationResult::appropriated(),
                    exchangeAim: DwsBillingCopayCoordinationExchangeAim::declaration(),
                    items: [
                        [
                            'itemNumber' => 1,
                            'officeId' => 24494897,
                            'subtotal' => DwsBillingCopayCoordinationPayment::create([
                                'fee' => 198000,
                                'copay' => 9300,
                                'coordinatedCopay' => 9300,
                            ]),
                        ],
                        [
                            'itemNumber' => 2,
                            'officeId' => 26457513,
                            'subtotal' => DwsBillingCopayCoordinationPayment::create([
                                'fee' => 123000,
                                'copay' => 9300,
                                'coordinatedCopay' => 0,
                            ]),
                        ],
                    ],
                );

                $this->assertModelStrictEquals($expected, $actual);
            }
        );
        $this->specify(
            '上限管理事業所が見つからない場合は NotFoundException を投げる',
            function (): void {
                $this->getOfficeListUseCase
                    ->expects('handle')
                    ->andReturn(Seq::from($this->serviceOffice));

                $this->assertThrows(NotFoundException::class, function (): void {
                    $this->interactor->handle(
                        context: $this->context,
                        billingId: 14141356,
                        bundleId: 17320508,
                        userId: 22360679,
                        result: CopayCoordinationResult::appropriated(),
                        exchangeAim: DwsBillingCopayCoordinationExchangeAim::declaration(),
                        items: [
                            [
                                'itemNumber' => 3,
                                'officeId' => 26457513,
                                'subtotal' => DwsBillingCopayCoordinationPayment::create([
                                    'fee' => 20000,
                                    'copay' => 10000,
                                    'coordinatedCopay' => 9000,
                                ]),
                            ],
                        ],
                    );
                });
            }
        );
        $this->specify(
            '関係事業所が見つからない場合は NotFoundException を投げる',
            function (): void {
                $this->getOfficeListUseCase
                    ->expects('handle')
                    ->andReturn(Seq::from($this->serviceOffice));

                $this->assertThrows(NotFoundException::class, function (): void {
                    $this->interactor->handle(
                        context: $this->context,
                        billingId: 14141356,
                        bundleId: 17320508,
                        userId: 22360679,
                        result: CopayCoordinationResult::appropriated(),
                        exchangeAim: DwsBillingCopayCoordinationExchangeAim::declaration(),
                        items: [
                            [
                                'itemNumber' => 3,
                                'officeId' => 26457513,
                                'subtotal' => DwsBillingCopayCoordinationPayment::create([
                                    'fee' => 20000,
                                    'copay' => 10000,
                                    'coordinatedCopay' => 9000,
                                ]),
                            ],
                        ],
                    );
                });
            }
        );
        $this->specify(
            '利用者負担上限額管理結果票に関する情報を連想配列で返す',
            function (): void {
                $expected = [
                    'billing' => $this->billing,
                    'bundle' => $this->bundle,
                    'copayCoordination' => $this->copayCoordination,
                ];

                $actual = $this->interactor->handle(
                    context: $this->context,
                    billingId: 14141356,
                    bundleId: 17320508,
                    userId: 22360679,
                    result: CopayCoordinationResult::appropriated(),
                    exchangeAim: DwsBillingCopayCoordinationExchangeAim::declaration(),
                    items: [
                        [
                            'itemNumber' => 1,
                            'officeId' => 24494897,
                            'subtotal' => DwsBillingCopayCoordinationPayment::create([
                                'fee' => 198000,
                                'copay' => 9300,
                                'coordinatedCopay' => 9300,
                            ]),
                        ],
                        [
                            'itemNumber' => 2,
                            'officeId' => 26457513,
                            'subtotal' => DwsBillingCopayCoordinationPayment::create([
                                'fee' => 123000,
                                'copay' => 9300,
                                'coordinatedCopay' => 0,
                            ]),
                        ],
                    ],
                );

                $this->assertCount(3, $actual);
                $this->assertModelStrictEquals($expected['billing'], $actual['billing']);
                $this->assertModelStrictEquals($expected['bundle'], $actual['bundle']);
                $this->assertModelStrictEquals($expected['copayCoordination'], $actual['copayCoordination']);
            }
        );
    }
}

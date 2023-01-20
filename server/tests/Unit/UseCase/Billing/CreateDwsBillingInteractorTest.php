<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\Billing;

use Domain\Billing\DwsBilling;
use Domain\Billing\DwsBillingBundle;
use Domain\Billing\DwsBillingInvoice;
use Domain\Billing\DwsBillingSource;
use Domain\Billing\DwsBillingStatement;
use Domain\Billing\DwsBillingUser;
use Domain\Common\Carbon;
use Domain\Common\CarbonRange;
use Domain\Common\Pagination;
use Domain\FinderResult;
use Domain\Permission\Permission;
use Domain\ProvisionReport\DwsProvisionReport;
use Domain\ProvisionReport\DwsProvisionReportStatus;
use Exception;
use Lib\Exceptions\NotFoundException;
use Mockery;
use ScalikePHP\Seq;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\BuildDwsBillingSourceListUseCaseMixin;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\CreateDwsBillingBundleListUseCaseMixin;
use Tests\Unit\Mixins\CreateDwsBillingInvoiceUseCaseMixin;
use Tests\Unit\Mixins\CreateDwsBillingServiceReportListUseCaseMixin;
use Tests\Unit\Mixins\CreateDwsBillingStatementListUseCaseMixin;
use Tests\Unit\Mixins\DwsBillingRepositoryMixin;
use Tests\Unit\Mixins\DwsProvisionReportFinderMixin;
use Tests\Unit\Mixins\LookupOfficeUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\TransactionManagerMixin;
use Tests\Unit\Test;
use UseCase\Billing\CreateDwsBillingInteractor;

/**
 * {@link \UseCase\Billing\CreateDwsBillingInteractor} のテスト.
 */
final class CreateDwsBillingInteractorTest extends Test
{
    use BuildDwsBillingSourceListUseCaseMixin;
    use CarbonMixin;
    use ContextMixin;
    use CreateDwsBillingBundleListUseCaseMixin;
    use CreateDwsBillingInvoiceUseCaseMixin;
    use CreateDwsBillingStatementListUseCaseMixin;
    use CreateDwsBillingServiceReportListUseCaseMixin;
    use DwsBillingRepositoryMixin;
    use DwsBillingTestSupport;
    use DwsProvisionReportFinderMixin;
    use ExamplesConsumer;
    use LookupOfficeUseCaseMixin;
    use MatchesSnapshots;
    use MockeryMixin;
    use TransactionManagerMixin;
    use UnitSupport;

    private Carbon $transactedIn;
    private CarbonRange $fixedAt;

    /** @var \Domain\Billing\DwsBillingBundle[]|\ScalikePHP\Seq */
    private Seq $bundles;

    private CreateDwsBillingInteractor $interactor;

    /**
     * 初期化処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachTest(function (self $self): void {
            $self->setupTestData();
            $self->providedIn = Carbon::create(2021, 1);
            $self->transactedIn = Carbon::create(2021, 2);
            $self->fixedAt = CarbonRange::create([
                'start' => Carbon::create(2021, 1, 11),
                'end' => Carbon::create(2021, 2, 10),
            ]);
            $self->reports = Seq::from(
                $self->report([
                    'id' => 1,
                    'userId' => $self->users[0]->id,
                    'providedIn' => Carbon::create(2021, 1),
                ]),
                $self->report([
                    'id' => 2,
                    'userId' => $self->users[1]->id,
                    'providedIn' => Carbon::create(2021, 1),
                ]),
                $self->report([
                    'id' => 3,
                    'userId' => $self->users[2]->id,
                    'providedIn' => Carbon::create(2021, 1),
                ]),
                $self->report([
                    'id' => 4,
                    'userId' => $self->users[0]->id,
                    'providedIn' => Carbon::create(2020, 12),
                ]),
                $self->report([
                    'id' => 5,
                    'userId' => $self->users[1]->id,
                    'providedIn' => Carbon::create(2020, 12),
                ]),
            );
            $self->sources = Seq::from(
                DwsBillingSource::create([
                    'certification' => $self->dwsCertifications[0],
                    'provisionReport' => $self->reports[0],
                ]),
                DwsBillingSource::create([
                    'certification' => $self->dwsCertifications[1],
                    'provisionReport' => $self->reports[1],
                ]),
                DwsBillingSource::create([
                    'certification' => $self->dwsCertifications[2],
                    'provisionReport' => $self->reports[2],
                ]),
                DwsBillingSource::create([
                    'certification' => $self->dwsCertifications[0],
                    'provisionReport' => $self->reports[3],
                ]),
                DwsBillingSource::create([
                    'certification' => $self->dwsCertifications[1],
                    'provisionReport' => $self->reports[4],
                ]),
            );
            $self->bundles = Seq::from(
                $self->bundle([
                    'id' => 1,
                    'providedIn' => Carbon::create(2021, 1),
                    'cityCode' => '141421',
                    'cityName' => '米花市',
                ]),
                $self->bundle([
                    'id' => 2,
                    'providedIn' => Carbon::create(2021, 1),
                    'cityCode' => '173205',
                    'cityName' => '古糸市',
                ]),
                $self->bundle([
                    'id' => 3,
                    'providedIn' => Carbon::create(2020, 12),
                    'cityCode' => '141421',
                    'cityName' => '米花市',
                ]),
            );
            $self->statements = Seq::from(
                $self->statement(['user' => DwsBillingUser::from($self->users[0], $self->dwsCertifications[0])]),
                $self->statement(['user' => DwsBillingUser::from($self->users[1], $self->dwsCertifications[1])]),
                $self->statement(['user' => DwsBillingUser::from($self->users[2], $self->dwsCertifications[2])]),
            );
        });
        self::beforeEachSpec(function (self $self): void {
            $self->lookupOfficeUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->office))
                ->byDefault();

            $self->dwsBillingRepository
                ->allows('store')
                ->andReturn($self->billing)
                ->byDefault();

            $self->dwsProvisionReportFinder
                ->allows('find')
                ->andReturn(FinderResult::from($self->reports, Pagination::create([])))
                ->byDefault();

            $self->buildDwsBillingSourceListUseCase
                ->allows('handle')
                ->andReturn(
                    $self->sources->take(3)->computed(),
                    $self->sources->drop(3)->computed(),
                )
                ->byDefault();

            $self->createDwsBillingBundleListUseCase
                ->allows('handle')
                ->andReturn(
                    $self->bundles->take(2),
                    $self->bundles->drop(2),
                )
                ->byDefault();

            $self->createDwsBillingStatementListUseCase
                ->allows('handle')
                ->andReturn(
                    $self->statements->take(2),
                    $self->statements->drop(2),
                    $self->statements->take(2),
                )
                ->byDefault();

            $self->createDwsBillingInvoiceUseCase
                ->allows('handle')
                ->andReturn(DwsBillingInvoice::create([]))
                ->byDefault();

            $self->createDwsBillingServiceReportListUseCase
                ->allows('handle')
                ->andReturn($self->serviceReports)
                ->byDefault();

            $self->interactor = app(CreateDwsBillingInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle()
    {
        $this->should('run in transaction', function (): void {
            $this->transactionManager->expects('run')->andReturn(DwsBilling::create([]));
            $this->lookupOfficeUseCase->expects('handle')->never();
            $this->dwsBillingRepository->expects('store')->never();
            $this->dwsProvisionReportFinder->expects('find')->never();
            $this->createDwsBillingBundleListUseCase->expects('handle')->never();
            $this->createDwsBillingStatementListUseCase->expects('handle')->never();
            $this->createDwsBillingInvoiceUseCase->expects('handle')->never();
            $this->createDwsBillingServiceReportListUseCase->expects('handle')->never();

            $this->interactor->handle($this->context, $this->office->id, $this->transactedIn, $this->fixedAt);
        });
        $this->should('lookup office', function (): void {
            $this->lookupOfficeUseCase
                ->expects('handle')
                ->with($this->context, [Permission::createBillings()], $this->office->id)
                ->andReturn(Seq::from($this->office));

            $this->interactor->handle($this->context, $this->office->id, $this->transactedIn, $this->fixedAt);
        });
        $this->should('store the DwsBilling into the repository', function (): void {
            $this->dwsBillingRepository
                ->expects('store')
                ->with(Mockery::capture($actual))
                ->andReturnUsing(fn (DwsBilling $x): DwsBilling => $x->copy(['id' => 20080517]));

            $this->interactor->handle($this->context, $this->office->id, $this->transactedIn, $this->fixedAt);

            $this->assertMatchesModelSnapshot($actual);
        });
        $this->should('find DwsProvisionReports', function (): void {
            $filterParams = [
                'officeId' => $this->office->id,
                'fixedAt' => $this->fixedAt,
                'status' => DwsProvisionReportStatus::fixed(),
            ];
            $paginationParams = [
                'all' => true,
                'sortBy' => 'id',
            ];
            $this->dwsProvisionReportFinder
                ->expects('find')
                ->with($filterParams, $paginationParams)
                ->andReturn(FinderResult::from($this->reports, Pagination::create([])));

            $this->interactor->handle($this->context, $this->office->id, $this->transactedIn, $this->fixedAt);
        });
        $this->should('find previous month DwsProvisionReports', function (): void {
            $filterParams = [
                'officeId' => $this->office->id,
                'providedIn' => $this->report->providedIn->subMonth(),
                'status' => DwsProvisionReportStatus::fixed(),
            ];
            $paginationParams = [
                'all' => true,
                'sortBy' => 'id',
            ];
            $this->dwsProvisionReportFinder
                ->expects('find')
                ->andReturn(FinderResult::from(Seq::from($this->report), Pagination::create([])));
            $this->dwsProvisionReportFinder
                ->expects('find')
                ->with($filterParams, $paginationParams)
                ->andReturn(FinderResult::from($this->reports, Pagination::create([])));

            $this->interactor->handle($this->context, $this->office->id, $this->transactedIn, $this->fixedAt);
        });
        $this->should('build a Seq of DwsBillingSource for each months', function (): void {
            $this->buildDwsBillingSourceListUseCase
                ->expects('handle')
                ->with($this->context, Mockery::capture($actual1st), Mockery::capture($previousActual1st))
                ->andReturn($this->sources->take(3)->computed());
            $this->buildDwsBillingSourceListUseCase
                ->expects('handle')
                ->with($this->context, Mockery::capture($actual2nd), Mockery::capture($previousActual2nd))
                ->andReturn($this->sources->drop(3)->computed());

            $this->interactor->handle($this->context, $this->office->id, $this->transactedIn, $this->fixedAt);

            $this->assertEach(
                fn (DwsProvisionReport $a, DwsProvisionReport $b): bool => $a === $b,
                [...$this->reports->take(3)],
                [...$actual1st]
            );
            $this->assertEach(
                fn (DwsProvisionReport $a, DwsProvisionReport $b): bool => $a === $b,
                [...$this->reports],
                [...$previousActual1st]
            );
            $this->assertEach(
                fn (DwsProvisionReport $a, DwsProvisionReport $b): bool => $a === $b,
                [...$this->reports->drop(3)],
                [...$actual2nd]
            );
            $this->assertEach(
                fn (DwsProvisionReport $a, DwsProvisionReport $b): bool => $a === $b,
                [...$this->reports],
                [...$previousActual2nd]
            );
        });
        $this->should(
            'create a Seq of DwsBillingBundle for each months',
            function (Carbon $providedIn, Seq $bundles): void {
                $this->createDwsBillingBundleListUseCase
                    ->expects('handle')
                    ->with(
                        $this->context,
                        $this->office,
                        $this->billing,
                        equalTo($providedIn),
                        Mockery::capture($actual)
                    )
                    ->andReturn($bundles);

                $this->interactor->handle($this->context, $this->office->id, $this->transactedIn, $this->fixedAt);

                $this->assertForAll(
                    $actual,
                    fn (DwsBillingSource $x): bool => $x->provisionReport->providedIn->eq($providedIn)
                );
            },
            [
                'examples' => [
                    [Carbon::create(2021, 1), $this->bundles->take(2)],
                    [Carbon::create(2020, 12), $this->bundles->drop(2)],
                ],
            ]
        );
        $this->should(
            'create a Seq of DwsBillingStatement for each DwsBillingBundles',
            function (DwsBillingBundle $bundle, Seq $statements): void {
                $this->createDwsBillingStatementListUseCase
                    ->expects('handle')
                    ->with($this->context, $this->office, $bundle)
                    ->andReturn($statements);

                $this->interactor->handle($this->context, $this->office->id, $this->transactedIn, $this->fixedAt);
            },
            [
                'examples' => [
                    [$this->bundles[0], $this->statements->take(2)],
                    [$this->bundles[1], $this->statements->drop(2)],
                    [$this->bundles[2], $this->statements->take(2)],
                ],
            ]
        );
        $this->should(
            'create DwsBillingInvoice for each DwsBillingBundles',
            function (DwsBillingBundle $bundle, Seq $statements): void {
                $this->createDwsBillingInvoiceUseCase
                    ->expects('handle')
                    ->with($this->context, $bundle, Mockery::capture($actualStatements))
                    ->andReturn(DwsBillingInvoice::create([]));

                $this->interactor->handle($this->context, $this->office->id, $this->transactedIn, $this->fixedAt);

                $this->assertEach(
                    function (DwsBillingStatement $expected, DwsBillingStatement $actual): void {
                        $this->assertModelStrictEquals($expected, $actual);
                    },
                    $statements->toArray(),
                    $actualStatements->toArray()
                );
            },
            [
                'examples' => [
                    [$this->bundles[0], $this->statements->take(2)],
                    [$this->bundles[1], $this->statements->drop(2)],
                    [$this->bundles[2], $this->statements->take(2)],
                ],
            ]
        );
        $this->should(
            'create DwsBillingServiceReports for each DwsBillingBundles',
            function (DwsBillingBundle $bundle, Seq $reports): void {
                $this->createDwsBillingServiceReportListUseCase
                    ->expects('handle')
                    ->with(
                        $this->context,
                        $bundle,
                        Mockery::capture($actualReport),
                        equalTo($this->reports)
                    )
                    ->andReturn($this->serviceReports);

                $this->interactor->handle($this->context, $this->office->id, $this->transactedIn, $this->fixedAt);

                $this->assertEach(
                    function (DwsProvisionReport $expected, DwsProvisionReport $actual): void {
                        $this->assertModelStrictEquals($expected, $actual);
                    },
                    $reports->toArray(),
                    $actualReport->toArray()
                );
            },
            [
                'examples' => [
                    [$this->bundles[0], $this->reports->take(2)],
                    [$this->bundles[1], $this->reports->drop(2)->take(1)],
                    [$this->bundles[2], $this->reports->drop(3)],
                ],
            ]
        );
        $this->should('return a DwsBilling', function (): void {
            $this->dwsBillingRepository
                ->expects('store')
                ->andReturnUsing(fn (DwsBilling $x): DwsBilling => $x->copy(['id' => 20080517]));

            $actual = $this->interactor->handle($this->context, $this->office->id, $this->transactedIn, $this->fixedAt);

            $this->assertMatchesModelSnapshot($actual);
        });
        $this->should('throw NotFoundException when the office is not found', function (): void {
            $this->lookupOfficeUseCase->expects('handle')->andReturn(Seq::emptySeq());

            $this->assertThrows(NotFoundException::class, function (): void {
                $this->interactor->handle($this->context, $this->office->id, $this->transactedIn, $this->fixedAt);
            });
        });
        $this->should('throw NotFoundException when DwsProvisionReports are not found', function (): void {
            $this->dwsProvisionReportFinder
                ->expects('find')
                ->andReturn(FinderResult::from([], Pagination::create([])));

            $this->assertThrows(NotFoundException::class, function (): void {
                $this->interactor->handle($this->context, $this->office->id, $this->transactedIn, $this->fixedAt);
            });
        });
        $this->should('throw Exception when LookupOfficeUseCase throws it', function (): void {
            $this->lookupOfficeUseCase->expects('handle')->andThrow(new Exception('Some error'));

            $this->assertThrows(Exception::class, function (): void {
                $this->interactor->handle($this->context, $this->office->id, $this->transactedIn, $this->fixedAt);
            });
        });
        $this->should('throw Exception when DwsBillingRepository throws it', function (): void {
            $this->dwsBillingRepository->expects('store')->andThrow(new Exception('Some error'));

            $this->assertThrows(Exception::class, function (): void {
                $this->interactor->handle($this->context, $this->office->id, $this->transactedIn, $this->fixedAt);
            });
        });
        $this->should('throw Exception when DwsProvisionReportFinder throws it', function (): void {
            $this->dwsProvisionReportFinder->expects('find')->andThrow(new Exception('Some error'));

            $this->assertThrows(Exception::class, function (): void {
                $this->interactor->handle($this->context, $this->office->id, $this->transactedIn, $this->fixedAt);
            });
        });
        $this->should('throw Exception when CreateDwsBillingBundleListUseCase throws it', function (): void {
            $this->createDwsBillingBundleListUseCase->expects('handle')->andThrow(new Exception('Some error'));

            $this->assertThrows(Exception::class, function (): void {
                $this->interactor->handle($this->context, $this->office->id, $this->transactedIn, $this->fixedAt);
            });
        });
        $this->should('throw Exception when CreateDwsBillingStatementListUseCase throws it', function (): void {
            $this->createDwsBillingStatementListUseCase->expects('handle')->andThrow(new Exception('Some error'));

            $this->assertThrows(Exception::class, function (): void {
                $this->interactor->handle($this->context, $this->office->id, $this->transactedIn, $this->fixedAt);
            });
        });
        $this->should('throw Exception when CreateDwsBillingInvoiceUseCase throws it', function (): void {
            $this->createDwsBillingInvoiceUseCase->expects('handle')->andThrow(new Exception('Some error'));

            $this->assertThrows(Exception::class, function (): void {
                $this->interactor->handle($this->context, $this->office->id, $this->transactedIn, $this->fixedAt);
            });
        });
    }
}

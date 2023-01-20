<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\Billing;

use Domain\Billing\DwsBillingBundle;
use Domain\Billing\DwsBillingCopayCoordination;
use Domain\Billing\DwsBillingInvoice;
use Domain\Billing\DwsBillingSource;
use Domain\Billing\DwsBillingStatement;
use Domain\Billing\DwsBillingStatementCopayCoordinationStatus;
use Domain\Common\Carbon;
use Domain\Common\Pagination;
use Domain\DwsCertification\CopayCoordination;
use Domain\DwsCertification\CopayCoordinationType;
use Domain\FinderResult;
use Domain\Permission\Permission;
use Domain\ProvisionReport\DwsProvisionReportStatus;
use Lib\Exceptions\LogicException;
use Lib\Exceptions\NotFoundException;
use Mockery;
use ScalikePHP\Option;
use ScalikePHP\Seq;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\BuildDwsBillingServiceDetailListUseCaseMixin;
use Tests\Unit\Mixins\BuildDwsBillingSourceListUseCaseMixin;
use Tests\Unit\Mixins\BuildDwsBillingStatementUseCaseMixin;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\CreateDwsBillingInvoiceUseCaseMixin;
use Tests\Unit\Mixins\DwsBillingBundleFinderMixin;
use Tests\Unit\Mixins\DwsBillingBundleRepositoryMixin;
use Tests\Unit\Mixins\DwsBillingCopayCoordinationFinderMixin;
use Tests\Unit\Mixins\DwsBillingInvoiceFinderMixin;
use Tests\Unit\Mixins\DwsBillingInvoiceRepositoryMixin;
use Tests\Unit\Mixins\DwsBillingServiceReportFinderMixin;
use Tests\Unit\Mixins\DwsBillingStatementRepositoryMixin;
use Tests\Unit\Mixins\DwsProvisionReportFinderMixin;
use Tests\Unit\Mixins\IdentifyDwsCertificationUseCaseMixin;
use Tests\Unit\Mixins\IdentifyHomeHelpServiceCalcSpecUseCaseMixin;
use Tests\Unit\Mixins\IdentifyVisitingCareForPwsdCalcSpecUseCaseMixin;
use Tests\Unit\Mixins\LoggerMixin;
use Tests\Unit\Mixins\LookupDwsBillingBundleUseCaseMixin;
use Tests\Unit\Mixins\LookupDwsBillingUseCaseMixin;
use Tests\Unit\Mixins\LookupOfficeUseCaseMixin;
use Tests\Unit\Mixins\LookupUserUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\RefreshDwsBillingCopayCoordinationUseCaseMixin;
use Tests\Unit\Mixins\RefreshDwsBillingServiceReportUseCaseMixin;
use Tests\Unit\Mixins\SimpleLookupDwsBillingStatementUseCaseMixin;
use Tests\Unit\Mixins\TransactionManagerMixin;
use Tests\Unit\Mixins\UpdateDwsBillingInvoiceUseCaseMixin;
use Tests\Unit\Test;
use UseCase\Billing\RefreshDwsBillingStatementInteractor;

/**
 * {@link \UseCase\Billing\RefreshDwsBillingStatementInteractor} のテスト.
 */
final class RefreshDwsBillingStatementInteractorTest extends Test
{
    use BuildDwsBillingStatementUseCaseMixin;
    use BuildDwsBillingSourceListUseCaseMixin;
    use BuildDwsBillingServiceDetailListUseCaseMixin;
    use CarbonMixin;
    use CreateDwsBillingInvoiceUseCaseMixin;
    use ContextMixin;
    use DwsBillingBundleRepositoryMixin;
    use DwsBillingCopayCoordinationFinderMixin;
    use DwsBillingBundleFinderMixin;
    use DwsBillingInvoiceFinderMixin;
    use DwsBillingInvoiceRepositoryMixin;
    use DwsBillingServiceReportFinderMixin;
    use DwsBillingStatementRepositoryMixin;
    use DwsProvisionReportFinderMixin;
    use IdentifyHomeHelpServiceCalcSpecUseCaseMixin;
    use IdentifyVisitingCareForPwsdCalcSpecUseCaseMixin;
    use IdentifyDwsCertificationUseCaseMixin;
    use LookupDwsBillingBundleUseCaseMixin;
    use LookupDwsBillingUseCaseMixin;
    use LookupOfficeUseCaseMixin;
    use LoggerMixin;
    use LookupUserUseCaseMixin;
    use UpdateDwsBillingInvoiceUseCaseMixin;
    use SimpleLookupDwsBillingStatementUseCaseMixin;
    use RefreshDwsBillingServiceReportUseCaseMixin;
    use RefreshDwsBillingCopayCoordinationUseCaseMixin;
    use TransactionManagerMixin;
    use MatchesSnapshots;
    use ExamplesConsumer;
    use MockeryMixin;
    use UnitSupport;

    private Seq $statements;
    private array $serviceDetails;
    private DwsBillingBundle $newBundle;
    private RefreshDwsBillingStatementInteractor $interactor;

    /**
     * 初期化処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachSpec(function (self $self): void {
            $self->statements = Seq::from($self->examples->dwsBillingStatements[0]);
            $self->serviceDetails = [
                'cityCode' => '141421',
                'cityName' => '米花市',
                'details' => [$self->examples->dwsBillingServiceDetails[0]],
            ];
            $self->newBundle = DwsBillingBundle::create([
                'id' => 1,
                'dwsBillingId' => $self->examples->dwsBillings[0]->id,
                'providedIn' => $self->examples->dwsBillingBundles[0]->providedIn,
                'cityCode' => 141421,
                'cityName' => '米花市',
                'details' => [],
                'createdAt' => Carbon::now(),
                'updatedAt' => Carbon::now(),
            ]);
            $self->buildDwsBillingStatementUseCase
                ->allows('handle')
                ->andReturn($self->examples->dwsBillingStatements[0])
                ->byDefault();
            $self->buildDwsBillingSourceListUseCase
                ->allows('handle')
                ->andReturn(Seq::from(
                    DwsBillingSource::create([
                        'certification' => $self->examples->dwsCertifications[0],
                        'provisionReport' => $self->examples->dwsBillingServiceReports[0],
                    ])
                ))
                ->byDefault();
            $self->buildDwsBillingServiceDetailListUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->serviceDetails))
                ->byDefault();
            $self->createDwsBillingInvoiceUseCase
                ->allows('handle')
                ->andReturn($self->examples->dwsBillingInvoices[0])
                ->byDefault();
            $self->dwsBillingBundleFinder
                ->allows('find')
                ->andReturn(FinderResult::from([$self->examples->dwsBillingBundles[0]], Pagination::create()))
                ->byDefault();
            $self->dwsBillingBundleRepository
                ->allows('store')
                ->andReturnUsing(fn (DwsBillingBundle $x): DwsBillingBundle => $x->copy(['id' => 1]))
                ->byDefault();
            $self->dwsBillingBundleRepository
                ->allows('remove')
                ->andReturnNull()
                ->byDefault();
            $self->dwsBillingCopayCoordinationFinder
                ->allows('find')
                ->andReturn(FinderResult::from(Seq::from($self->examples->dwsBillingCopayCoordinations[0]), Pagination::create()))
                ->byDefault();
            $self->dwsBillingInvoiceFinder
                ->allows('find')
                ->andReturn(FinderResult::from(Seq::from($self->examples->dwsBillingInvoices[0]), Pagination::create()))
                ->byDefault();
            $self->dwsBillingInvoiceRepository
                ->allows('store')
                ->andReturn($self->examples->dwsBillingInvoices[0])
                ->byDefault();
            $self->dwsBillingInvoiceRepository
                ->allows('remove')
                ->andReturnNull()
                ->byDefault();
            $self->dwsBillingServiceReportFinder
                ->allows('find')
                ->andReturn(FinderResult::from(Seq::from($self->examples->dwsBillingServiceReports[0]), Pagination::create()))
                ->byDefault();
            $self->dwsBillingStatementRepository
                ->allows('store')
                ->andReturnUsing(fn (DwsBillingStatement $x): DwsBillingStatement => $x->copy(['id' => 1]))
                ->byDefault();
            $self->dwsProvisionReportFinder
                ->allows('find')
                ->andReturn(FinderResult::from(Seq::from($self->examples->dwsProvisionReports[0]), Pagination::create()))
                ->byDefault();
            $self->identifyHomeHelpServiceCalcSpecUseCase
                ->allows('handle')
                ->andReturn(Option::from($self->examples->homeHelpServiceCalcSpecs[0]))
                ->byDefault();
            $self->identifyVisitingCareForPwsdCalcSpecUseCase
                ->allows('handle')
                ->andReturn(Option::from($self->examples->visitingCareForPwsdCalcSpecs[0]))
                ->byDefault();
            $self->identifyDwsCertificationUseCase
                ->allows('handle')
                ->andReturn(Option::from($self->examples->dwsCertifications[0]))
                ->byDefault();
            $self->lookupDwsBillingUseCase
                ->allows('handle')
                ->andReturn(Seq::from(
                    $self->examples->dwsBillings[0]
                ))
                ->byDefault();
            $self->lookupOfficeUseCase
                ->allows('handle')
                ->andReturn(Seq::from(
                    $self->examples->offices[0]
                ))
                ->byDefault();
            $self->lookupUserUseCase
                ->allows('handle')
                ->andReturn(Seq::from(
                    $self->examples->users[0]
                ))
                ->byDefault();
            $self->updateDwsBillingInvoiceUseCase
                ->allows('handle')
                ->andReturn($self->examples->dwsBillingInvoices[0])
                ->byDefault();
            $self->simpleLookupDwsBillingStatementUseCase
                ->allows('handle')
                ->andReturn($self->statements)
                ->byDefault();
            $self->refreshDwsBillingServiceReportUseCase
                ->allows('handle')
                ->andReturnNull()
                ->byDefault();
            $self->refreshDwsBillingCopayCoordinationUseCase
                ->allows('handle')
                ->andReturnNull()
                ->byDefault();
            $self->logger
                ->allows('info')
                ->byDefault();

            $self->interactor = app(RefreshDwsBillingStatementInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('run in transaction', function (): void {
            $this->transactionManager
                ->expects('run')
                ->andReturn([
                    Seq::from($this->examples->dwsBillingInvoices[0]),
                    Seq::from($this->examples->dwsBillingInvoices[1]),
                    Seq::from($this->examples->dwsBillingInvoices[2]),
                ]);
            $this->buildDwsBillingServiceDetailListUseCase->expects('find')->never();
            $this->buildDwsBillingStatementUseCase->expects('handle')->never();
            $this->refreshDwsBillingServiceReportUseCase->expects('handle')->never();
            $this->identifyDwsCertificationUseCase->expects('handle')->never();
            $this->refreshDwsBillingCopayCoordinationUseCase->expects('handle')->never();
            $this->dwsBillingStatementRepository->expects('store')->never();

            $this->interactor->handle(
                $this->context,
                $this->examples->dwsBillings[0]->id,
                $this->statements->map(fn (DwsBillingStatement $x): int => $x->id)->toArray()
            );
        });
        $this->should('use BuildDwsBillingStatementUseCase', function (): void {
            $bundle = $this->examples->dwsBillingBundles[0]->copy([
                'cityCode' => 141421,
                'details' => [],
            ]);
            $dwsCertification = $this->examples->dwsCertifications[0]->copy([
                'copayCoordination' => CopayCoordination::create([
                    'copayCoordinationType' => CopayCoordinationType::internal(),
                    'officeId' => $this->examples->offices[0]->id,
                ]),
            ]);

            $this->identifyDwsCertificationUseCase
                ->allows('handle')
                ->andReturn(Option::from($dwsCertification));

            $this->dwsBillingBundleFinder
                ->allows('find')
                ->andReturn(FinderResult::from([$bundle], Pagination::create()));

            $this->buildDwsBillingStatementUseCase
                ->expects('handle')
                ->with(
                    $this->context,
                    $this->examples->offices[0],
                    Mockery::capture($actualBundle),
                    $this->examples->homeHelpServiceCalcSpecs[0],
                    $this->examples->visitingCareForPwsdCalcSpecs[0],
                    $this->examples->users[0],
                    Mockery::capture($actual),
                    Option::none(),
                    Option::none()
                )
                ->andReturn($this->examples->dwsBillingStatements[0]);

            $this->interactor->handle(
                $this->context,
                $this->examples->dwsBillings[0]->id,
                $this->statements->map(fn (DwsBillingStatement $x): int => $x->id)->toArray()
            );
            $this->assertModelStrictEquals($bundle, $actualBundle);
            $this->assertArrayStrictEquals($this->serviceDetails['details'], $actual->toArray());
        });
        $this->should('use BuildDwsBillingSourceListUseCase', function (): void {
            $previousProvidedIn = $this->examples->dwsBillingBundles[0]->providedIn->subMonth();
            $previousProvisionReport = $this->examples->dwsProvisionReports[0]->copy([
                'providedIn' => $previousProvidedIn,
            ]);
            $this->dwsProvisionReportFinder
                ->expects('find')
                ->with(
                    equalTo([
                        'officeId' => $this->examples->offices[0]->id,
                        'userIds' => $this->statements
                            ->map(fn (DwsBillingStatement $x): int => $x->user->userId)
                            ->toArray(),
                        'providedIn' => $previousProvidedIn,
                        'status' => DwsProvisionReportStatus::fixed(),
                    ]),
                    Mockery::any()
                )
                ->andReturn(FinderResult::from(Seq::from($previousProvisionReport), Pagination::create()));
            $this->buildDwsBillingSourceListUseCase
                ->expects('handle')
                ->with(
                    $this->context,
                    Mockery::capture($actual),
                    equalTo(Seq::from($previousProvisionReport))
                )
                ->andReturn(Seq::from(
                    DwsBillingSource::create([
                        'certification' => $this->examples->dwsCertifications[0],
                        'provisionReport' => $this->examples->dwsBillingServiceReports[0],
                    ])
                ));

            $this->interactor->handle(
                $this->context,
                $this->examples->dwsBillings[0]->id,
                $this->statements->map(fn (DwsBillingStatement $x): int => $x->id)->toArray()
            );
            $this->assertArrayStrictEquals([$this->examples->dwsProvisionReports[0]], $actual->toArray());
        });
        $this->should('use store on DwsBillingBundleRepository', function (): void {
            $expects = $this->examples->dwsBillingBundles[0]->copy([
                'id' => 1,
                'cityCode' => 141421,
                'details' => $this->serviceDetails['details'],
                'updatedAt' => Carbon::now(),
            ]);
            $this->dwsBillingBundleFinder
                ->expects('find')
                ->andReturn(FinderResult::from(
                    [$this->examples->dwsBillingBundles[0]->copy(['cityCode' => 141421])],
                    Pagination::create()
                ));
            $this->dwsBillingBundleRepository
                ->expects('store')
                ->with(Mockery::capture($actual))
                ->andReturnUsing(fn (DwsBillingBundle $x): DwsBillingBundle => $x->copy(['id' => 1]))
                ->twice();

            $this->interactor->handle(
                $this->context,
                $this->examples->dwsBillings[0]->id,
                $this->statements->map(fn (DwsBillingStatement $x): int => $x->id)->toArray()
            );
            $this->assertEquals($expects, $actual);
        });
        $this->should('use store on DwsBillingBundleRepository three times if a new bundle is created', function (): void {
            $expects = $this->newBundle->copy([
                'details' => $this->serviceDetails['details'],
                'updatedAt' => Carbon::now(),
            ]);
            $this->dwsBillingBundleRepository
                ->expects('store')
                ->with(Mockery::capture($actual))
                ->andReturnUsing(fn (DwsBillingBundle $x): DwsBillingBundle => $x->copy(['id' => 1]))
                ->times(3);

            $this->interactor->handle(
                $this->context,
                $this->examples->dwsBillings[0]->id,
                $this->statements->map(fn (DwsBillingStatement $x): int => $x->id)->toArray()
            );
            $this->assertEquals($expects, $actual);
        });
        $this->should('use DwsBillingCopayCoordinationFinder', function (): void {
            $filterParams = [
                'dwsBillingBundleIds' => [$this->examples->dwsBillingBundles[0]->id],
                'userIds' => [$this->examples->users[0]->id],
            ];
            $paginationParams = [
                'all' => true,
                'sortBy' => 'id',
            ];
            $this->dwsBillingCopayCoordinationFinder
                ->expects('find')
                ->with($filterParams, $paginationParams)
                ->andReturn(FinderResult::from(
                    Seq::from($this->examples->dwsBillingCopayCoordinations[0]),
                    Pagination::create()
                ));

            $this->interactor->handle(
                $this->context,
                $this->examples->dwsBillings[0]->id,
                $this->statements->map(fn (DwsBillingStatement $x): int => $x->id)->toArray()
            );
        });
        $this->should('use DwsBillingServiceReportFinder', function (): void {
            $filterParams = [
                'dwsBillingBundleIds' => [$this->examples->dwsBillingBundles[0]->id],
                'userIds' => [$this->examples->users[0]->id],
            ];
            $paginationParams = [
                'all' => true,
                'sortBy' => 'id',
            ];
            $this->dwsBillingServiceReportFinder
                ->expects('find')
                ->with($filterParams, $paginationParams)
                ->andReturn(FinderResult::from(
                    Seq::from($this->examples->dwsBillingServiceReports[0]),
                    Pagination::create()
                ));

            $this->interactor->handle(
                $this->context,
                $this->examples->dwsBillings[0]->id,
                $this->statements->map(fn (DwsBillingStatement $x): int => $x->id)->toArray()
            );
        });
        $this->should('use DwsBillingStatementRepository case uncreated', function (): void {
            $copayCoordination = $this->examples->dwsCertifications[0]->copayCoordination->copy([
                'copayCoordinationType' => CopayCoordinationType::internal(),
                'officeId' => $this->examples->offices[0]->id,
            ]);
            $certification = $this->examples->dwsCertifications[0]->copy([
                'copayCoordination' => $copayCoordination,
            ]);
            $this->identifyDwsCertificationUseCase
                ->allows('handle')
                ->andReturn(Option::from($certification));
            $this->dwsBillingCopayCoordinationFinder
                ->allows('find')
                ->andReturn(FinderResult::from(Seq::empty(), Pagination::create()));
            $expects = $this->examples->dwsBillingStatements[0]->copy([
                'id' => $this->examples->dwsBillingStatements[0]->id,
                'copayCoordinationStatus' => DwsBillingStatementCopayCoordinationStatus::uncreated(),
                'createdAt' => $this->examples->dwsBillingStatements[0]->createdAt,
            ]);
            $this->dwsBillingStatementRepository
                ->expects('store')
                ->with(Mockery::capture($actual))
                ->andReturnUsing(fn (DwsBillingStatement $x): DwsBillingStatement => $x->copy(['id' => 1]));

            $this->interactor->handle(
                $this->context,
                $this->examples->dwsBillings[0]->id,
                $this->statements->map(fn (DwsBillingStatement $x): int => $x->id)->toArray()
            );
            $this->assertModelStrictEquals($expects, $actual);
        });
        $this->should('use DwsBillingStatementRepository case checking', function (): void {
            $copayCoordination = $this->examples->dwsCertifications[0]->copayCoordination->copy([
                'copayCoordinationType' => CopayCoordinationType::internal(),
                'officeId' => $this->examples->offices[0]->id,
            ]);
            $certification = $this->examples->dwsCertifications[0]->copy([
                'copayCoordination' => $copayCoordination,
            ]);
            $this->identifyDwsCertificationUseCase
                ->allows('handle')
                ->andReturn(Option::from($certification));
            $expects = $this->examples->dwsBillingStatements[0]->copy([
                'id' => $this->examples->dwsBillingStatements[0]->id,
                'copayCoordinationStatus' => DwsBillingStatementCopayCoordinationStatus::uncreated(),
                'createdAt' => $this->examples->dwsBillingStatements[0]->createdAt,
            ]);
            $this->dwsBillingStatementRepository
                ->expects('store')
                ->with(Mockery::capture($actual))
                ->andReturnUsing(fn (DwsBillingStatement $x): DwsBillingStatement => $x->copy(['id' => 1]));

            $this->interactor->handle(
                $this->context,
                $this->examples->dwsBillings[0]->id,
                $this->statements->map(fn (DwsBillingStatement $x): int => $x->id)->toArray()
            );
            $this->assertModelStrictEquals($expects, $actual);
        });
        $this->should('use DwsBillingStatementRepository case no change', function (): void {
            $copayCoordination = $this->examples->dwsCertifications[0]->copayCoordination->copy([
                'copayCoordinationType' => CopayCoordinationType::external(),
                'officeId' => $this->examples->offices[1]->id,
            ]);
            $certification = $this->examples->dwsCertifications[0]->copy([
                'copayCoordination' => $copayCoordination,
            ]);
            $this->identifyDwsCertificationUseCase
                ->allows('handle')
                ->andReturn(Option::from($certification));
            $expects = $this->examples->dwsBillingStatements[0]->copy([
                'id' => $this->examples->dwsBillingStatements[0]->id,
                'copayCoordinationStatus' => $this->examples->dwsBillingStatements[0]->copayCoordinationStatus,
                'createdAt' => $this->examples->dwsBillingStatements[0]->createdAt,
            ]);
            $this->dwsBillingStatementRepository
                ->expects('store')
                ->with(Mockery::capture($actual))
                ->andReturnUsing(fn (DwsBillingStatement $x): DwsBillingStatement => $x->copy(['id' => 1]));

            $this->interactor->handle(
                $this->context,
                $this->examples->dwsBillings[0]->id,
                $this->statements->map(fn (DwsBillingStatement $x): int => $x->id)->toArray()
            );
            $this->assertModelStrictEquals($expects, $actual);
        });
        $this->should('use DwsProvisionReportFinder', function (): void {
            $filterParams = [
                'officeId' => $this->examples->offices[0]->id,
                'userIds' => [$this->examples->users[0]->id],
                'providedIn' => $this->examples->dwsBillingBundles[0]->providedIn,
                'status' => DwsProvisionReportStatus::fixed(),
            ];
            $paginationParams = [
                'all' => true,
                'sortBy' => 'id',
            ];
            $this->dwsProvisionReportFinder
                ->expects('find')
                ->with($filterParams, $paginationParams)
                ->andReturn(FinderResult::from(
                    Seq::from($this->examples->dwsProvisionReports[0]),
                    Pagination::create()
                ));

            $this->interactor->handle(
                $this->context,
                $this->examples->dwsBillings[0]->id,
                $this->statements->map(fn (DwsBillingStatement $x): int => $x->id)->toArray()
            );
        });
        $this->should('use IdentifyHomeHelpServiceCalcSpecUseCase', function (): void {
            $this->identifyHomeHelpServiceCalcSpecUseCase
                ->expects('handle')
                ->with($this->context, $this->examples->offices[0], $this->examples->dwsBillingBundles[0]->providedIn)
                ->andReturn(Option::from($this->examples->homeHelpServiceCalcSpecs[0]));

            $this->interactor->handle(
                $this->context,
                $this->examples->dwsBillings[0]->id,
                $this->statements->map(fn (DwsBillingStatement $x): int => $x->id)->toArray()
            );
        });
        $this->should('use IdentifyVisitingCareForPwsdCalcSpecUseCase', function (): void {
            $this->identifyVisitingCareForPwsdCalcSpecUseCase
                ->expects('handle')
                ->with($this->context, $this->examples->offices[0], $this->examples->dwsBillingBundles[0]->providedIn)
                ->andReturn(Option::from($this->examples->visitingCareForPwsdCalcSpecs[0]));

            $this->interactor->handle(
                $this->context,
                $this->examples->dwsBillings[0]->id,
                $this->statements->map(fn (DwsBillingStatement $x): int => $x->id)->toArray()
            );
        });
        $this->should('use IdentifyDwsCertificationUseCase', function (): void {
            $this->identifyDwsCertificationUseCase
                ->expects('handle')
                ->with($this->context, $this->examples->users[0]->id, $this->examples->dwsBillingBundles[0]->providedIn)
                ->andReturn(Option::from($this->examples->dwsCertifications[0]));

            $this->interactor->handle(
                $this->context,
                $this->examples->dwsBillings[0]->id,
                $this->statements->map(fn (DwsBillingStatement $x): int => $x->id)->toArray()
            );
        });
        $this->should('use LookupDwsBillingUseCase', function (): void {
            $this->lookupDwsBillingUseCase
                ->expects('handle')
                ->with($this->context, Permission::updateBillings(), $this->examples->dwsBillings[0]->id)
                ->andReturn(Seq::from(
                    $this->examples->dwsBillings[0]
                ));
            $this->interactor->handle(
                $this->context,
                $this->examples->dwsBillings[0]->id,
                $this->statements->map(fn (DwsBillingStatement $x): int => $x->id)->toArray()
            );
        });
        $this->should('use LookupOfficeUseCase', function (): void {
            $this->lookupOfficeUseCase
                ->expects('handle')
                ->with($this->context, [Permission::updateBillings()], $this->examples->offices[0]->id)
                ->andReturn(Seq::from(
                    $this->examples->offices[0]
                ));
            $this->interactor->handle(
                $this->context,
                $this->examples->dwsBillings[0]->id,
                $this->statements->map(fn (DwsBillingStatement $x): int => $x->id)->toArray()
            );
        });
        $this->should('use LookupUserUseCase', function (): void {
            $this->lookupUserUseCase
                ->expects('handle')
                ->with($this->context, Permission::updateBillings(), $this->examples->users[0]->id)
                ->andReturn(Seq::from(
                    $this->examples->users[0]
                ));
            $this->interactor->handle(
                $this->context,
                $this->examples->dwsBillings[0]->id,
                $this->statements->map(fn (DwsBillingStatement $x): int => $x->id)->toArray()
            );
        });
        $this->should('use UpdateDwsBillingInvoiceUseCase', function (): void {
            $this->updateDwsBillingInvoiceUseCase
                ->expects('handle')
                ->with($this->context, $this->examples->dwsBillings[0]->id, $this->examples->dwsBillingBundles[0]->id)
                ->andReturn($this->examples->dwsBillingInvoices[0])
                ->twice();
            $this->interactor->handle(
                $this->context,
                $this->examples->dwsBillings[0]->id,
                $this->statements->map(fn (DwsBillingStatement $x): int => $x->id)->toArray()
            );
        });
        $this->should('use CreateDwsBillingInvoiceUseCase', function (): void {
            $expectBundle = $this->newBundle->copy([
                'details' => $this->serviceDetails['details'],
                'updatedAt' => Carbon::now(),
            ]);
            $this->createDwsBillingInvoiceUseCase
                ->expects('handle')
                ->with($this->context, Mockery::capture($actualBundle), Mockery::capture($actualStatements))
                ->andReturn($this->examples->dwsBillingInvoices[0]);
            $this->interactor->handle(
                $this->context,
                $this->examples->dwsBillings[0]->id,
                $this->statements->map(fn (DwsBillingStatement $x): int => $x->id)->toArray()
            );
            $this->assertModelStrictEquals($expectBundle, $actualBundle);
            $this->assertArrayStrictEquals([$this->examples->dwsBillingStatements[0]], $actualStatements->toArray());
        });
        $this->should('use SimpleLookupDwsBillingStatementUseCase', function (): void {
            $this->simpleLookupDwsBillingStatementUseCase
                ->expects('handle')
                ->with($this->context, Permission::updateBillings(), ...$this->statements->map(fn (DwsBillingStatement $x): int => $x->id)->toArray())
                ->andReturn($this->statements);
            $this->interactor->handle(
                $this->context,
                $this->examples->dwsBillings[0]->id,
                $this->statements->map(fn (DwsBillingStatement $x): int => $x->id)->toArray()
            );
        });
        $this->should('use RefreshDwsBillingServiceReportUseCase', function (): void {
            $previousProvidedIn = $this->examples->dwsBillingBundles[0]->providedIn->subMonth();
            $previousProvisionReport = $this->examples->dwsProvisionReports[0]->copy([
                'providedIn' => $previousProvidedIn,
            ]);
            $this->dwsProvisionReportFinder
                ->expects('find')
                ->with(
                    equalTo([
                        'officeId' => $this->examples->offices[0]->id,
                        'userIds' => $this->statements
                            ->map(fn (DwsBillingStatement $x): int => $x->user->userId)
                            ->toArray(),
                        'providedIn' => $previousProvidedIn,
                        'status' => DwsProvisionReportStatus::fixed(),
                    ]),
                    Mockery::any()
                )
                ->andReturn(FinderResult::from(Seq::from($previousProvisionReport), Pagination::create()));
            $this->refreshDwsBillingServiceReportUseCase
                ->expects('handle')
                ->with(
                    $this->context,
                    Mockery::capture($actual),
                    equalTo(Seq::from($this->examples->dwsProvisionReports[0])),
                    equalTo(Seq::from($this->examples->dwsBillingServiceReports[0])),
                    equalTo(Seq::from($previousProvisionReport)),
                )
                ->andReturnNull();
            $this->interactor->handle(
                $this->context,
                $this->examples->dwsBillings[0]->id,
                $this->statements->map(fn (DwsBillingStatement $x): int => $x->id)->toArray()
            );
            $this->assertEquals($this->newBundle, $actual);
        });
        $this->should('use RefreshDwsBillingCopayCoordinationUseCase', function (): void {
            $copayCoordination = Seq::from($this->examples->dwsBillingCopayCoordinations[0])
                ->toMap(fn (DwsBillingCopayCoordination $x): int => $x->user->userId)
                ->get($this->examples->dwsBillingStatements[0]->user->userId);
            $this->refreshDwsBillingCopayCoordinationUseCase
                ->expects('handle')
                ->with($this->context, $this->examples->dwsBillingStatements[0], $copayCoordination, $this->examples->dwsCertifications[0], $this->examples->offices[0])
                ->andReturnNull();
            $this->interactor->handle(
                $this->context,
                $this->examples->dwsBillings[0]->id,
                $this->statements->map(fn (DwsBillingStatement $x): int => $x->id)->toArray()
            );
        });
        $this->should('use remove on DwsBillingBundleRepository', function (): void {
            $bundle = $this->examples->dwsBillingBundles[0]->copy([
                'details' => [],
            ]);
            $this->dwsBillingBundleRepository
                ->expects('remove')
                ->with(equalTo($bundle))
                ->andReturnNull();
            $this->interactor->handle(
                $this->context,
                $this->examples->dwsBillings[0]->id,
                $this->statements->map(fn (DwsBillingStatement $x): int => $x->id)->toArray()
            );
        });
        $this->should('throw LogicException when DwsBillingBundle contains multiple providedIn', function (): void {
            $this->dwsBillingBundleFinder
                ->expects('find')
                ->andReturn(FinderResult::from(
                    [
                        $this->examples->dwsBillingBundles[0],
                        $this->examples->dwsBillingBundles[0]->copy(['providedIn' => Carbon::now()->addMonth()]),
                    ],
                    Pagination::create()
                ));

            $this->assertThrows(LogicException::class, function (): void {
                $this->interactor->handle(
                    $this->context,
                    $this->examples->dwsBillings[0]->id,
                    $this->statements->map(fn (DwsBillingStatement $x): int => $x->id)->toArray()
                );
            });
        });
        $this->should('throw LogicException when statement user not Found', function (): void {
            $this->lookupUserUseCase
                ->allows('handle')
                ->andReturn(Seq::from(
                    $this->examples->users[1]
                ));

            $this->assertThrows(LogicException::class, function (): void {
                $this->interactor->handle(
                    $this->context,
                    $this->examples->dwsBillings[0]->id,
                    $this->statements->map(fn (DwsBillingStatement $x): int => $x->id)->toArray()
                );
            });
        });
        $this->should('throw NotFoundException when simpleLookupDwsBillingStatementUseCase return empty', function (): void {
            $this->simpleLookupDwsBillingStatementUseCase
                ->allows('handle')
                ->andReturn(Seq::empty());

            $this->assertThrows(NotFoundException::class, function (): void {
                $this->interactor->handle(
                    $this->context,
                    $this->examples->dwsBillings[0]->id,
                    $this->statements->map(fn (DwsBillingStatement $x): int => $x->id)->toArray()
                );
            });
        });
        $this->should('throw NotFoundException when lookupOfficeUseCase return empty', function (): void {
            $this->lookupOfficeUseCase
                ->allows('handle')
                ->andReturn(Seq::empty());

            $this->assertThrows(NotFoundException::class, function (): void {
                $this->interactor->handle(
                    $this->context,
                    $this->examples->dwsBillings[0]->id,
                    $this->statements->map(fn (DwsBillingStatement $x): int => $x->id)->toArray()
                );
            });
        });
        $this->should('throw NotFoundException when lookupDwsBillingUseCase return empty', function (): void {
            $this->lookupDwsBillingUseCase
                ->allows('handle')
                ->andReturn(Seq::empty());

            $this->assertThrows(NotFoundException::class, function (): void {
                $this->interactor->handle(
                    $this->context,
                    $this->examples->dwsBillings[0]->id,
                    $this->statements->map(fn (DwsBillingStatement $x): int => $x->id)->toArray()
                );
            });
        });
        $this->should('throw NotFoundException when dwsProvisionReportFinder return empty', function (): void {
            $this->dwsProvisionReportFinder
                ->allows('find')
                ->andReturn(FinderResult::from([], Pagination::create()));

            $this->assertThrows(NotFoundException::class, function (): void {
                $this->interactor->handle(
                    $this->context,
                    $this->examples->dwsBillings[0]->id,
                    $this->statements->map(fn (DwsBillingStatement $x): int => $x->id)->toArray()
                );
            });
        });
        $this->should('throw NotFoundException when identifyDwsCertificationUseCase return none', function (): void {
            $this->identifyDwsCertificationUseCase
                ->allows('handle')
                ->andReturn(Option::none());

            $this->assertThrows(NotFoundException::class, function (): void {
                $this->interactor->handle(
                    $this->context,
                    $this->examples->dwsBillings[0]->id,
                    $this->statements->map(fn (DwsBillingStatement $x): int => $x->id)->toArray()
                );
            });
        });
        $this->should('output logs', function (): void {
            $context = ['organizationId' => $this->examples->organizations[0]->id];
            $storedInvoices = Seq::from($this->examples->dwsBillingInvoices[0]);
            $disusedInvoices = Seq::from($this->examples->dwsBillingInvoices[1]);
            $updatedInvoices = Seq::from($this->examples->dwsBillingInvoices[2]);
            $this->transactionManager
                ->expects('run')
                ->andReturn([
                    $storedInvoices,
                    $disusedInvoices,
                    $updatedInvoices,
                ]);
            $this->context
                ->expects('logContext')
                ->andReturn($context)
                ->times(7);
            $this->logger
                ->expects('info')
                ->with(
                    '障害福祉サービス：請求単位が登録されました',
                    ['id' => [$this->examples->dwsBillingBundles[0]->id]] + $context
                )
                ->andReturnNull();
            $this->logger
                ->expects('info')
                ->with(
                    '障害福祉サービス：請求書が登録されました',
                    ['id' => $storedInvoices->map(fn (DwsBillingInvoice $x): int => $x->id)->toArray()] + $context
                )
                ->andReturnNull();
            $this->logger
                ->expects('info')
                ->with(
                    '障害福祉サービス：請求単位が削除されました',
                    ['id' => $disusedInvoices->map(fn (DwsBillingInvoice $x): int => $x->dwsBillingBundleId)->toArray()] + $context
                )
                ->andReturnNull();
            $this->logger
                ->expects('info')
                ->with(
                    '障害福祉サービス：請求書が削除されました',
                    ['id' => $disusedInvoices->map(fn (DwsBillingInvoice $x): int => $x->id)->toArray()] + $context
                )
                ->andReturnNull();
            $this->logger
                ->expects('info')
                ->with(
                    '障害福祉サービス：請求単位が更新されました',
                    ['id' => $updatedInvoices->map(fn (DwsBillingInvoice $x): int => $x->dwsBillingBundleId)->toArray()] + $context
                )
                ->andReturnNull();
            $this->logger
                ->expects('info')
                ->with(
                    '障害福祉サービス：請求書が更新されました',
                    ['id' => $updatedInvoices->map(fn (DwsBillingInvoice $x): int => $x->id)->toArray()] + $context
                )
                ->andReturnNull();
            $this->logger
                ->expects('info')
                ->with('障害福祉サービス：明細書が更新されました', ['id' => [$this->examples->dwsBillingStatements[0]->id]] + $context)
                ->andReturnNull();

            $this->interactor->handle(
                $this->context,
                $this->examples->dwsBillings[0]->id,
                $this->statements->map(fn (DwsBillingStatement $x): int => $x->id)->toArray()
            );
        });
        $this->should('not output logs of registration if storedInvoices is empty', function (): void {
            $context = ['organizationId' => $this->examples->organizations[0]->id];
            $storedInvoices = Seq::empty();
            $disusedInvoices = Seq::from($this->examples->dwsBillingInvoices[1]);
            $updatedInvoices = Seq::from($this->examples->dwsBillingInvoices[2]);
            $this->transactionManager
                ->expects('run')
                ->andReturn([
                    $storedInvoices,
                    $disusedInvoices,
                    $updatedInvoices,
                ]);
            $this->context
                ->expects('logContext')
                ->andReturn($context)
                ->times(5);
            $this->logger
                ->expects('info')
                ->with(
                    '障害福祉サービス：請求単位が登録されました',
                    ['id' => [$this->examples->dwsBillingBundles[0]->id]] + $context
                )
                ->never();
            $this->logger
                ->expects('info')
                ->with(
                    '障害福祉サービス：請求書が登録されました',
                    ['id' => $storedInvoices->map(fn (DwsBillingInvoice $x): int => $x->id)->toArray()] + $context
                )
                ->never();
            $this->logger
                ->expects('info')
                ->with(
                    '障害福祉サービス：請求単位が削除されました',
                    ['id' => $disusedInvoices->map(fn (DwsBillingInvoice $x): int => $x->dwsBillingBundleId)->toArray()] + $context
                )
                ->andReturnNull();
            $this->logger
                ->expects('info')
                ->with(
                    '障害福祉サービス：請求書が削除されました',
                    ['id' => $disusedInvoices->map(fn (DwsBillingInvoice $x): int => $x->id)->toArray()] + $context
                )
                ->andReturnNull();
            $this->logger
                ->expects('info')
                ->with(
                    '障害福祉サービス：請求単位が更新されました',
                    ['id' => $updatedInvoices->map(fn (DwsBillingInvoice $x): int => $x->dwsBillingBundleId)->toArray()] + $context
                )
                ->andReturnNull();
            $this->logger
                ->expects('info')
                ->with(
                    '障害福祉サービス：請求書が更新されました',
                    ['id' => $updatedInvoices->map(fn (DwsBillingInvoice $x): int => $x->id)->toArray()] + $context
                )
                ->andReturnNull();
            $this->logger
                ->expects('info')
                ->with('障害福祉サービス：明細書が更新されました', ['id' => [$this->examples->dwsBillingStatements[0]->id]] + $context)
                ->andReturnNull();

            $this->interactor->handle(
                $this->context,
                $this->examples->dwsBillings[0]->id,
                $this->statements->map(fn (DwsBillingStatement $x): int => $x->id)->toArray()
            );
        });
        $this->should('not output logs of deletion if disusedInvoices is empty', function (): void {
            $context = ['organizationId' => $this->examples->organizations[0]->id];
            $storedInvoices = Seq::from($this->examples->dwsBillingInvoices[0]);
            $disusedInvoices = Seq::empty();
            $updatedInvoices = Seq::from($this->examples->dwsBillingInvoices[2]);
            $this->transactionManager
                ->expects('run')
                ->andReturn([
                    $storedInvoices,
                    $disusedInvoices,
                    $updatedInvoices,
                ]);
            $this->context
                ->expects('logContext')
                ->andReturn($context)
                ->times(5);
            $this->logger
                ->expects('info')
                ->with(
                    '障害福祉サービス：請求単位が登録されました',
                    ['id' => [$this->examples->dwsBillingBundles[0]->id]] + $context
                )
                ->andReturnNull();
            $this->logger
                ->expects('info')
                ->with(
                    '障害福祉サービス：請求書が登録されました',
                    ['id' => $storedInvoices->map(fn (DwsBillingInvoice $x): int => $x->id)->toArray()] + $context
                )
                ->andReturnNull();
            $this->logger
                ->expects('info')
                ->with(
                    '障害福祉サービス：請求単位が削除されました',
                    ['id' => $disusedInvoices->map(fn (DwsBillingInvoice $x): int => $x->dwsBillingBundleId)->toArray()] + $context
                )
                ->never();
            $this->logger
                ->expects('info')
                ->with(
                    '障害福祉サービス：請求書が削除されました',
                    ['id' => $disusedInvoices->map(fn (DwsBillingInvoice $x): int => $x->id)->toArray()] + $context
                )
                ->never();
            $this->logger
                ->expects('info')
                ->with(
                    '障害福祉サービス：請求単位が更新されました',
                    ['id' => $updatedInvoices->map(fn (DwsBillingInvoice $x): int => $x->dwsBillingBundleId)->toArray()] + $context
                )
                ->andReturnNull();
            $this->logger
                ->expects('info')
                ->with(
                    '障害福祉サービス：請求書が更新されました',
                    ['id' => $updatedInvoices->map(fn (DwsBillingInvoice $x): int => $x->id)->toArray()] + $context
                )
                ->andReturnNull();
            $this->logger
                ->expects('info')
                ->with('障害福祉サービス：明細書が更新されました', ['id' => [$this->examples->dwsBillingStatements[0]->id]] + $context)
                ->andReturnNull();

            $this->interactor->handle(
                $this->context,
                $this->examples->dwsBillings[0]->id,
                $this->statements->map(fn (DwsBillingStatement $x): int => $x->id)->toArray()
            );
        });
    }
}

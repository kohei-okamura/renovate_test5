<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\Billing;

use App\Concretes\PermanentDatabaseTransactionManager;
use Domain\Billing\DwsBillingStatus;
use Domain\Common\Carbon;
use Domain\Permission\Permission;
use Lib\Exceptions\NotFoundException;
use ScalikePHP\Map;
use ScalikePHP\Seq;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\DwsBillingBundleRepositoryMixin;
use Tests\Unit\Mixins\DwsBillingCopayCoordinationRepositoryMixin;
use Tests\Unit\Mixins\DwsBillingInvoiceRepositoryMixin;
use Tests\Unit\Mixins\DwsBillingRepositoryMixin;
use Tests\Unit\Mixins\DwsBillingServiceReportRepositoryMixin;
use Tests\Unit\Mixins\DwsBillingStatementRepositoryMixin;
use Tests\Unit\Mixins\LoggerMixin;
use Tests\Unit\Mixins\LookupDwsBillingUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\TransactionManagerMixin;
use Tests\Unit\Test;
use UseCase\Billing\CopyDwsBillingInteractor;

/**
 * {@link \UseCase\Billing\CopyDwsBillingInteractor} のテスト.
 */
final class CopyDwsBillingInteractorTest extends Test
{
    use CarbonMixin;
    use ContextMixin;
    use DwsBillingBundleRepositoryMixin;
    use DwsBillingCopayCoordinationRepositoryMixin;
    use DwsBillingInvoiceRepositoryMixin;
    use DwsBillingRepositoryMixin;
    use DwsBillingServiceReportRepositoryMixin;
    use DwsBillingStatementRepositoryMixin;
    use ExamplesConsumer;
    use LoggerMixin;
    use LookupDwsBillingUseCaseMixin;
    use MatchesSnapshots;
    use MockeryMixin;
    use TransactionManagerMixin;
    use UnitSupport;

    private CopyDwsBillingInteractor $interactor;

    /**
     * 初期化処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachSpec(function (self $self): void {
            $self->dwsBillingRepository
                ->allows('store')
                ->andReturn($self->examples->dwsBillings[0])
                ->byDefault();
            $self->dwsBillingRepository
                ->allows('transactionManager')
                ->andReturn(PermanentDatabaseTransactionManager::class)
                ->passthru();
            $self->dwsBillingBundleRepository
                ->allows('store')
                ->andReturn($self->examples->dwsBillingBundles[0])
                ->byDefault();
            $self->dwsBillingBundleRepository
                ->allows('lookupByBillingId')
                ->andReturn(Map::from([
                    $self->examples->dwsBillingBundles[0]->dwsBillingId => Seq::from(
                        $self->examples->dwsBillingBundles[0],
                        $self->examples->dwsBillingBundles[1],
                    ),
                ]))
                ->byDefault();
            $self->dwsBillingBundleRepository
                ->allows('transactionManager')
                ->andReturn(PermanentDatabaseTransactionManager::class)
                ->passthru();
            $self->dwsBillingStatementRepository
                ->allows('store')
                ->andReturn($self->examples->dwsBillingStatements[0])
                ->byDefault();
            $self->dwsBillingStatementRepository
                ->allows('lookupByBundleId')
                ->andReturn(Map::from([
                    $self->examples->dwsBillingStatements[0]->dwsBillingBundleId => Seq::from(
                        $self->examples->dwsBillingStatements[0],
                        $self->examples->dwsBillingStatements[1],
                    ),
                ]))
                ->byDefault();
            $self->dwsBillingStatementRepository
                ->allows('transactionManager')
                ->andReturn(PermanentDatabaseTransactionManager::class)
                ->passthru();
            $self->dwsBillingServiceReportRepository
                ->allows('store')
                ->andReturn($self->examples->dwsBillingServiceReports[0])
                ->byDefault();
            $self->dwsBillingServiceReportRepository
                ->allows('lookupByBundleId')
                ->andReturn(Map::from([
                    $self->examples->dwsBillingServiceReports[0]->dwsBillingBundleId => Seq::from(
                        $self->examples->dwsBillingServiceReports[0],
                        $self->examples->dwsBillingServiceReports[1],
                    ),
                ]))
                ->byDefault();
            $self->dwsBillingServiceReportRepository
                ->allows('transactionManager')
                ->andReturn(PermanentDatabaseTransactionManager::class)
                ->passthru();
            $self->dwsBillingCopayCoordinationRepository
                ->allows('store')
                ->andReturn($self->examples->dwsBillingCopayCoordinations[0])
                ->byDefault();
            $self->dwsBillingCopayCoordinationRepository
                ->allows('lookupByBundleId')
                ->andReturn(Map::from([
                    $self->examples->dwsBillingCopayCoordinations[0]->dwsBillingBundleId => Seq::from(
                        $self->examples->dwsBillingCopayCoordinations[0],
                        $self->examples->dwsBillingCopayCoordinations[1],
                    ),
                ]))
                ->byDefault();
            $self->dwsBillingCopayCoordinationRepository
                ->allows('transactionManager')
                ->andReturn(PermanentDatabaseTransactionManager::class)
                ->passthru();
            $self->dwsBillingInvoiceRepository
                ->allows('store')
                ->andReturn($self->examples->dwsBillingInvoices[0])
                ->byDefault();
            $self->dwsBillingInvoiceRepository
                ->allows('lookupByBundleId')
                ->andReturn(Map::from([
                    $self->examples->dwsBillingInvoices[0]->dwsBillingBundleId => Seq::from(
                        $self->examples->dwsBillingInvoices[0],
                        $self->examples->dwsBillingInvoices[1],
                    ),
                ]))
                ->byDefault();
            $self->dwsBillingInvoiceRepository
                ->allows('transactionManager')
                ->andReturn(PermanentDatabaseTransactionManager::class)
                ->passthru();
            $self->lookupDwsBillingUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->examples->dwsBillings[0]))
                ->byDefault();
            $self->logger
                ->allows('info')
                ->andReturnNull()
                ->byDefault();

            $self->interactor = app(CopyDwsBillingInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('run in transaction', function (): void {
            $this->transactionManager
                ->expects('run')
                ->andReturn([$this->examples->dwsBillings[0], $this->examples->dwsBillings[0]]);
            $this->dwsBillingRepository
                ->expects('store')
                ->never();
            $this->dwsBillingBundleRepository
                ->expects('store')
                ->never();
            $this->dwsBillingBundleRepository
                ->expects('lookupByBillingId')
                ->never();
            $this->dwsBillingStatementRepository
                ->expects('store')
                ->never();
            $this->dwsBillingStatementRepository
                ->expects('lookupByBundleId')
                ->never();
            $this->dwsBillingServiceReportRepository
                ->expects('store')
                ->never();
            $this->dwsBillingServiceReportRepository
                ->expects('lookupByBundleId')
                ->never();
            $this->dwsBillingCopayCoordinationRepository
                ->expects('store')
                ->never();
            $this->dwsBillingCopayCoordinationRepository
                ->expects('lookupByBundleId')
                ->never();

            $this->interactor->handle($this->context, $this->examples->dwsBillings[0]->id);
        });
        $this->should('use LookupDwsBillingUseCase', function (): void {
            $this->lookupDwsBillingUseCase
                ->expects('handle')
                ->with($this->context, Permission::createBillings(), $this->examples->dwsBillings[0]->id)
                ->andReturn(Seq::from($this->examples->dwsBillings[0]));

            $this->interactor->handle($this->context, $this->examples->dwsBillings[0]->id);
        });
        $this->should('throw NotFoundException when LookupDwsBillingUseCase return empty', function (): void {
            $this->lookupDwsBillingUseCase
                ->expects('handle')
                ->with($this->context, Permission::createBillings(), $this->examples->dwsBillings[0]->id)
                ->andReturn(Seq::empty());

            $this->assertThrows(NotFoundException::class, function (): void {
                $this->interactor->handle($this->context, $this->examples->dwsBillings[0]->id);
            });
        });
        $this->should('copy Billing', function (): void {
            $this->dwsBillingRepository
                ->expects('store')
                ->with(equalTo($this->examples->dwsBillings[0]->copy([
                    'id' => null,
                    'files' => [],
                    'status' => DwsBillingStatus::ready(),
                    'fixedAt' => null,
                    'updatedAt' => Carbon::now(),
                ])))
                ->andReturn($this->examples->dwsBillings[0]);

            $this->interactor->handle($this->context, $this->examples->dwsBillings[0]->id);
        });
        $this->should('use lookupByBillingId on bundleRepository', function (): void {
            $this->dwsBillingBundleRepository
                ->expects('lookupByBillingId')
                ->with($this->examples->dwsBillings[0]->id)
                ->andReturn(Map::from([
                    $this->examples->dwsBillingBundles[0]->dwsBillingId => Seq::from(
                        $this->examples->dwsBillingBundles[0],
                        $this->examples->dwsBillingBundles[1],
                    ),
                ]));

            $this->interactor->handle($this->context, $this->examples->dwsBillings[0]->id);
        });
        $this->should('copy Bundle', function (): void {
            $this->dwsBillingBundleRepository
                ->expects('store')
                ->with(equalTo($this->examples->dwsBillingBundles[0]->copy([
                    'id' => null,
                    'dwsBillingId' => $this->examples->dwsBillings[0]->id,
                    'updatedAt' => Carbon::now(),
                ])))
                ->andReturn($this->examples->dwsBillingBundles[0]);
            $this->dwsBillingBundleRepository
                ->expects('store')
                ->with(equalTo($this->examples->dwsBillingBundles[1]->copy([
                    'id' => null,
                    'dwsBillingId' => $this->examples->dwsBillings[0]->id,
                    'updatedAt' => Carbon::now(),
                ])))
                ->andReturn($this->examples->dwsBillingBundles[1]);

            $this->interactor->handle($this->context, $this->examples->dwsBillings[0]->id);
        });
        $this->should('use lookupByBundleId on DwsBillingStatementRepository', function (): void {
            $this->dwsBillingStatementRepository
                ->expects('lookupByBundleId')
                ->with($this->examples->dwsBillingBundles[0]->id, $this->examples->dwsBillingBundles[1]->id)
                ->andReturn(Map::from([
                    $this->examples->dwsBillingStatements[0]->dwsBillingBundleId => Seq::from(
                        $this->examples->dwsBillingStatements[0],
                        $this->examples->dwsBillingStatements[1],
                    ),
                ]));

            $this->interactor->handle($this->context, $this->examples->dwsBillings[0]->id);
        });
        $this->should('copy Statement', function (): void {
            $this->dwsBillingStatementRepository
                ->expects('store')
                ->with(equalTo($this->examples->dwsBillingStatements[0]->copy([
                    'id' => null,
                    'dwsBillingId' => $this->examples->dwsBillings[0]->id,
                    'dwsBillingBundleId' => $this->examples->dwsBillingBundles[0]->id,
                    'updatedAt' => Carbon::now(),
                ])))
                ->andReturn($this->examples->dwsBillingStatements[0]);
            $this->dwsBillingStatementRepository
                ->expects('store')
                ->with(equalTo($this->examples->dwsBillingStatements[1]->copy([
                    'id' => null,
                    'dwsBillingId' => $this->examples->dwsBillings[0]->id,
                    'dwsBillingBundleId' => $this->examples->dwsBillingBundles[0]->id,
                    'updatedAt' => Carbon::now(),
                ])))
                ->andReturn($this->examples->dwsBillingStatements[1]);

            $this->interactor->handle($this->context, $this->examples->dwsBillings[0]->id);
        });
        $this->should('use lookupByBundleId on DwsBillingServiceReportRepository', function (): void {
            $this->dwsBillingServiceReportRepository
                ->expects('lookupByBundleId')
                ->with($this->examples->dwsBillingBundles[0]->id, $this->examples->dwsBillingBundles[1]->id)
                ->andReturn(Map::from([
                    $this->examples->dwsBillingServiceReports[0]->dwsBillingBundleId => Seq::from(
                        $this->examples->dwsBillingServiceReports[0],
                        $this->examples->dwsBillingServiceReports[1],
                    ),
                ]));

            $this->interactor->handle($this->context, $this->examples->dwsBillings[0]->id);
        });
        $this->should('copy ServiceReport', function (): void {
            $this->dwsBillingServiceReportRepository
                ->expects('store')
                ->with(equalTo($this->examples->dwsBillingServiceReports[0]->copy([
                    'id' => null,
                    'dwsBillingId' => $this->examples->dwsBillings[0]->id,
                    'dwsBillingBundleId' => $this->examples->dwsBillingBundles[0]->id,
                    'updatedAt' => Carbon::now(),
                ])))
                ->andReturn($this->examples->dwsBillingServiceReports[0]);
            $this->dwsBillingServiceReportRepository
                ->expects('store')
                ->with(equalTo($this->examples->dwsBillingServiceReports[1]->copy([
                    'id' => null,
                    'dwsBillingId' => $this->examples->dwsBillings[0]->id,
                    'dwsBillingBundleId' => $this->examples->dwsBillingBundles[0]->id,
                    'updatedAt' => Carbon::now(),
                ])))
                ->andReturn($this->examples->dwsBillingServiceReports[1]);

            $this->interactor->handle($this->context, $this->examples->dwsBillings[0]->id);
        });
        $this->should('use lookupByBundleId on DwsBillingCopayCoordinationRepository', function (): void {
            $this->dwsBillingCopayCoordinationRepository
                ->expects('lookupByBundleId')
                ->with($this->examples->dwsBillingBundles[0]->id, $this->examples->dwsBillingBundles[1]->id)
                ->andReturn(Map::from([
                    $this->examples->dwsBillingCopayCoordinations[0]->dwsBillingBundleId => Seq::from(
                        $this->examples->dwsBillingCopayCoordinations[0],
                        $this->examples->dwsBillingCopayCoordinations[1],
                    ),
                ]));

            $this->interactor->handle($this->context, $this->examples->dwsBillings[0]->id);
        });
        $this->should('copy CopayCoordination', function (): void {
            $this->dwsBillingCopayCoordinationRepository
                ->expects('store')
                ->with(equalTo($this->examples->dwsBillingCopayCoordinations[0]->copy([
                    'id' => null,
                    'dwsBillingId' => $this->examples->dwsBillings[0]->id,
                    'dwsBillingBundleId' => $this->examples->dwsBillingBundles[0]->id,
                    'updatedAt' => Carbon::now(),
                ])))
                ->andReturn($this->examples->dwsBillingCopayCoordinations[0]);
            $this->dwsBillingCopayCoordinationRepository
                ->expects('store')
                ->with(equalTo($this->examples->dwsBillingCopayCoordinations[1]->copy([
                    'id' => null,
                    'dwsBillingId' => $this->examples->dwsBillings[0]->id,
                    'dwsBillingBundleId' => $this->examples->dwsBillingBundles[0]->id,
                    'updatedAt' => Carbon::now(),
                ])))
                ->andReturn($this->examples->dwsBillingCopayCoordinations[1]);

            $this->interactor->handle($this->context, $this->examples->dwsBillings[0]->id);
        });
        $this->should('use lookupByBundleId on DwsBillingInvoiceRepository', function (): void {
            $this->dwsBillingInvoiceRepository
                ->expects('lookupByBundleId')
                ->with($this->examples->dwsBillingBundles[0]->id, $this->examples->dwsBillingBundles[1]->id)
                ->andReturn(Map::from([
                    $this->examples->dwsBillingInvoices[0]->dwsBillingBundleId => Seq::from(
                        $this->examples->dwsBillingInvoices[0],
                        $this->examples->dwsBillingInvoices[1],
                    ),
                ]));

            $this->interactor->handle($this->context, $this->examples->dwsBillings[0]->id);
        });
        $this->should('copy Invoice', function (): void {
            $this->dwsBillingInvoiceRepository
                ->expects('store')
                ->with(equalTo($this->examples->dwsBillingInvoices[0]->copy([
                    'id' => null,
                    'dwsBillingId' => $this->examples->dwsBillings[0]->id,
                    'dwsBillingBundleId' => $this->examples->dwsBillingBundles[0]->id,
                    'updatedAt' => Carbon::now(),
                ])))
                ->andReturn($this->examples->dwsBillingInvoices[0]);
            $this->dwsBillingInvoiceRepository
                ->expects('store')
                ->with(equalTo($this->examples->dwsBillingInvoices[1]->copy([
                    'id' => null,
                    'dwsBillingId' => $this->examples->dwsBillings[0]->id,
                    'dwsBillingBundleId' => $this->examples->dwsBillingBundles[0]->id,
                    'updatedAt' => Carbon::now(),
                ])))
                ->andReturn($this->examples->dwsBillingInvoices[1]);

            $this->interactor->handle($this->context, $this->examples->dwsBillings[0]->id);
        });
        $this->should('return new Billing', function (): void {
            $this->assertModelStrictEquals(
                $this->examples->dwsBillings[0],
                $this->interactor->handle($this->context, $this->examples->dwsBillings[0]->id)
            );
        });
        $this->should('disable the old billing', function (): void {
            $this->dwsBillingRepository
                ->expects('store')
                ->with(equalTo($this->examples->dwsBillings[0]->copy([
                    'status' => DwsBillingStatus::disabled(),
                    'updatedAt' => Carbon::now(),
                ])))
                ->andReturn($this->examples->dwsBillings[0]);

            $this->interactor->handle($this->context, $this->examples->dwsBillings[0]->id);
        });
        $this->should('output log about disabling the old billing', function (): void {
            $this->logger
                ->expects('info')
                ->with(
                    '障害福祉サービス：請求が更新されました',
                    ['id' => $this->examples->dwsBillings[0]->id] + $this->context->logContext()
                )
                ->andReturnNull();

            $this->interactor->handle($this->context, $this->examples->dwsBillings[0]->id);
        });
    }
}

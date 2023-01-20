<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\Billing;

use App\Concretes\PermanentDatabaseTransactionManager;
use Closure;
use Domain\Billing\DwsBillingServiceReport;
use Domain\Billing\DwsBillingStatus;
use Domain\Common\Carbon;
use Domain\Permission\Permission;
use Lib\Exceptions\NotFoundException;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\DwsBillingServiceReportRepositoryMixin;
use Tests\Unit\Mixins\LoggerMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\SimpleLookupDwsBillingServiceReportUseCaseMixin;
use Tests\Unit\Mixins\TransactionManagerMixin;
use Tests\Unit\Test;
use UseCase\Billing\BulkUpdateDwsBillingServiceReportStatusInteractor;

/**
 * {@link \UseCase\Billing\BulkUpdateDwsBillingServiceReportStatusInteractor} のテスト.
 */
final class BulkUpdateDwsBillingServiceReportStatusInteractorTest extends Test
{
    use CarbonMixin;
    use ContextMixin;
    use DwsBillingServiceReportRepositoryMixin;
    use ExamplesConsumer;
    use LoggerMixin;
    use MockeryMixin;
    use SimpleLookupDwsBillingServiceReportUseCaseMixin;
    use TransactionManagerMixin;
    use UnitSupport;

    private DwsBillingServiceReport $serviceReport;
    private BulkUpdateDwsBillingServiceReportStatusInteractor $interactor;

    /**
     * 初期化処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachSpec(function (self $self): void {
            $self->serviceReport = $self->examples->dwsBillingServiceReports[0];
            $self->dwsBillingServiceReportRepository
                ->allows('store')
                ->andReturn($self->serviceReport)
                ->byDefault();
            $self->dwsBillingServiceReportRepository
                ->allows('transactionManager')
                ->andReturn(PermanentDatabaseTransactionManager::class)
                ->passthru();
            $self->logger
                ->allows('info')
                ->byDefault();
            $self->simpleLookupDwsBillingServiceReportUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->serviceReport))
                ->byDefault();

            $self->interactor = app(BulkUpdateDwsBillingServiceReportStatusInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('edit the DwsBillingServiceReport after transaction begun', function (): void {
            $this->transactionManager
                ->expects('run')
                ->andReturnUsing(function (Closure $callback) {
                    $this->simpleLookupDwsBillingServiceReportUseCase
                        ->expects('handle')
                        ->with(
                            $this->context,
                            Permission::updateBillings(),
                            $this->serviceReport->id,
                        )
                        ->andReturn(Seq::from($this->serviceReport));
                    $this->dwsBillingServiceReportRepository
                        ->expects('store')
                        ->with(equalTo($this->serviceReport->copy([
                            'status' => DwsBillingStatus::fixed(),
                            'updatedAt' => Carbon::now(),
                        ])))
                        ->andReturn($this->serviceReport);
                    return $callback();
                });

            $this->interactor->handle(
                $this->context,
                $this->serviceReport->dwsBillingId,
                [$this->serviceReport->id],
                DwsBillingStatus::fixed()
            );
        });
        $this->should(
            'throw a NotFoundException when the number of returned serviceReport is different from the number of ids',
            function (): void {
                $this->simpleLookupDwsBillingServiceReportUseCase
                    ->expects('handle')
                    ->with(
                        $this->context,
                        Permission::updateBillings(),
                        $this->serviceReport->id,
                        $this->examples->dwsBillingServiceReports[1]->id
                    )
                    ->andReturn(Seq::from($this->serviceReport));

                $this->assertThrows(
                    NotFoundException::class,
                    function (): void {
                        $this->interactor->handle(
                            $this->context,
                            $this->serviceReport->dwsBillingId,
                            [$this->serviceReport->id, $this->examples->dwsBillingServiceReports[1]->id],
                            DwsBillingStatus::fixed()
                        );
                    }
                );
            }
        );
        $this->should('log using info', function (): void {
            $context = ['organizationId' => $this->examples->organizations[0]->id];
            $this->context
                ->expects('logContext')
                ->andReturn($context);
            $this->logger
                ->expects('info')
                ->with(
                    'サービス提供実績記録票が更新されました',
                    ['id' => $this->serviceReport->id] + $context
                );

            $this->interactor->handle(
                $this->context,
                $this->serviceReport->dwsBillingId,
                [$this->serviceReport->id],
                DwsBillingStatus::fixed()
            );
        });
    }
}

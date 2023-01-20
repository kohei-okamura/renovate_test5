<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\ProvisionReport;

use App\Concretes\PermanentDatabaseTransactionManager;
use Closure;
use Domain\Common\Carbon;
use Domain\Permission\Permission;
use Domain\ProvisionReport\DwsProvisionReport;
use Domain\ProvisionReport\DwsProvisionReportStatus;
use Lib\Exceptions\NotFoundException;
use ScalikePHP\Option;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\DwsProvisionReportRepositoryMixin;
use Tests\Unit\Mixins\GetDwsProvisionReportUseCaseMixin;
use Tests\Unit\Mixins\LoggerMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\TransactionManagerMixin;
use Tests\Unit\Test;
use UseCase\ProvisionReport\UpdateDwsProvisionReportStatusInteractor;

/**
 * UpdateDwsProvisionReportStatusInteractor のテスト.
 */
class UpdateDwsProvisionReportStatusInteractorTest extends Test
{
    use CarbonMixin;
    use ContextMixin;
    use DwsProvisionReportRepositoryMixin;
    use ExamplesConsumer;
    use GetDwsProvisionReportUseCaseMixin;
    use LoggerMixin;
    use MockeryMixin;
    use TransactionManagerMixin;
    use UnitSupport;

    private DwsProvisionReport $dwsProvisionReport;
    private UpdateDwsProvisionReportStatusInteractor $interactor;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (UpdateDwsProvisionReportStatusInteractorTest $self): void {
            $self->dwsProvisionReport = $self->examples->dwsProvisionReports[0];
            $self->getDwsProvisionReportUseCase
                ->allows('handle')
                ->andReturn(Option::from($self->dwsProvisionReport))
                ->byDefault();
            $self->dwsProvisionReportRepository
                ->allows('store')
                ->andReturn($self->dwsProvisionReport)
                ->byDefault();
            $self->dwsProvisionReportRepository
                ->allows('transactionManager')
                ->andReturn(PermanentDatabaseTransactionManager::class)
                ->passthru();
            $self->logger
                ->allows('info')
                ->byDefault();
            $self->interactor = app(UpdateDwsProvisionReportStatusInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('use GetDwsProvisionReportUseCase', function (): void {
            $this->getDwsProvisionReportUseCase
                ->expects('handle')
                ->with(
                    $this->context,
                    Permission::updateDwsProvisionReports(),
                    $this->dwsProvisionReport->officeId,
                    $this->dwsProvisionReport->userId,
                    equalTo($this->dwsProvisionReport->providedIn),
                )
                ->andReturn(Option::from($this->dwsProvisionReport));

            $this->interactor->handle(
                $this->context,
                $this->dwsProvisionReport->officeId,
                $this->dwsProvisionReport->userId,
                $this->dwsProvisionReport->providedIn->format('Y-m'),
                ['status' => $this->dwsProvisionReport->status]
            );
        });
        $this->should('throw NotFoundException when GetDwsProvisionReportUseCase return None', function (): void {
            $this->getDwsProvisionReportUseCase
                ->expects('handle')
                ->andReturn(Option::none());

            $this->assertThrows(NotFoundException::class, function (): void {
                $this->interactor->handle(
                    $this->context,
                    $this->dwsProvisionReport->officeId,
                    $this->dwsProvisionReport->userId,
                    $this->dwsProvisionReport->providedIn->format('Y-m'),
                    ['status' => $this->dwsProvisionReport->status]
                );
            });
        });
        $this->should('edit the DwsProvisionReport after transaction begun', function (): void {
            $this->transactionManager
                ->expects('run')
                ->andReturnUsing(function (Closure $callback) {
                    $this->dwsProvisionReportRepository
                        ->expects('store')
                        ->with(equalTo($this->dwsProvisionReport->copy([
                            'status' => $this->dwsProvisionReport->status,
                            'fixedAt' => null,
                            'updatedAt' => Carbon::now(),
                        ])))
                        ->andReturn($this->dwsProvisionReport);
                    return $callback();
                });

            $this->interactor->handle(
                $this->context,
                $this->dwsProvisionReport->officeId,
                $this->dwsProvisionReport->userId,
                $this->dwsProvisionReport->providedIn->format('Y-m'),
                ['status' => $this->dwsProvisionReport->status]
            );
        });
        $this->should('set fixedAt to now when given status is fixed', function (): void {
            $status = DwsProvisionReportStatus::fixed();
            $this->transactionManager
                ->expects('run')
                ->andReturnUsing(function (Closure $callback) use ($status) {
                    $this->dwsProvisionReportRepository
                        ->expects('store')
                        ->with(equalTo($this->dwsProvisionReport->copy([
                            'status' => $status,
                            'fixedAt' => Carbon::now(),
                            'updatedAt' => Carbon::now(),
                        ])))
                        ->andReturn($this->dwsProvisionReport);
                    return $callback();
                });

            $this->interactor->handle(
                $this->context,
                $this->dwsProvisionReport->officeId,
                $this->dwsProvisionReport->userId,
                $this->dwsProvisionReport->providedIn->format('Y-m'),
                ['status' => $status]
            );
        });
        $this->should('set fixedAt to null when given status is not fixed', function (): void {
            $status = DwsProvisionReportStatus::inProgress();
            $this->transactionManager
                ->expects('run')
                ->andReturnUsing(function (Closure $callback) use ($status) {
                    $this->dwsProvisionReportRepository
                        ->expects('store')
                        ->with(equalTo($this->dwsProvisionReport->copy([
                            'status' => $status,
                            'fixedAt' => null,
                            'updatedAt' => Carbon::now(),
                        ])))
                        ->andReturn($this->dwsProvisionReport);
                    return $callback();
                });

            $this->interactor->handle(
                $this->context,
                $this->dwsProvisionReport->officeId,
                $this->dwsProvisionReport->userId,
                $this->dwsProvisionReport->providedIn->format('Y-m'),
                ['status' => $status]
            );
        });
        $this->should('log using info', function (): void {
            $context = ['organizationId' => $this->examples->organizations[0]->id];
            $this->context
                ->expects('logContext')
                ->andReturn($context);
            $this->logger
                ->expects('info')
                ->with('障害福祉サービス：予実が更新されました', ['id' => $this->dwsProvisionReport->id] + $context);

            $this->interactor->handle(
                $this->context,
                $this->dwsProvisionReport->officeId,
                $this->dwsProvisionReport->userId,
                $this->dwsProvisionReport->providedIn->format('Y-m'),
                ['status' => $this->dwsProvisionReport->status]
            );
        });
    }
}

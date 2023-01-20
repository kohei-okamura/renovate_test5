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
use Domain\ProvisionReport\LtcsProvisionReport;
use Domain\ProvisionReport\LtcsProvisionReportStatus;
use Lib\Exceptions\NotFoundException;
use ScalikePHP\Option;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\GetLtcsProvisionReportUseCaseMixin;
use Tests\Unit\Mixins\LoggerMixin;
use Tests\Unit\Mixins\LtcsProvisionReportRepositoryMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\TransactionManagerMixin;
use Tests\Unit\Test;
use UseCase\ProvisionReport\UpdateLtcsProvisionReportStatusInteractor;

/**
 * UpdateLtcsProvisionReportStatusInteractor のテスト.
 */
class UpdateLtcsProvisionReportStatusInteractorTest extends Test
{
    use CarbonMixin;
    use ContextMixin;
    use LtcsProvisionReportRepositoryMixin;
    use ExamplesConsumer;
    use GetLtcsProvisionReportUseCaseMixin;
    use LoggerMixin;
    use MockeryMixin;
    use TransactionManagerMixin;
    use UnitSupport;

    private LtcsProvisionReport $ltcsProvisionReport;
    private UpdateLtcsProvisionReportStatusInteractor $interactor;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (UpdateLtcsProvisionReportStatusInteractorTest $self): void {
            $self->ltcsProvisionReport = $self->examples->ltcsProvisionReports[0];
            $self->getLtcsProvisionReportUseCase
                ->allows('handle')
                ->andReturn(Option::from($self->ltcsProvisionReport))
                ->byDefault();
            $self->ltcsProvisionReportRepository
                ->allows('store')
                ->andReturn($self->ltcsProvisionReport)
                ->byDefault();
            $self->ltcsProvisionReportRepository
                ->allows('transactionManager')
                ->andReturn(PermanentDatabaseTransactionManager::class)
                ->passthru();
            $self->logger
                ->allows('info')
                ->byDefault();
            $self->interactor = app(UpdateLtcsProvisionReportStatusInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('use GetLtcsProvisionReportUseCase', function (): void {
            $this->getLtcsProvisionReportUseCase
                ->expects('handle')
                ->with(
                    $this->context,
                    Permission::updateLtcsProvisionReports(),
                    $this->ltcsProvisionReport->officeId,
                    $this->ltcsProvisionReport->userId,
                    equalTo($this->ltcsProvisionReport->providedIn),
                )
                ->andReturn(Option::from($this->ltcsProvisionReport));

            $this->interactor->handle(
                $this->context,
                $this->ltcsProvisionReport->officeId,
                $this->ltcsProvisionReport->userId,
                $this->ltcsProvisionReport->providedIn->format('Y-m'),
                ['status' => $this->ltcsProvisionReport->status]
            );
        });
        $this->should('throw NotFoundException when GetLtcsProvisionReportUseCase return None', function (): void {
            $this->getLtcsProvisionReportUseCase
                ->expects('handle')
                ->andReturn(Option::none());

            $this->assertThrows(NotFoundException::class, function (): void {
                $this->interactor->handle(
                    $this->context,
                    $this->ltcsProvisionReport->officeId,
                    $this->ltcsProvisionReport->userId,
                    $this->ltcsProvisionReport->providedIn->format('Y-m'),
                    ['status' => $this->ltcsProvisionReport->status]
                );
            });
        });
        $this->should('edit the LtcsProvisionReport after transaction begun', function (): void {
            $this->transactionManager
                ->expects('run')
                ->andReturnUsing(function (Closure $callback) {
                    $this->ltcsProvisionReportRepository
                        ->expects('store')
                        ->with(equalTo($this->ltcsProvisionReport->copy([
                            'status' => $this->ltcsProvisionReport->status,
                            'fixedAt' => null,
                            'updatedAt' => Carbon::now(),
                        ])))
                        ->andReturn($this->ltcsProvisionReport);
                    return $callback();
                });

            $this->interactor->handle(
                $this->context,
                $this->ltcsProvisionReport->officeId,
                $this->ltcsProvisionReport->userId,
                $this->ltcsProvisionReport->providedIn->format('Y-m'),
                ['status' => $this->ltcsProvisionReport->status]
            );
        });
        $this->should('set fixedAt to now when given status is fixed', function (): void {
            $status = LtcsProvisionReportStatus::fixed();
            $this->transactionManager
                ->expects('run')
                ->andReturnUsing(function (Closure $callback) use ($status) {
                    $this->ltcsProvisionReportRepository
                        ->expects('store')
                        ->with(equalTo($this->ltcsProvisionReport->copy([
                            'status' => $status,
                            'fixedAt' => Carbon::now(),
                            'updatedAt' => Carbon::now(),
                        ])))
                        ->andReturn($this->ltcsProvisionReport);
                    return $callback();
                });

            $this->interactor->handle(
                $this->context,
                $this->ltcsProvisionReport->officeId,
                $this->ltcsProvisionReport->userId,
                $this->ltcsProvisionReport->providedIn->format('Y-m'),
                ['status' => $status]
            );
        });
        $this->should('set fixedAt to null when given status is not fixed', function (): void {
            $status = LtcsProvisionReportStatus::inProgress();
            $this->transactionManager
                ->expects('run')
                ->andReturnUsing(function (Closure $callback) use ($status) {
                    $this->ltcsProvisionReportRepository
                        ->expects('store')
                        ->with(equalTo($this->ltcsProvisionReport->copy([
                            'status' => $status,
                            'fixedAt' => null,
                            'updatedAt' => Carbon::now(),
                        ])))
                        ->andReturn($this->ltcsProvisionReport);
                    return $callback();
                });

            $this->interactor->handle(
                $this->context,
                $this->ltcsProvisionReport->officeId,
                $this->ltcsProvisionReport->userId,
                $this->ltcsProvisionReport->providedIn->format('Y-m'),
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
                ->with('介護保険サービス：予実が更新されました', ['id' => $this->ltcsProvisionReport->id] + $context);

            $this->interactor->handle(
                $this->context,
                $this->ltcsProvisionReport->officeId,
                $this->ltcsProvisionReport->userId,
                $this->ltcsProvisionReport->providedIn->format('Y-m'),
                ['status' => $this->ltcsProvisionReport->status]
            );
        });
    }
}

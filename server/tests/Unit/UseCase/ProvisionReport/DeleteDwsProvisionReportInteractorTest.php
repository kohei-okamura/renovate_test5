<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\ProvisionReport;

use App\Concretes\PermanentDatabaseTransactionManager;
use Closure;
use Domain\Permission\Permission;
use Domain\ProvisionReport\DwsProvisionReport;
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
use UseCase\ProvisionReport\DeleteDwsProvisionReportInteractor;

/**
 * {@link \UseCase\ProvisionReport\DeleteDwsProvisionReportInteractor} のテスト.
 */
class DeleteDwsProvisionReportInteractorTest extends Test
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
    private DeleteDwsProvisionReportInteractor $interactor;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (DeleteDwsProvisionReportInteractorTest $self): void {
            $self->getDwsProvisionReportUseCase
                ->allows('handle')
                ->andReturn(Option::from($self->examples->dwsProvisionReports[0]))
                ->byDefault();
            $self->dwsProvisionReportRepository
                ->allows('removeById')
                ->andReturn($self->examples->dwsProvisionReports[0])
                ->byDefault();
            $self->dwsProvisionReportRepository
                ->allows('transactionManager')
                ->andReturn(PermanentDatabaseTransactionManager::class)
                ->passthru();
            $self->logger
                ->allows('info')
                ->andReturnNull()
                ->byDefault();

            $self->dwsProvisionReport = $self->examples->dwsProvisionReports[0];
            $self->interactor = app(DeleteDwsProvisionReportInteractor::class);
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
                    equalTo(Permission::updateDwsProvisionReports()),
                    $this->dwsProvisionReport->officeId,
                    $this->dwsProvisionReport->userId,
                    $this->dwsProvisionReport->providedIn
                )
                ->andReturn(Option::from($this->dwsProvisionReport));

            $this->interactor->handle(
                $this->context,
                $this->dwsProvisionReport->officeId,
                $this->dwsProvisionReport->userId,
                $this->dwsProvisionReport->providedIn,
            );
        });
        $this->should('throw NotFoundException when GetDwsProvisionReportUseCase return none', function (): void {
            $this->getDwsProvisionReportUseCase
                ->expects('handle')
                ->with(
                    $this->context,
                    equalTo(Permission::updateDwsProvisionReports()),
                    $this->dwsProvisionReport->officeId,
                    $this->dwsProvisionReport->userId,
                    $this->dwsProvisionReport->providedIn
                )
                ->andReturn(Option::none());

            $this->assertThrows(NotFoundException::class, function (): void {
                $this->interactor->handle(
                    $this->context,
                    $this->dwsProvisionReport->officeId,
                    $this->dwsProvisionReport->userId,
                    $this->dwsProvisionReport->providedIn,
                );
            });
        });
        $this->should('delete the DwsProvisionReport after transaction begun', function (): void {
            $this->transactionManager
                ->expects('run')
                ->andReturnUsing(function (Closure $callback) {
                    $this->dwsProvisionReportRepository
                        ->expects('removeById')
                        ->with($this->dwsProvisionReport->id)
                        ->andReturnNull();
                    return $callback();
                });

            $this->interactor->handle(
                $this->context,
                $this->dwsProvisionReport->officeId,
                $this->dwsProvisionReport->userId,
                $this->dwsProvisionReport->providedIn,
            );
        });
        $this->should('log using info', function (): void {
            $context = ['organizationId' => $this->examples->organizations[0]->id];
            $this->context
                ->expects('logContext')
                ->andReturn($context);
            $this->logger
                ->expects('info')
                ->with('障害福祉サービス：予実が削除されました', ['id' => $this->dwsProvisionReport->id] + $context);

            $this->interactor->handle(
                $this->context,
                $this->dwsProvisionReport->officeId,
                $this->dwsProvisionReport->userId,
                $this->dwsProvisionReport->providedIn,
            );
        });
    }
}

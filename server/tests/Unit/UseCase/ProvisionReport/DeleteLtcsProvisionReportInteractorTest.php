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
use Domain\ProvisionReport\LtcsProvisionReport;
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
use UseCase\ProvisionReport\DeleteLtcsProvisionReportInteractor;

/**
 * {@link \UseCase\ProvisionReport\DeleteLtcsProvisionReportInteractor} のテスト.
 */
class DeleteLtcsProvisionReportInteractorTest extends Test
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
    private DeleteLtcsProvisionReportInteractor $interactor;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (DeleteLtcsProvisionReportInteractorTest $self): void {
            $self->getLtcsProvisionReportUseCase
                ->allows('handle')
                ->andReturn(Option::from($self->examples->ltcsProvisionReports[0]))
                ->byDefault();
            $self->ltcsProvisionReportRepository
                ->allows('removeById')
                ->andReturn($self->examples->ltcsProvisionReports[0])
                ->byDefault();
            $self->ltcsProvisionReportRepository
                ->allows('transactionManager')
                ->andReturn(PermanentDatabaseTransactionManager::class)
                ->passthru();
            $self->logger
                ->allows('info')
                ->andReturnNull()
                ->byDefault();

            $self->ltcsProvisionReport = $self->examples->ltcsProvisionReports[0];
            $self->interactor = app(DeleteLtcsProvisionReportInteractor::class);
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
                    equalTo(Permission::updateLtcsProvisionReports()),
                    $this->ltcsProvisionReport->officeId,
                    $this->ltcsProvisionReport->userId,
                    $this->ltcsProvisionReport->providedIn
                )
                ->andReturn(Option::from($this->ltcsProvisionReport));

            $this->interactor->handle(
                $this->context,
                $this->ltcsProvisionReport->officeId,
                $this->ltcsProvisionReport->userId,
                $this->ltcsProvisionReport->providedIn,
            );
        });
        $this->should('throw NotFoundException when GetLtcsProvisionReportUseCase return none', function (): void {
            $this->getLtcsProvisionReportUseCase
                ->expects('handle')
                ->with(
                    $this->context,
                    equalTo(Permission::updateLtcsProvisionReports()),
                    $this->ltcsProvisionReport->officeId,
                    $this->ltcsProvisionReport->userId,
                    $this->ltcsProvisionReport->providedIn
                )
                ->andReturn(Option::none());

            $this->assertThrows(NotFoundException::class, function (): void {
                $this->interactor->handle(
                    $this->context,
                    $this->ltcsProvisionReport->officeId,
                    $this->ltcsProvisionReport->userId,
                    $this->ltcsProvisionReport->providedIn,
                );
            });
        });
        $this->should('delete the LtcsProvisionReport after transaction begun', function (): void {
            $this->transactionManager
                ->expects('run')
                ->andReturnUsing(function (Closure $callback) {
                    $this->ltcsProvisionReportRepository
                        ->expects('removeById')
                        ->with($this->ltcsProvisionReport->id)
                        ->andReturnNull();
                    return $callback();
                });

            $this->interactor->handle(
                $this->context,
                $this->ltcsProvisionReport->officeId,
                $this->ltcsProvisionReport->userId,
                $this->ltcsProvisionReport->providedIn,
            );
        });
        $this->should('log using info', function (): void {
            $context = ['organizationId' => $this->examples->organizations[0]->id];
            $this->context
                ->expects('logContext')
                ->andReturn($context);
            $this->logger
                ->expects('info')
                ->with('介護保険サービス：予実が削除されました', ['id' => $this->ltcsProvisionReport->id] + $context);

            $this->interactor->handle(
                $this->context,
                $this->ltcsProvisionReport->officeId,
                $this->ltcsProvisionReport->userId,
                $this->ltcsProvisionReport->providedIn,
            );
        });
    }
}

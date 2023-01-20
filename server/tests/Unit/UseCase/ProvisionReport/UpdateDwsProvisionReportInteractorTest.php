<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\ProvisionReport;

use App\Concretes\PermanentDatabaseTransactionManager;
use Closure;
use Domain\Common\Carbon;
use Domain\Common\Pagination;
use Domain\Common\ServiceSegment;
use Domain\FinderResult;
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
use Tests\Unit\Mixins\FindDwsProvisionReportUseCaseMixin;
use Tests\Unit\Mixins\IdentifyContractUseCaseMixin;
use Tests\Unit\Mixins\LoggerMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\OrganizationRepositoryMixin;
use Tests\Unit\Mixins\TransactionManagerMixin;
use Tests\Unit\Test;
use UseCase\ProvisionReport\UpdateDwsProvisionReportInteractor;

/**
 * UpdateDwsProvisionReportInteractor のテスト.
 */
class UpdateDwsProvisionReportInteractorTest extends Test
{
    use CarbonMixin;
    use ContextMixin;
    use DwsProvisionReportRepositoryMixin;
    use ExamplesConsumer;
    use FindDwsProvisionReportUseCaseMixin;
    use IdentifyContractUseCaseMixin;
    use LoggerMixin;
    use MockeryMixin;
    use OrganizationRepositoryMixin;
    use TransactionManagerMixin;
    use UnitSupport;

    private DwsProvisionReport $dwsProvisionReport;
    private UpdateDwsProvisionReportInteractor $interactor;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (UpdateDwsProvisionReportInteractorTest $self): void {
            $self->identifyContractUseCase
                ->allows('handle')
                ->andReturn(Option::from($self->examples->contracts[0]))
                ->byDefault();
            $self->findDwsProvisionReportUseCase
                ->allows('handle')
                ->andReturn(FinderResult::from($self->examples->dwsProvisionReports, Pagination::create()))
                ->byDefault();
            $self->dwsProvisionReportRepository
                ->allows('store')
                ->andReturn($self->examples->dwsProvisionReports[0])
                ->byDefault();
            $self->dwsProvisionReportRepository
                ->allows('transactionManager')
                ->andReturn(PermanentDatabaseTransactionManager::class)
                ->passthru();
            $self->logger
                ->allows('info')
                ->byDefault();
            $self->dwsProvisionReport = $self->examples->dwsProvisionReports[0];
            $self->interactor = app(UpdateDwsProvisionReportInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('use IdentifyContractUseCase', function (): void {
            $this->identifyContractUseCase
                ->expects('handle')
                ->with(
                    $this->context,
                    Permission::updateDwsProvisionReports(),
                    $this->dwsProvisionReport->officeId,
                    $this->dwsProvisionReport->userId,
                    ServiceSegment::disabilitiesWelfare(),
                    equalTo($this->dwsProvisionReport->providedIn->lastOfMonth())
                )
                ->andReturn(Option::from($this->examples->contracts[0]));

            $this->interactor->handle(
                $this->context,
                $this->dwsProvisionReport->officeId,
                $this->dwsProvisionReport->userId,
                $this->dwsProvisionReport->providedIn->format('Y-m'),
                $this->payload()
            );
        });
        $this->should('throw NotFoundException when IdentifyContractUseCase return None', function (): void {
            $this->identifyContractUseCase
                ->expects('handle')
                ->andReturn(Option::none());

            $this->assertThrows(NotFoundException::class, function (): void {
                $this->interactor->handle(
                    $this->context,
                    $this->dwsProvisionReport->officeId,
                    $this->dwsProvisionReport->userId,
                    $this->dwsProvisionReport->providedIn->format('Y-m'),
                    $this->payload()
                );
            });
        });
        $this->should('use FindDwsProvisionReportUseCase', function (): void {
            $this->findDwsProvisionReportUseCase
                ->expects('handle')
                ->with(
                    $this->context,
                    Permission::updateDwsProvisionReports(),
                    [
                        'officeId' => $this->dwsProvisionReport->officeId,
                        'userId' => $this->dwsProvisionReport->userId,
                        'providedIn' => $this->dwsProvisionReport->providedIn,
                    ],
                    ['all' => true],
                )
                ->andReturn(FinderResult::from([$this->dwsProvisionReport], Pagination::create()));

            $this->interactor->handle(
                $this->context,
                $this->dwsProvisionReport->officeId,
                $this->dwsProvisionReport->userId,
                $this->dwsProvisionReport->providedIn->format('Y-m'),
                $this->payload()
            );
        });
        $this->should('edit the DwsProvisionReport after transaction begun', function (): void {
            $this->transactionManager
                ->expects('run')
                ->andReturnUsing(function (Closure $callback) {
                    $this->dwsProvisionReportRepository
                        ->expects('store')
                        ->with(equalTo($this->dwsProvisionReport->copy(['updatedAt' => Carbon::now()] + $this->payload())))
                        ->andReturn($this->dwsProvisionReport);
                    return $callback();
                });

            $this->interactor->handle(
                $this->context,
                $this->dwsProvisionReport->officeId,
                $this->dwsProvisionReport->userId,
                $this->dwsProvisionReport->providedIn->format('Y-m'),
                $this->payload()
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
                $this->payload()
            );
        });
        $this->should('create the DwsProvisionReport after transaction begun when FindDwsProvisionReportUseCase return empty list', function (): void {
            $this->findDwsProvisionReportUseCase
                ->expects('handle')
                ->andReturn(FinderResult::from([], Pagination::create()));
            $this->dwsProvisionReportRepository
                ->expects('store')
                ->with(equalTo(DwsProvisionReport::create(
                    [
                        'officeId' => $this->dwsProvisionReport->officeId,
                        'userId' => $this->dwsProvisionReport->userId,
                        'contractId' => $this->examples->contracts[0]->id,
                        'providedIn' => Carbon::parse($this->dwsProvisionReport->providedIn->format('Y-m')),
                        'status' => DwsProvisionReportStatus::inProgress(),
                        'createdAt' => Carbon::now(),
                        'updatedAt' => Carbon::now(),
                    ] + $this->payload()
                )))
                ->andReturn($this->examples->dwsProvisionReports[0]);

            $this->interactor->handle(
                $this->context,
                $this->dwsProvisionReport->officeId,
                $this->dwsProvisionReport->userId,
                $this->dwsProvisionReport->providedIn->format('Y-m'),
                $this->payload()
            );
        });
        $this->should('log using info when FindDwsProvisionReportUseCase return empty list', function (): void {
            $this->findDwsProvisionReportUseCase
                ->expects('handle')
                ->andReturn(FinderResult::from([], Pagination::create()));
            $context = ['organizationId' => $this->examples->organizations[0]->id];
            $this->context
                ->expects('logContext')
                ->andReturn($context);
            $this->logger
                ->expects('info')
                ->with('障害福祉サービス：予実が登録されました', ['id' => $this->dwsProvisionReport->id] + $context);

            $this->interactor->handle(
                $this->context,
                $this->dwsProvisionReport->officeId,
                $this->dwsProvisionReport->userId,
                $this->dwsProvisionReport->providedIn->format('Y-m'),
                $this->payload()
            );
        });
    }

    /**
     * 更新時のペイロード.
     *
     * @return array
     */
    private function payload(): array
    {
        return [
            'plans' => $this->dwsProvisionReport->plans,
            'results' => $this->dwsProvisionReport->results,
        ];
    }
}

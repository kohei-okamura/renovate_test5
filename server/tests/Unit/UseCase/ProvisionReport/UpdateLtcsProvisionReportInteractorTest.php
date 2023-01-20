<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\ProvisionReport;

use Closure;
use Domain\Common\Carbon;
use Domain\Common\Pagination;
use Domain\Common\ServiceSegment;
use Domain\FinderResult;
use Domain\Permission\Permission;
use Domain\ProvisionReport\LtcsProvisionReport;
use Domain\ProvisionReport\LtcsProvisionReportStatus;
use Lib\Exceptions\NotFoundException;
use Lib\Json;
use ScalikePHP\Option;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\FindLtcsProvisionReportUseCaseMixin;
use Tests\Unit\Mixins\IdentifyContractUseCaseMixin;
use Tests\Unit\Mixins\LoggerMixin;
use Tests\Unit\Mixins\LookupOfficeUseCaseMixin;
use Tests\Unit\Mixins\LtcsProvisionReportRepositoryMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\OrganizationRepositoryMixin;
use Tests\Unit\Mixins\TransactionManagerMixin;
use Tests\Unit\Test;
use UseCase\ProvisionReport\UpdateLtcsProvisionReportInteractor;

class UpdateLtcsProvisionReportInteractorTest extends Test
{
    use CarbonMixin;
    use ContextMixin;
    use ExamplesConsumer;
    use FindLtcsProvisionReportUseCaseMixin;
    use IdentifyContractUseCaseMixin;
    use LoggerMixin;
    use LookupOfficeUseCaseMixin;
    use LtcsProvisionReportRepositoryMixin;
    use MockeryMixin;
    use OrganizationRepositoryMixin;
    use TransactionManagerMixin;
    use UnitSupport;

    private LtcsProvisionReport $ltcsProvisionReport;
    private UpdateLtcsProvisionReportInteractor $interactor;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (UpdateLtcsProvisionReportInteractorTest $self): void {
            $self->findLtcsProvisionReportUseCase
                ->allows('handle')
                ->andReturn(FinderResult::from([$self->examples->ltcsProvisionReports[0]], Pagination::create()))
                ->byDefault();
            $self->identifyContractUseCase
                ->allows('handle')
                ->andReturn(Option::from($self->examples->contracts[0]))
                ->byDefault();
            $self->logger
                ->allows('info')
                ->byDefault();
            $self->lookupOfficeUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->examples->offices[0]))
                ->byDefault();
            $self->ltcsProvisionReportRepository
                ->allows('store')
                ->andReturn($self->examples->ltcsProvisionReports[0])
                ->byDefault();

            $self->ltcsProvisionReport = $self->examples->ltcsProvisionReports[0];
            $self->interactor = app(UpdateLtcsProvisionReportInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('use LookupOfficeUseCase', function (): void {
            $this->lookupOfficeUseCase
                ->expects('handle')
                ->with(
                    $this->context,
                    [Permission::updateLtcsProvisionReports()],
                    $this->ltcsProvisionReport->officeId,
                )
                ->andReturn(Seq::from($this->examples->offices[0]));

            $this->interactor->handle(
                $this->context,
                $this->ltcsProvisionReport->officeId,
                $this->ltcsProvisionReport->userId,
                $this->ltcsProvisionReport->providedIn->format('Y-m'),
                $this->payload()
            );
        });
        $this->should('throw NotFoundException when LookupOfficeUseCase return None', function (): void {
            $this->lookupOfficeUseCase
                ->expects('handle')
                ->andReturn(Seq::emptySeq());

            $this->assertThrows(NotFoundException::class, function (): void {
                $this->interactor->handle(
                    $this->context,
                    $this->ltcsProvisionReport->officeId,
                    $this->ltcsProvisionReport->userId,
                    $this->ltcsProvisionReport->providedIn->format('Y-m'),
                    $this->payload()
                );
            });
        });
        $this->should('use IdentifyContractUseCase', function (): void {
            $this->identifyContractUseCase
                ->expects('handle')
                ->with(
                    $this->context,
                    Permission::updateLtcsProvisionReports(),
                    $this->ltcsProvisionReport->officeId,
                    $this->ltcsProvisionReport->userId,
                    ServiceSegment::longTermCare(),
                    equalTo(Carbon::now())
                )
                ->andReturn(Option::from($this->examples->contracts[0]));

            $this->interactor->handle(
                $this->context,
                $this->ltcsProvisionReport->officeId,
                $this->ltcsProvisionReport->userId,
                $this->ltcsProvisionReport->providedIn->format('Y-m'),
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
                    $this->ltcsProvisionReport->officeId,
                    $this->ltcsProvisionReport->userId,
                    $this->ltcsProvisionReport->providedIn->format('Y-m'),
                    $this->payload()
                );
            });
        });
        $this->should('use FindLtcsProvisionReportUseCase', function (): void {
            $this->findLtcsProvisionReportUseCase
                ->expects('handle')
                ->with(
                    $this->context,
                    Permission::updateLtcsProvisionReports(),
                    [
                        'officeId' => $this->ltcsProvisionReport->officeId,
                        'userId' => $this->ltcsProvisionReport->userId,
                        'providedIn' => $this->ltcsProvisionReport->providedIn,
                    ],
                    ['all' => true],
                )
                ->andReturn(FinderResult::from([$this->ltcsProvisionReport], Pagination::create()));

            $this->interactor->handle(
                $this->context,
                $this->ltcsProvisionReport->officeId,
                $this->ltcsProvisionReport->userId,
                $this->ltcsProvisionReport->providedIn->format('Y-m'),
                $this->payload()
            );
        });
        $this->should('edit the LtcsProvisionReport after transaction begun', function (): void {
            $homeVisitLongTermCareCalcSpec = $this->examples->homeVisitLongTermCareCalcSpecs[0];
            $this->transactionManager
                ->expects('run')
                ->andReturnUsing(function (Closure $callback) {
                    $this->ltcsProvisionReportRepository
                        ->expects('store')
                        ->with(equalTo($this->ltcsProvisionReport->copy(['updatedAt' => Carbon::now()] + $this->payload())))
                        ->andReturn($this->ltcsProvisionReport);
                    return $callback();
                });

            $this->interactor->handle(
                $this->context,
                $this->ltcsProvisionReport->officeId,
                $this->ltcsProvisionReport->userId,
                $this->ltcsProvisionReport->providedIn->format('Y-m'),
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
                ->with('介護保険サービス：予実が更新されました', ['id' => $this->ltcsProvisionReport->id] + $context);

            $this->interactor->handle(
                $this->context,
                $this->ltcsProvisionReport->officeId,
                $this->ltcsProvisionReport->userId,
                $this->ltcsProvisionReport->providedIn->format('Y-m'),
                $this->payload()
            );
        });
        $this->should('create the LtcsProvisionReport after transaction begun when FindLtcsProvisionReportUseCase return empty list', function (): void {
            $homeVisitLongTermCareCalcSpec = $this->examples->homeVisitLongTermCareCalcSpecs[0];
            $this->findLtcsProvisionReportUseCase
                ->expects('handle')
                ->andReturn(FinderResult::from([], Pagination::create()));
            $this->ltcsProvisionReportRepository
                ->expects('store')
                ->with(equalTo(LtcsProvisionReport::create(
                    [
                        'officeId' => $this->ltcsProvisionReport->officeId,
                        'userId' => $this->ltcsProvisionReport->userId,
                        'contractId' => $this->examples->contracts[0]->id,
                        'providedIn' => Carbon::parse($this->ltcsProvisionReport->providedIn->format('Y-m')),
                        'status' => LtcsProvisionReportStatus::inProgress(),
                        'createdAt' => Carbon::now(),
                        'updatedAt' => Carbon::now(),
                    ] + $this->payload()
                )))
                ->andReturn($this->examples->ltcsProvisionReports[0]);

            $this->interactor->handle(
                $this->context,
                $this->ltcsProvisionReport->officeId,
                $this->ltcsProvisionReport->userId,
                $this->ltcsProvisionReport->providedIn->format('Y-m'),
                $this->payload()
            );
        });
        $this->should('log using info when FindLtcsProvisionReportUseCase return empty list', function (): void {
            $this->findLtcsProvisionReportUseCase
                ->expects('handle')
                ->andReturn(FinderResult::from([], Pagination::create()));
            $context = ['organizationId' => $this->examples->organizations[0]->id];
            $this->context
                ->expects('logContext')
                ->andReturn($context);
            $this->logger
                ->expects('info')
                ->with('介護保険サービス：予実が登録されました', ['id' => $this->ltcsProvisionReport->id] + $context);

            $this->interactor->handle(
                $this->context,
                $this->ltcsProvisionReport->officeId,
                $this->ltcsProvisionReport->userId,
                $this->ltcsProvisionReport->providedIn->format('Y-m'),
                $this->payload()
            );
        });
    }

    /**
     * payload が返す配列.
     *
     * @return array
     */
    private function payload(): array
    {
        return Json::decode(
            Json::encode($this->examples->ltcsProvisionReports[0]->copy([
                'officeId' => null,
                'userId' => null,
                'contractId' => null,
                'providedIn' => null,
            ])),
            true
        );
    }
}

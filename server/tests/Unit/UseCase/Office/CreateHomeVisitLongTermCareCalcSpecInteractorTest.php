<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\Office;

use App\Concretes\PermanentDatabaseTransactionManager;
use Closure;
use Domain\Common\Pagination;
use Domain\FinderResult;
use Domain\Permission\Permission;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\EnsureOfficeUseCaseMixin;
use Tests\Unit\Mixins\FindLtcsProvisionReportUseCaseMixin;
use Tests\Unit\Mixins\HomeVisitLongTermCareCalcSpecRepositoryMixin;
use Tests\Unit\Mixins\LoggerMixin;
use Tests\Unit\Mixins\LookupOfficeUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\TransactionManagerMixin;
use Tests\Unit\Test;
use UseCase\Office\CreateHomeVisitLongTermCareCalcSpecInteractor;

/**
 * CreateHomeVisitLongTermCareCalcSpecInteractor のテスト
 */
class CreateHomeVisitLongTermCareCalcSpecInteractorTest extends Test
{
    use CarbonMixin;
    use ContextMixin;
    use EnsureOfficeUseCaseMixin;
    use ExamplesConsumer;
    use FindLtcsProvisionReportUseCaseMixin;
    use HomeVisitLongTermCareCalcSpecRepositoryMixin;
    use LookupOfficeUseCaseMixin;
    use LoggerMixin;
    use MockeryMixin;
    use TransactionManagerMixin;
    use UnitSupport;

    private CreateHomeVisitLongTermCareCalcSpecInteractor $interactor;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (CreateHomeVisitLongTermCareCalcSpecInteractorTest $self): void {
            $self->ensureOfficeUseCase
                ->allows('handle')
                ->andReturnNull()
                ->byDefault();

            $self->findLtcsProvisionReportUseCase
                ->allows('handle')
                ->andReturn(FinderResult::from(Seq::from($self->examples->ltcsProvisionReports[0]), Pagination::create()))
                ->byDefault();

            $self->homeVisitLongTermCareCalcSpecRepository
                ->allows('store')
                ->andReturn($self->examples->homeVisitLongTermCareCalcSpecs[0])
                ->byDefault();

            $self->homeVisitLongTermCareCalcSpecRepository
                ->allows('transactionManager')
                ->andReturn(PermanentDatabaseTransactionManager::class)
                ->passthru();

            $self->lookupOfficeUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->examples->offices[0]))
                ->byDefault();

            $self->logger
                ->allows('info')
                ->byDefault();

            $self->interactor = app(CreateHomeVisitLongTermCareCalcSpecInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('store the HomeVisitLongTermCareCalcSpec after transaction begun', function (): void {
            $this->transactionManager
                ->expects('run')
                ->andReturnUsing(function (Closure $callback) {
                    // トランザクションを開始する前に登録処理が行われないことを検証するため
                    // `run` に渡されたコールバック関数を呼び出す直前にモックの定義を行う
                    // なお、トランザクションの終了後に登録処理が行われないことの検証は（恐らく）できない
                    $this->homeVisitLongTermCareCalcSpecRepository
                        ->expects('store')
                        ->andReturn($this->examples->homeVisitLongTermCareCalcSpecs[0]);
                    return $callback();
                });

            $this->interactor->handle($this->context, $this->examples->offices[0]->id, $this->examples->homeVisitLongTermCareCalcSpecs[0]);
        });
        $this->should('return the HomeVisitLongTermCareCalcSpec', function (): void {
            $actual = $this->interactor->handle($this->context, $this->examples->offices[0]->id, $this->examples->homeVisitLongTermCareCalcSpecs[0]);
            $this->assertEquals($this->examples->homeVisitLongTermCareCalcSpecs[0], $actual['homeVisitLongTermCareCalcSpec']);
            $this->assertEquals(1, $actual['provisionReportCount']);
        });
        $this->should('log using info', function (): void {
            $context = ['organizationId' => $this->examples->organizations[0]->id];
            $this->context
                ->expects('logContext')
                ->andReturn($context);
            $this->logger
                ->expects('info')
                ->with('事業所算定情報（介保・訪問介護）が登録されました', ['id' => $this->examples->homeVisitLongTermCareCalcSpecs[0]->id] + $context)
                ->andReturnNull();

            $this->interactor->handle($this->context, $this->examples->offices[0]->id, $this->examples->homeVisitLongTermCareCalcSpecs[0]);
        });
        $this->should('use EnsureOfficeUseCase', function (): void {
            $this->ensureOfficeUseCase
                ->expects('handle')
                ->with($this->context, [Permission::createInternalOffices()], $this->examples->offices[0]->id)
                ->andReturnNull();

            $this->interactor->handle(
                $this->context,
                $this->examples->offices[0]->id,
                $this->examples->homeVisitLongTermCareCalcSpecs[0],
            );
        });
        $this->should('use findLtcsProvisionReportUseCase', function (): void {
            $filterParams = [
                'officeId' => $this->examples->offices[0]->id,
                'provideInForBetween' => $this->examples->homeVisitLongTermCareCalcSpecs[0]->period,
            ];
            $this->findLtcsProvisionReportUseCase
                ->expects('handle')
                ->with(
                    $this->context,
                    Permission::createInternalOffices(),
                    equalTo($filterParams),
                    ['all' => true],
                )
                ->andReturn(FinderResult::from(Seq::from($this->examples->ltcsProvisionReports[0]), Pagination::create()));
            $this->interactor->handle(
                $this->context,
                $this->examples->offices[0]->id,
                $this->examples->homeVisitLongTermCareCalcSpecs[0],
            );
        });
    }
}

<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\Office;

use App\Concretes\PermanentDatabaseTransactionManager;
use Closure;
use Domain\Office\VisitingCareForPwsdCalcSpec;
use Domain\Permission\Permission;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\EnsureOfficeUseCaseMixin;
use Tests\Unit\Mixins\LoggerMixin;
use Tests\Unit\Mixins\LookupOfficeUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\TransactionManagerMixin;
use Tests\Unit\Mixins\VisitingCareForPwsdCalcSpecRepositoryMixin;
use Tests\Unit\Test;
use UseCase\Office\CreateVisitingCareForPwsdCalcSpecInteractor;

/**
 * CreateVisitingCareForPwsdCalcSpecInteractor のテスト.
 */
class CreateVisitingCareForPwsdCalcSpecInteractorTest extends Test
{
    use CarbonMixin;
    use ContextMixin;
    use EnsureOfficeUseCaseMixin;
    use ExamplesConsumer;
    use LookupOfficeUseCaseMixin;
    use LoggerMixin;
    use MockeryMixin;
    use TransactionManagerMixin;
    use UnitSupport;
    use VisitingCareForPwsdCalcSpecRepositoryMixin;

    private CreateVisitingCareForPwsdCalcSpecInteractor $interactor;
    private VisitingCareForPwsdCalcSpec $visitingCareForPwsdCalcSpec;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (CreateVisitingCareForPwsdCalcSpecInteractorTest $self): void {
            $self->visitingCareForPwsdCalcSpec = $self->examples->visitingCareForPwsdCalcSpecs[0];

            $self->visitingCareForPwsdCalcSpecRepository
                ->allows('store')
                ->andReturn($self->visitingCareForPwsdCalcSpec)
                ->byDefault();

            $self->visitingCareForPwsdCalcSpecRepository
                ->allows('transactionManager')
                ->andReturn(PermanentDatabaseTransactionManager::class)
                ->passthru();

            $self->ensureOfficeUseCase
                ->allows('handle')
                ->andReturnNull()
                ->byDefault();

            $self->logger
                ->allows('info')
                ->byDefault();

            $self->interactor = app(CreateVisitingCareForPwsdCalcSpecInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('store the VisitingCareForPwsdCalcSpec after transaction begun', function (): void {
            $this->transactionManager
                ->expects('run')
                ->andReturnUsing(function (Closure $callback) {
                    // トランザクションを開始する前に登録処理が行われないことを検証するため
                    // `run` に渡されたコールバック関数を呼び出す直前にモックの定義を行う
                    // なお、トランザクションの終了後に登録処理が行われないことの検証は（恐らく）できない
                    $this->visitingCareForPwsdCalcSpecRepository
                        ->expects('store')
                        ->withArgs(function (VisitingCareForPwsdCalcSpec $x) {
                            $this->assertEquals($this->visitingCareForPwsdCalcSpec->officeId, $x->officeId);
                            $this->assertEquals($this->visitingCareForPwsdCalcSpec->id, $x->id);
                            return true;
                        })
                        ->andReturn($this->visitingCareForPwsdCalcSpec);
                    return $callback();
                });

            $this->interactor->handle($this->context, $this->visitingCareForPwsdCalcSpec->officeId, $this->visitingCareForPwsdCalcSpec);
        });
        $this->should('return the VisitingCareForPwsdCalcSpec', function (): void {
            $this->assertModelStrictEquals(
                $this->visitingCareForPwsdCalcSpec,
                $this->interactor->handle($this->context, $this->visitingCareForPwsdCalcSpec->officeId, $this->visitingCareForPwsdCalcSpec)
            );
        });
        $this->should('log using info', function (): void {
            $context = ['organizationId' => $this->examples->organizations[0]->id];
            $this->context
                ->expects('logContext')
                ->andReturn($context);
            $this->logger
                ->expects('info')
                ->with('事業所算定情報（障害・重度訪問介護）が登録されました', ['id' => $this->visitingCareForPwsdCalcSpec->id] + $context)
                ->andReturnNull();

            $this->interactor->handle($this->context, $this->visitingCareForPwsdCalcSpec->officeId, $this->visitingCareForPwsdCalcSpec);
        });
        $this->should('use EnsureOfficeUseCase', function (): void {
            $this->ensureOfficeUseCase
                ->expects('handle')
                ->with($this->context, [Permission::createInternalOffices()], $this->visitingCareForPwsdCalcSpec->officeId)
                ->andReturnNull();

            $this->interactor->handle(
                $this->context,
                $this->visitingCareForPwsdCalcSpec->officeId,
                $this->visitingCareForPwsdCalcSpec
            );
        });
    }
}

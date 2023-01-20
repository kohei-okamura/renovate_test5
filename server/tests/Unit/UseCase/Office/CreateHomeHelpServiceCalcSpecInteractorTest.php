<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\Office;

use App\Concretes\PermanentDatabaseTransactionManager;
use Closure;
use Domain\Office\HomeHelpServiceCalcSpec;
use Domain\Permission\Permission;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\EnsureOfficeUseCaseMixin;
use Tests\Unit\Mixins\HomeHelpServiceCalcSpecRepositoryMixin;
use Tests\Unit\Mixins\LoggerMixin;
use Tests\Unit\Mixins\LookupOfficeUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\TransactionManagerMixin;
use Tests\Unit\Test;
use UseCase\Office\CreateHomeHelpServiceCalcSpecInteractor;

/**
 * CreateHomeHelpServiceCalcSpecInteractor のテスト.
 */
class CreateHomeHelpServiceCalcSpecInteractorTest extends Test
{
    use CarbonMixin;
    use ContextMixin;
    use EnsureOfficeUseCaseMixin;
    use ExamplesConsumer;
    use HomeHelpServiceCalcSpecRepositoryMixin;
    use LoggerMixin;
    use LookupOfficeUseCaseMixin;
    use MockeryMixin;
    use TransactionManagerMixin;
    use UnitSupport;

    private CreateHomeHelpServiceCalcSpecInteractor $interactor;
    private HomeHelpServiceCalcSpec $homeHelpServiceCalcSpec;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (CreateHomeHelpServiceCalcSpecInteractorTest $self): void {
            $self->homeHelpServiceCalcSpec = $self->examples->homeHelpServiceCalcSpecs[0];

            $self->ensureOfficeUseCase
                ->allows('handle')
                ->andReturnNull()
                ->byDefault();

            $self->homeHelpServiceCalcSpecRepository
                ->allows('store')
                ->andReturn($self->homeHelpServiceCalcSpec)
                ->byDefault();

            $self->homeHelpServiceCalcSpecRepository
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

            $self->interactor = app(CreateHomeHelpServiceCalcSpecInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('store the HomeHelpServiceCalcSpec after transaction begun', function (): void {
            $this->transactionManager
                ->expects('run')
                ->andReturnUsing(function (Closure $callback) {
                    // トランザクションを開始する前に登録処理が行われないことを検証するため
                    // `run` に渡されたコールバック関数を呼び出す直前にモックの定義を行う
                    // なお、トランザクションの終了後に登録処理が行われないことの検証は（恐らく）できない
                    $expects = $this->homeHelpServiceCalcSpec;
                    $this->homeHelpServiceCalcSpecRepository
                        ->expects('store')
                        ->with(equalTo($expects))
                        ->andReturn($this->homeHelpServiceCalcSpec);
                    return $callback();
                });

            $this->interactor->handle($this->context, $this->homeHelpServiceCalcSpec->officeId, $this->homeHelpServiceCalcSpec);
        });
        $this->should('return the HomeHelpServiceCalcSpec', function (): void {
            $this->assertModelStrictEquals(
                $this->homeHelpServiceCalcSpec,
                $this->interactor->handle($this->context, $this->homeHelpServiceCalcSpec->officeId, $this->homeHelpServiceCalcSpec)
            );
        });
        $this->should('log using info', function (): void {
            $context = ['organizationId' => $this->examples->organizations[0]->id];
            $this->context
                ->expects('logContext')
                ->andReturn($context);
            $this->logger
                ->expects('info')
                ->with('事業所算定情報（障害・居宅介護）が登録されました', ['id' => $this->homeHelpServiceCalcSpec->id] + $context)
                ->andReturnNull();

            $this->interactor->handle($this->context, $this->homeHelpServiceCalcSpec->officeId, $this->homeHelpServiceCalcSpec);
        });
        $this->should('use EnsureOfficeUseCase', function (): void {
            $this->ensureOfficeUseCase
                ->expects('handle')
                ->with($this->context, [Permission::createInternalOffices()], $this->examples->offices[0]->id)
                ->andReturnNull();

            $this->interactor->handle(
                $this->context,
                $this->examples->offices[0]->id,
                $this->homeHelpServiceCalcSpec
            );
        });
    }
}

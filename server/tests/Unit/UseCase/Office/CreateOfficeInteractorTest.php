<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\Office;

use App\Concretes\PermanentDatabaseTransactionManager;
use Closure;
use Domain\Office\Office;
use Mockery;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\LoggerMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\OfficeRepositoryMixin;
use Tests\Unit\Mixins\TransactionManagerMixin;
use Tests\Unit\Test;
use UseCase\Office\CreateOfficeInteractor;

/**
 * CreateOfficeInteractor のテスト.
 */
class CreateOfficeInteractorTest extends Test
{
    use CarbonMixin;
    use ContextMixin;
    use ExamplesConsumer;
    use LoggerMixin;
    use MockeryMixin;
    use OfficeRepositoryMixin;
    use TransactionManagerMixin;
    use UnitSupport;

    /**
     * @var callable|\Closure|\Mockery\MockInterface
     */
    private $callable;
    private CreateOfficeInteractor $interactor;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (CreateOfficeInteractorTest $self): void {
            $self->officeRepository
                ->allows('store')
                ->andReturn($self->examples->offices[0])
                ->byDefault();
            $self->officeRepository
                ->allows('transactionManager')
                ->andReturn(PermanentDatabaseTransactionManager::class)
                ->passthru();
            $self->logger
                ->allows('info')
                ->byDefault();

            $self->callable = Mockery::spy(fn (Office $office) => 'RUN CALLBACK');

            $self->interactor = app(CreateOfficeInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('store the Office after transaction begun', function (): void {
            $this->transactionManager
                ->expects('run')
                ->andReturnUsing(function (Closure $callback) {
                    // トランザクションを開始する前に登録処理が行われないことを検証するため
                    // `run` に渡されたコールバック関数を呼び出す直前にモックの定義を行う
                    // なお、トランザクションの終了後に登録処理が行われないことの検証は（恐らく）できない
                    $this->officeRepository->expects('store')->andReturn($this->examples->offices[0]);
                    return $callback();
                });

            $this->interactor->handle($this->context, $this->examples->offices[0], $this->callable);
        });
        $this->should('return the Office', function (): void {
            $this->assertModelStrictEquals(
                $this->examples->offices[0],
                $this->interactor->handle($this->context, $this->examples->offices[0], $this->callable)
            );
        });
        $this->should('log using info', function (): void {
            $context = ['organizationId' => $this->examples->organizations[0]->id];
            $this->context
                ->expects('logContext')
                ->andReturn($context);
            $this->logger
                ->expects('info')
                ->with('事業所が登録されました', ['id' => $this->examples->offices[0]->id] + $context);

            $this->interactor->handle($this->context, $this->examples->offices[0], $this->callable);
        });
        $this->should('call callable function', function (): void {
            $this->interactor->handle($this->context, $this->examples->offices[0], $this->callable);
            $this->callable->shouldHaveBeenCalled();
        });
    }
}

<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\Job;

use Closure;
use Domain\Job\Job;
use Lib\Exceptions\UnauthorizedException;
use Mockery;
use ScalikePHP\Option;
use Tests\Unit\App\Http\Concretes\TestingContext;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\JobRepositoryMixin;
use Tests\Unit\Mixins\LoggerMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\TokenMakerMixin;
use Tests\Unit\Mixins\TransactionManagerMixin;
use Tests\Unit\Test;
use UseCase\Job\CreateJobInteractor;

/**
 * CreateJobInteractorのTest.
 */
class CreateJobInteractorTest extends Test
{
    use CarbonMixin;
    use ContextMixin;
    use ExamplesConsumer;
    use JobRepositoryMixin;
    use LoggerMixin;
    use MockeryMixin;
    use TokenMakerMixin;
    use TransactionManagerMixin;
    use UnitSupport;

    /**
     * @var callable|\Closure|\Mockery\MockInterface
     */
    private $callable;
    private CreateJobInteractor $interactor;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (CreateJobInteractorTest $self): void {
            $self->jobRepository
                ->allows('lookupOptionByToken')
                ->andReturn(Option::none())
                ->byDefault();
            $self->jobRepository
                ->allows('store')
                ->andReturn($self->examples->jobs[0])
                ->byDefault();
            $self->tokenMaker
                ->allows('make')
                ->andReturn('test_token')
                ->byDefault();
            $self->logger
                ->allows('info')
                ->byDefault();

            $self->callable = Mockery::spy(fn (Job $job) => 'RUN CALLBACK');

            $self->interactor = app(CreateJobInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('return the Job', function (): void {
            $context = new TestingContext();
            TestingContext::prepare($context, $this->examples->organizations[0], Option::none());
            $this->assertThrows(
                UnauthorizedException::class,
                function () use ($context) {
                    $this->interactor->handle($context, $this->callable);
                }
            );
        });
        $this->should('store the Job after transaction begun', function (): void {
            $this->transactionManager
                ->expects('run')
                ->andReturnUsing(function (Closure $callback) {
                    // トランザクションを開始する前に登録処理が行われないことを検証するため
                    // `run` に渡されたコールバック関数を呼び出す直前にモックの定義を行う
                    // なお、トランザクションの終了後に登録処理が行われないことの検証は（恐らく）できない
                    $this->jobRepository->expects('store')->andReturn($this->examples->jobs[0]);
                    return $callback();
                });
            $this->interactor->handle($this->context, $this->callable);
        });
        $this->should('return the Job', function (): void {
            $this->assertModelStrictEquals(
                $this->examples->jobs[0],
                $this->interactor->handle($this->context, $this->callable)
            );
        });
        $this->should('log using info', function (): void {
            $context = ['organizationId' => $this->examples->organizations[0]->id];
            $this->context
                ->expects('logContext')
                ->andReturn($context);
            $this->logger
                ->expects('info')
                ->with('ジョブが登録されました', ['id' => $this->examples->jobs[0]->id] + $context);

            $this->interactor->handle($this->context, $this->callable);
        });
        $this->should('call callable function', function (): void {
            $this->interactor->handle($this->context, $this->callable);
            $this->callable->shouldHaveBeenCalled();
        });
    }
}

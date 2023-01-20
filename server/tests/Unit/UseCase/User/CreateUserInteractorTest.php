<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\User;

use App\Concretes\PermanentDatabaseTransactionManager;
use Closure;
use Domain\User\User;
use Mockery;
use ScalikePHP\Option;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\BankAccountRepositoryMixin;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\JobsDispatcherMixin;
use Tests\Unit\Mixins\LocationResolverMixin;
use Tests\Unit\Mixins\LoggerMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\TransactionManagerMixin;
use Tests\Unit\Mixins\UserRepositoryMixin;
use Tests\Unit\Test;
use UseCase\User\CreateUserInteractor;

/**
 * CreateUserInteractor のテスト.
 */
class CreateUserInteractorTest extends Test
{
    use BankAccountRepositoryMixin;
    use CarbonMixin;
    use ContextMixin;
    use ExamplesConsumer;
    use JobsDispatcherMixin;
    use LoggerMixin;
    use LocationResolverMixin;
    use MockeryMixin;
    use TransactionManagerMixin;
    use UnitSupport;
    use UserRepositoryMixin;

    /**
     * @var callable|\Closure|\Mockery\MockInterface
     */
    private $callable;
    private CreateUserInteractor $interactor;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (CreateUserInteractorTest $self): void {
            $self->bankAccountRepository
                ->allows('store')
                ->andReturn($self->examples->bankAccounts[0])
                ->byDefault();
            $self->userRepository
                ->allows('store')
                ->andReturn($self->examples->users[0])
                ->byDefault();
            $self->userRepository
                ->allows('transactionManager')
                ->andReturn(PermanentDatabaseTransactionManager::class)
                ->passthru();
            $self->resolver
                ->allows('resolve')
                ->andReturn(Option::from($self->examples->users[0]->location))
                ->byDefault();
            $self->logger
                ->allows('info')
                ->byDefault();

            $self->callable = Mockery::spy(fn (User $use) => 'RUN CALLBACK');

            $self->interactor = app(CreateUserInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('store the User after transaction begun', function (): void {
            $this->transactionManager
                ->expects('run')
                ->andReturnUsing(function (Closure $callback) {
                    // トランザクションを開始する前に登録処理が行われないことを検証するため
                    // `run` に渡されたコールバック関数を呼び出す直前にモックの定義を行う
                    // なお、トランザクションの終了後に登録処理が行われないことの検証は（恐らく）できない
                    $this->bankAccountRepository->expects('store')->andReturn($this->examples->bankAccounts[0]);
                    $this->userRepository->expects('store')->andReturn($this->examples->users[0]);
                    return $callback();
                });

            $this->interactor->handle($this->context, $this->examples->users[0], $this->callable);
        });
        $this->should('log using info', function (): void {
            $context = ['organizationId' => $this->examples->organizations[0]->id];
            $this->context
                ->expects('logContext')
                ->andReturn($context);
            $this->logger
                ->expects('info')
                ->with('利用者が登録されました', ['id' => $this->examples->users[0]->id] + $context);

            $this->interactor->handle($this->context, $this->examples->users[0], $this->callable);
        });
        $this->should('call callable function', function (): void {
            $this->interactor->handle($this->context, $this->examples->users[0], $this->callable);
            $this->callable->shouldHaveBeenCalled();
        });
    }
}

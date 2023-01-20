<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\User;

use Closure;
use Domain\Permission\Permission;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\EnsureUserUseCaseMixin;
use Tests\Unit\Mixins\LoggerMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\TransactionManagerMixin;
use Tests\Unit\Mixins\UserDwsSubsidyRepositoryMixin;
use Tests\Unit\Test;
use UseCase\User\CreateUserDwsSubsidyInteractor;

/**
 * CreateUserDwsSubsidyInteractor のテスト
 */
final class CreateUserDwsSubsidyInteractorTest extends Test
{
    use ContextMixin;
    use EnsureUserUseCaseMixin;
    use ExamplesConsumer;
    use LoggerMixin;
    use MockeryMixin;
    use UserDwsSubsidyRepositoryMixin;
    use TransactionManagerMixin;
    use UnitSupport;

    private CreateUserDwsSubsidyInteractor $interactor;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (CreateUserDwsSubsidyInteractorTest $self): void {
            $self->ensureUserUseCase
                ->allows('handle')
                ->andReturnNull()
                ->byDefault();
            $self->userDwsSubsidyRepository
                ->allows('store')
                ->andReturn($self->examples->userDwsSubsidies[0])
                ->byDefault();
            $self->logger
                ->allows('info')
                ->andReturnNull()
                ->byDefault();

            $self->interactor = app(CreateUserDwsSubsidyInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('use EnsureUserUseCase', function (): void {
            $this->ensureUserUseCase
                ->expects('handle')
                ->with($this->context, Permission::createUserDwsSubsidies(), $this->examples->users[0]->id)
                ->andReturnNull();

            $this->interactor->handle($this->context, $this->examples->users[0]->id, $this->examples->userDwsSubsidies[0]);
        });
        $this->should('return the UserDwsSubsidy', function (): void {
            $this->assertModelStrictEquals(
                $this->examples->userDwsSubsidies[0],
                $this->interactor->handle(
                    $this->context,
                    $this->examples->users[0]->id,
                    $this->examples->userDwsSubsidies[0]
                )
            );
        });
        $this->should('store the UserDwsSubsidy after transaction begun', function (): void {
            $this->transactionManager
                ->expects('run')
                ->andReturnUsing(function (Closure $callback) {
                    // トランザクションを開始する前に登録処理が行われないことを検証するため
                    // `run` に渡されたコールバック関数を呼び出す直前にモックの定義を行う
                    // なお、トランザクションの終了後に登録処理が行われないことの検証は（恐らく）できない
                    $this->userDwsSubsidyRepository->expects('store')->andReturn($this->examples->userDwsSubsidies[0]);
                    return $callback();
                });

            $this->interactor->handle($this->context, $this->examples->users[0]->id, $this->examples->userDwsSubsidies[0]);
        });
        $this->should('log using info', function (): void {
            $context = ['organizationId' => $this->examples->organizations[0]->id];
            $this->context
                ->expects('logContext')
                ->andReturn($context);
            $this->logger
                ->expects('info')
                ->with('自治体助成情報が登録されました', ['id' => $this->examples->userDwsSubsidies[0]->id] + $context);

            $this->interactor->handle($this->context, $this->examples->users[0]->id, $this->examples->userDwsSubsidies[0]);
        });
        $this->should('use logger output log', function (): void {
            $this->logger
                ->expects('info')
                ->with('自治体助成情報が登録されました', typeOf('array'))
                ->andReturnNull();

            $this->assertNotNull($this->interactor->handle($this->context, $this->examples->users[0]->id, $this->examples->userDwsSubsidies[0]));
        });
    }
}

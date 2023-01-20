<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\User;

use App\Concretes\PermanentDatabaseTransactionManager;
use Domain\Permission\Permission;
use Domain\User\UserDwsCalcSpec;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\EnsureUserUseCaseMixin;
use Tests\Unit\Mixins\LoggerMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\TransactionManagerMixin;
use Tests\Unit\Mixins\UserDwsCalcSpecRepositoryMixin;
use Tests\Unit\Test;
use UseCase\User\CreateUserDwsCalcSpecInteractor;

/**
 * {@link \UseCase\User\CreateUserDwsCalcSpecInteractor} のテスト.
 */
final class CreateUserDwsCalcSpecInteractorTest extends Test
{
    use CarbonMixin;
    use ContextMixin;
    use UserDwsCalcSpecRepositoryMixin;
    use EnsureUserUseCaseMixin;
    use ExamplesConsumer;
    use LoggerMixin;
    use MockeryMixin;
    use TransactionManagerMixin;
    use UnitSupport;

    private UserDwsCalcSpec $calcSpec;
    private CreateUserDwsCalcSpecInteractor $interactor;

    /**
     * 初期化処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachSpec(function (self $self): void {
            $self->calcSpec = $self->examples->userDwsCalcSpecs[0];
            $self->ensureUserUseCase
                ->allows('handle')
                ->andReturnNull()
                ->byDefault();
            $self->userDwsCalcSpecRepository
                ->allows('store')
                ->andReturn($self->calcSpec)
                ->byDefault();
            $self->userDwsCalcSpecRepository
                ->allows('transactionManager')
                ->andReturn(PermanentDatabaseTransactionManager::class)
                ->passthru();
            $self->logger
                ->allows('info')
                ->byDefault();

            $self->interactor = app(CreateUserDwsCalcSpecInteractor::class);
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
                ->with($this->context, Permission::createUserDwsCalcSpecs(), $this->calcSpec->userId)
                ->andReturnNull();

            $this->interactor->handle(
                $this->context,
                $this->calcSpec->userId,
                $this->calcSpec
            );
        });
        $this->should('run in transaction', function (): void {
            $this->transactionManager
                ->expects('run')
                ->andReturn($this->calcSpec);
            $this->userDwsCalcSpecRepository
                ->expects('store')
                ->never();

            $this->interactor->handle($this->context, $this->calcSpec->userId, $this->calcSpec);
        });
        $this->should('store the UserDwsCalcSpec', function (): void {
            $this->userDwsCalcSpecRepository
                ->expects('store')
                ->with($this->calcSpec)
                ->andReturn($this->calcSpec);

            $this->interactor->handle(
                $this->context,
                $this->calcSpec->userId,
                $this->calcSpec
            );
        });
        $this->should('log using info', function (): void {
            $this->logger
                ->expects('info')
                ->with(
                    '障害福祉サービス：利用者別算定情報が登録されました',
                    ['id' => $this->calcSpec->id] + $this->context->logContext()
                );

            $this->interactor->handle(
                $this->context,
                $this->examples->users[0]->id,
                $this->calcSpec
            );
        });
        $this->should('return the UserDwsCalcSpec', function (): void {
            $this->assertModelStrictEquals(
                $this->calcSpec,
                $this->interactor->handle(
                    $this->context,
                    $this->examples->users[0]->id,
                    $this->calcSpec
                )
            );
        });
    }
}

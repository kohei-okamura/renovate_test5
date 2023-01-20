<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\User;

use App\Concretes\PermanentDatabaseTransactionManager;
use Domain\Permission\Permission;
use Domain\User\UserLtcsCalcSpec;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\EnsureUserUseCaseMixin;
use Tests\Unit\Mixins\LoggerMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\TransactionManagerMixin;
use Tests\Unit\Mixins\UserLtcsCalcSpecRepositoryMixin;
use Tests\Unit\Test;
use UseCase\User\CreateUserLtcsCalcSpecInteractor;

/**
 * {@link \UseCase\User\CreateUserLtcsCalcSpecInteractor} のテスト.
 */
final class CreateUserLtcsCalcSpecInteractorTest extends Test
{
    use CarbonMixin;
    use ContextMixin;
    use UserLtcsCalcSpecRepositoryMixin;
    use EnsureUserUseCaseMixin;
    use ExamplesConsumer;
    use LoggerMixin;
    use MockeryMixin;
    use TransactionManagerMixin;
    use UnitSupport;

    private UserLtcsCalcSpec $calcSpec;
    private CreateUserLtcsCalcSpecInteractor $interactor;

    /**
     * 初期化処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachSpec(function (self $self): void {
            $self->calcSpec = $self->examples->userLtcsCalcSpecs[0];
            $self->ensureUserUseCase
                ->allows('handle')
                ->andReturnNull()
                ->byDefault();
            $self->userLtcsCalcSpecRepository
                ->allows('store')
                ->andReturn($self->calcSpec)
                ->byDefault();
            $self->userLtcsCalcSpecRepository
                ->allows('transactionManager')
                ->andReturn(PermanentDatabaseTransactionManager::class)
                ->passthru();
            $self->logger
                ->allows('info')
                ->byDefault();

            $self->interactor = app(CreateUserLtcsCalcSpecInteractor::class);
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
                ->with($this->context, Permission::createUserLtcsCalcSpecs(), $this->calcSpec->userId)
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
            $this->userLtcsCalcSpecRepository
                ->expects('store')
                ->never();

            $this->interactor->handle($this->context, $this->calcSpec->userId, $this->calcSpec);
        });
        $this->should('store the UserLtcsCalcSpec', function (): void {
            $this->userLtcsCalcSpecRepository
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
                    '介護保険サービス：利用者別算定情報が登録されました',
                    ['id' => $this->calcSpec->id] + $this->context->logContext()
                );

            $this->interactor->handle(
                $this->context,
                $this->examples->users[0]->id,
                $this->calcSpec
            );
        });
        $this->should('return the UserLtcsCalcSpec', function (): void {
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

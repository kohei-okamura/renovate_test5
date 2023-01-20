<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\Role;

use APP\Concretes\PermanentDatabaseTransactionManager;
use Closure;
use Domain\Common\Carbon;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\LoggerMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\RoleRepositoryMixin;
use Tests\Unit\Mixins\TransactionManagerMixin;
use Tests\Unit\Test;
use UseCase\Role\CreateRoleInteractor;

/**
 * CreateRoleInteractorのテスト.
 */
class CreateRoleInteractorTest extends Test
{
    use CarbonMixin;
    use ContextMixin;
    use ExamplesConsumer;
    use LoggerMixin;
    use MockeryMixin;
    use RoleRepositoryMixin;
    use TransactionManagerMixin;
    use UnitSupport;

    private CreateRoleInteractor $interactor;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (CreateRoleInteractorTest $self): void {
            $self->roleRepository
                ->allows('store')
                ->andReturn($self->examples->roles[0])
                ->byDefault();

            $self->roleRepository
                ->allows('transactionManager')
                ->andReturn(PermanentDatabaseTransactionManager::class)
                ->passthru();
            $self->logger->allows('info')->byDefault();

            $self->interactor = app(CreateRoleInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('store the Role after transaction begun', function (): void {
            $this->transactionManager
                ->expects('run')
                ->andReturnUsing(function (Closure $callback) {
                    // トランザクションを開始する前に登録処理が行われないことを検証するため
                    // `run`に渡されたコールバック関数を呼び出す直前にモックの定義を行う
                    // なお、トランザクションの終了後に登録処理が行われないことの検証は（恐らく）できない
                    $this->roleRepository->expects('store')->andReturn($this->examples->roles[0]);
                    return $callback();
                });

            $this->interactor->handle($this->context, $this->examples->roles[0]);
        });
        $this->should('set createAt and updatedAt', function (): void {
            $updates = [
                'createdAt' => Carbon::now(),
                'updatedAt' => Carbon::now(),
            ];
            $this->roleRepository
                ->expects('store')
                ->with(equalTo($this->examples->roles[0]->copy($updates)))
                ->andReturn($this->examples->roles[0]);

            $this->interactor->handle($this->context, $this->examples->roles[0]);
        });
        $this->should('return the Role', function (): void {
            $this->assertEquals(
                $this->examples->roles[0],
                $this->interactor->handle($this->context, $this->examples->roles[0])
            );
        });
        $this->should('log using info', function (): void {
            $context = ['organizationId' => $this->examples->organizations[0]->id];
            $this->context
                ->expects('logContext')
                ->andReturn($context);
            $this->logger
                ->expects('info')
                ->with('権限情報が登録されました', ['id' => $this->examples->roles[0]->id] + $context);

            $this->interactor->handle($this->context, $this->examples->roles[0]);
        });
    }
}

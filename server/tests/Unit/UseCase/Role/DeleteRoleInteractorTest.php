<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\Role;

use App\Concretes\PermanentDatabaseTransactionManager;
use Closure;
use Lib\Exceptions\NotFoundException;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\LoggerMixin;
use Tests\Unit\Mixins\LookupRoleUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\OrganizationRepositoryMixin;
use Tests\Unit\Mixins\RoleRepositoryMixin;
use Tests\Unit\Mixins\TransactionManagerMixin;
use Tests\Unit\Test;
use UseCase\Role\DeleteRoleInteractor;

/**
 * DeleteRoleInteractor のテスト.
 */
class DeleteRoleInteractorTest extends Test
{
    use CarbonMixin;
    use ContextMixin;
    use ExamplesConsumer;
    use LoggerMixin;
    use LookupRoleUseCaseMixin;
    use MockeryMixin;
    use RoleRepositoryMixin;
    use OrganizationRepositoryMixin;
    use TransactionManagerMixin;
    use UnitSupport;

    private DeleteRoleInteractor $interactor;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (DeleteRoleInteractorTest $self): void {
            $self->lookupRoleUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->examples->roles[0]))
                ->byDefault();
            $self->roleRepository
                ->allows('store')
                ->andReturn($self->examples->roles[0])
                ->byDefault();
            $self->roleRepository
                ->allows('transactionManager')
                ->andReturn(PermanentDatabaseTransactionManager::class)
                ->passthru();
            $self->organizationRepository
                ->allows('lookupOptionByCode')
                ->andReturn(Seq::from($self->examples->organizations[0]))
                ->byDefault();
            $self->logger
                ->allows('info')
                ->byDefault();
            $self->interactor = app(DeleteRoleInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('delete the role after transaction begun', function (): void {
            $this->lookupRoleUseCase
                ->expects('handle')
                ->with($this->context, $this->examples->roles[0]->id)
                ->andReturn(Seq::from($this->examples->roles[0]));
            $this->transactionManager
                ->expects('run')
                ->andReturnUsing(function (Closure $callback) {
                    // トランザクションを開始する前に削除処理が行われないことを検証するため
                    // `run` に渡されたコールバック関数を呼び出す直前にモックの定義を行う
                    // なお、トランザクションの終了後に削除処理が行われないことの検証は（恐らく）できない
                    $this->roleRepository
                        ->expects('removeById')
                        ->andReturn($this->examples->roles[0]);
                    return $callback();
                });
            $this->interactor->handle($this->context, $this->examples->roles[0]->id);
        });
        $this->should('log using info', function (): void {
            $context = ['organizationId' => $this->examples->organizations[0]->id];
            $this->context
                ->expects('logContext')
                ->andReturn($context);
            $this->logger
                ->expects('info')
                ->with('権限情報が削除されました', ['id' => $this->examples->roles[0]->id] + $context);
            $this->transactionManager
                ->expects('run')
                ->andReturnUsing(function (Closure $callback) {
                    // トランザクションを開始する前に削除処理が行われないことを検証するため
                    // `run` に渡されたコールバック関数を呼び出す直前にモックの定義を行う
                    // なお、トランザクションの終了後に削除処理が行われないことの検証は（恐らく）できない
                    $this->roleRepository
                        ->expects('removeById')
                        ->andReturn($this->examples->roles[0]);
                    return $callback();
                });
            $this->interactor->handle($this->context, $this->examples->roles[0]->id);
        });
        $this->should('throw a NotFoundException when the id not exists in db', function (): void {
            $this->lookupRoleUseCase
                ->expects('handle')
                ->with($this->context, self::NOT_EXISTING_ID)
                ->andReturn(Seq::emptySeq());

            $this->assertThrows(
                NotFoundException::class,
                function (): void {
                    $this->interactor->handle($this->context, self::NOT_EXISTING_ID);
                }
            );
        });
    }
}

<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\User;

use App\Concretes\PermanentDatabaseTransactionManager;
use Closure;
use Domain\Permission\Permission;
use Lib\Exceptions\NotFoundException;
use Lib\Json;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\LoggerMixin;
use Tests\Unit\Mixins\LookupUserDwsSubsidyUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\TransactionManagerMixin;
use Tests\Unit\Mixins\UserDwsSubsidyRepositoryMixin;
use Tests\Unit\Test;
use UseCase\User\EditUserDwsSubsidyInteractor;

/**
 * EditUserDwsSubsidyInteractor のテスト.
 */
class EditUserDwsSubsidyInteractorTest extends Test
{
    use ContextMixin;
    use ExamplesConsumer;
    use LoggerMixin;
    use LookupUserDwsSubsidyUseCaseMixin;
    use MockeryMixin;
    use UserDwsSubsidyRepositoryMixin;
    use TransactionManagerMixin;
    use UnitSupport;

    private EditUserDwsSubsidyInteractor $interactor;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (EditUserDwsSubsidyInteractorTest $self): void {
            $self->lookupUserDwsSubsidyUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->examples->userDwsSubsidies[0]))
                ->byDefault();
            $self->userDwsSubsidyRepository
                ->allows('store')
                ->andReturn($self->examples->userDwsSubsidies[0])
                ->byDefault();
            $self->userDwsSubsidyRepository
                ->allows('transactionManager')
                ->andReturn(PermanentDatabaseTransactionManager::class)
                ->passthru();
            $self->logger
                ->allows('info')
                ->byDefault();

            $self->interactor = app(EditUserDwsSubsidyInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('throw a NotFoundException when the UserDwsSubsidy not exists in db', function (): void {
            $this->lookupUserDwsSubsidyUseCase
                ->expects('handle')
                ->with($this->context, Permission::updateUserDwsSubsidies(), $this->examples->users[0]->id, $this->examples->userDwsSubsidies[0]->id)
                ->andReturn(Seq::emptySeq());

            $this->assertThrows(
                NotFoundException::class,
                function (): void {
                    $this->interactor->handle(
                        $this->context,
                        $this->examples->users[0]->id,
                        $this->examples->userDwsSubsidies[0]->id,
                        $this->payload()
                    );
                }
            );
        });
        $this->should('log using info', function (): void {
            $context = ['organizationId' => $this->examples->organizations[0]->id];
            $this->context
                ->expects('logContext')
                ->andReturn($context);
            $this->logger
                ->expects('info')
                ->with('自治体助成情報が更新されました', ['id' => $this->examples->userDwsSubsidies[0]->id] + $context);

            $this->interactor->handle(
                $this->context,
                $this->examples->users[0]->id,
                $this->examples->userDwsSubsidies[0]->id,
                $this->payload()
            );
        });
        $this->should('edit the UserDwsSubsidy after transaction begun', function (): void {
            $this->transactionManager
                ->expects('run')
                ->andReturnUsing(function (Closure $callback) {
                    // トランザクションを開始する前に編集処理が行われないことを検証するため
                    // `run` に渡されたコールバック関数を呼び出す直前にモックの定義を行う
                    // なお、トランザクションの終了後に編集処理が行われないことの検証は（恐らく）できない
                    $this->userDwsSubsidyRepository
                        ->expects('store')
                        ->andReturn($this->examples->userDwsSubsidies[0]);
                    return $callback();
                });

            $this->interactor->handle(
                $this->context,
                $this->examples->users[0]->id,
                $this->examples->userDwsSubsidies[0]->id,
                $this->payload()
            );
        });
        $this->should('return the UserDwsSubsidy', function (): void {
            $this->assertModelStrictEquals(
                $this->examples->userDwsSubsidies[0],
                $this->interactor->handle(
                    $this->context,
                    $this->examples->users[0]->id,
                    $this->examples->userDwsSubsidies[0]->id,
                    $this->payload()
                )
            );
        });
    }

    /**
     * payload が返す配列.
     *
     * @return array
     */
    private function payload(): array
    {
        return Json::decode(Json::encode($this->examples->userDwsSubsidies[0]), true);
    }
}

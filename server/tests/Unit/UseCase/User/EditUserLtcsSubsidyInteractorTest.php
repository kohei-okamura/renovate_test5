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
use Tests\Unit\Mixins\LookupUserLtcsSubsidyUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\TransactionManagerMixin;
use Tests\Unit\Mixins\UserLtcsSubsidyRepositoryMixin;
use Tests\Unit\Test;
use UseCase\User\EditUserLtcsSubsidyInteractor;

/**
 * EditUserLtcsSubsidyInteractor のテスト.
 */
class EditUserLtcsSubsidyInteractorTest extends Test
{
    use ContextMixin;
    use ExamplesConsumer;
    use LoggerMixin;
    use LookupUserLtcsSubsidyUseCaseMixin;
    use MockeryMixin;
    use UserLtcsSubsidyRepositoryMixin;
    use TransactionManagerMixin;
    use UnitSupport;

    private EditUserLtcsSubsidyInteractor $interactor;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (EditUserLtcsSubsidyInteractorTest $self): void {
            $self->lookupUserLtcsSubsidyUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->examples->userLtcsSubsidies[0]))
                ->byDefault();
            $self->userLtcsSubsidyRepository
                ->allows('store')
                ->andReturn($self->examples->userLtcsSubsidies[0])
                ->byDefault();
            $self->userLtcsSubsidyRepository
                ->allows('transactionManager')
                ->andReturn(PermanentDatabaseTransactionManager::class)
                ->passthru();
            $self->logger
                ->allows('info')
                ->byDefault();

            $self->interactor = app(EditUserLtcsSubsidyInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('throw a NotFoundException when the Subsidy not exists in db', function (): void {
            $this->lookupUserLtcsSubsidyUseCase
                ->expects('handle')
                ->with($this->context, Permission::updateUserLtcsSubsidies(), $this->examples->users[0]->id, $this->examples->userLtcsSubsidies[0]->id)
                ->andReturn(Seq::emptySeq());

            $this->assertThrows(
                NotFoundException::class,
                function (): void {
                    $this->interactor->handle(
                        $this->context,
                        $this->examples->users[0]->id,
                        $this->examples->userLtcsSubsidies[0]->id,
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
                ->with('公費情報が更新されました', ['id' => $this->examples->userLtcsSubsidies[0]->id] + $context);

            $this->interactor->handle(
                $this->context,
                $this->examples->users[0]->id,
                $this->examples->userLtcsSubsidies[0]->id,
                $this->payload()
            );
        });
        $this->should('edit the Subsidy after transaction begun', function (): void {
            $this->transactionManager
                ->expects('run')
                ->andReturnUsing(function (Closure $callback) {
                    // トランザクションを開始する前に編集処理が行われないことを検証するため
                    // `run` に渡されたコールバック関数を呼び出す直前にモックの定義を行う
                    // なお、トランザクションの終了後に編集処理が行われないことの検証は（恐らく）できない
                    $this->userLtcsSubsidyRepository
                        ->expects('store')
                        ->andReturn($this->examples->userLtcsSubsidies[0]);
                    return $callback();
                });

            $this->interactor->handle(
                $this->context,
                $this->examples->users[0]->id,
                $this->examples->userLtcsSubsidies[0]->id,
                $this->payload()
            );
        });
        $this->should('return the Subsidy', function (): void {
            $this->assertModelStrictEquals(
                $this->examples->userLtcsSubsidies[0],
                $this->interactor->handle(
                    $this->context,
                    $this->examples->users[0]->id,
                    $this->examples->userLtcsSubsidies[0]->id,
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
        return Json::decode(Json::encode($this->examples->userLtcsSubsidies[0]), true);
    }
}

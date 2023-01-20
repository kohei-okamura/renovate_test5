<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\User;

use App\Concretes\PermanentDatabaseTransactionManager;
use Closure;
use Domain\Common\Carbon;
use Domain\Permission\Permission;
use Lib\Exceptions\NotFoundException;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\LoggerMixin;
use Tests\Unit\Mixins\LookupUserLtcsCalcSpecUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\TransactionManagerMixin;
use Tests\Unit\Mixins\UserLtcsCalcSpecRepositoryMixin;
use Tests\Unit\Test;
use UseCase\User\EditUserLtcsCalcSpecInteractor;

/**
 * EditUserLtcsCalcSpecInteractor のテスト.
 */
class EditUserLtcsCalcSpecInteractorTest extends Test
{
    use CarbonMixin;
    use ContextMixin;
    use ExamplesConsumer;
    use LoggerMixin;
    use LookupUserLtcsCalcSpecUseCaseMixin;
    use MockeryMixin;
    use UserLtcsCalcSpecRepositoryMixin;
    use TransactionManagerMixin;
    use UnitSupport;

    private EditUserLtcsCalcSpecInteractor $interactor;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (EditUserLtcsCalcSpecInteractorTest $self): void {
            $self->lookupUserLtcsCalcSpecUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->examples->userLtcsCalcSpecs[0]))
                ->byDefault();
            $self->userLtcsCalcSpecRepository
                ->allows('store')
                ->andReturn($self->examples->userLtcsCalcSpecs[0])
                ->byDefault();
            $self->userLtcsCalcSpecRepository
                ->allows('transactionManager')
                ->andReturn(PermanentDatabaseTransactionManager::class)
                ->passthru();
            $self->logger
                ->allows('info')
                ->byDefault();

            $self->interactor = app(EditUserLtcsCalcSpecInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('throw a NotFoundException when the UserLtcsCalcSpec not exists in db', function (): void {
            $this->lookupUserLtcsCalcSpecUseCase
                ->expects('handle')
                ->with(
                    $this->context,
                    Permission::updateUserLtcsCalcSpecs(),
                    $this->examples->users[0]->id,
                    $this->examples->userLtcsCalcSpecs[0]->id
                )
                ->andReturn(Seq::empty());

            $this->assertThrows(
                NotFoundException::class,
                function (): void {
                    $this->interactor->handle(
                        $this->context,
                        $this->examples->users[0]->id,
                        $this->examples->userLtcsCalcSpecs[0]->id,
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
                ->with('介護保険サービス：利用者別算定情報が更新されました', ['id' => $this->examples->userLtcsCalcSpecs[0]->id] + $context);

            $this->interactor->handle(
                $this->context,
                $this->examples->users[0]->id,
                $this->examples->userLtcsCalcSpecs[0]->id,
                $this->payload()
            );
        });
        $this->should('edit the UserLtcsCalcSpec after transaction begun', function (): void {
            $this->transactionManager
                ->expects('run')
                ->andReturnUsing(function (Closure $callback) {
                    $entity = $this->examples->userLtcsCalcSpecs[0];
                    // トランザクションを開始する前に編集処理が行われないことを検証するため
                    // `run` に渡されたコールバック関数を呼び出す直前にモックの定義を行う
                    // なお、トランザクションの終了後に編集処理が行われないことの検証は（恐らく）できない
                    $this->userLtcsCalcSpecRepository
                        ->expects('store')
                        ->with(equalTo($entity->copy($this->payload() + [
                            'version' => $entity->version + 1,
                            'updatedAt' => Carbon::now(),
                        ])))
                        ->andReturn($this->examples->userLtcsCalcSpecs[0]);
                    return $callback();
                });

            $this->interactor->handle(
                $this->context,
                $this->examples->users[0]->id,
                $this->examples->userLtcsCalcSpecs[0]->id,
                $this->payload()
            );
        });
        $this->should('return the UserLtcsCalcSpec', function (): void {
            $this->assertModelStrictEquals(
                $this->examples->userLtcsCalcSpecs[0],
                $this->interactor->handle(
                    $this->context,
                    $this->examples->users[0]->id,
                    $this->examples->userLtcsCalcSpecs[0]->id,
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
        return [
            'effectivatedOn' => $this->examples->userLtcsCalcSpecs[0]->effectivatedOn,
            'locationAddition' => $this->examples->userLtcsCalcSpecs[0]->locationAddition,
        ];
    }
}

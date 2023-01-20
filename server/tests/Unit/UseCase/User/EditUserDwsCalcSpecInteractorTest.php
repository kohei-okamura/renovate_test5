<?php
/**
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
use Tests\Unit\Mixins\LookupUserDwsCalcSpecUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\TransactionManagerMixin;
use Tests\Unit\Mixins\UserDwsCalcSpecRepositoryMixin;
use Tests\Unit\Test;
use UseCase\User\EditUserDwsCalcSpecInteractor;

/**
 * EditUserDwsCalcSpecInteractor のテスト.
 */
class EditUserDwsCalcSpecInteractorTest extends Test
{
    use CarbonMixin;
    use ContextMixin;
    use ExamplesConsumer;
    use LoggerMixin;
    use LookupUserDwsCalcSpecUseCaseMixin;
    use MockeryMixin;
    use UserDwsCalcSpecRepositoryMixin;
    use TransactionManagerMixin;
    use UnitSupport;

    private EditUserDwsCalcSpecInteractor $interactor;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (EditUserDwsCalcSpecInteractorTest $self): void {
            $self->lookupUserDwsCalcSpecUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->examples->userDwsCalcSpecs[0]))
                ->byDefault();
            $self->userDwsCalcSpecRepository
                ->allows('store')
                ->andReturn($self->examples->userDwsCalcSpecs[0])
                ->byDefault();
            $self->userDwsCalcSpecRepository
                ->allows('transactionManager')
                ->andReturn(PermanentDatabaseTransactionManager::class)
                ->passthru();
            $self->logger
                ->allows('info')
                ->byDefault();

            $self->interactor = app(EditUserDwsCalcSpecInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('throw a NotFoundException when the UserDwsCalcSpec not exists in db', function (): void {
            $this->lookupUserDwsCalcSpecUseCase
                ->expects('handle')
                ->with(
                    $this->context,
                    Permission::updateUserDwsCalcSpecs(),
                    $this->examples->users[0]->id,
                    $this->examples->userDwsCalcSpecs[0]->id
                )
                ->andReturn(Seq::empty());

            $this->assertThrows(
                NotFoundException::class,
                function (): void {
                    $this->interactor->handle(
                        $this->context,
                        $this->examples->users[0]->id,
                        $this->examples->userDwsCalcSpecs[0]->id,
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
                ->with('障害福祉サービス：利用者別算定情報が更新されました', ['id' => $this->examples->userDwsCalcSpecs[0]->id] + $context);

            $this->interactor->handle(
                $this->context,
                $this->examples->users[0]->id,
                $this->examples->userDwsCalcSpecs[0]->id,
                $this->payload()
            );
        });
        $this->should('edit the UserDwsCalcSpec after transaction begun', function (): void {
            $this->transactionManager
                ->expects('run')
                ->andReturnUsing(function (Closure $callback) {
                    $entity = $this->examples->userDwsCalcSpecs[0];
                    // トランザクションを開始する前に編集処理が行われないことを検証するため
                    // `run` に渡されたコールバック関数を呼び出す直前にモックの定義を行う
                    // なお、トランザクションの終了後に編集処理が行われないことの検証は（恐らく）できない
                    $this->userDwsCalcSpecRepository
                        ->expects('store')
                        ->with(equalTo($entity->copy($this->payload() + [
                            'version' => $entity->version + 1,
                            'updatedAt' => Carbon::now(),
                        ])))
                        ->andReturn($this->examples->userDwsCalcSpecs[0]);
                    return $callback();
                });

            $this->interactor->handle(
                $this->context,
                $this->examples->users[0]->id,
                $this->examples->userDwsCalcSpecs[0]->id,
                $this->payload()
            );
        });
        $this->should('return the UserDwsCalcSpec', function (): void {
            $this->assertModelStrictEquals(
                $this->examples->userDwsCalcSpecs[0],
                $this->interactor->handle(
                    $this->context,
                    $this->examples->users[0]->id,
                    $this->examples->userDwsCalcSpecs[0]->id,
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
            'effectivatedOn' => $this->examples->userDwsCalcSpecs[0]->effectivatedOn,
            'locationAddition' => $this->examples->userDwsCalcSpecs[0]->locationAddition,
        ];
    }
}

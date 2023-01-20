<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\DwsCertification;

use App\Concretes\PermanentDatabaseTransactionManager;
use Closure;
use Domain\Permission\Permission;
use Lib\Exceptions\NotFoundException;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\DwsCertificationRepositoryMixin;
use Tests\Unit\Mixins\LoggerMixin;
use Tests\Unit\Mixins\LookupDwsCertificationUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\TransactionManagerMixin;
use Tests\Unit\Test;
use UseCase\DwsCertification\DeleteDwsCertificationInteractor;

/**
 * DeleteDwsCertificationInteractor のテスト.
 */
class DeleteDwsCertificationInteractorTest extends Test
{
    use CarbonMixin;
    use ContextMixin;
    use DwsCertificationRepositoryMixin;
    use ExamplesConsumer;
    use LoggerMixin;
    use LookupDwsCertificationUseCaseMixin;
    use MockeryMixin;
    use TransactionManagerMixin;
    use UnitSupport;

    private DeleteDwsCertificationInteractor $interactor;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (DeleteDwsCertificationInteractorTest $self): void {
            $self->lookupDwsCertificationUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->examples->dwsCertifications[0]))
                ->byDefault();
            $self->dwsCertificationRepository
                ->allows('store')
                ->andReturn($self->examples->dwsCertifications[0])
                ->byDefault();
            $self->dwsCertificationRepository
                ->allows('transactionManager')
                ->andReturn(PermanentDatabaseTransactionManager::class)
                ->passthru();
            $self->logger
                ->allows('info')
                ->byDefault();
            $self->interactor = app(DeleteDwsCertificationInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('throw a NotFoundException when the userId not have DwsCertificationId', function (): void {
            $this->lookupDwsCertificationUseCase
                ->expects('handle')
                ->with($this->context, Permission::deleteDwsCertifications(), self::NOT_EXISTING_ID, $this->examples->dwsCertifications[0]->id)
                ->andReturn(Seq::emptySeq());

            $this->assertThrows(
                NotFoundException::class,
                function (): void {
                    $this->interactor->handle(
                        $this->context,
                        self::NOT_EXISTING_ID,
                        $this->examples->dwsCertifications[0]->id
                    );
                }
            );
        });
        $this->should('throw a NotFoundException when the DwsCertificationId not exists in db', function (): void {
            $this->lookupDwsCertificationUseCase
                ->expects('handle')
                ->with($this->context, Permission::deleteDwsCertifications(), $this->examples->users[0]->id, self::NOT_EXISTING_ID)
                ->andReturn(Seq::emptySeq());

            $this->assertThrows(
                NotFoundException::class,
                function (): void {
                    $this->interactor->handle(
                        $this->context,
                        $this->examples->users[0]->id,
                        self::NOT_EXISTING_ID
                    );
                }
            );
        });
        $this->should('delete the DwsCertification after transaction begun', function (): void {
            $this->transactionManager
                ->expects('run')
                ->andReturnUsing(function (Closure $callback) {
                    // トランザクションを開始する前に編集処理が行われないことを検証するため
                    // `run` に渡されたコールバック関数を呼び出す直前にモックの定義を行う
                    // なお、トランザクションの終了後に編集処理が行われないことの検証は（恐らく）できない
                    $this->dwsCertificationRepository
                        ->expects('removeById')
                        ->andReturn($this->examples->dwsCertifications[0]);
                    return $callback();
                });

            $this->interactor->handle(
                $this->context,
                $this->examples->dwsCertifications[0]->userId,
                $this->examples->dwsCertifications[0]->id
            );
        });
        $this->should('log using info', function (): void {
            $this->transactionManager
                ->expects('run')
                ->andReturnUsing(function (Closure $callback) {
                    // トランザクションを開始する前に編集処理が行われないことを検証するため
                    // `run` に渡されたコールバック関数を呼び出す直前にモックの定義を行う
                    // なお、トランザクションの終了後に編集処理が行われないことの検証は（恐らく）できない
                    $this->dwsCertificationRepository
                        ->expects('removeById')
                        ->andReturn($this->examples->dwsCertifications[0]);
                    return $callback();
                });

            $context = ['organizationId' => $this->examples->organizations[0]->id];
            $this->context
                ->expects('logContext')
                ->andReturn($context);
            $this->logger
                ->expects('info')
                ->with('障害福祉サービス受給者証が削除されました', ['id' => $this->examples->dwsCertifications[0]->id] + $context);

            $this->interactor->handle(
                $this->context,
                $this->examples->dwsCertifications[0]->userId,
                $this->examples->dwsCertifications[0]->id
            );
        });
    }
}

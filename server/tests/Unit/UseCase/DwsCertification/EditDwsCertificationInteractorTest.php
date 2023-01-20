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
use UseCase\DwsCertification\EditDwsCertificationInteractor;

/**
 * EditDwsCertificationInteractor のテスト.
 */
final class EditDwsCertificationInteractorTest extends Test
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

    private EditDwsCertificationInteractor $interactor;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (EditDwsCertificationInteractorTest $self): void {
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
            $self->interactor = app(EditDwsCertificationInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('throw a NotFoundException when the DwsCertificationId not exists in db', function (): void {
            $this->lookupDwsCertificationUseCase
                ->expects('handle')
                ->with($this->context, Permission::updateDwsCertifications(), $this->examples->users[0]->id, $this->examples->dwsCertifications[0]->id)
                ->andReturn(Seq::emptySeq());

            $this->assertThrows(
                NotFoundException::class,
                function (): void {
                    $this->interactor->handle(
                        $this->context,
                        $this->examples->users[0]->id,
                        $this->examples->dwsCertifications[0]->id,
                        $this->getEditValues()
                    );
                }
            );
        });
        $this->should('edit the DwsCertification after transaction begun', function (): void {
            $this->transactionManager
                ->expects('run')
                ->andReturnUsing(function (Closure $callback) {
                    // トランザクションを開始する前に編集処理が行われないことを検証するため
                    // `run` に渡されたコールバック関数を呼び出す直前にモックの定義を行う
                    // なお、トランザクションの終了後に編集処理が行われないことの検証は（恐らく）できない
                    $this->dwsCertificationRepository
                        ->expects('store')
                        ->andReturn($this->examples->dwsCertifications[0]);
                    return $callback();
                });

            $this->interactor->handle(
                $this->context,
                $this->examples->users[2]->id,
                $this->examples->dwsCertifications[0]->id,
                $this->getEditValues()
            );
        });
        $this->should('return the DwsCertification', function (): void {
            $this->assertModelStrictEquals(
                $this->examples->dwsCertifications[0],
                $this->interactor->handle(
                    $this->context,
                    $this->examples->users[2]->id,
                    $this->examples->dwsCertifications[0]->id,
                    $this->getEditValues()
                )
            );
        });
        $this->should('log using info', function (): void {
            $context = ['organizationId' => $this->examples->organizations[0]->id];
            $this->context
                ->expects('logContext')
                ->andReturn($context);
            $this->logger
                ->expects('info')
                ->with('障害福祉サービス受給者証が更新されました', ['id' => $this->examples->dwsCertifications[0]->id] + $context);

            $this->interactor->handle(
                $this->context,
                $this->examples->users[2]->id,
                $this->examples->dwsCertifications[0]->id,
                $this->getEditValues()
            );
        });
    }

    /**
     * 更新用の配列を生成する.
     *
     * @return array
     */
    public function getEditValues(): array
    {
        return [
            'dwsLevel' => $this->examples->dwsCertifications[0]->dwsLevel,
            'status' => $this->examples->dwsCertifications[0]->status,
            'dwsTypes' => $this->examples->dwsCertifications[0]->dwsTypes,
            'isSubjectOfComprehensiveSupport' => $this->examples->dwsCertifications[0]->isSubjectOfComprehensiveSupport,
            'agreements' => $this->examples->dwsCertifications[0]->agreements,
            'grants' => $this->examples->dwsCertifications[0]->grants,
            'issuedOn' => $this->examples->dwsCertifications[0]->issuedOn,
            'effectivatedOn' => $this->examples->dwsCertifications[0]->effectivatedOn,
            'activatedOn' => $this->examples->dwsCertifications[0]->activatedOn,
            'deactivatedOn' => $this->examples->dwsCertifications[0]->deactivatedOn,
            'copayActivatedOn' => $this->examples->dwsCertifications[0]->copayActivatedOn,
            'copayDeactivatedOn' => $this->examples->dwsCertifications[0]->copayDeactivatedOn,
            'isEnabled' => $this->examples->dwsCertifications[0]->isEnabled,
        ];
    }
}

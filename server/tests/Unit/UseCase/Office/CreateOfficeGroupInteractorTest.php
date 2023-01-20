<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\Office;

use App\Concretes\PermanentDatabaseTransactionManager;
use Closure;
use Domain\Common\Carbon;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\LoggerMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\OfficeGroupRepositoryMixin;
use Tests\Unit\Mixins\TransactionManagerMixin;
use Tests\Unit\Test;
use UseCase\Office\CreateOfficeGroupInteractor;

/**
 * CreateOfficeGroupInteractor のテスト.
 */
class CreateOfficeGroupInteractorTest extends Test
{
    use CarbonMixin;
    use ContextMixin;
    use ExamplesConsumer;
    use LoggerMixin;
    use MockeryMixin;
    use OfficeGroupRepositoryMixin;
    use TransactionManagerMixin;
    use UnitSupport;

    private CreateOfficeGroupInteractor $interactor;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (CreateOfficeGroupInteractorTest $self): void {
            $self->officeGroupRepository
                ->allows('store')
                ->andReturn($self->examples->officeGroups[0])
                ->byDefault();

            $self->officeGroupRepository
                ->allows('transactionManager')
                ->andReturn(PermanentDatabaseTransactionManager::class)
                ->passthru();

            $self->logger
                ->allows('info')
                ->byDefault();

            $self->interactor = app(CreateOfficeGroupInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('store the OfficeGroup after transaction begun', function (): void {
            $this->transactionManager
                ->expects('run')
                ->andReturnUsing(function (Closure $callback) {
                    // トランザクションを開始する前に登録処理が行われないことを検証するため
                    // `run` に渡されたコールバック関数を呼び出す直前にモックの定義を行う
                    // なお、トランザクションの終了後に登録処理が行われないことの検証は（恐らく）できない
                    $this->officeGroupRepository->expects('store')->andReturn($this->examples->officeGroups[0]);
                    return $callback();
                });

            $this->interactor->handle($this->context, $this->examples->officeGroups[0]);
        });
        $this->should('set sortOrder using unix timestamp', function (): void {
            $values = ['sortOrder' => Carbon::now()->unix()];
            $this->officeGroupRepository
                ->expects('store')
                ->with(equalTo($this->examples->officeGroups[0]->copy($values)))
                ->andReturn($this->examples->officeGroups[0]);

            $this->interactor->handle($this->context, $this->examples->officeGroups[0]);
        });
        $this->should('return the OfficeGroup', function (): void {
            $this->assertModelStrictEquals(
                $this->examples->officeGroups[0],
                $this->interactor->handle($this->context, $this->examples->officeGroups[0])
            );
        });
        $this->should('log using info', function (): void {
            $context = ['organizationId' => $this->examples->organizations[0]->id];
            $this->context
                ->expects('logContext')
                ->andReturn($context);
            $this->logger
                ->expects('info')
                ->with('事業所グループが登録されました', ['id' => $this->examples->officeGroups[0]->id] + $context);

            $this->interactor->handle($this->context, $this->examples->officeGroups[0]);
        });
    }
}

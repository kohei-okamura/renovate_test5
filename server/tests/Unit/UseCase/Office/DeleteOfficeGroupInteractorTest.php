<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\Office;

use Closure;
use Domain\Office\OfficeGroup;
use Lib\Exceptions\NotFoundException;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\LoggerMixin;
use Tests\Unit\Mixins\LookupOfficeGroupUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\OfficeGroupRepositoryMixin;
use Tests\Unit\Mixins\TransactionManagerMixin;
use Tests\Unit\Test;
use UseCase\Office\DeleteOfficeGroupInteractor;

/**
 * DeleteOfficeGroupInteractor のテスト.
 */
class DeleteOfficeGroupInteractorTest extends Test
{
    use ContextMixin;
    use ExamplesConsumer;
    use LoggerMixin;
    use LookupOfficeGroupUseCaseMixin;
    use MockeryMixin;
    use OfficeGroupRepositoryMixin;
    use TransactionManagerMixin;
    use UnitSupport;

    private DeleteOfficeGroupInteractor $interactor;
    private OfficeGroup $officeGroup;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (DeleteOfficeGroupInteractorTest $self): void {
            $self->interactor = app(DeleteOfficeGroupInteractor::class);
            $self->lookupOfficeGroupUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->examples->officeGroups[0]))
                ->byDefault();
            $self->officeGroupRepository
                ->allows('removeById')
                ->byDefault();
            $self->logger
                ->allows('info')
                ->byDefault();
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('delete the OfficeGroup after transaction begun', function (): void {
            $this->transactionManager
                ->expects('run')
                ->andReturnUsing(function (Closure $callback) {
                    // トランザクションを開始する前に削除処理が行われないことを検証するため
                    // `run` に渡されたコールバック関数を呼び出す直前にモックの定義を行う
                    // なお、トランザクションの終了後に削除処理が行われないことの検証は（恐らく）できない
                    $this->officeGroupRepository
                        ->expects('removeById')
                        ->with($this->examples->officeGroups[0]->id);
                    return $callback();
                });

            $this->interactor->handle($this->context, $this->examples->officeGroups[0]->id);
        });
        $this->should('log using info', function (): void {
            $context = ['organizationId' => $this->examples->organizations[0]->id];
            $this->context
                ->expects('logContext')
                ->andReturn($context);
            $this->logger
                ->expects('info')
                ->with('事業所グループが削除されました', ['id' => $this->examples->officeGroups[0]->id] + $context);

            $this->interactor->handle($this->context, $this->examples->officeGroups[0]->id);
        });
        $this->should('throw a NotFoundException when the OfficeGroupId not exists in db', function (): void {
            $this->lookupOfficeGroupUseCase
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

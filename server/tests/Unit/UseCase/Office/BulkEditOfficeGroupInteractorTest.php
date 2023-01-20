<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\Office;

use Closure;
use Domain\Common\Carbon;
use Domain\Office\OfficeGroup;
use Lib\Exceptions\NotFoundException;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\LoggerMixin;
use Tests\Unit\Mixins\LookupOfficeGroupUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\OfficeGroupRepositoryMixin;
use Tests\Unit\Mixins\TransactionManagerMixin;
use Tests\Unit\Test;
use UseCase\Office\BulkEditOfficeGroupInteractor;

/**
 * UpdateOfficeGroupInteractor のテスト.
 */
class BulkEditOfficeGroupInteractorTest extends Test
{
    use CarbonMixin;
    use ContextMixin;
    use ExamplesConsumer;
    use LoggerMixin;
    use LookupOfficeGroupUseCaseMixin;
    use MockeryMixin;
    use OfficeGroupRepositoryMixin;
    use TransactionManagerMixin;
    use UnitSupport;

    private BulkEditOfficeGroupInteractor $interactor;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (BulkEditOfficeGroupInteractorTest $self): void {
            $self->lookupOfficeGroupUseCase
                ->allows('handle')
                ->with($self->context, $self->examples->officeGroups[0]->id)
                ->andReturn(Seq::from($self->examples->officeGroups[0]));
            $self->lookupOfficeGroupUseCase
                ->allows('handle')
                ->with($self->context, $self->examples->officeGroups[1]->id)
                ->andReturn(Seq::from($self->examples->officeGroups[1]));
            $self->lookupOfficeGroupUseCase
                ->allows('handle')
                ->with($self->context, $self->examples->officeGroups[2]->id)
                ->andReturn(Seq::from($self->examples->officeGroups[2]));
            $self->officeGroupRepository
                ->allows('store')
                ->andReturnUsing(function (OfficeGroup $x): OfficeGroup {
                    return $x;
                })
                ->byDefault();

            $self->context->allows('organization')->andReturn($self->examples->organizations[0]);

            $self->logger->allows('info')->byDefault();

            $self->interactor = app(BulkEditOfficeGroupInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('log using info', function (): void {
            $context = ['organizationId' => $this->examples->organizations[0]->id];
            $this->context
                ->expects('logContext')
                ->andReturn($context);
            $this->logger
                ->expects('info')
                ->with('事業所グループが一括更新されました', ['id' => ''] + $context);

            $this->interactor->handle($this->context, $this->bulkEditInput());
        });
        $this->should('throw a NotFoundException when the requestList is empty', function (): void {
            $this->assertThrows(
                NotFoundException::class,
                function (): void {
                    $requestList = [];
                    $this->interactor->handle($this->context, $requestList);
                }
            );
        });
        $this->should('throw a NotFoundException when the Entity not found', function (): void {
            $this->lookupOfficeGroupUseCase
                ->expects('handle')
                ->with($this->context, self::NOT_EXISTING_ID)
                ->andReturn(Seq::emptySeq());

            $this->assertThrows(
                NotFoundException::class,
                function (): void {
                    $requestList = [
                        [
                            'id' => $this->examples->officeGroups[0]->id,
                            'parentOfficeGroupId' => null,
                            'sortOrder' => 1111111111,
                        ],
                        [
                            'id' => self::NOT_EXISTING_ID,
                            'parentOfficeGroupId' => null,
                            'sortOrder' => 1111111111,
                        ],
                    ];
                    $this->interactor->handle($this->context, $requestList);
                }
            );
        });
        $this->should('update the OfficeGroup after transaction begun', function (): void {
            $requestList = [
                [
                    'id' => $this->examples->officeGroups[0]->id,
                    'parentOfficeGroupId' => null,
                    'sortOrder' => 1111111111,
                ],
                [
                    'id' => $this->examples->officeGroups[0]->id, // Transactionないのテストを回すため同じIDで実施
                    'parentOfficeGroupId' => null,
                    'sortOrder' => 1111111111,
                ],
            ];
            $this->transactionManager
                ->expects('run')
                ->andReturnUsing(function (Closure $callback) {
                    // トランザクションを開始する前に更新処理が行われないことを検証するため
                    // `run` に渡されたコールバック関数を呼び出す直前にモックの定義を行う
                    // なお、トランザクションの終了後に更新処理が行われないことの検証は（恐らく）できない
                    $this->officeGroupRepository
                        ->expects('store')
                        ->with(equalTo($this->examples->officeGroups[0]->copy([
                            'parentOfficeGroupId' => null,
                            'sortOrder' => 1111111111,
                            'organizationId' => $this->context->organization->id,
                            'updatedAt' => Carbon::now(),
                        ])))
                        ->andReturn($this->examples->officeGroups[0])
                        ->times(2);
                    return $callback();
                });

            $this->interactor->handle($this->context, $requestList);
        });
    }

    /**
     * 一括更新用Input.
     *
     * @return array
     */
    private function bulkEditInput(): array
    {
        return [
            [
                'id' => $this->examples->officeGroups[0]->id,
                'parentOfficeGroupId' => null,
                'sortOrder' => 1111111111,
            ],
            [
                'id' => $this->examples->officeGroups[1]->id,
                'parentOfficeGroupId' => $this->examples->officeGroups[0]->id,
                'sortOrder' => 2222222222,
            ],
            [
                'id' => $this->examples->officeGroups[2]->id,
                'parentOfficeGroupId' => $this->examples->officeGroups[0]->id,
                'sortOrder' => 3333333333,
            ],
        ];
    }
}

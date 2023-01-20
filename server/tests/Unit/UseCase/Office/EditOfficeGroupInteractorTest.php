<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\Office;

use Closure;
use Domain\Common\Carbon;
use Domain\Common\Pagination;
use Domain\FinderResult;
use Domain\Office\OfficeGroup;
use Lib\Exceptions\NotFoundException;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\FindOfficeGroupUseCaseMixin;
use Tests\Unit\Mixins\LoggerMixin;
use Tests\Unit\Mixins\LookupOfficeGroupUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\OfficeGroupRepositoryMixin;
use Tests\Unit\Mixins\TransactionManagerMixin;
use Tests\Unit\Test;
use UseCase\Office\EditOfficeGroupInteractor;

/**
 * EditOfficeGroupInteractor のテスト.
 */
class EditOfficeGroupInteractorTest extends Test
{
    use CarbonMixin;
    use ContextMixin;
    use ExamplesConsumer;
    use FindOfficeGroupUseCaseMixin;
    use LoggerMixin;
    use LookupOfficeGroupUseCaseMixin;
    use MockeryMixin;
    use OfficeGroupRepositoryMixin;
    use TransactionManagerMixin;
    use UnitSupport;

    private array $officeGroups;
    private FinderResult $finderResult;
    private EditOfficeGroupInteractor $interactor;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (EditOfficeGroupInteractorTest $self): void {
            $self->officeGroups = Seq::fromArray($self->examples->officeGroups)
                ->filter(function (OfficeGroup $x) use ($self): bool {
                    return $x->organizationId === $self->examples->organizations[0]->id;
                })
                ->toArray();
            $self->finderResult = FinderResult::from($self->officeGroups, Pagination::create());
            $self->findOfficeGroupUseCase
                ->allows('handle')
                ->andReturn($self->finderResult)
                ->byDefault();
            $self->lookupOfficeGroupUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->examples->officeGroups[0]))
                ->byDefault();
            $self->officeGroupRepository
                ->allows('store')
                ->andReturnUsing(function (OfficeGroup $x): OfficeGroup {
                    return $x;
                })
                ->byDefault();

            $self->context->allows('organization')->andReturn($self->examples->organizations[0]);

            $self->logger->allows('info')->byDefault();

            $self->interactor = app(EditOfficeGroupInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('return a FinderResult of the OfficeGroups', function (): void {
            $this->assertModelStrictEquals(
                $this->finderResult,
                $this->interactor->handle($this->context, $this->examples->officeGroups[0]->id, $this->getEditValue())
            );
        });
        $this->should('use FindUseCase', function (): void {
            $this->findOfficeGroupUseCase
                ->expects('handle')
                ->with($this->context, [], ['all' => true])
                ->andReturn($this->finderResult);
            $this->assertModelStrictEquals(
                $this->finderResult,
                $this->interactor->handle($this->context, $this->examples->officeGroups[0]->id, $this->getEditValue())
            );
        });
        $this->should('log using info', function (): void {
            $context = ['organizationId' => $this->examples->organizations[0]->id];
            $this->context
                ->expects('logContext')
                ->andReturn($context);
            $this->logger
                ->expects('info')
                ->with('事業所グループが更新されました', ['id' => $this->examples->officeGroups[0]->id] + $context);

            $this->interactor->handle($this->context, $this->examples->officeGroups[0]->id, $this->getEditValue());
        });
        $this->should('throw a NotFoundException when the id not exists in db', function (): void {
            $this->lookupOfficeGroupUseCase
                ->expects('handle')
                ->with($this->context, self::NOT_EXISTING_ID)
                ->andReturn(Seq::emptySeq());

            $this->assertThrows(
                NotFoundException::class,
                function (): void {
                    $this->interactor->handle($this->context, self::NOT_EXISTING_ID, $this->getEditValue());
                }
            );
        });
        $this->should('update the OfficeGroup after transaction begun', function (): void {
            $this->transactionManager
                ->expects('run')
                ->andReturnUsing(function (Closure $callback) {
                    // トランザクションを開始する前に更新処理が行われないことを検証するため
                    // `run` に渡されたコールバック関数を呼び出す直前にモックの定義を行う
                    // なお、トランザクションの終了後に更新処理が行われないことの検証は（恐らく）できない
                    $this->officeGroupRepository
                        ->expects('store')
                        ->with(equalTo($this->examples->officeGroups[0]->copy(
                            $this->getEditValue()
                            + [
                                'organizationId' => $this->context->organization->id,
                                'updatedAt' => Carbon::now(),
                            ]
                        )))
                        ->andReturn($this->examples->officeGroups[0]);
                    return $callback();
                });

            $this->interactor->handle($this->context, $this->examples->officeGroups[0]->id, $this->getEditValue());
        });
    }

    /**
     * 更新用情報を取得する.
     *
     * @return array
     */
    public function getEditValue(): array
    {
        return [
            'organizationId' => $this->examples->officeGroups[0]->organizationId,
            'parentOfficeGroupId' => $this->examples->officeGroups[0]->parentOfficeGroupId,
            'name' => '東海',
            'sortOrder' => $this->examples->officeGroups[0]->sortOrder,
        ];
    }
}

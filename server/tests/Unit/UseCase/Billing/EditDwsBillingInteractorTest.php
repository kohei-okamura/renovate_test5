<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\Billing;

use Closure;
use Domain\Billing\DwsBilling;
use Domain\Billing\DwsBillingStatus;
use Domain\Permission\Permission;
use Lib\Exceptions\NotFoundException;
use ScalikePHP\Seq;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\DwsBillingRepositoryMixin;
use Tests\Unit\Mixins\LoggerMixin;
use Tests\Unit\Mixins\LookupDwsBillingUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\TransactionManagerMixin;
use Tests\Unit\Test;
use UseCase\Billing\EditDwsBillingInteractor;

/**
 * {@link \UseCase\Billing\EditDwsBillingInteractor} のテスト.
 */
final class EditDwsBillingInteractorTest extends Test
{
    use ContextMixin;
    use ExamplesConsumer;
    use LoggerMixin;
    use LookupDwsBillingUseCaseMixin;
    use DwsBillingRepositoryMixin;
    use MatchesSnapshots;
    use MockeryMixin;
    use TransactionManagerMixin;
    use UnitSupport;

    private array $editValue;
    private EditDwsBillingInteractor $interactor;

    /**
     * 初期化処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachSpec(function (self $self): void {
            $self->lookupDwsBillingUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->examples->dwsBillings[1]))
                ->byDefault();

            $self->dwsBillingRepository
                ->allows('store')
                ->andReturnUsing(fn (DwsBilling $x): DwsBilling => $x)
                ->byDefault();

            $self->logger
                ->allows('info')
                ->byDefault();

            $self->interactor = app(EditDwsBillingInteractor::class);
            $self->editValue = [
                'status' => DwsBillingStatus::ready(),
            ];
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('throw Exception', function (): void {
            $this->lookupDwsBillingUseCase
                ->expects('handle')
                ->with(
                    $this->context,
                    Permission::updateBillings(),
                    $this->examples->dwsBillings[1]->id,
                )
                ->andReturn(Seq::emptySeq());

            $this->assertThrows(
                NotFoundException::class,
                function (): void {
                    $this->interactor->handle($this->context, $this->examples->dwsBillings[1]->id, $this->editValue);
                }
            );
        });
        $this->should('call store() of repository after transaction begun', function (): void {
            $this->transactionManager
                ->expects('run')
                ->andReturnUsing(function (Closure $callback) {
                    // トランザクションを開始する前に編集処理が行われないことを検証するため
                    // `run` に渡されたコールバック関数を呼び出す直前にモックの定義を行う
                    // なお、トランザクションの終了後に編集処理が行われないことの検証は（恐らく）できない
                    $this->dwsBillingRepository
                        ->expects('store')
                        ->andReturnUsing(fn (DwsBilling $x): DwsBilling => $x);
                    return $callback();
                });

            $this->interactor->handle($this->context, $this->examples->dwsBillings[1]->id, $this->editValue);
        });
        $this->should('log using info', function (): void {
            $context = ['organizationId' => $this->examples->organizations[0]->id];
            $this->context
                ->expects('logContext')
                ->andReturn($context);
            $this->logger
                ->expects('info')
                ->with(
                    '障害福祉サービス：請求が更新されました',
                    ['id' => $this->examples->dwsBillings[1]->id] + $context
                );
            $this->interactor->handle($this->context, $this->examples->dwsBillings[1]->id, $this->editValue);
        });
    }
}

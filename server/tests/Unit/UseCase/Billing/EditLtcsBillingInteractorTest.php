<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\Billing;

use Closure;
use Domain\Billing\LtcsBilling;
use Domain\Billing\LtcsBillingStatus;
use Domain\Permission\Permission;
use Lib\Exceptions\NotFoundException;
use ScalikePHP\Seq;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\LoggerMixin;
use Tests\Unit\Mixins\LookupLtcsBillingUseCaseMixin;
use Tests\Unit\Mixins\LtcsBillingRepositoryMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\TransactionManagerMixin;
use Tests\Unit\Test;
use UseCase\Billing\EditLtcsBillingInteractor;

/**
 * {@link \UseCase\Billing\EditLtcsBillingInteractor} のテスト.
 */
final class EditLtcsBillingInteractorTest extends Test
{
    use ContextMixin;
    use ExamplesConsumer;
    use LoggerMixin;
    use LookupLtcsBillingUseCaseMixin;
    use LtcsBillingRepositoryMixin;
    use MatchesSnapshots;
    use MockeryMixin;
    use TransactionManagerMixin;
    use UnitSupport;

    private array $editValue;
    private EditLtcsBillingInteractor $interactor;

    /**
     * 初期化処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachSpec(function (self $self): void {
            $self->lookupLtcsBillingUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->examples->ltcsBillings[1]))
                ->byDefault();

            $self->ltcsBillingRepository
                ->allows('store')
                ->andReturnUsing(fn (LtcsBilling $x): LtcsBilling => $x)
                ->byDefault();

            $self->logger
                ->allows('info')
                ->byDefault();

            $self->interactor = app(EditLtcsBillingInteractor::class);
            $self->editValue = [
                'status' => LtcsBillingStatus::ready(),
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
            $this->lookupLtcsBillingUseCase
                ->expects('handle')
                ->with(
                    $this->context,
                    Permission::updateBillings(),
                    $this->examples->ltcsBillings[1]->id,
                )
                ->andReturn(Seq::emptySeq());

            $this->assertThrows(
                NotFoundException::class,
                function (): void {
                    $this->interactor->handle($this->context, $this->examples->ltcsBillings[1]->id, $this->editValue);
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
                    $this->ltcsBillingRepository
                        ->expects('store')
                        ->andReturnUsing(fn (LtcsBilling $x): LtcsBilling => $x);
                    return $callback();
                });

            $this->interactor->handle($this->context, $this->examples->ltcsBillings[1]->id, $this->editValue);
        });
        $this->should('log using info', function (): void {
            $context = ['organizationId' => $this->examples->organizations[0]->id];
            $this->context
                ->expects('logContext')
                ->andReturn($context);
            $this->logger
                ->expects('info')
                ->with(
                    '介護保険サービス：請求が更新されました',
                    ['id' => $this->examples->ltcsBillings[1]->id] + $context
                );
            $this->interactor->handle($this->context, $this->examples->ltcsBillings[1]->id, $this->editValue);
        });
    }
}

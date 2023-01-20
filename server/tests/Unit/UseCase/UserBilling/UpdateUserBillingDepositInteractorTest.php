<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\UserBilling;

use App\Concretes\PermanentDatabaseTransactionManager;
use Closure;
use Domain\Common\Carbon;
use Domain\Permission\Permission;
use Domain\UserBilling\UserBilling;
use Domain\UserBilling\UserBillingResult;
use Lib\Exceptions\NotFoundException;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\LoggerMixin;
use Tests\Unit\Mixins\LookupUserBillingUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\TransactionManagerMixin;
use Tests\Unit\Mixins\UserBillingRepositoryMixin;
use Tests\Unit\Test;
use UseCase\UserBilling\UpdateUserBillingDepositInteractor;

/**
 * {@link \UseCase\UserBilling\UpdateUserBillingDepositInteractor} Test.
 */
class UpdateUserBillingDepositInteractorTest extends Test
{
    use CarbonMixin;
    use ContextMixin;
    use ExamplesConsumer;
    use LoggerMixin;
    use MockeryMixin;
    use UserBillingRepositoryMixin;
    use TransactionManagerMixin;
    use UnitSupport;
    use LookupUserBillingUseCaseMixin;

    private UserBilling $userBilling;
    private UpdateUserBillingDepositInteractor $interactor;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (UpdateUserBillingDepositInteractorTest $self): void {
            $self->userBillingRepository
                ->allows('store')
                ->andReturn($self->examples->userBillings[0])
                ->byDefault();
            $self->userBillingRepository
                ->allows('transactionManager')
                ->andReturn(PermanentDatabaseTransactionManager::class)
                ->passthru();
            $self->logger
                ->allows('info')
                ->byDefault();
            $self->lookupUserBillingUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->examples->userBillings[0]))
                ->byDefault();

            $self->userBilling = $self->examples->userBillings[0];
            $self->interactor = app(UpdateUserBillingDepositInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('edit the userBilling after transaction begun', function (): void {
            $this->transactionManager
                ->expects('run')
                ->andReturnUsing(function (Closure $callback) {
                    $this->lookupUserBillingUseCase
                        ->expects('handle')
                        ->with($this->context, Permission::updateUserBillings(), $this->userBilling->id)
                        ->andReturn(Seq::from($this->userBilling));
                    $this->userBillingRepository
                        ->expects('store')
                        ->with(equalTo($this->userBilling->copy([
                            'result' => UserBillingResult::paid(),
                            'depositedAt' => $this->userBilling->depositedAt,
                            'updatedAt' => Carbon::now(),
                        ])))
                        ->andReturn($this->userBilling);
                    return $callback();
                });

            $this->interactor->handle($this->context, $this->userBilling->depositedAt, [$this->userBilling->id]);
        });
        $this->should('throw a NotFoundException when LookupUserBillingUseCase return Seq Empty', function (): void {
            $this->lookupUserBillingUseCase
                ->expects('handle')
                ->with($this->context, Permission::updateUserBillings(), $this->userBilling->id)
                ->andReturn(Seq::empty());

            $this->assertThrows(
                NotFoundException::class,
                function (): void {
                    $this->interactor->handle($this->context, $this->userBilling->depositedAt, [$this->userBilling->id]);
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
                ->with('利用者請求の入金日が更新されました', ['id' => ''] + $context);

            $this->interactor->handle($this->context, $this->userBilling->depositedAt, [$this->userBilling->id]);
        });
    }
}

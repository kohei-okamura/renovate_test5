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
use Tests\Unit\Mixins\OrganizationRepositoryMixin;
use Tests\Unit\Mixins\TransactionManagerMixin;
use Tests\Unit\Mixins\UserBillingRepositoryMixin;
use Tests\Unit\Test;
use UseCase\UserBilling\DeleteUserBillingDepositInteractor;

/**
 * {@link \UseCase\UserBilling\DeleteUserBillingDepositInteractor} のテスト.
 */
class DeleteUserBillingDepositInteractorTest extends Test
{
    use CarbonMixin;
    use ContextMixin;
    use ExamplesConsumer;
    use LoggerMixin;
    use LookupUserBillingUseCaseMixin;
    use MockeryMixin;
    use OrganizationRepositoryMixin;
    use TransactionManagerMixin;
    use UnitSupport;
    use UserBillingRepositoryMixin;

    private UserBilling $userBilling;
    private DeleteUserBillingDepositInteractor $interactor;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (DeleteUserBillingDepositInteractorTest $self): void {
            $self->lookupUserBillingUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->examples->userBillings[0], $self->examples->userBillings[1]))
                ->byDefault();
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
                ->andReturnNull()
                ->byDefault();

            $self->userBilling = $self->examples->userBillings[0];
            $self->interactor = app(DeleteUserBillingDepositInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('use LookupUserBillingUseCase after transaction begun', function (): void {
            $this->transactionManager
                ->expects('run')
                ->andReturnUsing(function (Closure $callback) {
                    $this->lookupUserBillingUseCase
                        ->expects('handle')
                        ->with(
                            $this->context,
                            Permission::updateUserBillings(),
                            $this->examples->userBillings[0]->id,
                            $this->examples->userBillings[1]->id,
                        )
                        ->andReturn(Seq::from($this->examples->userBillings[0], $this->examples->userBillings[1]));
                    return $callback();
                });

            $this->interactor->handle($this->context, $this->examples->userBillings[0]->id, $this->examples->userBillings[1]->id);
        });
        $this->should('throw NotFoundException when LookupUserBillingUseCase return empty', function (): void {
            $this->lookupUserBillingUseCase
                ->expects('handle')
                ->andReturn(Seq::empty());

            $this->assertThrows(NotFoundException::class, function (): void {
                $this->interactor->handle($this->context, $this->examples->userBillings[0]->id, $this->examples->userBillings[1]->id);
            });
        });
        $this->should('set the depositedAt to null after transaction begun', function (): void {
            $this->transactionManager
                ->expects('run')
                ->andReturnUsing(function (Closure $callback) {
                    $this->userBillingRepository
                        ->expects('store')
                        ->with(equalTo($this->examples->userBillings[0]->copy([
                            'result' => UserBillingResult::pending(),
                            'depositedAt' => null,
                            'updatedAt' => Carbon::now(),
                        ])))
                        ->andReturn($this->examples->userBillings[0]);
                    $this->userBillingRepository
                        ->expects('store')
                        ->with(equalTo($this->examples->userBillings[1]->copy([
                            'result' => UserBillingResult::pending(),
                            'depositedAt' => null,
                            'updatedAt' => Carbon::now(),
                        ])))
                        ->andReturn($this->examples->userBillings[1]);
                    return $callback();
                });

            $this->interactor->handle($this->context, $this->examples->userBillings[0]->id, $this->examples->userBillings[1]->id);
        });
        $this->should('log using info', function (): void {
            $context = ['organizationId' => $this->examples->organizations[0]->id];
            $this->context
                ->expects('logContext')
                ->andReturn($context);
            $this->logger
                ->expects('info')
                ->with('利用者請求入金日が削除されました', ['id' => ''] + $context)
                ->andReturnNull();

            $this->interactor->handle($this->context, $this->examples->userBillings[0]->id, $this->examples->userBillings[1]->id);
        });
    }
}

<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\UserBilling;

use App\Concretes\PermanentDatabaseTransactionManager;
use Closure;
use Domain\BankAccount\BankAccountType;
use Domain\Common\Carbon;
use Domain\Permission\Permission;
use Domain\User\PaymentMethod;
use Domain\UserBilling\UserBilling;
use Domain\UserBilling\UserBillingBankAccount;
use Domain\UserBilling\UserBillingResult;
use Lib\Exceptions\InvalidArgumentException;
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
use UseCase\UserBilling\UpdateUserBillingInteractor;

/**
 * {@link \UseCase\UserBilling\UpdateUserBillingInteractor} のテスト.
 */
class UpdateUserBillingInteractorTest extends Test
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
    private UpdateUserBillingInteractor $interactor;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (UpdateUserBillingInteractorTest $self): void {
            $self->lookupUserBillingUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->examples->userBillings[0]))
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
                ->byDefault();

            $self->userBilling = $self->examples->userBillings[0];
            $self->interactor = app(UpdateUserBillingInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('update the UserBilling after transaction begun', function (): void {
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
                            'carriedOverAmount' => $this->payload()['carriedOverAmount'],
                            'user' => $this->userBilling->user->copy([
                                'billingDestination' => $this->userBilling->user->billingDestination->copy([
                                    'paymentMethod' => $this->payload()['paymentMethod'],
                                ]),
                                'bankAccount' => $this->payload()['bankAccount'],
                            ]),
                            'result' => UserBillingResult::pending(),
                            'updatedAt' => Carbon::now(),
                        ])))
                        ->andReturn($this->userBilling);
                    return $callback();
                });

            $this->interactor->handle($this->context, $this->userBilling->id, $this->payload());
        });
        $this->should('update the result of the UserBilling to none when the totalAmount is 0', function (): void {
            $userBilling = $this->userBilling->copy([
                'dwsItem' => $this->userBilling->dwsItem->copy([
                    'copayWithTax' => 40,
                ]),
                'ltcsItem' => $this->userBilling->ltcsItem->copy([
                    'copayWithTax' => 60,
                ]),
                'otherItems' => [],
            ]);
            $this->transactionManager
                ->expects('run')
                ->andReturnUsing(function (Closure $callback) use ($userBilling) {
                    $this->lookupUserBillingUseCase
                        ->expects('handle')
                        ->andReturn(Seq::from($userBilling));
                    $this->userBillingRepository
                        ->expects('store')
                        ->with(equalTo($userBilling->copy([
                            'carriedOverAmount' => $this->payload()['carriedOverAmount'],
                            'user' => $userBilling->user->copy([
                                'billingDestination' => $userBilling->user->billingDestination->copy([
                                    'paymentMethod' => $this->payload()['paymentMethod'],
                                ]),
                                'bankAccount' => $this->payload()['bankAccount'],
                            ]),
                            'result' => UserBillingResult::none(),
                            'updatedAt' => Carbon::now(),
                        ])))
                        ->andReturn($userBilling);
                    return $callback();
                });

            $this->interactor->handle($this->context, $userBilling->id, $this->payload());
        });
        $this->should('return the UserBilling', function (): void {
            $this->assertModelStrictEquals(
                $this->userBilling,
                $this->interactor->handle($this->context, $this->userBilling->id, $this->payload())
            );
        });
        $this->should('throw a InvalidArgumentException when the totalAmount is negative', function (): void {
            $this->lookupUserBillingUseCase
                ->expects('handle')
                ->with($this->context, Permission::updateUserBillings(), $this->userBilling->id)
                ->andReturn(Seq::from($this->userBilling->copy([
                    'dwsItem' => $this->userBilling->dwsItem->copy([
                        'copayWithTax' => 40,
                    ]),
                    'ltcsItem' => $this->userBilling->ltcsItem->copy([
                        'copayWithTax' => 59,
                    ]),
                    'otherItems' => [],
                ])));

            $this->assertThrows(InvalidArgumentException::class, function (): void {
                $this->interactor->handle($this->context, $this->userBilling->id, $this->payload());
            });
        });
        $this->should('throw a NotFoundException when LookupUserBillingUseCase return empty seq', function (): void {
            $this->lookupUserBillingUseCase
                ->expects('handle')
                ->with($this->context, Permission::updateUserBillings(), $this->userBilling->id)
                ->andReturn(Seq::empty());

            $this->assertThrows(
                NotFoundException::class,
                function (): void {
                    $this->interactor->handle(
                        $this->context,
                        $this->userBilling->id,
                        $this->payload()
                    );
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
                ->with('利用者請求が更新されました', ['id' => $this->userBilling->id] + $context);

            $this->interactor->handle(
                $this->context,
                $this->userBilling->id,
                $this->payload()
            );
        });
    }

    /**
     * payload が返す配列.
     *
     * @return array
     */
    private function payload(): array
    {
        return [
            'carriedOverAmount' => -100,
            'paymentMethod' => PaymentMethod::transfer(),
            'bankAccount' => UserBillingBankAccount::create([
                'bankName' => '銀行名',
                'bankCode' => '1234',
                'bankBranchName' => '支店名',
                'bankBranchCode' => '5678',
                'bankAccountType' => BankAccountType::currentDeposit(),
                'bankAccountNumber' => '12345678',
                'bankAccountHolder' => '名 義',
            ]),
        ];
    }
}

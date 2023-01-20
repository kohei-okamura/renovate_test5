<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\UserBilling;

use Closure;
use Domain\Common\Carbon;
use Domain\Common\Pagination;
use Domain\FinderResult;
use Domain\Permission\Permission;
use Domain\UserBilling\UserBillingResult;
use Domain\UserBilling\WithdrawalResultCode;
use Domain\UserBilling\WithdrawalTransaction;
use Domain\UserBilling\WithdrawalTransactionItem;
use Domain\UserBilling\ZenginDataRecord;
use Domain\UserBilling\ZenginDataRecordCode;
use Lib\Exceptions\NotFoundException;
use ScalikePHP\Option;
use ScalikePHP\Seq;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\CreateWithdrawalTransactionAsyncValidatorMixin;
use Tests\Unit\Mixins\FindUserBillingUseCaseMixin;
use Tests\Unit\Mixins\LoggerMixin;
use Tests\Unit\Mixins\LookupOrganizationSettingUseCaseMixin;
use Tests\Unit\Mixins\LookupUserBillingUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\TransactionManagerMixin;
use Tests\Unit\Mixins\UserBillingRepositoryMixin;
use Tests\Unit\Mixins\WithdrawalTransactionRepositoryMixin;
use Tests\Unit\Test;
use UseCase\UserBilling\CreateWithdrawalTransactionInteractor;

/**
 * {@link \UseCase\UserBilling\CreateWithdrawalTransactionInteractor} のテスト.
 */
final class CreateWithdrawalTransactionInteractorTest extends Test
{
    use CarbonMixin;
    use ContextMixin;
    use CreateWithdrawalTransactionAsyncValidatorMixin;
    use ExamplesConsumer;
    use FindUserBillingUseCaseMixin;
    use MatchesSnapshots;
    use MockeryMixin;
    use TransactionManagerMixin;
    use LoggerMixin;
    use LookupOrganizationSettingUseCaseMixin;
    use LookupUserBillingUseCaseMixin;
    use UnitSupport;
    use WithdrawalTransactionRepositoryMixin;
    use UserBillingRepositoryMixin;

    private CreateWithdrawalTransactionInteractor $interactor;

    /**
     * 初期化処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachSpec(function (self $self): void {
            $self->createWithdrawalTransactionAsyncValidator
                ->allows('validate')
                ->andReturnNull()
                ->byDefault();
            $self->lookupUserBillingUseCase
                ->allows('handle')
                ->andReturn(Seq::from(
                    $self->examples->userBillings[0],
                    $self->examples->userBillings[1],
                    $self->examples->userBillings[4],
                    $self->examples->userBillings[13]
                ))
                ->byDefault();
            $self->lookupOrganizationSettingUseCase
                ->allows('handle')
                ->andReturn(Option::some($self->examples->organizationSettings[0]))
                ->byDefault();
            $self->findUserBillingUseCase
                ->allows('handle')
                ->andReturn(FinderResult::from($self->examples->userBillings, Pagination::create()))
                ->byDefault();
            $self->withdrawalTransactionRepository
                ->allows('store')
                ->andReturn($self->examples->withdrawalTransactions[0])
                ->byDefault();
            $self->userBillingRepository
                ->allows('store')
                ->andReturn($self->examples->userBillings[0])
                ->byDefault();
            $self->logger
                ->allows('info')
                ->byDefault();

            $self->interactor = app(CreateWithdrawalTransactionInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('use createWithdrawalTransactionAsyncValidator', function (): void {
            $this->createWithdrawalTransactionAsyncValidator
                ->expects('validate')
                ->with(
                    $this->context,
                    [
                        'userBillingIds' => [
                            $this->examples->userBillings[0]->id,
                            $this->examples->userBillings[1]->id,
                            $this->examples->userBillings[4]->id,
                            $this->examples->userBillings[13]->id,
                        ],
                    ]
                )
                ->andReturnNull();
            $this->interactor->handle(
                $this->context,
                [
                    $this->examples->userBillings[0]->id,
                    $this->examples->userBillings[1]->id,
                    $this->examples->userBillings[4]->id,
                    $this->examples->userBillings[13]->id,
                ]
            );
        });
        $this->should('use LookupUserBillingUseCase after transaction begun', function (): void {
            $this->transactionManager
                ->expects('run')
                ->andReturnUsing(function (Closure $callback) {
                    $this->lookupUserBillingUseCase
                        ->expects('handle')
                        ->with(
                            $this->context,
                            Permission::createWithdrawalTransactions(),
                            $this->examples->userBillings[0]->id,
                            $this->examples->userBillings[1]->id,
                            $this->examples->userBillings[4]->id,
                            $this->examples->userBillings[13]->id,
                        )
                        ->andReturn(Seq::from(
                            $this->examples->userBillings[0],
                            $this->examples->userBillings[1],
                            $this->examples->userBillings[4],
                            $this->examples->userBillings[13]
                        ));
                    return $callback();
                });

            $this->interactor->handle(
                $this->context,
                [
                    $this->examples->userBillings[0]->id,
                    $this->examples->userBillings[1]->id,
                    $this->examples->userBillings[4]->id,
                    $this->examples->userBillings[13]->id,
                ]
            );
        });
        $this->should('throw NotFoundException when LookupUserBillingUseCase return empty', function (): void {
            $this->lookupUserBillingUseCase
                ->expects('handle')
                ->andReturn(Seq::empty());

            $this->assertThrows(NotFoundException::class, function (): void {
                $this->interactor->handle(
                    $this->context,
                    [
                        $this->examples->userBillings[0]->id,
                        $this->examples->userBillings[1]->id,
                        $this->examples->userBillings[4]->id,
                        $this->examples->userBillings[13]->id,
                    ]
                );
            });
        });
        $this->should('use LookupOrganizationSettingUseCase after transaction begun', function (): void {
            $this->transactionManager
                ->expects('run')
                ->andReturnUsing(function (Closure $callback) {
                    $this->lookupOrganizationSettingUseCase
                        ->expects('handle')
                        ->with($this->context, Permission::createWithdrawalTransactions())
                        ->andReturn(Option::some($this->examples->organizationSettings[0]));
                    return $callback();
                });

            $this->interactor->handle(
                $this->context,
                [
                    $this->examples->userBillings[0]->id,
                    $this->examples->userBillings[1]->id,
                    $this->examples->userBillings[4]->id,
                    $this->examples->userBillings[13]->id,
                ]
            );
        });
        $this->should('throw NotFoundException when LookupOrganizationSettingUseCase return none', function (): void {
            $this->lookupOrganizationSettingUseCase
                ->expects('handle')
                ->andReturn(Option::none());

            $this->assertThrows(NotFoundException::class, function (): void {
                $this->interactor->handle(
                    $this->context,
                    [
                        $this->examples->userBillings[0]->id,
                        $this->examples->userBillings[1]->id,
                        $this->examples->userBillings[4]->id,
                        $this->examples->userBillings[13]->id,
                    ]
                );
            });
        });
        $this->should('use FindUserBillingUseCase', function (): void {
            $this->findUserBillingUseCase
                ->expects('handle')
                ->with(
                    $this->context,
                    Permission::createWithdrawalTransactions(),
                    [
                        'contractNumber' => $this->examples->userBillings[0]->user->billingDestination->contractNumber,
                        'withdrawalResultCode' => WithdrawalResultCode::done()->value(),
                    ],
                    [
                        'all' => true,
                        'sortBy' => 'date',
                        'desc' => true,
                    ]
                )
                ->andReturn(FinderResult::from($this->examples->userBillings, Pagination::create()));
            $this->findUserBillingUseCase
                ->expects('handle')
                ->with(
                    $this->context,
                    Permission::createWithdrawalTransactions(),
                    [
                        'contractNumber' => $this->examples->userBillings[1]->user->billingDestination->contractNumber,
                        'withdrawalResultCode' => WithdrawalResultCode::done()->value(),
                    ],
                    [
                        'all' => true,
                        'sortBy' => 'date',
                        'desc' => true,
                    ]
                )
                ->andReturn(FinderResult::from($this->examples->userBillings, Pagination::create()));
            $this->findUserBillingUseCase
                ->expects('handle')
                ->with(
                    $this->context,
                    Permission::createWithdrawalTransactions(),
                    [
                        'contractNumber' => $this->examples->userBillings[4]->user->billingDestination->contractNumber,
                        'withdrawalResultCode' => WithdrawalResultCode::done()->value(),
                    ],
                    [
                        'all' => true,
                        'sortBy' => 'date',
                        'desc' => true,
                    ]
                )
                ->andReturn(FinderResult::from($this->examples->userBillings, Pagination::create()));

            $this->interactor->handle(
                $this->context,
                [
                    $this->examples->userBillings[0]->id,
                    $this->examples->userBillings[1]->id,
                    $this->examples->userBillings[4]->id,
                    $this->examples->userBillings[13]->id,
                ]
            );
        });
        $this->should('log using info', function (): void {
            $context = ['organizationId' => $this->examples->organizations[0]->id];
            $this->context
                ->expects('logContext')
                ->andReturn($context);
            $this->logger
                ->expects('info')
                ->with('口座振替データが登録されました', ['id' => $this->examples->withdrawalTransactions[0]->id] + $context);

            $this->interactor->handle(
                $this->context,
                [
                    $this->examples->userBillings[0]->id,
                    $this->examples->userBillings[1]->id,
                    $this->examples->userBillings[4]->id,
                    $this->examples->userBillings[13]->id,
                ]
            );
        });
        $this->should('store WithdrawalTransaction', function (): void {
            $this->findUserBillingUseCase
                ->expects('handle')
                ->andReturn(FinderResult::from($this->examples->userBillings, Pagination::create()))
                ->twice();
            $this->findUserBillingUseCase
                ->expects('handle')
                ->andReturn(FinderResult::from([], Pagination::create()));

            $this->withdrawalTransactionRepository
                ->expects('store')
                ->with(equalTo(
                    WithdrawalTransaction::create([
                        'id' => null,
                        'organizationId' => $this->context->organization->id,
                        'items' => [
                            WithdrawalTransactionItem::create([
                                'userBillingIds' => [$this->examples->userBillings[0]->id],
                                'zenginRecord' => ZenginDataRecord::from(
                                    Seq::from($this->examples->userBillings[0]),
                                    $this->examples->organizationSettings[0],
                                    ZenginDataRecordCode::other()
                                ),
                            ]),
                            WithdrawalTransactionItem::create([
                                'userBillingIds' => [$this->examples->userBillings[1]->id],
                                'zenginRecord' => ZenginDataRecord::from(
                                    Seq::from($this->examples->userBillings[1]),
                                    $this->examples->organizationSettings[0],
                                    ZenginDataRecordCode::firstTime()
                                ),
                            ]),
                            WithdrawalTransactionItem::create([
                                'userBillingIds' => [$this->examples->userBillings[4]->id],
                                'zenginRecord' => ZenginDataRecord::from(
                                    Seq::from($this->examples->userBillings[4]),
                                    $this->examples->organizationSettings[0],
                                    ZenginDataRecordCode::firstTime()
                                ),
                            ]),
                        ],
                        'deductedOn' => $this->examples->userBillings[0]->deductedOn,
                        'downloadedAt' => null,
                        'createdAt' => Carbon::now(),
                        'updatedAt' => Carbon::now(),
                    ]),
                ))
                ->andReturn($this->examples->withdrawalTransactions[0]);

            $this->interactor->handle(
                $this->context,
                [
                    $this->examples->userBillings[0]->id,
                    $this->examples->userBillings[1]->id,
                    $this->examples->userBillings[4]->id,
                    $this->examples->userBillings[13]->id,
                ]
            );
        });
        $this->should('store(update) UserBilling', function (): void {
            $userBilling = $this->examples->userBillings[0];

            $this->findUserBillingUseCase
                ->expects('handle')
                ->andReturn(FinderResult::from([$userBilling], Pagination::create()))
                ->times(3);

            $this->userBillingRepository
                ->expects('store')
                ->with(equalTo($userBilling->copy([
                    'result' => UserBillingResult::inProgress(),
                    'updatedAt' => Carbon::now(),
                ])))
                ->andReturn($userBilling);

            $this->interactor->handle(
                $this->context,
                [
                    $this->examples->userBillings[0]->id,
                    $this->examples->userBillings[1]->id,
                    $this->examples->userBillings[4]->id,
                    $this->examples->userBillings[13]->id,
                ]
            );
        });
        $this->should('return WithdrawalTransaction', function (): void {
            $this->assertModelStrictEquals(
                $this->examples->withdrawalTransactions[0],
                $this->interactor->handle(
                    $this->context,
                    [
                        $this->examples->userBillings[0]->id,
                        $this->examples->userBillings[1]->id,
                        $this->examples->userBillings[4]->id,
                        $this->examples->userBillings[13]->id,
                    ]
                )
            );
        });
    }
}

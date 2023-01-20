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
use Domain\UserBilling\UserBilling;
use Lib\Exceptions\LogicException;
use ScalikePHP\Option;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\BuildUserBillingUseCaseMixin;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\TransactionManagerMixin;
use Tests\Unit\Mixins\UserBillingRepositoryMixin;
use Tests\Unit\Test;
use UseCase\UserBilling\CreateUserBillingInteractor;

/**
 * {@link \UseCase\UserBilling\CreateUserBillingInteractor} のテスト.
 */
final class CreateUserBillingInteractorTest extends Test
{
    use BuildUserBillingUseCaseMixin;
    use CarbonMixin;
    use ContextMixin;
    use ExamplesConsumer;
    use MockeryMixin;
    use TransactionManagerMixin;
    use UnitSupport;
    use UserBillingRepositoryMixin;

    private CreateUserBillingInteractor $interactor;
    private UserBilling $userBilling;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (CreateUserBillingInteractorTest $self): void {
            $self->userBillingRepository
                ->allows('store')
                ->andReturn($self->examples->userBillings[0])
                ->byDefault();
            $self->userBillingRepository
                ->allows('transactionManager')
                ->andReturn(PermanentDatabaseTransactionManager::class)
                ->passthru();
            $self->buildUserBillingUseCase
                ->allows('handle')
                ->andReturn($self->examples->userBillings[0])
                ->byDefault();
            $self->userBilling = $self->examples->userBillings[0];
            $self->interactor = app(CreateUserBillingInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('call store() of repository after transaction begun', function (): void {
            $this->transactionManager
                ->expects('run')
                ->andReturnUsing(function (Closure $callback) {
                    // トランザクションを開始する前に編集処理が行われないことを検証するため
                    // `run` に渡されたコールバック関数を呼び出す直前にモックの定義を行う
                    // なお、トランザクションの終了後に編集処理が行われないことの検証は（恐らく）できない
                    $this->userBillingRepository
                        ->expects('store')
                        ->andReturnUsing(fn (UserBilling $x): UserBilling => $x);
                    return $callback();
                });

            $this->interactor->handle(
                $this->context,
                $this->examples->users[0],
                $this->examples->offices[0],
                Carbon::now(),
                Option::from($this->examples->dwsBillingStatements[0]),
                Option::from($this->examples->ltcsBillingStatements[0]),
                Option::from($this->examples->dwsProvisionReports[0]),
                Option::from($this->examples->ltcsProvisionReports[0])
            );
        });
        $this->should('return UserBilling', function (): void {
            $actual = $this->interactor->handle(
                $this->context,
                $this->examples->users[0],
                $this->examples->offices[0],
                Carbon::now(),
                Option::from($this->examples->dwsBillingStatements[0]),
                Option::from($this->examples->ltcsBillingStatements[0]),
                Option::from($this->examples->dwsProvisionReports[0]),
                Option::from($this->examples->ltcsProvisionReports[0])
            );

            $this->assertModelStrictEquals($this->examples->userBillings[0], $actual);
        });
        $this->should('use buildUserBillingUseCase handle', function (): void {
            $dwsStatement = Option::from($this->examples->dwsBillingStatements[0]);
            $ltcsStatement = Option::from($this->examples->ltcsBillingStatements[0]);
            $dwsProvisionReport = Option::from($this->examples->dwsProvisionReports[0]);
            $ltcsProvisionReport = Option::from($this->examples->ltcsProvisionReports[0]);
            $this->buildUserBillingUseCase
                ->expects('handle')
                ->with(
                    $this->context,
                    $this->examples->users[0],
                    $this->examples->offices[0],
                    equalTo(Carbon::now()),
                    $dwsStatement,
                    $ltcsStatement,
                    $dwsProvisionReport,
                    $ltcsProvisionReport
                )
                ->andReturn($this->examples->userBillings[0]);

            $this->interactor->handle(
                $this->context,
                $this->examples->users[0],
                $this->examples->offices[0],
                Carbon::now(),
                $dwsStatement,
                $ltcsStatement,
                $dwsProvisionReport,
                $ltcsProvisionReport
            );
        });
        $this->should('throw a LogicException when statement and provisionReport is null', function (): void {
            $this->assertThrows(LogicException::class, function (): void {
                $this->interactor->handle(
                    $this->context,
                    $this->examples->users[0],
                    $this->examples->offices[0],
                    Carbon::now(),
                    Option::none(),
                    Option::none(),
                    Option::none(),
                    Option::none()
                );
            });
        });
    }
}

<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\App\Validations\Rules;

use Domain\Permission\Permission;
use Domain\UserBilling\UserBilling;
use Domain\UserBilling\UserBillingResult;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\LookupUserBillingUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;

/**
 * {@link \App\Validations\Rules\AllUserBillingsArePendingRule} のテスト.
 */
final class AllUserBillingsArePendingRuleTest extends Test
{
    use ContextMixin;
    use ExamplesConsumer;
    use MockeryMixin;
    use LookupUserBillingUseCaseMixin;
    use RuleTestSupport;
    use UnitSupport;

    /**
     * @test
     * @return void
     */
    public function describe_validateNoUserBillingsTransacted(): void
    {
        $this->should('pass when values contain not existing userBillingId', function (): void {
            $this->lookupUserBillingUseCase
                ->expects('handle')
                ->with(
                    $this->context,
                    Permission::createWithdrawalTransactions(),
                    self::NOT_EXISTING_ID,
                    ...Seq::from(...$this->examples->userBillings)->map(fn (UserBilling $x) => $x->id)
                )
                ->andReturn(Seq::from(...$this->examples->userBillings));

            $this->assertTrue(
                $this->buildCustomValidator(
                    [
                        'values' => [
                            self::NOT_EXISTING_ID,
                            ...Seq::from(...$this->examples->userBillings)->map(fn (UserBilling $x) => $x->id),
                        ],
                    ],
                    ['values' => 'all_user_billings_are_pending:' . Permission::createWithdrawalTransactions()]
                )
                    ->passes()
            );
        });
        $this->should('fail when values contain userBillingId whose result is not pending', function (): void {
            $this->lookupUserBillingUseCase
                ->expects('handle')
                ->andReturn(Seq::from(...[
                    $this->examples->userBillings[0]->copy(['result' => UserBillingResult::pending()]),
                    $this->examples->userBillings[1]->copy(['result' => UserBillingResult::inProgress()]),
                ]))
                ->twice();

            $this->assertTrue(
                $this->buildCustomValidator(
                    ['values' => [$this->examples->userBillings[0]->id, $this->examples->userBillings[1]->id]],
                    ['values' => 'all_user_billings_are_pending:' . Permission::createWithdrawalTransactions()]
                )
                    ->fails()
            );
        });
        $this->should('pass when values dont contain userBillingId whose createdAt is not null', function (): void {
            $this->lookupUserBillingUseCase
                ->expects('handle')
                ->andReturn(Seq::from(...[
                    $this->examples->userBillings[0]->copy(['result' => UserBillingResult::pending()]),
                    $this->examples->userBillings[1]->copy(['result' => UserBillingResult::pending()]),
                ]))
                ->twice();

            $this->assertTrue(
                $this->buildCustomValidator(
                    ['values' => [$this->examples->userBillings[0]->id, $this->examples->userBillings[1]->id]],
                    ['values' => 'all_user_billings_are_pending:' . Permission::createWithdrawalTransactions()]
                )
                    ->passes()
            );
        });
    }
}

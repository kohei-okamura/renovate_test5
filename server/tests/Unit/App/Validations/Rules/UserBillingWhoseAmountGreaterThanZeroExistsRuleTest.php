<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\App\Validations\Rules;

use Domain\Permission\Permission;
use Domain\UserBilling\UserBilling;
use Domain\UserBilling\UserBillingDwsItem;
use Domain\UserBilling\UserBillingLtcsItem;
use Domain\UserBilling\UserBillingOtherItem;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\LookupUserBillingUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;

/**
 * {@link \App\Validations\Rules\UserBillingWhoseAmountGreaterThanZeroExistsRule} のテスト.
 */
final class UserBillingWhoseAmountGreaterThanZeroExistsRuleTest extends Test
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
    public function describe_validateUserBillingWhoseAmountGreaterThanZeroExists(): void
    {
        $this->should('pass when values contain not existing userBillingId', function (): void {
            $this->lookupUserBillingUseCase
                ->expects('handle')
                ->with(
                    $this->context,
                    Permission::createWithdrawalTransactions(),
                    self::NOT_EXISTING_ID,
                    ...Seq::from(...$this->examples->userBillings)->map(fn (UserBilling $x): int => $x->id)
                )
                ->andReturn(Seq::from(...$this->examples->userBillings));

            $this->assertTrue(
                $this->buildCustomValidator(
                    [
                        'values' => [
                            self::NOT_EXISTING_ID,
                            ...Seq::from(...$this->examples->userBillings)->map(fn (UserBilling $x): int => $x->id),
                        ],
                    ],
                    ['values' => 'user_billing_whose_amount_greater_than_zero_exists:' . Permission::createWithdrawalTransactions()]
                )
                    ->passes()
            );
        });
        $this->should('fail when values do not contain userBillingId whose amount greater than 0', function (): void {
            $this->lookupUserBillingUseCase
                ->expects('handle')
                ->andReturn(Seq::from(...[
                    $this->examples->userBillings[0]->copy([
                        'user' => $this->examples->userBillings[0]->user->copy([
                            'billingDestination' => $this->examples->userBillings[0]->user->billingDestination->copy([
                                'contractNumber' => '1234567890',
                            ]),
                        ]),
                        'dwsItem' => UserBillingDwsItem::create(['copayWithTax' => 30]),
                        'ltcsItem' => UserBillingLtcsItem::create(['copayWithTax' => 40]),
                        'otherItems' => [
                            UserBillingOtherItem::create(['copayWithTax' => 20]),
                            UserBillingOtherItem::create(['copayWithTax' => 20]),
                        ],
                        'carriedOverAmount' => -3000,
                    ]),
                    $this->examples->userBillings[1]->copy([
                        'user' => $this->examples->userBillings[0]->user->copy([
                            'billingDestination' => $this->examples->userBillings[0]->user->billingDestination->copy([
                                'contractNumber' => '5678901234',
                            ]),
                        ]),
                        'dwsItem' => null,
                        'ltcsItem' => UserBillingLtcsItem::create(['copayWithTax' => 40]),
                        'otherItems' => [],
                        'carriedOverAmount' => 0,
                    ]),
                    $this->examples->userBillings[2]->copy([
                        'user' => $this->examples->userBillings[0]->user->copy([
                            'billingDestination' => $this->examples->userBillings[0]->user->billingDestination->copy([
                                'contractNumber' => '5678901234',
                            ]),
                        ]),
                        'dwsItem' => null,
                        'ltcsItem' => UserBillingLtcsItem::create(['copayWithTax' => 60]),
                        'otherItems' => [],
                        'carriedOverAmount' => -100,
                    ]),
                ]))
                ->twice();

            $this->assertTrue(
                $this->buildCustomValidator(
                    ['values' => [$this->examples->userBillings[0]->id, $this->examples->userBillings[1]->id, $this->examples->userBillings[2]->id]],
                    ['values' => 'user_billing_whose_amount_greater_than_zero_exists:' . Permission::createWithdrawalTransactions()]
                )
                    ->fails()
            );
        });
        $this->should('pass when values contain userBillingId whose amount greater than 0', function (): void {
            $this->lookupUserBillingUseCase
                ->expects('handle')
                ->andReturn(Seq::from(...[
                    $this->examples->userBillings[0]->copy([
                        'user' => $this->examples->userBillings[0]->user->copy([
                            'billingDestination' => $this->examples->userBillings[0]->user->billingDestination->copy([
                                'contractNumber' => '1234567890',
                            ]),
                        ]),
                        'dwsItem' => UserBillingDwsItem::create(['copayWithTax' => 30]),
                        'ltcsItem' => UserBillingLtcsItem::create(['copayWithTax' => 40]),
                        'otherItems' => [
                            UserBillingOtherItem::create(['copayWithTax' => 20]),
                            UserBillingOtherItem::create(['copayWithTax' => 20]),
                        ],
                        'carriedOverAmount' => -3000,
                    ]),
                    $this->examples->userBillings[1]->copy([
                        'user' => $this->examples->userBillings[0]->user->copy([
                            'billingDestination' => $this->examples->userBillings[0]->user->billingDestination->copy([
                                'contractNumber' => '5678901234',
                            ]),
                        ]),
                        'dwsItem' => null,
                        'ltcsItem' => UserBillingLtcsItem::create(['copayWithTax' => 40]),
                        'otherItems' => [],
                        'carriedOverAmount' => -50,
                    ]),
                    $this->examples->userBillings[2]->copy([
                        'user' => $this->examples->userBillings[0]->user->copy([
                            'billingDestination' => $this->examples->userBillings[0]->user->billingDestination->copy([
                                'contractNumber' => '5678901234',
                            ]),
                        ]),
                        'dwsItem' => null,
                        'ltcsItem' => UserBillingLtcsItem::create(['copayWithTax' => 10]),
                        'otherItems' => [],
                        'carriedOverAmount' => 1,
                    ]),
                ]))
                ->twice();

            $this->assertTrue(
                $this->buildCustomValidator(
                    ['values' => [$this->examples->userBillings[0]->id, $this->examples->userBillings[1]->id, $this->examples->userBillings[2]->id]],
                    ['values' => 'user_billing_whose_amount_greater_than_zero_exists:' . Permission::createWithdrawalTransactions()]
                )
                    ->passes()
            );
        });
    }
}

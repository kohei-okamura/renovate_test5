<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\App\Validations\Rules;

use Domain\Permission\Permission;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\LookupUserBillingUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;

/**
 * {@link \App\Validations\Rules\UserBillingAmountIsNonNegativeRule} のテスト.
 */
final class UserBillingAmountIsNonNegativeRuleTest extends Test
{
    use ExamplesConsumer;
    use MockeryMixin;
    use RuleTestSupport;
    use UnitSupport;
    use LookupUserBillingUseCaseMixin;

    /**
     * 初期化処理.
     */
    public static function _setUpSuite(): void
    {
    }

    /**
     * @test
     * @return void
     */
    public function describe_validateUserBillingAmountIsNonNegative(): void
    {
        $this->should('pass when value is not integer', function (): void {
            $this->assertTrue(
                $this->buildCustomValidator(
                    ['value' => 'string'],
                    ['value' => ['user_billing_amount_is_non_negative']]
                )
                    ->passes()
            );
        });
        $this->should('use LookupUserBillingUseCase', function (): void {
            $id = 1;
            $this->lookupUserBillingUseCase
                ->expects('handle')
                ->with($this->context, Permission::updateUserBillings(), $id)
                ->andReturn(Seq::from($this->examples->userBillings[0]));
            $this->buildCustomValidator(
                ['value' => 10, 'id' => $id],
                ['value' => ['user_billing_amount_is_non_negative']]
            )
                ->validate();
        });
        $this->should('pass when LookupUserBillingUseCase return empty', function (): void {
            $id = 1;
            $this->lookupUserBillingUseCase
                ->expects('handle')
                ->andReturn(Seq::empty());
            $this->assertTrue(
                $this->buildCustomValidator(
                    ['value' => 10, 'id' => $id],
                    ['value' => ['user_billing_amount_is_non_negative']]
                )
                    ->passes()
            );
        });
        $this->should('fail when user billing amount is less than 0', function (): void {
            $id = 1;
            $this->lookupUserBillingUseCase
                ->expects('handle')
                ->andReturn(Seq::from($this->examples->userBillings[0]->copy([
                    'dwsItem' => $this->examples->userBillings[0]->dwsItem->copy([
                        'copayWithTax' => 1000,
                    ]),
                    'ltcsItem' => $this->examples->userBillings[0]->ltcsItem->copy([
                        'copayWithTax' => 1000,
                    ]),
                    'otherItems' => [$this->examples->userBillings[0]->otherItems[0]->copy([
                        'copayWithTax' => 1000,
                    ])],
                    'carriedOverAmount' => 1000,
                ])));
            $this->assertTrue(
                $this->buildCustomValidator(
                    ['value' => -3001, 'id' => $id],
                    ['value' => ['user_billing_amount_is_non_negative']]
                )
                    ->fails()
            );
        });
        $this->should('pass when user billing amount is 0', function (): void {
            $id = 1;
            $this->lookupUserBillingUseCase
                ->expects('handle')
                ->andReturn(Seq::from($this->examples->userBillings[0]->copy([
                    'dwsItem' => $this->examples->userBillings[0]->dwsItem->copy([
                        'copayWithTax' => 1000,
                    ]),
                    'ltcsItem' => $this->examples->userBillings[0]->ltcsItem->copy([
                        'copayWithTax' => 1000,
                    ]),
                    'otherItems' => [$this->examples->userBillings[0]->otherItems[0]->copy([
                        'copayWithTax' => 1000,
                    ])],
                    'carriedOverAmount' => -2000,
                ])));
            $this->assertTrue(
                $this->buildCustomValidator(
                    ['value' => -3000, 'id' => $id],
                    ['value' => ['user_billing_amount_is_non_negative']]
                )
                    ->passes()
            );
        });
        $this->should('pass when user billing amount is greater than 0', function (): void {
            $id = 1;
            $this->lookupUserBillingUseCase
                ->expects('handle')
                ->andReturn(Seq::from($this->examples->userBillings[0]->copy([
                    'dwsItem' => $this->examples->userBillings[0]->dwsItem->copy([
                        'copayWithTax' => 1000,
                    ]),
                    'ltcsItem' => $this->examples->userBillings[0]->ltcsItem->copy([
                        'copayWithTax' => 1000,
                    ]),
                    'otherItems' => [$this->examples->userBillings[0]->otherItems[0]->copy([
                        'copayWithTax' => 1000,
                    ])],
                    'carriedOverAmount' => -2000,
                ])));
            $this->assertTrue(
                $this->buildCustomValidator(
                    ['value' => -2999, 'id' => $id],
                    ['value' => ['user_billing_amount_is_non_negative']]
                )
                    ->passes()
            );
        });
        $this->should('pass when user billing does not have dws item', function (): void {
            $id = 1;
            $this->lookupUserBillingUseCase
                ->expects('handle')
                ->andReturn(Seq::from($this->examples->userBillings[0]->copy([
                    'dwsItem' => null,
                    'ltcsItem' => $this->examples->userBillings[0]->ltcsItem->copy([
                        'copayWithTax' => 1000,
                    ]),
                    'otherItems' => [$this->examples->userBillings[0]->otherItems[0]->copy([
                        'copayWithTax' => 1000,
                    ])],
                ])));
            $this->assertTrue(
                $this->buildCustomValidator(
                    ['value' => -2000, 'id' => $id],
                    ['value' => ['user_billing_amount_is_non_negative']]
                )
                    ->passes()
            );
        });
        $this->should('pass when user billing does not have ltcs item', function (): void {
            $id = 1;
            $this->lookupUserBillingUseCase
                ->expects('handle')
                ->andReturn(Seq::from($this->examples->userBillings[0]->copy([
                    'dwsItem' => $this->examples->userBillings[0]->dwsItem->copy([
                        'copayWithTax' => 1000,
                    ]),
                    'ltcsItem' => null,
                    'otherItems' => [$this->examples->userBillings[0]->otherItems[0]->copy([
                        'copayWithTax' => 1000,
                    ])],
                ])));
            $this->assertTrue(
                $this->buildCustomValidator(
                    ['value' => -2000, 'id' => $id],
                    ['value' => ['user_billing_amount_is_non_negative']]
                )
                    ->passes()
            );
        });
        $this->should('pass when user billing does not have other items', function (): void {
            $id = 1;
            $this->lookupUserBillingUseCase
                ->expects('handle')
                ->andReturn(Seq::from($this->examples->userBillings[0]->copy([
                    'dwsItem' => $this->examples->userBillings[0]->dwsItem->copy([
                        'copayWithTax' => 1000,
                    ]),
                    'ltcsItem' => $this->examples->userBillings[0]->ltcsItem->copy([
                        'copayWithTax' => 1000,
                    ]),
                    'otherItems' => [],
                ])));
            $this->assertTrue(
                $this->buildCustomValidator(
                    ['value' => -2000, 'id' => $id],
                    ['value' => ['user_billing_amount_is_non_negative']]
                )
                    ->passes()
            );
        });
    }
}

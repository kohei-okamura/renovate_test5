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
 * {@link \App\Validations\Rules\UserBillingDepositCanDeleteRule} のテスト.
 */
final class UserBillingDepositCanDeleteRuleTest extends Test
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
    public function describe_validateUserBillingDepositCanDelete(): void
    {
        $this->should('pass when value is not array', function (): void {
            $this->assertTrue(
                $this->buildCustomValidator(
                    ['value' => 'string'],
                    ['value' => ['user_billing_deposit_can_delete']]
                )
                    ->passes()
            );
        });
        $this->should('use  LookupUserBillingUseCase', function (): void {
            $this->lookupUserBillingUseCase
                ->expects('handle')
                ->with($this->context, Permission::updateUserBillings(), 1, 6)
                ->andReturn(Seq::from($this->examples->userBillings[0], $this->examples->userBillings[5]));
            $this->buildCustomValidator(
                ['value' => [1, 6]],
                ['value' => ['user_billing_deposit_can_delete']]
            )
                ->validate();
        });
        $this->should('pass when value is not array', function (): void {
            $this->lookupUserBillingUseCase
                ->expects('handle')
                ->andReturn(Seq::empty());
            $this->assertTrue(
                $this->buildCustomValidator(
                    ['value' => [1, 5]],
                    ['value' => ['user_billing_deposit_can_delete']]
                )
                    ->passes()
            );
        });
        $this->should('fail when paymentMethod is withdrawal', function (): void {
            $this->lookupUserBillingUseCase
                ->expects('handle')
                ->with($this->context, Permission::updateUserBillings(), 4)
                ->andReturn(Seq::from($this->examples->userBillings[3]));
            $this->assertTrue(
                $this->buildCustomValidator(
                    ['value' => [4]],
                    ['value' => ['user_billing_deposit_can_delete']]
                )
                    ->fails()
            );
        });
        $this->should('fail when depositedAt is null', function (): void {
            $this->lookupUserBillingUseCase
                ->expects('handle')
                ->with($this->context, Permission::updateUserBillings(), 2)
                ->andReturn(Seq::from($this->examples->userBillings[1]));
            $this->assertTrue(
                $this->buildCustomValidator(
                    ['value' => [2]],
                    ['value' => ['user_billing_deposit_can_delete']]
                )
                    ->fails()
            );
        });
        $this->should('pass when paymentMethod is not withdrawal and depositedAt is not null', function (): void {
            $this->lookupUserBillingUseCase
                ->expects('handle')
                ->with($this->context, Permission::updateUserBillings(), 1, 6)
                ->andReturn(Seq::from($this->examples->userBillings[0], $this->examples->userBillings[5]));
            $this->assertTrue(
                $this->buildCustomValidator(
                    ['value' => [1, 6]],
                    ['value' => ['user_billing_deposit_can_delete']]
                )
                    ->passes()
            );
        });
    }
}

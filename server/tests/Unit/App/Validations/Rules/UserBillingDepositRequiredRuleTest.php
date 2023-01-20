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
 * {@link \App\Validations\Rules\UserBillingDepositRequiredRule} のテスト.
 */
final class UserBillingDepositRequiredRuleTest extends Test
{
    use ExamplesConsumer;
    use MockeryMixin;
    use RuleTestSupport;
    use UnitSupport;
    use LookupUserBillingUseCaseMixin;

    private $ruleName = 'user_billing_deposit_required';

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
    public function describe_testException()
    {
        $this->should('fail when the parameter is missing', function (): void {
            $this->expectExceptionObject(
                new \InvalidArgumentException("Validation rule {$this->ruleName} requires at least 1 parameters.")
            );
            $this->buildCustomValidator(
                ['value' => [1]],
                ['value' => ["{$this->ruleName}"]]
            )
                ->validate();
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_validateUserBillingDepositRequired(): void
    {
        $this->should('pass when value is not array', function (): void {
            $this->assertTrue(
                $this->buildCustomValidator(
                    ['value' => 'string'],
                    ['value' => ["{$this->ruleName}:" . Permission::viewUserBillings()]]
                )
                    ->passes()
            );
        });
        $this->should('use LookupUserBillingUseCase', function (): void {
            $this->lookupUserBillingUseCase
                ->expects('handle')
                ->with($this->context, Permission::viewUserBillings(), 1, 6)
                ->andReturn(Seq::from($this->examples->userBillings[0], $this->examples->userBillings[5]));
            $this->buildCustomValidator(
                ['value' => [1, 6]],
                ['value' => ["{$this->ruleName}:" . Permission::viewUserBillings()]]
            )
                ->validate();
        });
        $this->should('pass when userBillings is not found', function (): void {
            $this->lookupUserBillingUseCase
                ->expects('handle')
                ->andReturn(Seq::empty());
            $this->assertTrue(
                $this->buildCustomValidator(
                    ['value' => [1, 5]],
                    ['value' => ["{$this->ruleName}:" . Permission::viewUserBillings()]]
                )
                    ->passes()
            );
        });
        $this->should('fail when depositedAt is null', function (): void {
            $this->lookupUserBillingUseCase
                ->expects('handle')
                ->with($this->context, Permission::viewUserBillings(), 2)
                ->andReturn(Seq::from($this->examples->userBillings[1]));
            $this->assertTrue(
                $this->buildCustomValidator(
                    ['value' => [2]],
                    ['value' => ["{$this->ruleName}:" . Permission::viewUserBillings()]]
                )
                    ->fails()
            );
        });
        $this->should('pass when depositedAt is not null', function (): void {
            $this->lookupUserBillingUseCase
                ->expects('handle')
                ->with($this->context, Permission::viewUserBillings(), 1, 6)
                ->andReturn(Seq::from($this->examples->userBillings[0], $this->examples->userBillings[5]));
            $this->assertTrue(
                $this->buildCustomValidator(
                    ['value' => [1, 6]],
                    ['value' => ["{$this->ruleName}:" . Permission::viewUserBillings()]]
                )
                    ->passes()
            );
        });
    }
}

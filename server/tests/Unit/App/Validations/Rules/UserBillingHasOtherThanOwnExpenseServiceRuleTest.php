<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\App\Validations\Rules;

use Domain\Permission\Permission;
use Domain\UserBilling\UserBilling;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\LookupUserBillingUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;

/**
 * {@link \App\Validations\Rules\UserBillingHasOtherThanOwnExpenseServiceRule} のテスト.
 */
final class UserBillingHasOtherThanOwnExpenseServiceRuleTest extends Test
{
    use ExamplesConsumer;
    use MockeryMixin;
    use RuleTestSupport;
    use UnitSupport;
    use LookupUserBillingUseCaseMixin;

    private $ruleName = 'user_billing_has_other_than_own_expense_service';
    private $validUserBillings;

    /**
     * 初期化処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachSpec(function (self $self): void {
            $self->validUserBillings = Seq::fromArray($self->examples->userBillings)
                ->filter(fn (UserBilling $x) => $x->dwsItem !== null && $x->ltcsItem !== null)
                ->take(2);
        });
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
    public function describe_validateUserBillingHasOtherThanOwnExpenseService(): void
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
        $this->should('fail when dwsItem and ltcsItem are null', function (): void {
            $this->lookupUserBillingUseCase
                ->expects('handle')
                ->with($this->context, Permission::viewUserBillings(), 1, 2)
                ->andReturn(Seq::from(
                    $this->validUserBillings[0],
                    $this->validUserBillings[1]->copy([
                        'dwsItem' => null,
                        'ltcsItem' => null,
                    ])
                ));
            $this->assertTrue(
                $this->buildCustomValidator(
                    ['value' => [1, 2]],
                    ['value' => ["{$this->ruleName}:" . Permission::viewUserBillings()]]
                )
                    ->fails()
            );
        });
        $this->should('pass when dwsItem is not null', function (): void {
            $this->lookupUserBillingUseCase
                ->expects('handle')
                ->with($this->context, Permission::viewUserBillings(), 1, 2)
                ->andReturn(Seq::from(
                    $this->validUserBillings[0],
                    $this->validUserBillings[1]->copy([
                        'ltcsItem' => null,
                    ])
                ));
            $this->assertTrue(
                $this->buildCustomValidator(
                    ['value' => [1, 2]],
                    ['value' => ["{$this->ruleName}:" . Permission::viewUserBillings()]]
                )
                    ->passes()
            );
        });
        $this->should('pass when ltcsItem is not null', function (): void {
            $this->lookupUserBillingUseCase
                ->expects('handle')
                ->with($this->context, Permission::viewUserBillings(), 1, 2)
                ->andReturn(Seq::from(
                    $this->validUserBillings[0],
                    $this->validUserBillings[1]->copy([
                        'dwsItem' => null,
                    ])
                ));
            $this->assertTrue(
                $this->buildCustomValidator(
                    ['value' => [1, 2]],
                    ['value' => ["{$this->ruleName}:" . Permission::viewUserBillings()]]
                )
                    ->passes()
            );
        });
    }
}

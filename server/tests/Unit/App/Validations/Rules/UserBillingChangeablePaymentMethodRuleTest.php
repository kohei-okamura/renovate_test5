<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\App\Validations\Rules;

use Domain\Permission\Permission;
use Domain\User\PaymentMethod;
use Domain\UserBilling\UserBilling;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\LookupUserBillingUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;

/**
 * {@link \App\Validations\Rules\UserBillingChangeablePaymentMethodRule} のテスト.
 */
final class UserBillingChangeablePaymentMethodRuleTest extends Test
{
    use ExamplesConsumer;
    use MockeryMixin;
    use RuleTestSupport;
    use UnitSupport;
    use LookupUserBillingUseCaseMixin;

    private $ruleName = 'user_billing_changeable_payment_method';

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
    public function describe_validateUserBillingChangeablePaymentMethod(): void
    {
        $this->should('use LookupUserBillingUseCase', function (): void {
            $id = $this->examples->userBillings[0]->id;
            $this->lookupUserBillingUseCase
                ->expects('handle')
                ->with($this->context, Permission::updateUserBillings(), $id)
                ->andReturn(Seq::from($this->createUserBilling($id)));
            $this->buildCustomValidator(
                ['id' => $id, 'paymentMethod' => PaymentMethod::collection()->value()],
                ['paymentMethod' => [$this->ruleName]]
            )
                ->validate();
        });
        $this->should('pass when LookupUserBillingUseCase return empty', function (): void {
            $id = $this->examples->userBillings[0]->id;
            $this->lookupUserBillingUseCase
                ->expects('handle')
                ->andReturn(Seq::empty());
            $this->assertTrue(
                $this->buildCustomValidator(
                    ['id' => $id, 'paymentMethod' => PaymentMethod::transfer()->value()],
                    ['paymentMethod' => [$this->ruleName]]
                )
                    ->passes()
            );
        });
        $this->should('passes when payment method change from none to transfer', function (): void {
            $this->paymentMethodCommonPassesCase(PaymentMethod::none(), PaymentMethod::transfer());
        });
        $this->should('passes when payment method change from none to collection', function (): void {
            $this->paymentMethodCommonPassesCase(PaymentMethod::none(), PaymentMethod::collection());
        });
        $this->should('passes when payment method change from withdrawal to transfer', function (): void {
            $this->paymentMethodCommonPassesCase(PaymentMethod::withdrawal(), PaymentMethod::transfer());
        });
        $this->should('passes when payment method change from withdrawal to collection', function (): void {
            $this->paymentMethodCommonPassesCase(PaymentMethod::withdrawal(), PaymentMethod::collection());
        });
        $this->should('passes when payment method change from transfer to collection', function (): void {
            $this->paymentMethodCommonPassesCase(PaymentMethod::transfer(), PaymentMethod::collection());
        });
        $this->should('passes when payment method change from collection to transfer', function (): void {
            $this->paymentMethodCommonPassesCase(PaymentMethod::collection(), PaymentMethod::transfer());
        });
        $this->should('passes when payment method change from none to none', function (): void {
            $this->paymentMethodCommonPassesCase(PaymentMethod::none(), PaymentMethod::none());
        });
        $this->should('passes when payment method change from withdrawal to withdrawal', function (): void {
            $this->paymentMethodCommonPassesCase(PaymentMethod::withdrawal(), PaymentMethod::withdrawal());
        });
        $this->should('passes when payment method change from transfer to transfer', function (): void {
            $this->paymentMethodCommonPassesCase(PaymentMethod::transfer(), PaymentMethod::transfer());
        });
        $this->should('passes when payment method change from collection to collection', function (): void {
            $this->paymentMethodCommonPassesCase(PaymentMethod::collection(), PaymentMethod::collection());
        });
        $this->should('fail when payment method change to none', function (): void {
            $id = $this->examples->userBillings[0]->id;
            $this->lookupUserBillingUseCase
                ->expects('handle')
                ->andReturn(Seq::from($this->createUserBilling($id)));
            $this->assertTrue(
                $this->buildCustomValidator(
                    ['id' => $id, 'paymentMethod' => PaymentMethod::none()->value()],
                    ['paymentMethod' => [$this->ruleName]]
                )
                    ->fails()
            );
        });
        $this->should('fail when payment method change to withdrawal', function (): void {
            $id = $this->examples->userBillings[0]->id;
            $this->lookupUserBillingUseCase
                ->expects('handle')
                ->andReturn(Seq::from($this->createUserBilling($id)));
            $this->assertTrue(
                $this->buildCustomValidator(
                    ['id' => $id, 'paymentMethod' => PaymentMethod::withdrawal()->value()],
                    ['paymentMethod' => [$this->ruleName]]
                )
                    ->fails()
            );
        });
    }

    /**
     * テスト用の利用者請求を返す.
     *
     * @param \Domain\User\PaymentMethod $paymentMethod
     * @param int $id
     * @return \Domain\UserBilling\UserBilling
     */
    private function createUserBilling(int $id, PaymentMethod $paymentMethod = null): UserBilling
    {
        $userBilling = Seq::fromArray($this->examples->userBillings)->filter(fn (UserBilling $x) => $x->id === $id)[0];
        return $userBilling->copy([
            'user' => $userBilling->user->copy([
                'billingDestination' => $userBilling->user->billingDestination->copy([
                    'paymentMethod' => $paymentMethod ?? PaymentMethod::transfer(),
                ]),
            ]),
        ]);
    }

    /**
     * 支払方法の正常系のテストの共通処理.
     *
     * @param \Domain\User\PaymentMethod $current
     * @param null|\Domain\User\PaymentMethod $next
     */
    private function paymentMethodCommonPassesCase(PaymentMethod $current, PaymentMethod $next = null): void
    {
        $id = $this->examples->userBillings[0]->id;
        $this->lookupUserBillingUseCase
            ->expects('handle')
            ->andReturn(Seq::from($this->createUserBilling($id, $current)));
        self::assertTrue(
            $this->buildCustomValidator(
                ['id' => $id, 'paymentMethod' => ($next ?? $current)->value()],
                ['paymentMethod' => [$this->ruleName]]
            )
                ->passes()
        );
    }
}

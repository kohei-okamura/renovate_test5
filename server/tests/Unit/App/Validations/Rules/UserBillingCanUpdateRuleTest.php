<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\App\Validations\Rules;

use Domain\Common\Carbon;
use Domain\Permission\Permission;
use Domain\UserBilling\UserBillingResult;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\LookupUserBillingUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;

/**
 * {@link \App\Validations\Rules\UserBillingCanUpdateRule} のテスト.
 */
final class UserBillingCanUpdateRuleTest extends Test
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
    public function describe_validateUserBillingCanUpdate(): void
    {
        $this->should('use LookupUserBillingUseCase', function (): void {
            $id = $this->examples->userBillings[0]->id;
            $this->lookupUserBillingUseCase
                ->expects('handle')
                ->with($this->context, Permission::updateUserBillings(), $id)
                ->andReturn(Seq::from($this->examples->userBillings[0]->copy([
                    'result' => UserBillingResult::pending(),
                    'transactedAt' => null,
                ])));
            $this->buildCustomValidator(
                ['id' => $id],
                ['id' => ['user_billing_can_update']]
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
                    ['id' => $id],
                    ['id' => ['user_billing_can_update']]
                )
                    ->passes()
            );
        });
        $this->should('fail when result is neither pending nor none', function (): void {
            $id = $this->examples->userBillings[0]->id;
            $this->lookupUserBillingUseCase
                ->expects('handle')
                ->andReturn(Seq::from($this->examples->userBillings[0]->copy([
                    'result' => UserBillingResult::paid(),
                    'transactedAt' => null,
                ])));
            $this->assertTrue(
                $this->buildCustomValidator(
                    ['id' => $id],
                    ['id' => ['user_billing_can_update']]
                )
                    ->fails()
            );
        });
        $this->should('fail when transactedAt is not null', function (): void {
            $id = $this->examples->userBillings[0]->id;
            $this->lookupUserBillingUseCase
                ->expects('handle')
                ->andReturn(Seq::from($this->examples->userBillings[0]->copy([
                    'result' => UserBillingResult::pending(),
                    'transactedAt' => Carbon::now(),
                ])));
            $this->assertTrue(
                $this->buildCustomValidator(
                    ['id' => $id],
                    ['id' => ['user_billing_can_update']]
                )
                    ->fails()
            );
        });
        $this->should('pass when result is pending and transactedAt is null', function (): void {
            $id = $this->examples->userBillings[0]->id;
            $this->lookupUserBillingUseCase
                ->expects('handle')
                ->andReturn(Seq::from($this->examples->userBillings[0]->copy([
                    'result' => UserBillingResult::pending(),
                    'transactedAt' => null,
                ])));
            $this->assertTrue(
                $this->buildCustomValidator(
                    ['id' => $id],
                    ['id' => ['user_billing_can_update']]
                )
                    ->passes()
            );
        });
        $this->should('pass when result is none and transactedAt is null', function (): void {
            $id = $this->examples->userBillings[0]->id;
            $this->lookupUserBillingUseCase
                ->expects('handle')
                ->andReturn(Seq::from($this->examples->userBillings[0]->copy([
                    'result' => UserBillingResult::none(),
                    'transactedAt' => null,
                ])));
            $this->assertTrue(
                $this->buildCustomValidator(
                    ['id' => $id],
                    ['id' => ['user_billing_can_update']]
                )
                    ->passes()
            );
        });
    }
}

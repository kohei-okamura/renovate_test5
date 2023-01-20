<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
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
 * {@link \App\Validations\Rules\UserBillingResultIsNotNoneRule} のテスト.
 */
final class UserBillingResultIsNotNoneRuleTest extends Test
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
    public function describe_validateUserBillingResultIsNotNone(): void
    {
        $this->should('use  LookupUserBillingUseCase', function (): void {
            $ids = [$this->examples->userBillings[0]->id, $this->examples->userBillings[5]->id];
            $this->lookupUserBillingUseCase
                ->expects('handle')
                ->with($this->context, Permission::viewUserBillings(), ...$ids)
                ->andReturn(Seq::from($this->examples->userBillings[0], $this->examples->userBillings[5]));
            $this->buildCustomValidator(
                ['value' => $ids],
                ['value' => ['user_billing_result_is_not_none']]
            )
                ->validate();
        });
        $this->should('pass if ids contain non-existent id', function (): void {
            $ids = [$this->examples->userBillings[0]->id, self::NOT_EXISTING_ID];
            $this->lookupUserBillingUseCase
                ->expects('handle')
                ->with($this->context, Permission::viewUserBillings(), ...$ids)
                ->andReturn(Seq::from($this->examples->userBillings[0]));
            $this->assertTrue(
                $this->buildCustomValidator(
                    ['value' => $ids],
                    ['value' => ['user_billing_result_is_not_none']]
                )
                    ->passes()
            );
        });
        $this->should('pass if the result is not "none" when a single ID is specified', function (): void {
            $id = $this->examples->userBillings[0]->id;
            $this->lookupUserBillingUseCase
                ->expects('handle')
                ->with($this->context, Permission::viewUserBillings(), $id)
                ->andReturn(Seq::from($this->examples->userBillings[0]));
            $this->assertTrue(
                $this->buildCustomValidator(
                    ['value' => $id],
                    ['value' => ['user_billing_result_is_not_none']]
                )
                    ->passes()
            );
        });
        $this->should('pass if the result of all Userbilling is not "none" when multiple IDs are specified', function (): void {
            $ids = [$this->examples->userBillings[0]->id, $this->examples->userBillings[5]->id];
            $this->lookupUserBillingUseCase
                ->expects('handle')
                ->with($this->context, Permission::viewUserBillings(), ...$ids)
                ->andReturn(Seq::from($this->examples->userBillings[0], $this->examples->userBillings[5]));
            $this->assertTrue(
                $this->buildCustomValidator(
                    ['value' => $ids],
                    ['value' => ['user_billing_result_is_not_none']]
                )
                    ->passes()
            );
        });
        $this->should('fail if the result of Userbillings contains even one "none"', function (): void {
            $ids = [$this->examples->userBillings[0]->id, $this->examples->userBillings[25]->id];
            $this->lookupUserBillingUseCase
                ->expects('handle')
                ->with($this->context, Permission::viewUserBillings(), ...$ids)
                ->andReturn(Seq::from($this->examples->userBillings[0], $this->examples->userBillings[25]));
            $this->assertTrue(
                $this->buildCustomValidator(
                    ['value' => $ids],
                    ['value' => ['user_billing_result_is_not_none']]
                )
                    ->fails()
            );
        });
    }
}

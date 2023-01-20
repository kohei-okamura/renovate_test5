<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\App\Validations\Rules;

use App\Validations\CustomValidator;
use Domain\Permission\Permission;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\LookupUserBillingUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;

/**
 * {@link \App\Validations\Rules\UserBillingCanCreateNoticeRule} のテスト.
 */
final class UserBillingCanCreateNoticeRuleTest extends Test
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
                    [
                        'ids' => 1,
                    ],
                    ['ids' => 'user_billing_can_create_notice:' . Permission::viewUserBillings()->value()]
                )
                    ->passes()
            );
        });
        $this->should('pass when userBilling is empty', function (): void {
            $this->lookupUserBillingUseCase
                ->expects('handle')
                ->with($this->context, Permission::viewUserBillings(), self::NOT_EXISTING_ID)
                ->andReturn(Seq::empty());
            $this->assertTrue(
                $this->buildSpecificValidator(
                    [self::NOT_EXISTING_ID],
                    Permission::viewUserBillings()->value()
                )
                    ->passes()
            );
        });
        $this->should('pass when dwsItem exist', function (): void {
            $this->lookupUserBillingUseCase
                ->expects('handle')
                ->with($this->context, Permission::viewUserBillings(), $this->examples->userBillings[0]->id)
                ->andReturn(Seq::from($this->examples->userBillings[0]));
            $this->assertTrue(
                $this->buildSpecificValidator(
                    [$this->examples->userBillings[0]->id],
                    Permission::viewUserBillings()->value()
                )
                    ->passes()
            );
        });
        $this->should('use LookupUserBillingUseCase', function (): void {
            $this->lookupUserBillingUseCase
                ->expects('handle')
                ->with($this->context, Permission::viewUserBillings(), 1, 6)
                ->andReturn(Seq::from($this->examples->userBillings[0], $this->examples->userBillings[5]));
            $this->buildSpecificValidator(
                [1, 6],
                Permission::viewUserBillings()->value()
            )
                ->validate();
        });
        $this->should('fail when dwsItem is null', function (): void {
            $this->lookupUserBillingUseCase
                ->expects('handle')
                ->with($this->context, Permission::viewUserBillings(), 12)
                ->andReturn(Seq::from($this->examples->userBillings[11]));
            $this->assertTrue(
                $this->buildSpecificValidator(
                    [12],
                    Permission::viewUserBillings()->value()
                )
                    ->fails()
            );
        });
    }

    /**
     * テスト固有のValidatorを作る.
     *
     * @param array $ids
     * @param string $permission
     * @return \App\Validations\CustomValidator
     */
    private function buildSpecificValidator(array $ids, string $permission): CustomValidator
    {
        return $this->buildCustomValidator(
            [
                'ids' => $ids,
            ],
            ['ids' => "user_billing_can_create_notice:{$permission}"],
        );
    }
}

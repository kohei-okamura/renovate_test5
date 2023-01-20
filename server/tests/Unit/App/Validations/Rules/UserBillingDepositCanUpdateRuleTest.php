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
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\LookupUserBillingUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;

/**
 * {@link \App\Validations\Rules\UserBillingDepositCanUpdateRule} のテスト.
 */
final class UserBillingDepositCanUpdateRuleTest extends Test
{
    use ExamplesConsumer;
    use LookupUserBillingUseCaseMixin;
    use MatchesSnapshots;
    use MockeryMixin;
    use RuleTestSupport;
    use UnitSupport;

    /**
     * 初期化処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachTest(function (self $self): void {
        });
        self::beforeEachSpec(function (self $self): void {
            $self->lookupUserBillingUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->examples->userBillings[4]))
                ->byDefault();
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_validateUserBelongsToOffice(): void
    {
        $this->should('pass when depositedAt is null And paymentMethod is other than Withdrawal', function (): void {
            $this->assertTrue(
                $this->buildSpecificValidator([$this->examples->userBillings[4]->id], Permission::updateUserBillings()->value())->passes()
            );
        });
        $this->should('fail when depositedAt is null And paymentMethod is Withdrawal', function (): void {
            $this->lookupUserBillingUseCase
                ->allows('handle')
                ->andReturn(Seq::from($this->examples->userBillings[2]->copy(['depositedAt' => null])));
            $this->assertTrue(
                $this->buildSpecificValidator([$this->examples->userBillings[2]->id], Permission::updateUserBillings()->value())->fails()
            );
        });
        $this->should('fail when depositedAt is not null And paymentMethod is other than Withdrawal', function (): void {
            $this->lookupUserBillingUseCase
                ->allows('handle')
                ->andReturn(Seq::from($this->examples->userBillings[5]));
            $this->assertTrue(
                $this->buildSpecificValidator([$this->examples->userBillings[5]->id], Permission::updateUserBillings()->value())->fails()
            );
        });
        $this->should('fail when depositedAt is not null And paymentMethod is other than Withdrawal', function (): void {
            $this->lookupUserBillingUseCase
                ->allows('handle')
                ->andReturn(Seq::from($this->examples->userBillings[5]));
            $this->assertTrue(
                $this->buildSpecificValidator([$this->examples->userBillings[5]->id], Permission::updateUserBillings()->value())->fails()
            );
        });
        $this->should('fail when useCase return Empty', function (): void {
            $this->lookupUserBillingUseCase
                ->allows('handle')
                ->andReturn(Seq::empty());
            $this->assertTrue(
                $this->buildSpecificValidator([$this->examples->userBillings[0]->id], Permission::updateUserBillings()->value())->fails()
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
            ['ids' => "user_billing_deposit_can_update:{$permission}"],
        );
    }
}

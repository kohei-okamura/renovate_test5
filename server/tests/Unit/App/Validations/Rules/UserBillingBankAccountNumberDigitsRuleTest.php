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
 * {@link \App\Validations\Rules\UserBillingBankAccountNumberDigitsRule} のテスト.
 */
final class UserBillingBankAccountNumberDigitsRuleTest extends Test
{
    use ExamplesConsumer;
    use LookupUserBillingUseCaseMixin;
    use MockeryMixin;
    use RuleTestSupport;
    use UnitSupport;

    /**
     * 初期化処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachSpec(function (self $self): void {
            $self->lookupUserBillingUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->examples->userBillings[0], $self->examples->userBillings[1]))
                ->byDefault();
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_validateUserBillingBankAccountNumberDigits(): void
    {
        $this->should('should pass when userBillingIds is not array', function (): void {
            $this->assertTrue(
                $this->buildCustomValidator(
                    ['userBillingIds' => 1],
                    ['userBillingIds' => 'user_billing_bank_account_number_digits:' . Permission::createWithdrawalTransactions()],
                )->passes()
            );
        });
        $this->should('should fail when BankAccountNumberDigitsRule return false at least once', function (): void {
            $this->lookupUserBillingUseCase
                ->expects('handle')
                ->andReturn(Seq::from(
                    $this->examples->userBillings[0]->copy([
                        'user' => $this->examples->userBillings[0]->user->copy([
                            'bankAccount' => $this->examples->userBillings[0]->user->bankAccount->copy([
                                'bankCode' => '0005',
                                'bankAccountNumber' => '252525',
                            ]),
                        ]),
                    ]),
                    $this->examples->userBillings[1]->copy([
                        'user' => $this->examples->userBillings[1]->user->copy([
                            'bankAccount' => $this->examples->userBillings[1]->user->bankAccount->copy([
                                'bankCode' => '0005',
                                'bankAccountNumber' => '0025251',
                            ]),
                        ]),
                    ]),
                ));
            $this->assertTrue(
                $this->buildCustomValidator(
                    ['userBillingIds' => [1, 2]],
                    ['userBillingIds' => 'user_billing_bank_account_number_digits:' . Permission::createWithdrawalTransactions()],
                )->fails()
            );
        });
        $this->should('should pass when BankAccountNumberDigitsRule return true for all', function (): void {
            $this->lookupUserBillingUseCase
                ->expects('handle')
                ->andReturn(Seq::from(
                    $this->examples->userBillings[0]->copy([
                        'user' => $this->examples->userBillings[0]->user->copy([
                            'bankAccount' => $this->examples->userBillings[0]->user->bankAccount->copy([
                                'bankCode' => '0004',
                                'bankAccountNumber' => '0025251',
                            ]),
                        ]),
                    ]),
                    $this->examples->userBillings[1]->copy([
                        'user' => $this->examples->userBillings[1]->user->copy([
                            'bankAccount' => $this->examples->userBillings[1]->user->bankAccount->copy([
                                'bankCode' => '0005',
                                'bankAccountNumber' => '0052521',
                            ]),
                        ]),
                    ]),
                ));
            $this->assertTrue(
                $this->buildCustomValidator(
                    ['userBillingIds' => [1, 2]],
                    ['userBillingIds' => 'user_billing_bank_account_number_digits:' . Permission::createWithdrawalTransactions()],
                )->passes()
            );
        });
    }
}

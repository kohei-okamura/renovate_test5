<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\App\Validations\Rules;

use ScalikePHP\Option;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\IdentifyStaffByEmailUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;

/**
 * {@link \App\Validations\Rules\EmailAddressIsNotUsedByAnyStaffRule} のテスト.
 */
final class EmailAddressIsNotUsedByAnyStaffRuleTest extends Test
{
    use ExamplesConsumer;
    use IdentifyStaffByEmailUseCaseMixin;
    use MockeryMixin;
    use RuleTestSupport;
    use UnitSupport;

    private $ruleName = 'email_address_is_not_used_by_any_staff';

    /**
     * @test
     * @return void
     */
    public function describe_validateEmailAddressIsNotUsedByAnyStaff(): void
    {
        $this->should('pass when staff using the email does not string', function (): void {
            $this->assertTrue(
                $this->buildCustomValidator(
                    ['email' => 1111111],
                    ['email' => "{$this->ruleName}:{$this->examples->staffs[0]->id}"]
                )
                    ->passes()
            );
        });
        $this->should('pass when staff using the email does not exist', function (): void {
            $this->identifyStaffByEmailUseCase
                ->expects('handle')
                ->with($this->context, 'eustylelab@example.com')
                ->andReturn(Option::from(null));
            $this->assertTrue(
                $this->buildCustomValidator(
                    ['email' => 'eustylelab@example.com'],
                    ['email' => "{$this->ruleName}:{$this->examples->staffs[0]->id}"]
                )
                    ->passes()
            );
        });
        $this->should('pass when staff using the email is self', function (): void {
            $this->identifyStaffByEmailUseCase
                ->expects('handle')
                ->with($this->context, 'eustylelab@example.com')
                ->andReturn(Option::from($this->examples->staffs[0]));
            $this->assertTrue(
                $this->buildCustomValidator(
                    ['email' => 'eustylelab@example.com'],
                    ['email' => "{$this->ruleName}:{$this->examples->staffs[0]->id}"]
                )
                    ->passes()
            );
        });
        $this->should('pass when staff using the email is already retired', function (): void {
            $retiredStaff = $this->examples->staffs[34];
            $this->identifyStaffByEmailUseCase
                ->expects('handle')
                ->with($this->context, $retiredStaff->email)
                ->andReturn(Option::from($retiredStaff));
            $this->assertTrue(
                $this->buildCustomValidator(
                    ['email' => $retiredStaff->email],
                    ['email' => "{$this->ruleName}:{$this->examples->staffs[0]->id}"]
                )
                    ->passes()
            );
        });
        $this->should('fail when staff using the email exists', function (): void {
            $this->identifyStaffByEmailUseCase
                ->expects('handle')
                ->with($this->context, 'eustylelab@example.com')
                ->andReturn(Option::from($this->examples->staffs[0]));
            $this->assertTrue(
                $this->buildCustomValidator(
                    ['email' => 'eustylelab@example.com'],
                    ['email' => "{$this->ruleName}:{$this->examples->staffs[1]->id}"]
                )
                    ->fails()
            );
        });
    }
}

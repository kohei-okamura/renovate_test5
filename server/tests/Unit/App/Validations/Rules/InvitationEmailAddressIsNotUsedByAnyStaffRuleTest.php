<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\App\Validations\Rules;

use ScalikePHP\Option;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\IdentifyStaffByEmailUseCaseMixin;
use Tests\Unit\Mixins\LookupInvitationUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;

/**
 * {@link \App\Validations\Rules\InvitationEmailAddressIsNotUsedByAnyStaffRule} のテスト.
 */
final class InvitationEmailAddressIsNotUsedByAnyStaffRuleTest extends Test
{
    use ExamplesConsumer;
    use LookupInvitationUseCaseMixin;
    use IdentifyStaffByEmailUseCaseMixin;
    use MockeryMixin;
    use RuleTestSupport;
    use UnitSupport;

    private const RULE_NAME = 'invitation_email_address_is_not_used_by_any_staff';

    /**
     * @test
     * @return void
     */
    public function describe_validateInvitationEmailAddressIsNotUsedByAnyStaff(): void
    {
        $this->should('pass when the invitation does not exist', function (): void {
            $this->lookupInvitationUseCase
                ->expects('handle')
                ->with($this->context, $this->examples->invitations[0]->id)
                ->andReturn(Seq::empty());
            $this->identifyStaffByEmailUseCase
                ->expects('handle')
                ->never();

            $this->assertTrue(
                $this->buildCustomValidator(
                    ['invitationId' => $this->examples->invitations[0]->id],
                    ['invitationId' => self::RULE_NAME]
                )
                    ->passes()
            );
        });
        $this->should('pass when staff using the email does not exist', function (): void {
            $this->lookupInvitationUseCase
                ->expects('handle')
                ->with($this->context, $this->examples->invitations[0]->id)
                ->andReturn(Seq::from($this->examples->invitations[0]->copy([
                    'email' => 'not-conflict@example.com',
                ])));
            $this->identifyStaffByEmailUseCase
                ->expects('handle')
                ->with($this->context, 'not-conflict@example.com')
                ->andReturn(Option::none());

            $this->assertTrue(
                $this->buildCustomValidator(
                    ['invitationId' => $this->examples->invitations[0]->id],
                    ['invitationId' => self::RULE_NAME]
                )
                    ->passes()
            );
        });
        $this->should('pass when staff using the email is already retired', function (): void {
            $retiredStaff = $this->examples->staffs[34];
            $this->lookupInvitationUseCase
                ->expects('handle')
                ->with($this->context, $this->examples->invitations[0]->id)
                ->andReturn(Seq::from($this->examples->invitations[0]->copy([
                    'email' => $retiredStaff->email,
                ])));
            $this->identifyStaffByEmailUseCase
                ->expects('handle')
                ->with($this->context, $retiredStaff->email)
                ->andReturn(Option::from($retiredStaff));

            $this->assertTrue(
                $this->buildCustomValidator(
                    ['invitationId' => $this->examples->invitations[0]->id],
                    ['invitationId' => self::RULE_NAME]
                )
                    ->passes()
            );
        });
        $this->should('fail when staff using the email exists', function (): void {
            $this->lookupInvitationUseCase
                ->expects('handle')
                ->with($this->context, $this->examples->invitations[0]->id)
                ->andReturn(Seq::from($this->examples->invitations[0]->copy([
                    'email' => 'not-conflict@example.com',
                ])));
            $this->identifyStaffByEmailUseCase
                ->expects('handle')
                ->with($this->context, 'not-conflict@example.com')
                ->andReturn(Option::some($this->examples->staffs[0]));

            $this->assertTrue(
                $this->buildCustomValidator(
                    ['invitationId' => $this->examples->invitations[0]->id],
                    ['invitationId' => self::RULE_NAME]
                )
                    ->fails()
            );
        });
    }
}

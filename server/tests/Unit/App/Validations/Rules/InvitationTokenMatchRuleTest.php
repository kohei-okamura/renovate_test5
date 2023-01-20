<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\App\Validations\Rules;

use App\Validations\CustomValidator;
use ScalikePHP\Seq;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\LookupInvitationUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;

/**
 * {@link \App\Validations\Rules\InvitationTokenMatchRule} のテスト.
 */
final class InvitationTokenMatchRuleTest extends Test
{
    use ExamplesConsumer;
    use LookupInvitationUseCaseMixin;
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
            $self->lookupInvitationUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->examples->invitations[0]))
                ->byDefault();
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_validateInvitationTokenMatch(): void
    {
        $this->should('pass when the token exists in db', function (): void {
            $this->assertTrue(
                $this->validateInvitationTokenMatch($this->examples->invitations[0]->token, $this->examples->invitations[0]->id)
                    ->passes()
            );
        });
        $this->should('use UseCase', function (): void {
            $this->lookupInvitationUseCase
                ->expects('handle')
                ->with($this->context, $this->examples->invitations[0]->id)
                ->andReturn(Seq::from($this->examples->invitations[0]));
            $this->assertTrue(
                $this->validateInvitationTokenMatch($this->examples->invitations[0]->token, $this->examples->invitations[0]->id)
                    ->passes()
            );
        });
        $this->should('fail when unknown token given', function (): void {
            $this->assertTrue(
                $this->validateInvitationTokenMatch(self::NOT_EXISTING_TOKEN, $this->examples->invitations[0]->id)
                    ->fails()
            );
        });
        $this->should('fail when lookupInvitationUseCase return empty', function (): void {
            $this->lookupInvitationUseCase
                ->expects('handle')
                ->with($this->context, self::NOT_EXISTING_ID)
                ->andReturn(Seq::emptySeq());
            $this->assertTrue(
                $this->validateInvitationTokenMatch(self::NOT_EXISTING_TOKEN, self::NOT_EXISTING_ID)
                    ->fails()
            );
        });
    }

    /**
     * 有効な招待トークンか検証するバリデータを生成する.
     *
     * @param $token
     * @param int $id
     * @return \App\Validations\CustomValidator
     */
    private function validateInvitationTokenMatch($token, int $id): CustomValidator
    {
        return $this->buildCustomValidator(
            ['token' => $token, 'invitationId' => $id],
            ['token' => 'invitation_token_match:invitationId'],
        );
    }
}

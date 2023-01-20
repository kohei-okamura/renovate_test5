<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\Staff;

use Domain\Staff\Invitation;
use ScalikePHP\Option;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\InvitationRepositoryMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\TransactionManagerMixin;
use Tests\Unit\Test;
use UseCase\Staff\LookupInvitationByTokenInteractor;

/**
 * {@link \UseCase\Staff\LookupInvitationByTokenInteractor} のテスト.
 */
final class LookupInvitationByTokenInteractorTest extends Test
{
    use ContextMixin;
    use ExamplesConsumer;
    use InvitationRepositoryMixin;
    use MockeryMixin;
    use TransactionManagerMixin;
    use UnitSupport;

    private Invitation $invitation;
    private LookupInvitationByTokenInteractor $interactor;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (LookupInvitationByTokenInteractorTest $self): void {
            $self->invitationRepository
                ->allows('lookupOptionByToken')
                ->andReturn(Option::from($self->examples->invitations[0]))
                ->byDefault();

            $self->invitation = $self->examples->invitations[0];
            $self->interactor = app(LookupInvitationByTokenInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('return Invitation', function (): void {
            $this->assertModelStrictEquals(
                $this->invitation,
                $this->interactor->handle($this->context, $this->invitation->token)->head()
            );
        });
        $this->should('use lookupOptionByToken on invitationRepository', function (): void {
            $this->invitationRepository
                ->expects('lookupOptionByToken')
                ->with($this->invitation->token)
                ->andReturn(Option::from($this->invitation));

            $this->interactor->handle($this->context, $this->invitation->token);
        });
    }
}

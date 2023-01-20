<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\Staff;

use Domain\Staff\Invitation;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\InvitationRepositoryMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\TransactionManagerMixin;
use Tests\Unit\Test;
use UseCase\Staff\LookupInvitationInteractor;

/**
 * {@link \UseCase\Staff\LookupInvitationInteractor} のテスト.
 */
final class LookupInvitationInteractorTest extends Test
{
    use ContextMixin;
    use ExamplesConsumer;
    use InvitationRepositoryMixin;
    use MockeryMixin;
    use TransactionManagerMixin;
    use UnitSupport;

    private Invitation $invitation;
    private LookupInvitationInteractor $interactor;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (LookupInvitationInteractorTest $self): void {
            $self->invitationRepository
                ->allows('lookup')
                ->andReturn(Seq::from($self->examples->invitations[0]))
                ->byDefault();

            $self->invitation = $self->examples->invitations[0];
            $self->interactor = app(LookupInvitationInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('return a seq of Invitation', function (): void {
            $this->invitationRepository
                ->expects('lookup')
                ->with($this->invitation->id)
                ->andReturn(Seq::from($this->invitation));

            $actual = $this->interactor->handle($this->context, $this->invitation->id);
            $this->assertCount(1, $actual);
            $this->assertModelStrictEquals($this->invitation, $actual->head());
        });
    }
}

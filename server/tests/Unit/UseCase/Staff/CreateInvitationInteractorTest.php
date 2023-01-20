<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\Staff;

use Domain\Common\Carbon;
use Domain\Staff\CreateInvitationEvent;
use Domain\Staff\Invitation;
use Mockery;
use ScalikePHP\Option;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\ConfigMixin;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\EventDispatcherMixin;
use Tests\Unit\Mixins\InvitationRepositoryMixin;
use Tests\Unit\Mixins\LoggerMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\TokenMakerMixin;
use Tests\Unit\Mixins\TransactionManagerMixin;
use Tests\Unit\Test;
use UseCase\Staff\CreateInvitationInteractor;

/**
 * {@link \UseCase\Staff\CreateInvitationInteractor} のテスト.
 */
final class CreateInvitationInteractorTest extends Test
{
    use CarbonMixin;
    use ConfigMixin;
    use ContextMixin;
    use EventDispatcherMixin;
    use ExamplesConsumer;
    use InvitationRepositoryMixin;
    use LoggerMixin;
    use MockeryMixin;
    use TokenMakerMixin;
    use TransactionManagerMixin;
    use UnitSupport;

    private const ID = 517;
    private const LIFETIME_MINUTES = 1440;
    private const TOKEN = '1234567890abcdefghij1234567890ABCDEFGHIJ12345678901234567890';

    private Seq $baseInvitations;
    private Seq $expectedInvitations;
    private Seq $storedInvitations;

    private CreateInvitationInteractor $interactor;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachSpec(function (self $self): void {
            $invitation = Invitation::create([
                'email' => $self->examples->invitations[0]->email,
                'roleIds' => $self->examples->invitations[0]->roleIds,
                'officeIds' => $self->examples->invitations[0]->officeIds,
            ]);
            $self->baseInvitations = Seq::from(
                $invitation,
                $invitation->copy(['email' => $self->examples->invitations[1]->email])
            );
            $self->expectedInvitations = $self->baseInvitations->map(fn (Invitation $x): Invitation => $x->copy([
                'staffId' => null,
                'token' => self::TOKEN,
                'expiredAt' => Carbon::now()->addMinutes(self::LIFETIME_MINUTES),
                'createdAt' => Carbon::now(),
            ]));
            $self->storedInvitations = $self->expectedInvitations->map(fn (Invitation $x, int $index): Invitation => $x->copy([
                'id' => self::ID + $index,
            ]));

            $self->config
                ->allows('get')
                ->with('zinger.invitation.lifetime_minutes')
                ->andReturn(self::LIFETIME_MINUTES)
                ->byDefault();

            $self->eventDispatcher
                ->allows('dispatch')
                ->andReturnNull()
                ->byDefault();

            $self->invitationRepository
                ->allows('store')
                ->andReturn($self->storedInvitations[0])
                ->byDefault();

            $self->invitationRepository
                ->allows('lookupOptionByToken')
                ->andReturn(Option::none())
                ->byDefault();

            $self->tokenMaker
                ->allows('make')
                ->andReturn(self::TOKEN)
                ->byDefault();

            $self->logger
                ->allows('info')
                ->andReturnNull()
                ->byDefault();

            $self->interactor = app(CreateInvitationInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('store the Invitation', function (): void {
            $this->expectedInvitations->each(function (Invitation $x, int $index): void {
                $this->invitationRepository
                    ->expects('store')
                    ->with(equalTo($x))
                    ->andReturn($this->storedInvitations[$index]);
            });

            $this->interactor->handle($this->context, $this->baseInvitations);
        });
        $this->should(
            'set expiredAt using config',
            function (int $lifetime): void {
                $this->config
                    ->expects('get')
                    ->with('zinger.invitation.lifetime_minutes')
                    ->andReturn($lifetime);
                $this->invitationRepository
                    ->expects('store')
                    ->with(Mockery::capture($actual))
                    ->andReturn($this->storedInvitations[0]);

                $this->interactor->handle($this->context, Seq::from($this->baseInvitations[0]));

                $this->assertSame(
                    Carbon::now()->addMinutes($lifetime)->timestamp,
                    $actual->expiredAt->timestamp
                );
            },
            ['examples' => [[120], [30], [1440], [90]]]
        );
        $this->should('store the Invitation in transaction', function (): void {
            $this->transactionManager->expects('run')->andReturn($this->storedInvitations);
            $this->invitationRepository->expects('store')->never();
            $this->interactor->handle($this->context, $this->baseInvitations);
        });
        $this->should('log info', function (): void {
            $this->expectedInvitations->each(function (Invitation $x, int $index): void {
                $this->invitationRepository
                    ->expects('store')
                    ->with(equalTo($x))
                    ->andReturn($this->storedInvitations[$index]);
            });
            $context = ['organizationId' => $this->examples->organizations[0]->id];
            $this->context
                ->expects('logContext')
                ->andReturn($context);
            $this->logger
                ->expects('info')
                ->with('招待が登録されました', ['ids' => $this->storedInvitations->map(fn (Invitation $x): int => $x->id)->toArray()] + $context)
                ->andReturnNull();

            $this->interactor->handle($this->context, $this->baseInvitations);
        });
        $this->should('dispatch a CreateInvitationEvent', function (): void {
            $this->expectedInvitations->each(function (Invitation $x, int $index): void {
                $this->invitationRepository
                    ->expects('store')
                    ->with(equalTo($x))
                    ->andReturn($this->storedInvitations[$index]);
            });
            $this->storedInvitations->each(function (Invitation $x): void {
                $this->eventDispatcher
                    ->expects('dispatch')
                    ->with(equalTo(new CreateInvitationEvent($this->context, $x, $this->context->staff)))
                    ->andReturnNull();
            });

            $this->interactor->handle($this->context, $this->baseInvitations);
        });
        $this->should('return the stored Invitation', function (): void {
            $this->expectedInvitations->each(function (Invitation $x, int $index): void {
                $this->invitationRepository
                    ->expects('store')
                    ->with(equalTo($x))
                    ->andReturn($this->storedInvitations[$index]);
            });

            $actual = $this->interactor->handle($this->context, $this->baseInvitations);
            $this->assertEach(
                function ($expectedValue, $actualValue): void {
                    $this->assertModelStrictEquals($expectedValue, $actualValue);
                },
                $this->storedInvitations->toArray(),
                $actual->toArray()
            );
        });
    }
}

<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\Staff;

use Domain\Common\Carbon;
use Domain\Staff\Invitation;
use Domain\Staff\Staff;
use Lib\Exceptions\NotFoundException;
use Mockery;
use ScalikePHP\Option;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CreateStaffUseCaseMixin;
use Tests\Unit\Mixins\DummyContextMixin;
use Tests\Unit\Mixins\LookupInvitationUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;
use UseCase\Staff\CreateStaffWithInvitationInteractor;

/**
 * {@link CreateStaffWithInvitationInteractor} のテスト.
 */
final class CreateStaffWithInvitationInteractorTest extends Test
{
    use CreateStaffUseCaseMixin;
    use DummyContextMixin;
    use ExamplesConsumer;
    use LookupInvitationUseCaseMixin;
    use MockeryMixin;
    use UnitSupport;

    private Invitation $invitation;
    private Staff $staff;

    private CreateStaffWithInvitationInteractor $interactor;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachSpec(function (self $self): void {
            $self->invitation = $self->examples->invitations[0];
            $self->staff = $self->examples->staffs[0]->copy([
                'roleIds' => [],
                'officeIds' => [],
            ]);

            $self->lookupInvitationUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->invitation))
                ->byDefault();

            $self->createStaffUseCase
                ->allows('handle')
                ->andReturn($self->staff)
                ->byDefault();

            $self->interactor = app(CreateStaffWithInvitationInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('lookup invitation', function (): void {
            $this->lookupInvitationUseCase
                ->expects('handle')
                ->with($this->context, $this->invitation->id)
                ->andReturn(Seq::from($this->invitation));

            $this->interactor->handle($this->context, $this->invitation->id, $this->staff);
        });
        $this->should('throw NotFoundException when the invitation not found', function (): void {
            $this->lookupInvitationUseCase
                ->expects('handle')
                ->with($this->context, $this->invitation->id)
                ->andReturn(Seq::emptySeq());

            $this->assertThrows(NotFoundException::class, function (): void {
                $this->interactor->handle($this->context, $this->invitation->id, $this->staff);
            });
        });
        $this->should('throw NotFoundException when the invitation has been expired', function (): void {
            $this->lookupInvitationUseCase
                ->expects('handle')
                ->with($this->context, $this->invitation->id)
                ->andReturn(
                    Seq::from($this->invitation->copy(['expiredAt' => Carbon::now()->subMinute()]))
                );

            $this->assertThrows(NotFoundException::class, function (): void {
                $this->interactor->handle($this->context, $this->invitation->id, $this->staff);
            });
        });
        $this->should('store the staff', function (): void {
            $staff = $this->staff;
            $expected = $staff->copy([
                'roleIds' => $this->invitation->roleIds,
                'email' => $this->invitation->email,
                'officeIds' => $this->invitation->officeIds,
            ]);
            $this->createStaffUseCase
                ->expects('handle')
                ->with($this->context, Mockery::capture($actual), Mockery::any())
                ->andReturn();
            $this->assertNotEquals($expected->roleIds, $staff->roleIds);
            $this->assertNotEquals($expected->email, $staff->email);
            $this->assertNotEquals($expected->officeIds, $staff->officeIds);

            $this->interactor->handle($this->context, $this->invitation->id, $this->staff);

            $this->assertModelStrictEquals($expected, $actual);
        });
        $this->should('store the staff with invitation', function (): void {
            $expected = Option::some($this->invitation);
            $this->createStaffUseCase
                ->expects('handle')
                ->with($this->context, Mockery::any(), Mockery::capture($actual))
                ->andReturn();

            $this->interactor->handle($this->context, $this->invitation->id, $this->staff);

            $this->assertNotEmpty($actual);
            $this->assertModelStrictEquals($expected->get(), $actual->get());
        });
    }
}

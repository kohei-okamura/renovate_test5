<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\Staff;

use Domain\Common\Password;
use Domain\Staff\Staff;
use Domain\Staff\StaffLoggedInEvent;
use ScalikePHP\None;
use ScalikePHP\Option;
use ScalikePHP\Some;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\BuildAuthResponseUseCaseMixin;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\EventDispatcherMixin;
use Tests\Unit\Mixins\IdentifyStaffByEmailUseCaseMixin;
use Tests\Unit\Mixins\LoggerMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;
use UseCase\Staff\AuthenticateStaffInteractor;

/**
 * Class AuthenticateStaffInteractor.
 */
class AuthenticateStaffInteractorTest extends Test
{
    use BuildAuthResponseUseCaseMixin;
    use ContextMixin;
    use EventDispatcherMixin;
    use ExamplesConsumer;
    use LoggerMixin;
    use IdentifyStaffByEmailUseCaseMixin;
    use MockeryMixin;
    use UnitSupport;

    public const PERMISSION_CODES = ['staff.create', 'staff.update'];

    private AuthenticateStaffInteractor $interactor;
    private Staff $staff;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (AuthenticateStaffInteractorTest $self): void {
            $self->staff = $self->examples->staffs[0]->copy([
                'organizationId' => $self->examples->organizations[0]->id,
                'password' => Password::fromString('PassWoRD'),
            ]);

            $self->logger->allows('info')->byDefault();

            $self->context->allows('organization')->andReturn($self->examples->organizations[0]);
            $self->identifyStaffByEmailUseCase->allows('handle')->andReturn(Option::from($self->staff))->byDefault();
            $self->buildAuthResponseUseCase
                ->allows('handle')
                ->andReturn(['auth' => [
                    'isSystemAdmin' => true,
                    'permissions' => self::PERMISSION_CODES,
                    'staff' => $self->staff,
                ],
                ])
                ->byDefault();
            $self->eventDispatcher
                ->allows('dispatch')
                ->byDefault();

            $self->interactor = app(AuthenticateStaffInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('return a some array of staff and permission codes', function (): void {
            $option = $this->interactor->handle($this->context, $this->staff->email, 'PassWoRD', false);

            $this->assertInstanceOf(Some::class, $option);
            $this->assertSame(
                [
                    'auth' => [
                        'isSystemAdmin' => true,
                        'permissions' => self::PERMISSION_CODES,
                        'staff' => $this->staff,
                    ],
                ],
                $option->get()
            );
        });
        $this->should('log using info', function (): void {
            $context = ['staffId' => $this->staff->id, 'organizationId' => $this->examples->organizations[0]->id];
            $this->context
                ->expects('logContext')
                ->andReturn($context);
            $this->logger
                ->expects('info')
                ->with('スタッフがログインしました', $context);

            $this->interactor->handle($this->context, $this->staff->email, 'PassWoRD', false);
        });
        $this->should('return None when staff not exists', function (): void {
            $this->identifyStaffByEmailUseCase->allows('handle')->andReturn(Option::none());

            $this->assertInstanceOf(
                None::class,
                $this->interactor->handle($this->context, $this->staff->email, 'PassWoRD', false)
            );
        });
        $this->should('return None when failed to check hash', function (): void {
            $this->assertInstanceOf(
                None::class,
                $this->interactor->handle($this->context, $this->staff->email, 'WRONG_PASSWORD', true)
            );
        });
        $this->should('lookup staff by email', function (): void {
            $this->identifyStaffByEmailUseCase->expects('handle')
                ->with($this->context, $this->staff->email)
                ->andReturn(Option::from($this->staff));

            $this->interactor->handle($this->context, $this->staff->email, 'PassWoRD', false);
        });
        $this->should('use EventDispatcher', function (): void {
            $event = new StaffLoggedInEvent($this->context, $this->staff, false);
            $this->eventDispatcher
                ->expects('dispatch')
                ->with(equalTo($event))
                ->andReturnNull();

            $this->interactor->handle($this->context, $this->staff->email, 'PassWoRD', false);
        });
        $this->should('return None when isVerified is false', function (): void {
            $staff = $this->examples->staffs[0]->copy([
                'organizationId' => $this->examples->organizations[0]->id,
                'password' => Password::fromString('PassWoRD'),
                'isVerified' => false,
            ]);
            $this->identifyStaffByEmailUseCase->allows('handle')->andReturn(Option::from($staff))->byDefault();

            $this->assertInstanceOf(
                None::class,
                $this->interactor->handle($this->context, $this->staff->email, 'PassWoRD', true)
            );
        });
    }
}

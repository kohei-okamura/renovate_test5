<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Providers;

use App\Listeners\CancelShiftEventListener;
use App\Listeners\CreateInvitationEventListener;
use App\Listeners\CreateStaffEventListener;
use App\Listeners\CreateStaffPasswordResetEventListener;
use App\Listeners\FirstCallingEventListener;
use App\Listeners\FourthCallingEventListener;
use App\Listeners\SecondCallingEventListener;
use App\Listeners\StaffLoggedInEventListener;
use App\Listeners\StaffLoggedOutEventListener;
use App\Listeners\ThirdCallingEventListener;
use App\Listeners\UpdateShiftEventListener;
use Domain\Calling\FirstCallingEvent;
use Domain\Calling\FourthCallingEvent;
use Domain\Calling\SecondCallingEvent;
use Domain\Calling\ThirdCallingEvent;
use Domain\Shift\CancelShiftEvent;
use Domain\Shift\UpdateShiftEvent;
use Domain\Staff\CreateInvitationEvent;
use Domain\Staff\CreateStaffEvent;
use Domain\Staff\CreateStaffPasswordResetEvent;
use Domain\Staff\StaffLoggedInEvent;
use Domain\Staff\StaffLoggedOutEvent;
use Laravel\Lumen\Providers\EventServiceProvider as ServiceProvider;

/**
 * Event Service Provider.
 */
final class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        CancelShiftEvent::class => [
            CancelShiftEventListener::class,
        ],
        CreateInvitationEvent::class => [
            CreateInvitationEventListener::class,
        ],
        CreateStaffEvent::class => [
            CreateStaffEventListener::class,
        ],
        CreateStaffPasswordResetEvent::class => [
            CreateStaffPasswordResetEventListener::class,
        ],
        FirstCallingEvent::class => [
            FirstCallingEventListener::class,
        ],
        SecondCallingEvent::class => [
            SecondCallingEventListener::class,
        ],
        ThirdCallingEvent::class => [
            ThirdCallingEventListener::class,
        ],
        FourthCallingEvent::class => [
            FourthCallingEventListener::class,
        ],
        StaffLoggedInEvent::class => [
            StaffLoggedInEventListener::class,
        ],
        StaffLoggedOutEvent::class => [
            StaffLoggedOutEventListener::class,
        ],
        UpdateShiftEvent::class => [
            UpdateShiftEventListener::class,
        ],
    ];
}

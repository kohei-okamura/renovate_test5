<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Providers\Dependencies;

use Domain\Calling\CallingFinder;
use Domain\Calling\CallingLogFinder;
use Domain\Calling\CallingLogRepository;
use Domain\Calling\CallingRepository;
use Domain\Calling\CallingResponseRepository;
use Domain\Sms\SmsGateway;
use Domain\Tel\TelGateway;
use Infrastructure\Calling\CallingFinderEloquentImpl;
use Infrastructure\Calling\CallingLogFinderEloquentImpl;
use Infrastructure\Calling\CallingLogRepositoryEloquentImpl;
use Infrastructure\Calling\CallingRepositoryEloquentImpl;
use Infrastructure\Calling\CallingResponseRepositoryEloquentImpl;
use Infrastructure\Sms\SmsGatewayTwilioImpl;
use Infrastructure\Tel\TelGatewayTwilioImpl;
use UseCase\Calling\AcknowledgeStaffAttendanceInteractor;
use UseCase\Calling\AcknowledgeStaffAttendanceUseCase;
use UseCase\Calling\CreateCallingsInteractor;
use UseCase\Calling\CreateCallingsUseCase;
use UseCase\Calling\FindCallingInteractor;
use UseCase\Calling\FindCallingUseCase;
use UseCase\Calling\GetShiftsByTokenInteractor;
use UseCase\Calling\GetShiftsByTokenUseCase;
use UseCase\Calling\LookupCallingByTokenInteractor;
use UseCase\Calling\LookupCallingByTokenUseCase;
use UseCase\Calling\SendFirstCallingInteractor;
use UseCase\Calling\SendFirstCallingUseCase;
use UseCase\Calling\SendFourthCallingInteractor;
use UseCase\Calling\SendFourthCallingUseCase;
use UseCase\Calling\SendSecondCallingInteractor;
use UseCase\Calling\SendSecondCallingUseCase;
use UseCase\Calling\SendThirdCallingInteractor;
use UseCase\Calling\SendThirdCallingUseCase;

/**
 * Calling Dependencies.
 *
 * @codeCoverageIgnore APPに処理が来る前のコードなのでUnitTest除外
 */
final class CallingDependencies implements DependenciesInterface
{
    /** {@inheritdoc} */
    public function getDependenciesList(): iterable
    {
        return [
            AcknowledgeStaffAttendanceUseCase::class => AcknowledgeStaffAttendanceInteractor::class,
            CallingFinder::class => CallingFinderEloquentImpl::class,
            CallingLogFinder::class => CallingLogFinderEloquentImpl::class,
            CallingLogRepository::class => CallingLogRepositoryEloquentImpl::class,
            CallingRepository::class => CallingRepositoryEloquentImpl::class,
            CallingResponseRepository::class => CallingResponseRepositoryEloquentImpl::class,
            CreateCallingsUseCase::class => CreateCallingsInteractor::class,
            FindCallingUseCase::class => FindCallingInteractor::class,
            GetShiftsByTokenUseCase::class => GetShiftsByTokenInteractor::class,
            LookupCallingByTokenUseCase::class => LookupCallingByTokenInteractor::class,
            SendFirstCallingUseCase::class => SendFirstCallingInteractor::class,
            SendSecondCallingUseCase::class => SendSecondCallingInteractor::class,
            SendThirdCallingUseCase::class => SendThirdCallingInteractor::class,
            SendFourthCallingUseCase::class => SendFourthCallingInteractor::class,
            SmsGateway::class => SmsGatewayTwilioImpl::class,
            TelGateway::class => TelGatewayTwilioImpl::class,
        ];
    }
}

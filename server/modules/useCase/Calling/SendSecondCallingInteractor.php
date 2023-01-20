<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Calling;

use Domain\Calling\Calling;
use Domain\Calling\CallingLog;
use Domain\Calling\CallingLogRepository;
use Domain\Calling\CallingResponseRepository;
use Domain\Calling\CallingType;
use Domain\Calling\SecondCallingEvent;
use Domain\Common\Carbon;
use Domain\Common\CarbonRange;
use Domain\Common\Range;
use Domain\Context\Context;
use Domain\Event\EventDispatcher;
use Domain\Permission\Permission;
use Domain\Shift\Shift;
use Domain\ShoutUrl\UrlShortenerGateway;
use Domain\Staff\Staff;
use Domain\TransactionManager;
use Domain\TransactionManagerFactory;
use Domain\Url\UrlBuilder;
use Lib\Logging;
use ScalikePHP\Option;
use ScalikePHP\Seq;
use UseCase\Shift\LookupShiftUseCase;
use UseCase\Staff\LookupStaffUseCase;
use UseCase\User\LookupUserUseCase;

/**
 * 出勤確認第二通知実装.
 */
class SendSecondCallingInteractor implements SendSecondCallingUseCase
{
    use Logging;

    private EventDispatcher $dispatcher;
    private FindCallingUseCase $findUseCase;
    private LookupShiftUseCase $lookupShiftUseCase;
    private LookupStaffUseCase $lookupStaffUseCase;
    private LookupUserUseCase $lookupUserUseCase;
    private CallingLogRepository $logRepository;
    private CallingResponseRepository $repository;
    private TransactionManager $transaction;
    private UrlShortenerGateway $shortener;
    private UrlBuilder $urlBuilder;

    /**
     * {@link \UseCase\Calling\SendSecondCallingInteractor} Constructor.
     *
     * @param \Domain\Event\EventDispatcher $dispatcher
     * @param \UseCase\Calling\FindCallingUseCase $findUseCase
     * @param \UseCase\Shift\LookupShiftUseCase $lookupShiftUseCase
     * @param \UseCase\Staff\LookupStaffUseCase $lookupStaffUseCase
     * @param \UseCase\User\LookupUserUseCase $lookupUserUseCase
     * @param \Domain\Calling\CallingLogRepository $logRepository
     * @param \Domain\Calling\CallingResponseRepository $repository
     * @param \Domain\TransactionManagerFactory $factory
     * @param \Domain\Url\UrlBuilder $urlBuilder
     * @param UrlShortenerGateway $shortener
     */
    public function __construct(
        EventDispatcher $dispatcher,
        FindCallingUseCase $findUseCase,
        LookupShiftUseCase $lookupShiftUseCase,
        LookupStaffUseCase $lookupStaffUseCase,
        LookupUserUseCase $lookupUserUseCase,
        CallingLogRepository $logRepository,
        CallingResponseRepository $repository,
        TransactionManagerFactory $factory,
        UrlBuilder $urlBuilder,
        UrlShortenerGateway $shortener
    ) {
        $this->dispatcher = $dispatcher;
        $this->findUseCase = $findUseCase;
        $this->lookupShiftUseCase = $lookupShiftUseCase;
        $this->lookupStaffUseCase = $lookupStaffUseCase;
        $this->lookupUserUseCase = $lookupUserUseCase;
        $this->logRepository = $logRepository;
        $this->repository = $repository;
        $this->transaction = $factory->factory($logRepository);
        $this->shortener = $shortener;
        $this->urlBuilder = $urlBuilder;
    }

    /** {@inheritdoc} */
    public function handle(Context $context, CarbonRange $range): void
    {
        $this->getCallings($context, $range)
            ->flatMap(fn (Calling $calling): Option => $this->buildEventOption($context, $calling))
            ->each(function (SecondCallingEvent $event) use ($context): void {
                $this->dispatcher->dispatch($event);
                $x = $this->storeLog($event);

                $this->logger()->info(
                    '出勤確認送信履歴が登録されました',
                    ['id' => $x->id] + $context->logContext()
                );
            });
    }

    /**
     * 通知確認を行っていない出勤確認一覧を取得する.
     *
     * @param \Domain\Context\Context $context
     * @param \Domain\Common\Range $range
     * @return \Domain\Calling\Calling[]|\ScalikePHP\Seq
     */
    private function getCallings(Context $context, Range $range): Seq
    {
        $filterParams = [
            'expiredRange' => $range,
            'response' => false,
        ];
        $paginationParams = [
            'all' => true,
            'sortBy' => 'id',
        ];
        return $this->findUseCase->handle($context, Permission::listShifts(), $filterParams, $paginationParams)->list;
    }

    /**
     * 出勤確認から {@link \Domain\Calling\SecondCallingEvent} を構築する.
     *
     * 対応するスタッフが存在しない場合は None を返す.
     *
     * @param \Domain\Context\Context $context
     * @param \Domain\Calling\Calling $calling
     * @return \Domain\Calling\SecondCallingEvent[]|\ScalikePHP\Option
     */
    private function buildEventOption(Context $context, Calling $calling): Option
    {
        return $this->lookupStaffUseCase
            ->handle($context, Permission::viewStaffs(), $calling->staffId)
            ->map(function (Staff $staff) use ($context, $calling): SecondCallingEvent {
                $plan = $this->lookupFirstScheduleShift($context, $calling);
                $minutes = Calling::SECOND_TARGET_MINUTES;
                $url = $this->urlBuilder->build($context, "/callings/{$calling->token}");
                $shortenUrl = $this->shortener->handle($url);
                return new SecondCallingEvent($context, $minutes, $calling, $plan, $staff, $shortenUrl);
            })
            ->headOption();
    }

    /**
     * 出勤確認の対象となる最初の勤務シフトを取得する.
     *
     * @param \Domain\Context\Context $context
     * @param \Domain\Calling\Calling $calling
     * @return \Domain\Shift\Shift
     */
    private function lookupFirstScheduleShift(Context $context, Calling $calling): Shift
    {
        return $this->lookupShiftUseCase->handle($context, Permission::viewShifts(), ...$calling->shiftIds)
            ->sortBy(fn (Shift $shift): Carbon => $shift->schedule->start)
            ->head();
    }

    /**
     * {@link \Domain\Calling\SecondCallingEvent} から出勤確認送信履歴を生成して保管する.
     *
     * @param \Domain\Calling\SecondCallingEvent $event
     * @throws \Throwable
     * @return \Domain\Calling\CallingLog
     */
    private function storeLog(SecondCallingEvent $event): CallingLog
    {
        $x = CallingLog::create([
            'callingId' => $event->calling()->id,
            'callingType' => CallingType::sms(),
            'isSucceeded' => true,
            'createdAt' => Carbon::now(),
        ]);
        return $this->transaction->run(function () use ($x): CallingLog {
            return $this->logRepository->store($x);
        });
    }
}

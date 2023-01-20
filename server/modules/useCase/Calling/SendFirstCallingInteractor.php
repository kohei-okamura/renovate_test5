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
use Domain\Calling\CallingType;
use Domain\Calling\FirstCallingEvent;
use Domain\Common\Carbon;
use Domain\Common\CarbonRange;
use Domain\Common\Range;
use Domain\Context\Context;
use Domain\Event\EventDispatcher;
use Domain\Permission\Permission;
use Domain\Staff\Staff;
use Domain\TransactionManager;
use Domain\TransactionManagerFactory;
use Domain\Url\UrlBuilder;
use Lib\Logging;
use ScalikePHP\Option;
use ScalikePHP\Seq;
use UseCase\Staff\LookupStaffUseCase;

/**
 * 出勤確認第一通知実装.
 */
class SendFirstCallingInteractor implements SendFirstCallingUseCase
{
    use Logging;

    private EventDispatcher $dispatcher;
    private FindCallingUseCase $findUseCase;
    private LookupStaffUseCase $lookupStaffUseCase;
    private CallingLogRepository $repository;
    private TransactionManager $transaction;
    private UrlBuilder $urlBuilder;

    /**
     * {@link \UseCase\Calling\SendFirstCallingInteractor} Constructor.
     *
     * @param \Domain\Event\EventDispatcher $dispatcher
     * @param \UseCase\Calling\FindCallingUseCase $findUseCase
     * @param \UseCase\Staff\LookupStaffUseCase $lookupStaffUseCase
     * @param \Domain\Calling\CallingLogRepository $repository
     * @param \Domain\TransactionManagerFactory $factory
     * @param \Domain\Url\UrlBuilder $urlBuilder
     */
    public function __construct(
        EventDispatcher $dispatcher,
        FindCallingUseCase $findUseCase,
        LookupStaffUseCase $lookupStaffUseCase,
        CallingLogRepository $repository,
        TransactionManagerFactory $factory,
        UrlBuilder $urlBuilder
    ) {
        $this->dispatcher = $dispatcher;
        $this->findUseCase = $findUseCase;
        $this->lookupStaffUseCase = $lookupStaffUseCase;
        $this->repository = $repository;
        $this->transaction = $factory->factory($repository);
        $this->urlBuilder = $urlBuilder;
    }

    /** {@inheritdoc} */
    public function handle(Context $context, CarbonRange $range): void
    {
        $this->getCallings($context, $range)
            ->flatMap(fn (Calling $calling): Option => $this->buildEventOption($context, $calling))
            ->each(function (FirstCallingEvent $event) use ($context): void {
                $this->dispatcher->dispatch($event);
                $x = $this->storeLog($event);

                $this->logger()->info(
                    '出勤確認送信履歴が登録されました',
                    ['id' => $x->id] + $context->logContext()
                );
            });
    }

    /**
     * 対象の出勤確認一覧を取得する.
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
     * 出勤確認から {@link \Domain\Calling\FirstCallingEvent} を構築する.
     *
     * 対応するスタッフが存在しない場合は None を返す.
     *
     * @param \Domain\Context\Context $context
     * @param \Domain\Calling\Calling $calling
     * @return \Domain\Calling\FirstCallingEvent[]|\ScalikePHP\Option
     */
    private function buildEventOption(Context $context, Calling $calling): Option
    {
        return $this->lookupStaffUseCase
            ->handle($context, Permission::viewStaffs(), $calling->staffId)
            ->map(function (Staff $staff) use ($context, $calling): FirstCallingEvent {
                $url = $this->urlBuilder->build($context, "/callings/{$calling->token}");
                return new FirstCallingEvent($context, $calling, $staff, $url);
            })
            ->headOption();
    }

    /**
     * {@link \Domain\Calling\FirstCallingEvent} から出勤確認送信履歴を生成して保管する.
     *
     * @param \Domain\Calling\FirstCallingEvent $event
     * @throws \Throwable
     * @return \Domain\Calling\CallingLog 保存したEntity
     */
    private function storeLog(FirstCallingEvent $event): CallingLog
    {
        $x = CallingLog::create([
            'callingId' => $event->calling()->id,
            'callingType' => CallingType::mail(),
            'isSucceeded' => true,
            'createdAt' => Carbon::now(),
        ]);
        return $this->transaction->run(function () use ($x): CallingLog {
            return $this->repository->store($x);
        });
    }
}

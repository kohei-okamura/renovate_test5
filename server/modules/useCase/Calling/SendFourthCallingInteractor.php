<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Calling;

use Domain\Calling\CallingLog;
use Domain\Calling\CallingLogRepository;
use Domain\Calling\CallingType;
use Domain\Calling\FourthCallingEvent;
use Domain\Common\Carbon;
use Domain\Common\CarbonRange;
use Domain\Common\Range;
use Domain\Context\Context;
use Domain\Event\EventDispatcher;
use Domain\Permission\Permission;
use Domain\Shift\Shift;
use Domain\Staff\Staff;
use Domain\TransactionManager;
use Domain\TransactionManagerFactory;
use Lib\Logging;
use ScalikePHP\Seq;
use UseCase\Shift\LookupShiftUseCase;
use UseCase\Staff\LookupStaffUseCase;

/**
 * 出勤確認第四通知実装.
 */
class SendFourthCallingInteractor implements SendFourthCallingUseCase
{
    use Logging;

    private EventDispatcher $dispatcher;
    private FindCallingUseCase $findCallingUseCase;
    private LookupStaffUseCase $lookupStaffUseCase;
    private LookupShiftUseCase $lookupShiftUseCase;
    private CallingLogRepository $callingLogRepository;
    private TransactionManager $transaction;

    public function __construct(
        EventDispatcher $dispatcher,
        FindCallingUseCase $findCallingUseCase,
        LookupStaffUseCase $lookupStaffUseCase,
        LookupShiftUseCase $lookupShiftUseCase,
        CallingLogRepository $callingLogRepository,
        TransactionManagerFactory $factory
    ) {
        $this->dispatcher = $dispatcher;
        $this->findCallingUseCase = $findCallingUseCase;
        $this->lookupStaffUseCase = $lookupStaffUseCase;
        $this->lookupShiftUseCase = $lookupShiftUseCase;
        $this->callingLogRepository = $callingLogRepository;
        $this->transaction = $factory->factory($callingLogRepository);
    }

    /** {@inheritdoc} */
    public function handle(Context $context, CarbonRange $range): void
    {
        $callings = $this->getCallings($context, $range);
        foreach ($callings as $calling) {
            $this->getAssigners($context, $calling->shiftIds)
                ->each(function (Staff $assignerStaff) use ($context, $calling): void {
                    $this->dispatcher->dispatch(new FourthCallingEvent($context, $assignerStaff));

                    $x = $this->transaction->run(
                        fn (): CallingLog => $this->callingLogRepository->store(
                            CallingLog::create([
                                'callingId' => $calling->id,
                                'callingType' => CallingType::telephoneCallAssigner(),
                                'isSucceeded' => true,
                                'createdAt' => Carbon::now(),
                            ])
                        )
                    );
                    $this->logger()->info(
                        '出勤確認送信履歴が登録されました',
                        ['id' => $x->id] + $context->logContext()
                    );
                });
        }
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
        return $this->findCallingUseCase
            ->handle($context, Permission::viewStaffs(), $filterParams, $paginationParams)
            ->list;
    }

    /**
     * 勤務シフトIDから管理スタッフを取得する.
     *
     * @param \Domain\Context\Context $context
     * @param array|int[] $shiftIds
     * @return \Domain\Staff\Staff[]|\ScalikePHP\Seq
     */
    private function getAssigners(Context $context, array $shiftIds): Seq
    {
        /** @var \Domain\Shift\Shift[]|\ScalikePHP\Seq $shifts */
        $shifts = $this->lookupShiftUseCase
            ->handle($context, Permission::viewStaffs(), ...$shiftIds);
        return $this->lookupStaffUseCase
            ->handle(
                $context,
                Permission::viewStaffs(),
                ...$shifts->map(fn (Shift $x): int => $x->assignerId)->toArray()
            );
    }
}

<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Calling;

use Domain\Calling\Calling;
use Domain\Calling\CallingRepository;
use Domain\Common\Carbon;
use Domain\Common\Range;
use Domain\Config\Config;
use Domain\Context\Context;
use Domain\Shift\Assignee;
use Domain\Shift\Shift;
use Domain\Shift\ShiftFinder;
use Domain\TransactionManager;
use Domain\TransactionManagerFactory;
use ScalikePHP\Map;
use ScalikePHP\Seq;
use UseCase\Concerns\UniqueTokenSupport;
use UseCase\Contracts\TokenMaker;

/**
 * 出勤確認作成ユースケース実装.
 */
final class CreateCallingsInteractor implements CreateCallingsUseCase
{
    use UniqueTokenSupport;

    private const INTERVAL_HOUR_FOR_SAME_DATE = 8; // 同一日の稼働とみなす時間の間隔[H]
    private const MAX_RETRY_COUNT = 100;
    private const TOKEN_LENGTH = 60;

    private Config $config;
    private CallingRepository $callingRepository;
    private ShiftFinder $shiftFinder;
    private TransactionManager $transaction;

    /**
     * {@link \UseCase\Calling\CreateCallingsInteractor} Constructor.
     *
     * @param \Domain\Config\Config $config
     * @param \Domain\Calling\CallingRepository $callingRepository
     * @param \Domain\Shift\ShiftFinder $shiftFinder
     * @param \UseCase\Contracts\TokenMaker $tokenMaker
     * @param \Domain\TransactionManagerFactory $factory
     */
    public function __construct(
        Config $config,
        CallingRepository $callingRepository,
        ShiftFinder $shiftFinder,
        TokenMaker $tokenMaker,
        TransactionManagerFactory $factory
    ) {
        $this->config = $config;
        $this->callingRepository = $callingRepository;
        $this->shiftFinder = $shiftFinder;
        $this->tokenMaker = $tokenMaker;
        $this->transaction = $factory->factory($callingRepository);
    }

    /** {@inheritdoc} */
    public function handle(Context $context, Range $datetimeRange): void
    {
        $this->transaction->run(function () use ($context, $datetimeRange): void {
            $lifetimeMinutes = $this->config->get('zinger.calling.lifetime_minutes');
            $createdAt = Carbon::now()->startOfMinute();
            $expiredAt = $createdAt->addMinutes($lifetimeMinutes);
            $targetShifts = $this->findTargetShifts($context->organization->id, $datetimeRange);
            $staffToShiftsMap = $this->createStaffToShiftsMap($targetShifts);
            foreach ($staffToShiftsMap as $staffId => $shiftIds) {
                $x = Calling::create([
                    'staffId' => $staffId,
                    'shiftIds' => $shiftIds,
                    'token' => $this->createUniqueToken(self::TOKEN_LENGTH, self::MAX_RETRY_COUNT),
                    'expiredAt' => $expiredAt,
                    'createdAt' => $createdAt,
                ]);
                $this->callingRepository->store($x);
            }
        });
    }

    /** {@inheritdoc} */
    protected function isUnique(string $token): bool
    {
        return $this->callingRepository->lookupOptionByToken($token)->isEmpty();
    }

    /**
     * 通知対象の勤務シフトの一覧を取得する.
     *
     * @param int $organizationId
     * @param \Domain\Common\Range $datetimeRange
     * @return \Domain\Shift\Shift[]|\ScalikePHP\Seq
     */
    private function findTargetShifts(int $organizationId, Range $datetimeRange): Seq
    {
        assert($datetimeRange->start instanceof Carbon);
        assert($datetimeRange->end instanceof Carbon);
        $filterParams = [
            'isConfirmed' => true,
            'notificationEnabled' => true,
            'organizationId' => $organizationId,
            'scheduleStart' => $datetimeRange,
            'isCanceled' => false,
        ];
        $paginationParams = [
            'sortBy' => 'id',
            'all' => true,
        ];
        return $this->shiftFinder->find($filterParams, $paginationParams)->list;
    }

    /**
     * 通知対象のスタッフ ID をキー、対象の勤務シフト ID の配列を値とする Map を取得する.
     *
     * @param \Domain\Shift\Shift[]|\ScalikePHP\Seq $shifts
     * @throws \Exception
     * @return int[][]|\ScalikePHP\Map
     */
    private function createStaffToShiftsMap(Seq $shifts): Map
    {
        return $shifts
            ->flatMap(fn (Shift $x): Seq => Seq::fromArray($x->assignees)->map(
                fn (Assignee $assignee): array => [$assignee->staffId, $x]
            ))
            ->toMap(fn (array $pair): int => $pair[0])
            ->mapValues(fn (array $pair): array => $this->findTargetShiftsForStaff($pair[0], $pair[1]));
    }

    /**
     * スタッフごとの通知対象となる勤務シフト一覧を取得する.
     *
     * @param int $staffId
     * @param \Domain\Shift\Shift $shift
     * @return array|int[]
     */
    private function findTargetShiftsForStaff(int $staffId, Shift $shift): array
    {
        /** \Domain\Common\Carbon $latestShiftEndTime 直前の勤務シフトの終了時刻 */
        $latestShiftEndTime = $shift->schedule->end;
        // 先頭の勤務シフト以降、勤務シフト同士の間隔が8時間未満の勤務シフトをすべて抽出する.
        $filterParams = [
            'isConfirmed' => true,
            'assigneeId' => $staffId,
            'scheduleDateBefore' => $shift->schedule->start->addSecond(),
            'isCanceled' => false,
        ];
        $paginationParams = [
            'sortBy' => 'id',
        ];
        $shiftIds = $this->shiftFinder
            ->cursor($filterParams, $paginationParams)
            ->takeWhile(function (Shift $item) use (&$latestShiftEndTime): bool {
                if ($item->schedule->start->diffInHours($latestShiftEndTime) > self::INTERVAL_HOUR_FOR_SAME_DATE) {
                    // 直前の勤務シフトの終了時刻と対象の勤務シフトの開始時刻の差 がオーバーしていたら終了
                    return false;
                }
                $latestShiftEndTime = $item->schedule->end;
                return true;
            })
            ->map(fn (Shift $x): int => $x->id)
            ->toArray();

        return [$shift->id, ...$shiftIds];
    }
}

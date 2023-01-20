<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\Billing;

use Domain\Model;
use Lib\Exceptions\LogicException;
use Lib\Math;
use ScalikePHP\Seq;

/**
 * 障害福祉サービス請求：サービス実績単位（重度訪問介護）
 *
 * @property-read \Domain\Billing\DwsVisitingCareForPwsdFragment $fragment 要素
 * @property-read \Domain\Common\Carbon $providedOn サービス提供年月日
 * @property-read bool $isEmergency 緊急対応フラグ
 * @property-read bool $isFirst 初回
 * @property-read bool $isBehavioralDisorderSupportCooperation 行動障害支援連携加算
 * @property-read \Domain\ServiceCodeDictionary\DwsServiceCodeCategory $category サービスコード区分
 * @property-read \Domain\Common\CarbonRange $range 全体の時間範囲
 * @property-read int $serviceCount サービス提供回数
 * @property-read bool $isTerminated 一連のサービスの最後かどうか
 * @property-read null|int $serviceDuration 算定時間数（分）
 * @property-read null|int $movingDuration 移動時間数（分）
 */
final class DwsVisitingCareForPwsdUnit extends Model
{
    // サービス時間の最小時間（分）（これ以上提供していないと算定されない）
    // 制度上は40分だが実際は30分でもよいとされる場合があるため30分とする
    public const MIN_DURATION_MINUTES_OF_DAY = 30;

    // 最小単位時間（分・1日のサービスの最初の場合）
    public const MIN_DURATION_MINUTES_OF_FIRST_HOUR = 60;

    // 移動加算の最小時間（分）（これ以上提供していないと算定されない）
    public const MIN_MOVING_DURATION_MINUTES = 1;

    // 最小単位時間（分）
    private const DURATION_THRESHOLD = 30;

    // 移動加算4の最低時間数（分）
    // この時間数より大きい場合は最大時間として計算する。
    private const MIN_MOVING4_DURATION_MINUTES = 180;

    // 移動加算の最大時間数（分）
    private const MAX_MOVING_DURATION_MINUTES = 240;

    /**
     * サービス単位（重度訪問介護）からインスタンスを生成する.
     *
     * @param \Domain\Billing\DwsVisitingCareForPwsdChunk $chunk
     * @return \ScalikePHP\Seq|self[]
     */
    public static function fromVisitingCareForPwsdChunk(DwsVisitingCareForPwsdChunk $chunk): Seq
    {
        // 1日のサービスがすべて2人で提供されている場合は要素を分割しない
        $noSplit = $chunk->fragments->forAll(fn (DwsVisitingCareForPwsdFragment $x): bool => $x->headcount === 2);

        // 1日のサービスで2人と1人のサービスが混在している場合2人のサービスは分割して扱う
        $splittedFragments = $chunk->fragments
            ->flatMap(function (DwsVisitingCareForPwsdFragment $x) use ($noSplit): iterable {
                return !$noSplit && $x->headcount === 2
                    ? [
                        $x->copy(['headcount' => 1, 'isSecondary' => false]),
                        $x->copy(['headcount' => 1, 'isSecondary' => true]),
                    ]
                    : [$x];
            })
            ->computed();
        // 同一日の一人目・二人目毎に一連のサービスとして扱うためgroupByを行う
        $seqFragments = $splittedFragments
            ->sortBy(fn (DwsVisitingCareForPwsdFragment $x) => $x->range->start)
            ->groupBy(fn (DwsVisitingCareForPwsdFragment $x) => $x->isSecondary)
            ->values();

        $units = $seqFragments->flatMap(function (Seq $xs) use ($chunk, $splittedFragments): array {
            [$movingFragments, $serviceFragments] = $xs->partition(
                fn (DwsVisitingCareForPwsdFragment $x) => $x->isMoving
            );
            $serviceDuration = $serviceFragments->sumBy(
                fn ($z, DwsVisitingCareForPwsdFragment $x) => $z + $x->getDurationMinutes()
            );

            // 30分未満の場合は算定不可のため例外
            // 30分以上60分未満の場合は60分として算定
            // 60分以上の場合は30分単位の算定時間数となる。端数の時間は切り上げで処理する。
            if ($serviceDuration < self::MIN_DURATION_MINUTES_OF_DAY) {
                throw new LogicException('Invalid serviceDuration under 30min');
            }
            $adjustedServiceDuration = $serviceDuration < self::MIN_DURATION_MINUTES_OF_FIRST_HOUR
                ? self::MIN_DURATION_MINUTES_OF_FIRST_HOUR
                : Math::ceil($serviceDuration / self::DURATION_THRESHOLD) * self::DURATION_THRESHOLD;

            $movingDuration = $movingFragments->sumBy(
                fn ($z, DwsVisitingCareForPwsdFragment $x) => $z + $x->movingDurationMinutes
            );

            // 重訪の算定時間と基本的には同様だが、移動加算は1分以上からから算定できるため1分未満を算定時間数を0にする。
            if ($movingDuration < self::MIN_MOVING_DURATION_MINUTES) {
                $adjustedMovingDuration = null;
            } elseif ($movingDuration < self::MIN_DURATION_MINUTES_OF_FIRST_HOUR) {
                $adjustedMovingDuration = self::MIN_DURATION_MINUTES_OF_FIRST_HOUR;
            } else {
                // 3時間より多い場合は4時間とする実績記録票の仕様。
                $adjustedMovingDuration = $movingDuration > self::MIN_MOVING4_DURATION_MINUTES
                    ? self::MAX_MOVING_DURATION_MINUTES
                    : Math::ceil($movingDuration / self::DURATION_THRESHOLD) * self::DURATION_THRESHOLD;
            }

            $count = $serviceFragments->count();

            if ($count === 0) {
                return [];
            } elseif ($count > 1) {
                return [
                    ...$serviceFragments->take($count - 1)->map(fn (DwsVisitingCareForPwsdFragment $x) => self::create([
                        'fragment' => $x,
                        'providedOn' => $chunk->providedOn,
                        'isEmergency' => false,
                        'isFirst' => false,
                        'isBehavioralDisorderSupportCooperation' => false,
                        'category' => $chunk->category,
                        'range' => $x->range,
                        'isTerminated' => false,
                        'serviceCount' => self::computeServiceCount($x, $splittedFragments),
                        'serviceDuration' => null,
                        'movingDuration' => null,
                    ])),
                    ...$serviceFragments->drop($count - 1)->map(fn (DwsVisitingCareForPwsdFragment $x) => self::create([
                        'fragment' => $x,
                        'providedOn' => $chunk->providedOn,
                        'isEmergency' => $chunk->isEmergency,
                        'isFirst' => $chunk->isFirst,
                        'isBehavioralDisorderSupportCooperation' => $chunk->isBehavioralDisorderSupportCooperation,
                        'category' => $chunk->category,
                        'range' => $x->range,
                        'isTerminated' => true,
                        'serviceCount' => self::computeServiceCount($x, $splittedFragments),
                        'serviceDuration' => $adjustedServiceDuration,
                        'movingDuration' => $adjustedMovingDuration,
                    ])),
                ];
            } else {
                $fragment = $serviceFragments->head();
                assert($fragment instanceof DwsVisitingCareForPwsdFragment);
                return [
                    self::create([
                        'fragment' => $fragment,
                        'providedOn' => $chunk->providedOn,
                        'isEmergency' => $chunk->isEmergency,
                        'isFirst' => $chunk->isFirst,
                        'isBehavioralDisorderSupportCooperation' => $chunk->isBehavioralDisorderSupportCooperation,
                        'category' => $chunk->category,
                        'range' => $fragment->range,
                        'isTerminated' => true,
                        'serviceCount' => self::computeServiceCount($fragment, $splittedFragments),
                        'serviceDuration' => $adjustedServiceDuration,
                        'movingDuration' => $adjustedMovingDuration,
                    ]),
                ];
            }
        });

        return $units->computed();
    }

    /**
     * 算定時間数を「時」単位で返す（整数部 n 桁 + 小数部4桁の整数表現）.
     *
     * @return int
     */
    public function getServiceDurationHours(): int
    {
        // `null` は一旦 0 として扱っておく.
        $x = $this->serviceDuration;
        return $x === null ? 0 : (int)($x / 60 * 10000);
    }

    /**
     * 移動時間数を「時」単位で返す（整数部 n 桁 + 小数部4桁の整数表現）.
     *
     * @return int
     */
    public function getMovingDurationHours(): int
    {
        // `null` は一旦 0 として扱っておく.
        $x = $this->movingDuration;
        return $x === null ? 0 : (int)($x / 60 * 10000);
    }

    /** {@inheritdoc} */
    protected function attrs(): array
    {
        return [
            'fragment',
            'providedOn',
            'isEmergency',
            'isFirst',
            'isBehavioralDisorderSupportCooperation',
            'category',
            'range',
            'serviceCount',
            'isTerminated',
            'serviceDuration',
            'movingDuration',
        ];
    }

    /** {@inheritdoc} */
    protected function jsonables(): array
    {
        return [
            'fragment' => true,
            'providedOn' => true,
            'isEmergency' => true,
            'isFirst' => true,
            'isBehavioralDisorderSupportCooperation' => true,
            'category' => true,
            'range' => true,
            'serviceCount' => true,
            'isTerminated' => true,
            'serviceDuration' => true,
            'movingDuration' => true,
        ];
    }

    /**
     * サービス提供回数を計算する.
     *
     * - 1日のサービスすべてにおいて提供人数が一致する場合はサービス提供回数を記載しない -> 0
     * - 1日のサービスで提供人数が一致しないサービスがある場合、1人目・2人目それぞれにサービス提供回数を記載する -> 1 or 2
     *
     * @param \Domain\Billing\DwsVisitingCareForPwsdFragment $fragment 比較したい要素
     * @param \Domain\Billing\DwsVisitingCareForPwsdFragment[]|\ScalikePHP\Seq $fragments 分割済みの要素
     * @return int サービス提供回数
     */
    private static function computeServiceCount(DwsVisitingCareForPwsdFragment $fragment, Seq $fragments): int
    {
        // 一部のサービスに2人目がいる = `isSecondary` が `true` である要素が存在する
        if ($fragments->exists(fn (DwsVisitingCareForPwsdFragment $x): bool => $x->isSecondary)) {
            return $fragment->isSecondary ? 2 : 1;
        } else {
            return 0;
        }
    }
}

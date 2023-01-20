<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\Billing;

use Domain\Common\Carbon;
use Domain\Common\CarbonRange;
use Domain\Model;
use Domain\ServiceCodeDictionary\DwsHomeHelpServiceProviderType;
use Domain\ServiceCodeDictionary\DwsServiceCodeCategory;
use Lib\Math;
use ScalikePHP\Seq;

/**
 * 障害福祉サービス請求：サービス実績単位（居宅介護）
 *
 * @property-read \Domain\Billing\DwsHomeHelpServiceFragment $fragment 要素
 * @property-read bool $isEmergency 緊急時対応
 * @property-read bool $isFirst 初回
 * @property-read bool $isWelfareSpecialistCooperation 福祉専門職員等連携
 * @property-read bool $isPlannedByNovice 初計（初任者研修課程修了者が作成した居宅介護計画に基づき提供する場合）
 * @property-read \Domain\ServiceCodeDictionary\DwsHomeHelpServiceBuildingType $buildingType 建物区分
 * @property-read \Domain\ServiceCodeDictionary\DwsServiceCodeCategory $category サービスコード区分
 * @property-read \Domain\Common\CarbonRange $range 全体の時間範囲
 * @property-read bool $isTerminated 一連のサービスの最後かどうか
 * @property-read null|int $serviceDuration 算定時間数（分）
 */
final class DwsHomeHelpServiceUnit extends Model
{
    /**
     * サービス単位（居宅介護）からサービス実績単位を生成する.
     *
     * @param \Domain\Billing\DwsHomeHelpServiceChunk $chunk
     * @param Carbon $providedIn
     * @return \Domain\Billing\DwsHomeHelpServiceUnit[]&\ScalikePHP\Seq
     */
    public static function fromHomeHelpServiceChunk(DwsHomeHelpServiceChunk $chunk, Carbon $providedIn): Seq
    {
        // 一連のサービスとして扱わない条件は次の通り
        // - ヘルパー資格が一致してしない
        // - 二人目
        $seqFragments = Seq::fromArray($chunk->fragments)
            ->flatMap(function (DwsHomeHelpServiceFragment $x, $index) use ($chunk, $providedIn): iterable {
                $dayBoundary = $chunk->category->getDayBoundary($x->range->start, $x->providerType, $index === 0);
                if (($x->range->start->isSameMonth($providedIn) && $x->range->end->isSameMonth($providedIn)) || $x->range->end->equalTo($dayBoundary)) {
                    // 月を跨いでいない場合はそのまま返す
                    return [$x];
                } elseif ($x->range->start->isSameMonth($providedIn)) {
                    // 翌月に跨いでいる場合は今月分のみ返す
                    return [
                        $x->copy([
                            'range' => CarbonRange::create([
                                'start' => $x->range->start,
                                'end' => $dayBoundary,
                            ]),
                        ]),
                    ];
                } else {
                    // 前月から継続しているサービスは前月分と今月分に分割して返す
                    return [
                        $x->copy([
                            'range' => CarbonRange::create([
                                'start' => $x->range->start,
                                'end' => $dayBoundary,
                            ]),
                        ]),
                        $x->copy([
                            'range' => CarbonRange::create([
                                'start' => $dayBoundary,
                                'end' => $x->range->end,
                            ]),
                        ]),
                    ];
                }
            })
            ->sortBy(fn (DwsHomeHelpServiceFragment $x) => $x->range->start)
            ->groupBy(fn (DwsHomeHelpServiceFragment $x) => $x->providerType->value() . ':' . $x->isSecondary)
            ->values();

        $units = $seqFragments->flatMap(function (Seq $xs) use ($providedIn, $chunk): array {
            $serviceDuration = $xs->sumBy(function (int $z, DwsHomeHelpServiceFragment $x) use ($providedIn): int {
                // 開始と終了がともに前月の場合に時間数が計算されてしまうため、算定対象の開始時間が前月の場合は時間数を0とする。
                return $x->range->start->isSameMonth($providedIn) ? $z + $x->range->end->diffInMinutes($x->range->start) : $z + 0;
            });

            /** @var \Domain\Billing\DwsHomeHelpServiceFragment $fragment */
            $fragment = $xs->head();

            // 時間数が最小単位になるように調整する
            $adjustedServiceDuration = self::adjustedServiceDuration(
                $serviceDuration,
                $chunk->category,
                $fragment->providerType
            );

            $count = $xs->count();
            if ($count > 1) {
                // 一連のサービスの要素が複数の場合。算定時間は最後の実績のみ。
                return [
                    ...$xs->take($count - 1)->map(fn (DwsHomeHelpServiceFragment $x): self => self::create([
                        'fragment' => $x,
                        'isEmergency' => false,
                        'isFirst' => false,
                        'isWelfareSpecialistCooperation' => false,
                        'isPlannedByNovice' => false,
                        'buildingType' => $chunk->buildingType,
                        'category' => $chunk->category,
                        'range' => $x->range,
                        'isTerminated' => false,
                        'serviceDuration' => null,
                    ])),
                    ...$xs->drop($count - 1)->map(fn (DwsHomeHelpServiceFragment $x): self => self::create([
                        'fragment' => $x,
                        'isEmergency' => $chunk->isEmergency,
                        'isFirst' => $chunk->isFirst,
                        'isWelfareSpecialistCooperation' => $chunk->isWelfareSpecialistCooperation,
                        'isPlannedByNovice' => $chunk->isPlannedByNovice,
                        'buildingType' => $chunk->buildingType,
                        'category' => $chunk->category,
                        'range' => $x->range,
                        'isTerminated' => true,
                        'serviceDuration' => $adjustedServiceDuration,
                    ])),
                ];
            } else {
                return [
                    self::create([
                        'fragment' => $xs->head(),
                        'isEmergency' => $chunk->isEmergency,
                        'isFirst' => $chunk->isFirst,
                        'isWelfareSpecialistCooperation' => $chunk->isWelfareSpecialistCooperation,
                        'isPlannedByNovice' => $chunk->isPlannedByNovice,
                        'buildingType' => $chunk->buildingType,
                        'category' => $chunk->category,
                        'range' => $xs->head()->range,
                        'isTerminated' => true,
                        'serviceDuration' => $adjustedServiceDuration,
                    ]),
                ];
            }
        });

        return Seq::from(...$units);
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

    /** {@inheritdoc} */
    protected function attrs(): array
    {
        return [
            'fragment',
            'isEmergency',
            'isFirst',
            'isWelfareSpecialistCooperation',
            'isPlannedByNovice',
            'buildingType',
            'category',
            'range',
            'isTerminated',
            'serviceDuration',
        ];
    }

    /** {@inheritdoc} */
    protected function jsonables(): array
    {
        return [
            'fragment' => true,
            'isEmergency' => true,
            'isFirst' => true,
            'isWelfareSpecialistCooperation' => true,
            'isPlannedByNovice' => true,
            'buildingType' => true,
            'category' => true,
            'range' => true,
            'isTerminated' => true,
            'serviceDuration' => true,
        ];
    }

    /**
     * 算定時間数（分）が最小単位の倍数になるように調整する
     *
     * @param int $serviceDuration 算定時間数（分）
     * @param \Domain\ServiceCodeDictionary\DwsServiceCodeCategory $category
     * @param \Domain\ServiceCodeDictionary\DwsHomeHelpServiceProviderType $providerType
     * @return int 調整後の算定時間数（分）
     */
    private static function adjustedServiceDuration(
        int $serviceDuration,
        DwsServiceCodeCategory $category,
        DwsHomeHelpServiceProviderType $providerType
    ): int {
        $minMinutesForStart = $category->getMinDurationMinutes($providerType, true);
        if ($serviceDuration < $minMinutesForStart) {
            return $minMinutesForStart;
        } else {
            $minMinutes = $category->getMinDurationMinutes($providerType, false);
            return Math::ceil($serviceDuration / $minMinutes) * $minMinutes;
        }
    }
}

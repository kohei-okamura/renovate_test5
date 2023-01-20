<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\Billing;

use Domain\Common\Carbon;
use ScalikePHP\Seq;

/**
 * 障害福祉サービス請求：サービス単位（重度訪問介護）.
 *
 * @property-read int $id ID
 * @property-read int $userId 利用者ID
 * @property-read bool $isEmergency 緊急対応フラグ
 * @property-read bool $isFirst 初回
 * @property-read bool $isBehavioralDisorderSupportCooperation 行動障害支援連携加算
 * @property-read \Domain\ServiceCodeDictionary\DwsServiceCodeCategory $category サービスコード区分
 * @property-read \Domain\Common\Carbon $providedOn サービス提供年月日
 * @property-read \Domain\Common\CarbonRange $range 全体の時間範囲
 * @property-read \Domain\Billing\DwsVisitingCareForPwsdFragment[]|\ScalikePHP\Seq $fragments 要素
 * @mixin \Domain\Model
 */
interface DwsVisitingCareForPwsdChunk
{
    // 最小単位時間（分）
    public const MIN_DURATION_MINUTES = 30;

    // 最小単位時間（分・1日のサービスの最初の場合）
    public const MIN_DURATION_MINUTES_OF_FIRST_HOUR = 60;

    // 1日の最低サービス提供時間数（概ね30分）
    // 制度上40分だが運用上30分となっている場合もあるため30分とする
    public const MIN_DURATION_MINUTES_OF_DAY = 30;

    /**
     * 1日の区切り位置を取得する.
     *
     * @param \Domain\Common\Carbon $start
     * @return \Carbon\CarbonImmutable|\Domain\Common\Carbon
     */
    public static function getDayBoundary(Carbon $start): Carbon;

    /**
     * 合成する.
     *
     * @param \Domain\Billing\DwsVisitingCareForPwsdChunk $that
     * @throws \Lib\Exceptions\LogicException
     * @return static
     */
    public function compose(DwsVisitingCareForPwsdChunk $that): self;

    /**
     * 時間帯別提供情報の一覧を取得する.
     *
     * - 1人の場合: 1組目（index = 0）のみ（$headcount は 1 となる）
     * - 2人の場合(isSecondary = true): 2組目（index = 1）が発生する
     *
     * Seqのイメージ
     * [
     *   [1組目時間帯1, 1組目時間帯2, ...],
     *   [2組目時間帯1, 2組目時間帯2, ...]
     * ]
     *
     * @return array[]|\Domain\Billing\DwsVisitingCareForPwsdDuration[][]|\ScalikePHP\Seq
     */
    public function getDurations(): Seq;

    /**
     * パラメータを指定して追加可能かどうか判定する.
     *
     * 次のすべての条件を満たす場合に追加可能とする.
     *
     * - サービスコード区分が一致する.
     * - サービス提供年月日が一致する.
     *
     * @param \Domain\Billing\DwsVisitingCareForPwsdChunk $that
     * @return bool
     */
    public function isComposable(DwsVisitingCareForPwsdChunk $that): bool;

    /**
     * 有効かどうか（算定可能かどうか）を判定する.
     *
     * @return bool
     */
    public function isEffective(): bool;

    /**
     * 日ごとに分割する.
     *
     * @throws \Exception
     * @return \Domain\Billing\DwsVisitingCareForPwsdChunk[]|\ScalikePHP\Seq
     */
    public function split(): Seq;
}

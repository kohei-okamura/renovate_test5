<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\Billing;

use ScalikePHP\Seq;

/**
 * 障害福祉サービス請求：サービス単位（居宅介護）.
 *
 * @property-read int $id
 * @property-read int $userId 利用者 ID
 * @property-read \Domain\ServiceCodeDictionary\DwsServiceCodeCategory $category サービスコード区分
 * @property-read \Domain\ServiceCodeDictionary\DwsHomeHelpServiceBuildingType $buildingType 建物区分
 * @property-read bool $isEmergency 緊急時対応
 * @property-read bool $isFirst 初回
 * @property-read bool $isWelfareSpecialistCooperation 福祉専門職員等連携
 * @property-read bool $isPlannedByNovice 初計（初任者研修課程修了者が作成した居宅介護計画に基づき提供する場合）
 * @property-read \Domain\Common\CarbonRange $range 全体の時間範囲
 * @property-read \Domain\Billing\DwsHomeHelpServiceFragment[]|\ScalikePHP\Seq $fragments 要素
 * @mixin \Domain\Model
 */
interface DwsHomeHelpServiceChunk
{
    // 身体介護を伴う場合の最小単位時間（分）
    // 身体介護、通院等介助（身体を伴う）、通院等介助（身体を伴わない）が該当する
    public const MIN_DURATION_MINUTES_DEFAULT = 30;

    // 身体介護を伴わない場合の最小単位時間（分）
    // 家事援助が該当する
    public const MIN_DURATION_MINUTES_HOUSEWORK = 15;

    /**
     * パラメータを指定して合成する.
     *
     * @param \Domain\Billing\DwsHomeHelpServiceChunk $that
     * @throws \Exception
     * @return static
     */
    public function compose(DwsHomeHelpServiceChunk $that): self;

    /**
     * パラメータを指定して追加可能かどうか判定する.
     *
     * 次のすべての条件を満たす場合に追加可能とする.
     *
     * - サービスコード区分が一致する.
     * - 建物区分が一致する.
     * - いずれのサービス単位も「緊急時対応」が `false` である.
     * - 初計（初任者研修課程修了者が作成した居宅介護計画に基づき提供する場合）の値が一致する.
     * - 時間範囲がこのサービス単位の時間範囲に2時間を追加した範囲と重複する.
     *
     * @param \Domain\Billing\DwsHomeHelpServiceChunk $that
     * @return bool
     */
    public function isComposable(DwsHomeHelpServiceChunk $that): bool;

    /**
     * 時間帯別提供情報の一覧を取得する.
     *
     * - 1人の場合: 1組目（index = 0）のみ（$headcount は 1 となる）
     * - 2人かつ提供時間および提供者区分が一致する場合: 1組目（index = 0）のみ（$headcount は 2 となる）
     * - 2人かつ提供時間または提供者区分が異なる場合: 2組目（index = 0 or 1）まで（$headcount はぞれぞれ 1 となる）
     *
     * Seqのイメージ
     * [
     *   [1組目時間帯1, 1組目時間帯2, ...],
     *   [2組目時間帯1, 2組目時間帯2, ...]
     * ]
     *
     * @return \Domain\Billing\DwsHomeHelpServiceDuration[][]|\ScalikePHP\Seq
     */
    public function getDurations(): Seq;
}

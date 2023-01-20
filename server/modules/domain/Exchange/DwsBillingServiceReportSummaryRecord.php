<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\Exchange;

use Domain\Attributes\JsonIgnore;
use Domain\Billing\DwsBilling;
use Domain\Billing\DwsBillingBundle;
use Domain\Billing\DwsBillingServiceReport;
use Domain\Billing\DwsBillingServiceReportAggregateCategory;
use Domain\Billing\DwsBillingServiceReportAggregateGroup;
use Domain\Billing\DwsBillingServiceReportFormat;
use Domain\Common\Carbon;
use Domain\Common\Decimal;

/**
 * 障害：サービス提供実績記録票：基本情報レコード.
 */
final class DwsBillingServiceReportSummaryRecord extends DwsBillingServiceReportRecord
{
    private const TOTAL_PHYSICAL_CARE_100_FRACTION_DIGITS = 2;
    private const TOTAL_PHYSICAL_CARE_70_FRACTION_DIGITS = 2;
    private const TOTAL_PHYSICAL_CARE_PWSD_FRACTION_DIGITS = 2;
    private const TOTAL_PHYSICAL_CARE_FRACTION_DIGITS = 2;
    private const TOTAL_ACCOMPANY_WITH_PHYSICAL_CARE_100_FRACTION_DIGITS = 2;
    private const TOTAL_ACCOMPANY_WITH_PHYSICAL_CARE_70_FRACTION_DIGITS = 2;
    private const TOTAL_ACCOMPANY_WITH_PHYSICAL_CARE_PWSD_FRACTION_DIGITS = 2;
    private const TOTAL_ACCOMPANY_WITH_PHYSICAL_CARE_FRACTION_DIGITS = 2;
    private const TOTAL_HOUSEWORK_100_FRACTION_DIGITS = 2;
    private const TOTAL_HOUSEWORK_90_FRACTION_DIGITS = 2;
    private const TOTAL_HOUSEWORK_FRACTION_DIGITS = 2;
    private const TOTAL_ACCOMPANY_100_FRACTION_DIGITS = 2;
    private const TOTAL_ACCOMPANY_90_FRACTION_DIGITS = 2;
    private const TOTAL_ACCOMPANY_FRACTION_DIGITS = 2;
    private const TOTAL_ACCESSIBLE_TAXI_100_FRACTION_DIGITS = 2;
    private const TOTAL_ACCESSIBLE_TAXI_90_FRACTION_DIGITS = 2;
    private const TOTAL_ACCESSIBLE_TAXI_FRACTION_DIGITS = 2;
    private const MOVING_DURATION_HOURS_FRACTION_DIGITS = 1;

    /**
     * {@link \Domain\Exchange\DwsBillingServiceReportSummaryRecord} constructor.
     *
     * @param \Domain\Common\Carbon $providedIn サービス提供年月
     * @param string $cityCode 市町村番号
     * @param string $officeCode 事業所番号
     * @param string $dwsNumber 受給者番号
     * @param \Domain\Billing\DwsBillingServiceReportFormat $format 様式種別番号
     * @param \Domain\Common\Decimal $totalPhysicalCare100 合計1：内訳100％: 居宅介護（身体介護）
     * @param \Domain\Common\Decimal $totalPhysicalCare70 合計1：内訳70％
     * @param \Domain\Common\Decimal $totalPhysicalCarePwsd 合計1：内訳重訪
     * @param \Domain\Common\Decimal $totalPhysicalCare 合計1：合計算定時間数計
     * @param \Domain\Common\Decimal $totalAccompanyWithPhysicalCare100 合計2：内訳100％: 居宅介護（通院介護（伴う））
     * @param \Domain\Common\Decimal $totalAccompanyWithPhysicalCare70 合計2：内訳70％
     * @param \Domain\Common\Decimal $totalAccompanyWithPhysicalCarePwsd 合計2：内訳重訪
     * @param \Domain\Common\Decimal $totalAccompanyWithPhysicalCare 合計2：合計算定時間数計
     * @param \Domain\Common\Decimal $totalHousework100 合計3：内訳100％: 居宅介護（家事援助）
     * @param \Domain\Common\Decimal $totalHousework90 合計3：内訳90％
     * @param \Domain\Common\Decimal $totalHousework 合計3：合計算定時間数計
     * @param \Domain\Common\Decimal $totalAccompany100 合計4：内訳100％
     * @param \Domain\Common\Decimal $totalAccompany90 合計4：内訳90％
     * @param \Domain\Common\Decimal $totalAccompany 合計4：合計算定時間数計
     * @param \Domain\Common\Decimal $totalAccessibleTaxi100 合計5：内訳100％: 居宅介護（通院等乗降介助）
     * @param \Domain\Common\Decimal $totalAccessibleTaxi90 合計5：内訳90％
     * @param \Domain\Common\Decimal $totalAccessibleTaxi 合計5：合計算定時間数計
     * @param \Domain\Common\Decimal $movingDurationHours 提供実績の合計：算定 移動介護分
     * @param int $emergencyCount 提供実績の合計2：緊急時対応加算（回）
     * @param int $firstTimeCount 提供実績の合計2：初回加算（回）
     * @param int $welfareSpecialistCooperationCount 提供実績の合計2：福祉専門職員等連携加算（回）
     * @param int $behavioralDisorderSupportCooperationCount 提供実績の合計2：行動障害支援連携加算（回）
     * @param int $movingCareSupport 提供実績の合計3：移動介護緊急時支援加算 (回)
     */
    public function __construct(
        Carbon $providedIn,
        string $cityCode,
        string $officeCode,
        string $dwsNumber,
        DwsBillingServiceReportFormat $format,
        #[JsonIgnore] public readonly Decimal $totalPhysicalCare100,
        #[JsonIgnore] public readonly Decimal $totalPhysicalCare70,
        #[JsonIgnore] public readonly Decimal $totalPhysicalCarePwsd,
        #[JsonIgnore] public readonly Decimal $totalPhysicalCare,
        #[JsonIgnore] public readonly Decimal $totalAccompanyWithPhysicalCare100,
        #[JsonIgnore] public readonly Decimal $totalAccompanyWithPhysicalCare70,
        #[JsonIgnore] public readonly Decimal $totalAccompanyWithPhysicalCarePwsd,
        #[JsonIgnore] public readonly Decimal $totalAccompanyWithPhysicalCare,
        #[JsonIgnore] public readonly Decimal $totalHousework100,
        #[JsonIgnore] public readonly Decimal $totalHousework90,
        #[JsonIgnore] public readonly Decimal $totalHousework,
        #[JsonIgnore] public readonly Decimal $totalAccompany100,
        #[JsonIgnore] public readonly Decimal $totalAccompany90,
        #[JsonIgnore] public readonly Decimal $totalAccompany,
        #[JsonIgnore] public readonly Decimal $totalAccessibleTaxi100,
        #[JsonIgnore] public readonly Decimal $totalAccessibleTaxi90,
        #[JsonIgnore] public readonly Decimal $totalAccessibleTaxi,
        #[JsonIgnore] public readonly Decimal $movingDurationHours,
        #[JsonIgnore] public readonly int $emergencyCount,
        #[JsonIgnore] public readonly int $firstTimeCount,
        #[JsonIgnore] public readonly int $welfareSpecialistCooperationCount,
        #[JsonIgnore] public readonly int $behavioralDisorderSupportCooperationCount,
        #[JsonIgnore] public readonly int $movingCareSupport
    ) {
        parent::__construct(
            recordFormat: self::RECORD_FORMAT_SUMMARY,
            providedIn: $providedIn,
            cityCode: $cityCode,
            officeCode: $officeCode,
            dwsNumber: $dwsNumber,
            format: $format
        );
    }

    /** {@inheritdoc} */
    public function toArray(int $recordNumber): array
    {
        return [
            ...parent::toArray($recordNumber),
            // 補足給付関係情報：補足給付適用の有無
            self::UNUSED,
            // 補足給付関係情報：補足給付額（円／日）
            self::UNUSED,
            // 補足給付関係情報：食費の単価 朝食（円／日）
            self::UNUSED,
            // 補足給付関係情報：食費の単価 昼食（円／日）
            self::UNUSED,
            // 補足給付関係情報：食費の単価 夕食（円／日）
            self::UNUSED,
            // 補足給付関係情報：食費の単価 一日（円／日）
            self::UNUSED,
            // 補足給付関係情報：光熱水費の単価 一日（円／日）
            self::UNUSED,
            // 補足給付関係情報：光熱水費の単価 一日（円／月）
            self::UNUSED,
            // 合計1：内訳100％
            $this->totalPhysicalCare100->isZero()
                ? ''
                : $this->totalPhysicalCare100->toInt(self::TOTAL_PHYSICAL_CARE_100_FRACTION_DIGITS),
            // 合計1：内訳70％
            $this->totalPhysicalCare70->isZero()
                ? ''
                : $this->totalPhysicalCare70->toInt(self::TOTAL_PHYSICAL_CARE_70_FRACTION_DIGITS),
            // 合計1：内訳重訪
            $this->totalPhysicalCarePwsd->isZero()
                ? ''
                : $this->totalPhysicalCarePwsd->toInt(self::TOTAL_PHYSICAL_CARE_PWSD_FRACTION_DIGITS),
            // 合計1：合計算定時間数計
            $this->totalPhysicalCare->isZero()
                ? ''
                : $this->totalPhysicalCare->toInt(self::TOTAL_PHYSICAL_CARE_FRACTION_DIGITS),
            // 合計2：内訳100％
            $this->totalAccompanyWithPhysicalCare100->isZero()
                ? ''
                : $this->totalAccompanyWithPhysicalCare100->toInt(self::TOTAL_ACCOMPANY_WITH_PHYSICAL_CARE_100_FRACTION_DIGITS),
            // 合計2：内訳70％
            $this->totalAccompanyWithPhysicalCare70->isZero()
                ? ''
                : $this->totalAccompanyWithPhysicalCare70->toInt(self::TOTAL_ACCOMPANY_WITH_PHYSICAL_CARE_70_FRACTION_DIGITS),
            // 合計2：内訳重訪
            $this->totalAccompanyWithPhysicalCarePwsd->isZero()
                ? ''
                : $this->totalAccompanyWithPhysicalCarePwsd->toInt(self::TOTAL_ACCOMPANY_WITH_PHYSICAL_CARE_PWSD_FRACTION_DIGITS),
            // 合計2：合計算定時間数計
            $this->totalAccompanyWithPhysicalCare->isZero()
                ? ''
                : $this->totalAccompanyWithPhysicalCare->toInt(self::TOTAL_ACCOMPANY_WITH_PHYSICAL_CARE_FRACTION_DIGITS),
            // 合計3：内訳100％
            $this->totalHousework100->isZero()
                ? ''
                : $this->totalHousework100->toInt(self::TOTAL_HOUSEWORK_100_FRACTION_DIGITS),
            // 合計3：内訳90％
            $this->totalHousework90->isZero()
                ? ''
                : $this->totalHousework90->toInt(self::TOTAL_HOUSEWORK_90_FRACTION_DIGITS),
            // 合計3：合計算定時間数計
            $this->totalHousework->isZero()
                ? ''
                : $this->totalHousework->toInt(self::TOTAL_HOUSEWORK_FRACTION_DIGITS),
            // 合計4：内訳100％
            $this->totalAccompany100->isZero()
                ? ''
                : $this->totalAccompany100->toInt(self::TOTAL_ACCOMPANY_100_FRACTION_DIGITS),
            // 合計4：内訳90％
            $this->totalAccompany90->isZero()
                ? ''
                : $this->totalAccompany90->toInt(self::TOTAL_ACCOMPANY_90_FRACTION_DIGITS),
            // 合計4：合計算定時間数計
            $this->totalAccompany->isZero()
                ? ''
                : $this->totalAccompany->toInt(self::TOTAL_ACCOMPANY_FRACTION_DIGITS),
            // 合計5：内訳100％
            $this->totalAccessibleTaxi100->isZero()
                ? ''
                : $this->totalAccessibleTaxi100->toInt(self::TOTAL_ACCESSIBLE_TAXI_100_FRACTION_DIGITS),
            // 合計5：内訳90％
            $this->totalAccessibleTaxi90->isZero()
                ? ''
                : $this->totalAccessibleTaxi90->toInt(self::TOTAL_ACCESSIBLE_TAXI_90_FRACTION_DIGITS),
            // 合計5：合計算定時間数計
            $this->totalAccessibleTaxi->isZero()
                ? ''
                : $this->totalAccessibleTaxi->toInt(self::TOTAL_ACCESSIBLE_TAXI_FRACTION_DIGITS),
            // 提供実績の合計：算定 移動介護分
            $this->movingDurationHours->isZero()
                ? ''
                : $this->movingDurationHours->toInt(self::MOVING_DURATION_HOURS_FRACTION_DIGITS),
            // 提供実績の合計：実績 送迎加算（回）
            self::UNUSED,
            // 提供実績の合計：実績 家庭連携加算（回）（サービス提供回数）
            self::UNUSED,
            // 提供実績の合計：実績 家庭連携加算（回）（算定回数）
            self::UNUSED,
            // 提供実績の合計：合計 算定日数（日）
            self::UNUSED,
            // 提供実績の合計：夜間支援体制加算（回）
            self::UNUSED,
            // 提供実績の合計：日中支援加算（回）（サービス提供回数）
            self::UNUSED,
            // 提供実績の合計：日中支援加算（回）（算定回数）
            self::UNUSED,
            // 提供実績の合計：通所型（回）
            self::UNUSED,
            // 提供実績の合計：訪問型1時間未満（回）
            self::UNUSED,
            // 提供実績の合計：訪問型1時間以上（回）
            self::UNUSED,
            // 提供実績の合計：短期滞在加算（回）
            self::UNUSED,
            // 提供実績の合計：食事提供加算（回）
            self::UNUSED,
            // 提供実績の合計：入院・外泊時加算（回）
            self::UNUSED,
            // 提供実績の合計：入院時支援特別加算（回）（サービス提供回数）
            self::UNUSED,
            // 提供実績の合計：入院時支援特別加算（回）（算定回数）
            self::UNUSED,
            // 提供実績の合計：自立生活支援加算（回）
            self::UNUSED,
            // 提供実績の合計：自活訓練加算（回）
            self::UNUSED,
            // 提供実績の合計：訪問支援特別加算（回）（サービス提供回数）
            self::UNUSED,
            // 提供実績の合計：訪問支援特別加算（回）（算定回数）
            self::UNUSED,
            // 提供実績の合計：施設外支援 当月（日）
            self::UNUSED,
            // 提供実績の合計：施設外支援 累計（日／180日）
            self::UNUSED,
            // 提供実績の合計：帰宅時支援加算（回）（サービス提供回数）
            self::UNUSED,
            // 提供実績の合計：帰宅時支援加算（回）（算定回数）
            self::UNUSED,
            // 実費算定の合計：朝食（回）
            self::UNUSED,
            // 実費算定の合計：昼食（回）
            self::UNUSED,
            // 実費算定の合計：夕食（回）
            self::UNUSED,
            // 実費算定の合計：光熱水費（回）
            self::UNUSED,
            // 実費算定の合計：各小計 食事（円）
            self::UNUSED,
            // 実費算定の合計：各小計 光熱水費（円）
            self::UNUSED,
            // 実費算定の合計：実費合計額（円）
            self::UNUSED,
            // 入所時特別支援加算：利用開始日（年月日）
            self::UNUSED,
            // 入所時特別支援加算：30日目（年月日）
            self::UNUSED,
            // 入所時特別支援加算：当月算定日数（日）
            self::UNUSED,
            // 退所時特別支援加算：入所中算定日（年月日）
            self::UNUSED,
            // 退所時特別支援加算：退所日（年月日）
            self::UNUSED,
            // 退所時特別支援加算：退所後算定日（年月日）
            self::UNUSED,
            // 初期加算：利用開始日（年月日）
            self::UNUSED,
            // 初期加算：30日目（年月日）
            self::UNUSED,
            // 初期加算：当月算定日数（日）
            self::UNUSED,
            // 地域移行加算：入所中算定日（年月日）
            self::UNUSED,
            // 地域移行加算：退所日（年月日）
            self::UNUSED,
            // 地域移行加算：退所後算定日（年月日）
            self::UNUSED,
            // 重度包括：実績単位数（単位）
            self::UNUSED,
            // 重度包括：実績割合（％）
            self::UNUSED,
            // 重度包括：支給決定量（単位）
            self::UNUSED,
            // 重度包括：報酬請求額（円）
            self::UNUSED,
            // 重度包括：利用者負担上限月額（円）
            self::UNUSED,
            // 重度包括：利用者負担額（円）
            self::UNUSED,
            // 重度包括：共同生活援助合計日数
            self::UNUSED,
            // 重度包括：短期入所合計日数
            self::UNUSED,
            // 重度包括：その他サービス合計時間数
            self::UNUSED,
            // 重度包括：当該月の日数
            self::UNUSED,
            // 重度包括：サービス担当者会議開催日
            self::UNUSED,
            // 重度訪問介護（様式3-2）集計欄：第1時間帯 早朝
            self::UNUSED,
            // 重度訪問介護（様式3-2）集計欄：第1時間帯 日中
            self::UNUSED,
            // 重度訪問介護（様式3-2）集計欄：第1時間帯 夜間
            self::UNUSED,
            // 重度訪問介護（様式3-2）集計欄：第1時間帯 深夜
            self::UNUSED,
            // 重度訪問介護（様式3-2）集計欄：第2時間帯 早朝
            self::UNUSED,
            // 重度訪問介護（様式3-2）集計欄：第2時間帯 日中
            self::UNUSED,
            // 重度訪問介護（様式3-2）集計欄：第2時間帯 夜間
            self::UNUSED,
            // 重度訪問介護（様式3-2）集計欄：第2時間帯 深夜
            self::UNUSED,
            // 重度訪問介護（様式3-2）集計欄：第3時間帯 早朝
            self::UNUSED,
            // 重度訪問介護（様式3-2）集計欄：第3時間帯 日中
            self::UNUSED,
            // 重度訪問介護（様式3-2）集計欄：第3時間帯 夜間
            self::UNUSED,
            // 重度訪問介護（様式3-2）集計欄：第3時間帯 深夜
            self::UNUSED,
            // 重度訪問介護（様式3-2）集計欄：第4時間帯 早朝
            self::UNUSED,
            // 重度訪問介護（様式3-2）集計欄：第4時間帯 日中
            self::UNUSED,
            // 重度訪問介護（様式3-2）集計欄：第4時間帯 夜間
            self::UNUSED,
            // 重度訪問介護（様式3-2）集計欄：第4時間帯 深夜
            self::UNUSED,
            // 重度訪問介護（様式3-2）集計欄：第5時間帯 早朝
            self::UNUSED,
            // 重度訪問介護（様式3-2）集計欄：第5時間帯 日中
            self::UNUSED,
            // 重度訪問介護（様式3-2）集計欄：第5時間帯 夜間
            self::UNUSED,
            // 重度訪問介護（様式3-2）集計欄：第5時間帯 深夜
            self::UNUSED,
            // 重度訪問介護（様式3-2）集計欄：第6時間帯 早朝
            self::UNUSED,
            // 重度訪問介護（様式3-2）集計欄：第6時間帯 日中
            self::UNUSED,
            // 重度訪問介護（様式3-2）集計欄：第6時間帯 夜間
            self::UNUSED,
            // 重度訪問介護（様式3-2）集計欄：第6時間帯 深夜
            self::UNUSED,
            // 施設種類
            self::UNUSED,
            // 提供実績の合計2：緊急時対応加算（回）
            empty($this->emergencyCount) ? '' : $this->emergencyCount,
            // 提供実績の合計2：初回加算（回）
            empty($this->firstTimeCount) ? '' : $this->firstTimeCount,
            // 提供実績の合計2：福祉専門職員等連携加算（回）
            empty($this->welfareSpecialistCooperationCount) ? '' : $this->welfareSpecialistCooperationCount,
            // 提供実績の合計2：行動障害支援連携加算（回）
            empty($this->behavioralDisorderSupportCooperationCount) ? '' : $this->behavioralDisorderSupportCooperationCount,
            // 提供実績の合計2：行動障害支援指導連携加算（回）
            self::UNUSED,
            // 提供実績の合計2：医療連携体制加算（回）
            self::UNUSED,
            // 提供実績の合計2：緊急短期入所受入加算（回）
            self::UNUSED,
            // 提供実績の合計2：単独型加算（一定の条件を満たす場合）（回）
            self::UNUSED,
            // 提供実績の合計2：重度障害者支援加算（一定の条件を満たす場合）（回）
            self::UNUSED,
            // 提供実績の合計2：事業所内相談支援加算（回）
            self::UNUSED,
            // 提供実績の合計2：同行支援（回）
            self::UNUSED,
            // 提供実績の合計2：特別地域加算（回）
            self::UNUSED,
            // 提供実績の合計2：低所得者利用加算（回）
            self::UNUSED,
            // 提供実績の合計2：体験利用支援加算（回）
            self::UNUSED,
            // 提供実績の合計2：定員超過特例加算（回）
            self::UNUSED,
            // 提供実績の合計2：通勤訓練加算（回）
            self::UNUSED,
            // 提供実績の合計2：地域移行加算（回）
            self::UNUSED,
            // 提供実績の合計2：体験宿泊支援加算（回）
            self::UNUSED,
            // 提供実績の合計2：住居外利用（日）
            self::UNUSED,
            // 合計1：内訳 生活援助
            self::UNUSED,
            // 合計2：内訳 90％
            self::UNUSED,
            // 合計2：内訳 生活援助
            self::UNUSED,
            // 合計3：内訳 生活援助
            self::UNUSED,
            // 合計４：内訳 生活援助
            self::UNUSED,
            // 合計５：内訳 生活援助
            self::UNUSED,
            // 重度包括：共同生活援助合計単位数
            self::UNUSED,
            // 重度包括：短期入所合計単位数
            self::UNUSED,
            // 重度包括：その他サービス合計単位数
            self::UNUSED,
            // 保育・教育等移行支援加算：移行日（年月日）
            self::UNUSED,
            // 保育・教育等移行支援加算：移行後算定日（ 年月日）
            self::UNUSED,
            // 通所施設移行支援加算：移行日（年月日）
            self::UNUSED,
            // 通所施設移行支援加算：算定日（年月日）
            self::UNUSED,
            // 提供実績の合計3：緊急時支援加算(回)
            self::UNUSED,
            // 提供実績の合計3：支援計画会議実施加算 (回)
            self::UNUSED,
            // 提供実績の合計3：定着支援連携促進加算 (回)
            self::UNUSED,
            // 提供実績の合計3：移動介護緊急時支援加算 (回)
            empty($this->movingCareSupport) ? '' : $this->movingCareSupport,
            // 提供実績の合計3：日常生活支援情報提供加算(回)(サービス提供回数)
            self::UNUSED,
            // 提供実績の合計3：日常生活支援情報提供加算(回)(算定回数)
            self::UNUSED,
            // 提供実績の合計3：地域居住支援体制強化推進加算(回)(サービス提供回数)
            self::UNUSED,
            // 提供実績の合計3：地域居住支援体制強化推進加算(回)(算定回数)
            self::UNUSED,
            // 提供実績の合計3：地域協働加算(回)
            self::UNUSED,
            // 支援レポート共有日(年月日)
            self::UNUSED,
            // 入院開始日(年月日)
            self::UNUSED,
        ];
    }

    /**
     * インスタンス生成.
     *
     * @param \Domain\Billing\DwsBilling $billing
     * @param \Domain\Billing\DwsBillingBundle $bundle
     * @param \Domain\Billing\DwsBillingServiceReport $report
     * @return static
     */
    public static function from(DwsBilling $billing, DwsBillingBundle $bundle, DwsBillingServiceReport $report): self
    {
        // 居宅介護の場合は身体介護の合計時間
        // 重度訪問介護の場合は重度訪問介護の合計時間
        $totalPhysicalCare = $report->format === DwsBillingServiceReportFormat::homeHelpService()
            ? $report->result->get(
                DwsBillingServiceReportAggregateGroup::physicalCare(),
                DwsBillingServiceReportAggregateCategory::categoryTotal()
            )
            : $report->result->get(
                DwsBillingServiceReportAggregateGroup::visitingCareForPwsd(),
                DwsBillingServiceReportAggregateCategory::categoryTotal()
            );
        return new self(
            providedIn: $bundle->providedIn,
            cityCode: $bundle->cityCode,
            officeCode: $billing->office->code,
            dwsNumber: $report->user->dwsNumber,
            format: $report->format,
            totalPhysicalCare100: $report->result->get(
                DwsBillingServiceReportAggregateGroup::physicalCare(),
                DwsBillingServiceReportAggregateCategory::category100()
            ),
            totalPhysicalCare70: $report->result->get(
                DwsBillingServiceReportAggregateGroup::physicalCare(),
                DwsBillingServiceReportAggregateCategory::category70()
            ),
            totalPhysicalCarePwsd: $report->result->get(
                DwsBillingServiceReportAggregateGroup::physicalCare(),
                DwsBillingServiceReportAggregateCategory::categoryPwsd()
            ),
            totalPhysicalCare: $totalPhysicalCare,
            totalAccompanyWithPhysicalCare100: $report->result->get(
                DwsBillingServiceReportAggregateGroup::accompanyWithPhysicalCare(),
                DwsBillingServiceReportAggregateCategory::category100()
            ),
            totalAccompanyWithPhysicalCare70: $report->result->get(
                DwsBillingServiceReportAggregateGroup::accompanyWithPhysicalCare(),
                DwsBillingServiceReportAggregateCategory::category70()
            ),
            totalAccompanyWithPhysicalCarePwsd: $report->result->get(
                DwsBillingServiceReportAggregateGroup::accompanyWithPhysicalCare(),
                DwsBillingServiceReportAggregateCategory::categoryPwsd()
            ),
            totalAccompanyWithPhysicalCare: $report->result->get(
                DwsBillingServiceReportAggregateGroup::accompanyWithPhysicalCare(),
                DwsBillingServiceReportAggregateCategory::categoryTotal()
            ),
            totalHousework100: $report->result->get(
                DwsBillingServiceReportAggregateGroup::housework(),
                DwsBillingServiceReportAggregateCategory::category100()
            ),
            totalHousework90: $report->result->get(
                DwsBillingServiceReportAggregateGroup::housework(),
                DwsBillingServiceReportAggregateCategory::category90()
            ),
            totalHousework: $report->result->get(
                DwsBillingServiceReportAggregateGroup::housework(),
                DwsBillingServiceReportAggregateCategory::categoryTotal()
            ),
            totalAccompany100: $report->result->get(
                DwsBillingServiceReportAggregateGroup::accompany(),
                DwsBillingServiceReportAggregateCategory::category100()
            ),
            totalAccompany90: $report->result->get(
                DwsBillingServiceReportAggregateGroup::accompany(),
                DwsBillingServiceReportAggregateCategory::category90()
            ),
            totalAccompany: $report->result->get(
                DwsBillingServiceReportAggregateGroup::accompany(),
                DwsBillingServiceReportAggregateCategory::categoryTotal()
            ),
            totalAccessibleTaxi100: $report->result->get(
                DwsBillingServiceReportAggregateGroup::accessibleTaxi(),
                DwsBillingServiceReportAggregateCategory::category100()
            ),
            totalAccessibleTaxi90: $report->result->get(
                DwsBillingServiceReportAggregateGroup::accessibleTaxi(),
                DwsBillingServiceReportAggregateCategory::category90()
            ),
            totalAccessibleTaxi: $report->result->get(
                DwsBillingServiceReportAggregateGroup::accessibleTaxi(),
                DwsBillingServiceReportAggregateCategory::categoryTotal()
            ),
            movingDurationHours: $report->result->get(
                DwsBillingServiceReportAggregateGroup::outingSupportForPwsd(),
                DwsBillingServiceReportAggregateCategory::categoryTotal()
            ),
            emergencyCount: $report->emergencyCount,
            firstTimeCount: $report->firstTimeCount,
            welfareSpecialistCooperationCount: $report->welfareSpecialistCooperationCount,
            behavioralDisorderSupportCooperationCount: $report->behavioralDisorderSupportCooperationCount,
            movingCareSupport: $report->movingCareSupportCount,
        );
    }
}

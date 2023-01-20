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
use Domain\Billing\DwsBillingServiceReportFormat;
use Domain\Billing\DwsBillingServiceReportItem;
use Domain\Billing\DwsBillingServiceReportProviderType;
use Domain\Billing\DwsBillingServiceReportSituation;
use Domain\Billing\DwsGrantedServiceCode;
use Domain\Billing\DwsServiceDivisionCode;
use Domain\Common\Carbon;
use Domain\Common\Decimal;
use Domain\Common\TimeRange;
use ScalikePHP\Seq;

/**
 * 障害：サービス提供実績記録票：明細情報レコード.
 */
final class DwsBillingServiceReportItemRecord extends DwsBillingServiceReportRecord
{
    private const SERVICE_DURATION_HOURS_FRACTION_DIGITS = 2;
    private const MOVING_DURATION_HOURS_FRACTION_DIGITS = 1;

    /**
     * {@link \Domain\Exchange\DwsBillingServiceReportItemRecord} constructor.
     *
     * @param \Domain\Common\Carbon $providedIn サービス提供年月
     * @param string $cityCode 市町村番号
     * @param string $officeCode 事業所番号
     * @param string $dwsNumber 受給者番号
     * @param int $serialNumber 提供通番
     * @param \Domain\Common\Carbon $providedOn 日付
     * @param int $serviceCount サービス提供回数
     * @param \Domain\Billing\DwsGrantedServiceCode $dwsGrantedServiceCode サービス内容
     * @param \Domain\Billing\DwsBillingServiceReportProviderType $providerType ヘルパー資格
     * @param bool $isDriving 運転フラグ
     * @param \Domain\Common\TimeRange $period 開始時間・終了時間
     * @param null|\Domain\Common\Decimal $serviceDurationHours 算定時間数
     * @param null|\Domain\Common\Decimal $movingDurationHours 移動
     * @param int $headcount 派遣人数
     * @param bool $isPreviousMonth 前月からの継続サービス
     * @param string $note 備考
     * @param \Domain\Billing\DwsBillingServiceReportSituation $situation サービス提供の状況
     * @param bool $isEmergency 緊急時対応加算
     * @param bool $isFirstTime 初回加算
     * @param bool $isWelfareSpecialistCooperation 福祉専門職員等連携加算
     * @param bool $isBehavioralDisorderSupportCooperation 行動障害支援連携加算
     * @param bool $isCoaching 同行支援
     * @param bool $isMovingCareSupport 移動介護緊急時支援加算
     * @param DwsBillingServiceReportFormat $format
     */
    public function __construct(
        Carbon $providedIn,
        string $cityCode,
        string $officeCode,
        string $dwsNumber,
        DwsBillingServiceReportFormat $format,
        #[JsonIgnore] public readonly int $serialNumber,
        #[JsonIgnore] public readonly Carbon $providedOn,
        #[JsonIgnore] public readonly int $serviceCount,
        #[JsonIgnore] public readonly DwsGrantedServiceCode $dwsGrantedServiceCode,
        #[JsonIgnore] public readonly DwsBillingServiceReportProviderType $providerType,
        #[JsonIgnore] public readonly bool $isDriving,
        #[JsonIgnore] public readonly TimeRange $period,
        #[JsonIgnore] public readonly ?Decimal $serviceDurationHours,
        #[JsonIgnore] public readonly ?Decimal $movingDurationHours,
        #[JsonIgnore] public readonly int $headcount,
        #[JsonIgnore] public readonly bool $isPreviousMonth,
        #[JsonIgnore] public readonly string $note,
        #[JsonIgnore] public readonly DwsBillingServiceReportSituation $situation,
        #[JsonIgnore] public readonly bool $isEmergency,
        #[JsonIgnore] public readonly bool $isFirstTime,
        #[JsonIgnore] public readonly bool $isWelfareSpecialistCooperation,
        #[JsonIgnore] public readonly bool $isBehavioralDisorderSupportCooperation,
        #[JsonIgnore] public readonly bool $isCoaching,
        #[JsonIgnore] public readonly bool $isMovingCareSupport
    ) {
        parent::__construct(
            recordFormat: self::RECORD_FORMAT_ITEM,
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
            // 提供通番
            $this->serialNumber,
            // 日付
            $this->providedOn->format('d'),
            // サービス提供回数
            $this->serviceCount === 0 ? '' : $this->serviceCount,
            // サービス内容（以下の4つ以外は入らない）
            // - 'physicalCare' => '111000'
            // - 'housework' => '112000'
            // - 'accompanyWithPhysicalCare' => '113000'
            // - 'accompany' => '114000'
            $this->dwsGrantedServiceCode === DwsGrantedServiceCode::none() || $this->dwsGrantedServiceCode->toDwsServiceDivisionCode() !== DwsServiceDivisionCode::homeHelpService()
                ? ''
                : $this->dwsGrantedServiceCode->value(),
            // ヘルパー資格
            $this->providerType === DwsBillingServiceReportProviderType::none() ? '' : $this->providerType->value(),
            // 運転フラグ
            self::formatBoolean($this->isDriving),
            // 開始時間
            self::formatTimeString($this->period->start),
            // 終了時間]
            self::formatTimeString($this->period->end),
            // 算定時間数 (時間数0の表記をするケースが存在するかわからなかったためnullの場合は空文字とする）
            $this->serviceDurationHours === null
                ? ''
                : $this->serviceDurationHours->toInt(self::SERVICE_DURATION_HOURS_FRACTION_DIGITS),
            // 乗降（回数）
            self::UNUSED,
            // 移動
            $this->movingDurationHours === null
                ? ''
                : $this->movingDurationHours->toInt(self::MOVING_DURATION_HOURS_FRACTION_DIGITS),
            // 派遣人数
            $this->headcount,
            // 前月からの継続サービス
            self::formatBoolean($this->isPreviousMonth),
            // 送迎加算 往
            self::UNUSED,
            // 送迎加算 復
            self::UNUSED,
            // 家庭連携加算（サービス提供時間数）
            self::UNUSED,
            // 家庭連携加算（算定時間数）
            self::UNUSED,
            // 自活訓練加算
            self::UNUSED,
            // 短期滞在加算
            self::UNUSED,
            // 訪問支援特別加算（サービス提供時間数）
            self::UNUSED,
            // 訪問支援特別加算（算定時間数）
            self::UNUSED,
            // 施設外支援
            self::UNUSED,
            // 退所時特別支援加算
            self::UNUSED,
            // 地域移行加算
            self::UNUSED,
            // 食事提供加算
            self::UNUSED,
            // 入院・外泊時加算
            self::UNUSED,
            // 提供形態
            self::UNUSED,
            // 備考
            $this->note,
            // サービス提供の状況
            $this->situation === DwsBillingServiceReportSituation::none() ? '' : $this->situation->value(),
            // 夜間支援体制加算
            self::UNUSED,
            // 入院時支援特別加算（サービス提供回数）
            self::UNUSED,
            // 入院時支援特別加算（算定回数）
            self::UNUSED,
            // 帰宅時支援加算（サービス提供回数）
            self::UNUSED,
            // 帰宅時支援加算（算定回数）
            self::UNUSED,
            // 自立生活支援加算
            self::UNUSED,
            // 日中支援加算（サービス提供回数）
            self::UNUSED,
            // 日中支援加算（算定回数）
            self::UNUSED,
            // 算定日数
            self::UNUSED,
            // 自立訓練 訪問型時間数
            self::UNUSED,
            // 実費算定：朝食
            self::UNUSED,
            // 実費算定：昼食
            self::UNUSED,
            // 実費算定：夕食
            self::UNUSED,
            // 実費算定：光熱水費
            self::UNUSED,
            // 重度包括：適用単価
            self::UNUSED,
            // 重度包括：基本単位数
            self::UNUSED,
            // 重度包括：加算
            self::UNUSED,
            // 重度包括：加算後単位数
            self::UNUSED,
            // 重度包括：単位数
            self::UNUSED,
            // 重度包括：1日計
            self::UNUSED,
            // 重度訪問介護（様式3-2）：1時間（13時間）
            self::UNUSED,
            // 重度訪問介護（様式3-2）：2時間（14時間）
            self::UNUSED,
            // 重度訪問介護（様式3-2）：3時間（15時間）
            self::UNUSED,
            // 重度訪問介護（様式3-2）：4時間（16時間）
            self::UNUSED,
            // 重度訪問介護（様式3-2）：5時間（17時間）
            self::UNUSED,
            // 重度訪問介護（様式3-2）：6時間（18時間）
            self::UNUSED,
            // 重度訪問介護（様式3-2）：7時間（19時間）
            self::UNUSED,
            // 重度訪問介護（様式3-2）：8時間（20時間）
            self::UNUSED,
            // 重度訪問介護（様式3-2）：9時間（21時間）
            self::UNUSED,
            // 重度訪問介護（様式3-2）：10時間（22時間）
            self::UNUSED,
            // 重度訪問介護（様式3-2）：11時間（23時間）
            self::UNUSED,
            // 重度訪問介護（様式3-2）：12時間（24時間）
            self::UNUSED,
            // 緊急時対応加算
            self::formatBoolean($this->isEmergency),
            // 初回加算
            self::formatBoolean($this->isFirstTime),
            // 福祉専門職員等連携加算
            self::formatBoolean($this->isWelfareSpecialistCooperation),
            // 行動障害支援連携加算
            self::formatBoolean($this->isBehavioralDisorderSupportCooperation),
            // 行動障害支援指導連携加算
            self::UNUSED,
            // 医療連携体制加算
            self::UNUSED,
            // 緊急短期入所受入加算
            self::UNUSED,
            // 単独型加算（一定の条件を満たす場合）
            self::UNUSED,
            // 重度障害者支援加算（一定の条件を満たす場合）
            self::UNUSED,
            // 事業所内相談支援加算
            self::UNUSED,
            // 利用人数
            self::UNUSED,
            // 同行支援
            self::formatBoolean($this->isCoaching),
            // 特別地域加算
            self::UNUSED,
            // 低所得者利用加算
            self::UNUSED,
            // 体験利用支援加算
            self::UNUSED,
            // 定員超過特例加算
            self::UNUSED,
            // 通勤訓練加算
            self::UNUSED,
            // 体験宿泊支援加算
            self::UNUSED,
            // 住居外利用
            self::UNUSED,
            // 緊急時支援加算
            self::UNUSED,
            // 支援計画会議実施加算
            self::UNUSED,
            // 定着支援連携促進加算
            self::UNUSED,
            // 移動介護緊急時支援加算
            self::formatBoolean($this->isMovingCareSupport),
            // 日常生活支援情報提供加算 (サービス提供回数)
            self::UNUSED,
            // 日常生活支援情報提供加算 (算定回数)
            self::UNUSED,
            // 地域居住支援体制強化推進 加算(サービス提供回数)
            self::UNUSED,
            // 地域居住支援体制強化推進 加算(算定回数)
            self::UNUSED,
            // 地域協働加算
            self::UNUSED,
        ];
    }

    /**
     * インスタンスを生成する.
     *
     * @param \Domain\Billing\DwsBilling $billing
     * @param \Domain\Billing\DwsBillingBundle $billingBundle
     * @param \Domain\Billing\DwsBillingServiceReport $billingServiceReport
     * @return \Domain\Billing\DwsBillingServiceReportItem&\ScalikePHP\Seq
     */
    public static function from(
        DwsBilling $billing,
        DwsBillingBundle $billingBundle,
        DwsBillingServiceReport $billingServiceReport
    ): Seq {
        return Seq::fromArray($billingServiceReport->items)
            ->filter(fn (DwsBillingServiceReportItem $x): bool => $x->result !== null)
            ->map(fn (DwsBillingServiceReportItem $item): self => new self(
                providedIn: $billingBundle->providedIn,
                cityCode: $billingBundle->cityCode,
                officeCode: $billing->office->code,
                dwsNumber: $billingServiceReport->user->dwsNumber,
                format: $billingServiceReport->format,
                serialNumber: $item->serialNumber,
                providedOn: $item->providedOn,
                serviceCount: $item->serviceCount,
                dwsGrantedServiceCode: $item->serviceType,
                providerType: $item->providerType,
                isDriving: $item->isDriving,
                period: TimeRange::create([
                    'start' => $item->result->period->start->format('H:i'),
                    'end' => $item->result->period->end->format('H:i'),
                ]),
                serviceDurationHours: $item->result->serviceDurationHours,
                movingDurationHours: $item->result->movingDurationHours,
                headcount: $item->headcount,
                isPreviousMonth: $item->isPreviousMonth,
                note: $item->note,
                situation: $item->situation,
                isEmergency: $item->isEmergency,
                isFirstTime: $item->isFirstTime,
                isWelfareSpecialistCooperation: $item->isWelfareSpecialistCooperation,
                isBehavioralDisorderSupportCooperation: $item->isBehavioralDisorderSupportCooperation,
                isCoaching: $item->isCoaching,
                isMovingCareSupport: $item->isMovingCareSupport,
            ));
    }
}

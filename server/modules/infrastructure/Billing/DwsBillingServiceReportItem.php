<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\Billing;

use Domain\Billing\DwsBillingServiceReportDuration;
use Domain\Billing\DwsBillingServiceReportItem as DomainItem;
use Domain\Common\Carbon;
use Domain\Common\CarbonRange;
use Domain\Common\Decimal;
use Infrastructure\Domainable;
use Infrastructure\Model;

/**
 * サービス提供実績記録票：明細 Eloquent モデル.
 *
 * @property int $id 明細 ID
 * @property int $dws_billing_service_report_id サービス提供実績記録票 ID
 * @property int $serial_number 提供通番
 * @property \Domain\Common\Carbon $provided_on 日付
 * @property \Domain\Billing\DwsGrantedServiceCode $service_type サービス内容
 * @property \Domain\Billing\DwsBillingServiceReportProviderType $provider_type ヘルパー資格
 * @property \Domain\Billing\DwsBillingServiceReportSituation $situation サービス提供の状況
 * @property null|\Domain\Billing\DwsBillingServiceReportDuration $plan 予定（計画）
 * @property null|\Domain\Billing\DwsBillingServiceReportDuration $result 実績
 * @property int $service_count サービスの提供回数
 * @property int $headcount 派遣人数
 * @property bool $is_coaching 同行支援
 * @property bool $is_first_time 初回加算
 * @property bool $is_emergency 緊急時対応加算
 * @property bool $is_welfare_specialist_cooperation 福祉専門職員等連携加算
 * @property bool $is_behavioral_disorder_support_cooperation 行動障害支援連携加算
 * @property bool $is_moving_care_support 移動介護緊急時支援加算
 * @property bool $is_driving 運転フラグ
 * @property bool $is_previous_month 前月からの継続サービス
 * @property string $note 備考
 * @property int $sort_order 並び順
 */
final class DwsBillingServiceReportItem extends Model implements Domainable
{
    /**
     * テーブル名.
     */
    public const TABLE = 'dws_billing_service_report_item';

    /**
     * ドメインモデル・Eloquent モデル共通の属性.
     */
    private const ATTRIBUTES = [
        'serial_number',
        'provided_on',
        'service_type',
        'provider_type',
        'situation',
        'plan',
        'result',
        'service_count',
        'headcount',
        'is_coaching',
        'is_first_time',
        'is_emergency',
        'is_welfare_specialist_cooperation',
        'is_behavioral_disorder_support_cooperation',
        'is_moving_care_support',
        'is_driving',
        'is_previous_month',
        'note',
    ];

    /**
     * ドメインモデルに定義されている属性.
     */
    private const DOMAIN_ATTRIBUTES = self::ATTRIBUTES;

    /** {@inheritdoc} */
    protected $table = self::TABLE;

    /** {@inheritdoc} */
    protected $fillable = [
        'id',
        'dws_billing_service_report_id',
        'sort_order',
        ...self::ATTRIBUTES,
    ];

    /** {@inheritdoc} */
    protected $casts = [
        'provided_on' => 'date',
        'service_type' => CastsDwsGrantedServiceCode::class,
        'provider_type' => CastsDwsBillingServiceReportProviderType::class,
        'situation' => CastsDwsBillingServiceReportSituation::class,
        'is_coaching' => 'bool',
        'is_first_time' => 'bool',
        'is_emergency' => 'bool',
        'is_welfare_specialist_cooperation' => 'bool',
        'is_behavioral_disorder_support_cooperation' => 'bool',
        'is_moving_care_support' => 'bool',
        'is_driving' => 'bool',
        'is_previous_month' => 'bool',
    ];

    /**
     * Create an instance from domain model.
     *
     * @param \Domain\Billing\DwsBillingServiceReportItem $domain
     * @param int $reportId
     * @param int $sortOrder
     * @return \Infrastructure\Billing\DwsBillingServiceReportItem
     */
    public static function fromDomain(DomainItem $domain, int $reportId, int $sortOrder): self
    {
        $keys = [
            'dws_billing_service_report_id' => $reportId,
            'sort_order' => $sortOrder,
        ];
        $attrs = self::getDomainValues($domain, self::ATTRIBUTES);
        return self::firstOrNew($keys, $attrs)->fill($attrs);
    }

    /** {@inheritdoc} */
    public function toDomain(): DomainItem
    {
        $attrs = $this->toDomainAttributes(self::DOMAIN_ATTRIBUTES);
        return DomainItem::create($attrs);
    }

    /**
     * Get mutator for plan attribute.
     *
     * @return null|\Domain\Billing\DwsBillingServiceReportDuration
     * @noinspection PhpUnused
     */
    protected function getPlanAttribute(): ?DwsBillingServiceReportDuration
    {
        return $this->getDuration('plan');
    }

    /**
     * Set mutator for plan attribute.
     *
     * @param null|\Domain\Billing\DwsBillingServiceReportDuration $value
     * @noinspection PhpUnused
     */
    protected function setPlanAttribute(?DwsBillingServiceReportDuration $value): void
    {
        $this->setDuration('plan', $value);
    }

    /**
     * Get mutator for result attribute.
     *
     * @return null|\Domain\Billing\DwsBillingServiceReportDuration
     * @noinspection PhpUnused
     */
    protected function getResultAttribute(): ?DwsBillingServiceReportDuration
    {
        return $this->getDuration('result');
    }

    /**
     * Set mutator for result attribute.
     *
     * @param null|\Domain\Billing\DwsBillingServiceReportDuration $value
     * @noinspection PhpUnused
     */
    protected function setResultAttribute(?DwsBillingServiceReportDuration $value): void
    {
        $this->setDuration('result', $value);
    }

    /**
     * 属性値からサービス提供実績記録票：明細：算定時間を生成して返す.
     *
     * @param string $prefix
     * @return null|\Domain\Billing\DwsBillingServiceReportDuration
     */
    private function getDuration(string $prefix): ?DwsBillingServiceReportDuration
    {
        $isEmpty = $this->attributes["{$prefix}_period_start"] === null
            || $this->attributes["{$prefix}_period_end"] === null;
        return $isEmpty
            ? null
            : DwsBillingServiceReportDuration::create([
                'period' => CarbonRange::create([
                    'start' => Carbon::parse($this->attributes["{$prefix}_period_start"]),
                    'end' => Carbon::parse($this->attributes["{$prefix}_period_end"]),
                ]),
                'serviceDurationHours' => $this->attributes["{$prefix}_service_duration_hours"]
                    ? Decimal::fromInt($this->attributes["{$prefix}_service_duration_hours"])
                    : null,
                'movingDurationHours' => $this->attributes["{$prefix}_moving_duration_hours"]
                    ? Decimal::fromInt($this->attributes["{$prefix}_moving_duration_hours"])
                    : null,
            ]);
    }

    /**
     * サービス提供実績記録票：明細：算定時間を属性値に設定する.
     *
     * @param string $prefix
     * @param null|\Domain\Billing\DwsBillingServiceReportDuration $value
     * @return void
     */
    private function setDuration(string $prefix, ?DwsBillingServiceReportDuration $value): void
    {
        if ($value === null) {
            $this->attributes["{$prefix}_period_start"] = null;
            $this->attributes["{$prefix}_period_end"] = null;
            $this->attributes["{$prefix}_service_duration_hours"] = null;
            $this->attributes["{$prefix}_moving_duration_hours"] = null;
        } else {
            $this->attributes["{$prefix}_period_start"] = $value->period->start;
            $this->attributes["{$prefix}_period_end"] = $value->period->end;
            $this->attributes["{$prefix}_service_duration_hours"] = $value->serviceDurationHours === null
                ? null
                : $value->serviceDurationHours->toInt();
            $this->attributes["{$prefix}_moving_duration_hours"] = $value->movingDurationHours === null
                ? null
                : $value->movingDurationHours->toInt();
        }
    }
}

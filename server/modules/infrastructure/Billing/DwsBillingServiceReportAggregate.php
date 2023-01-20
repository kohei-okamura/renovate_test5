<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\Billing;

use Domain\Billing\DwsBillingServiceReportAggregate as DomainAggregate;
use Domain\Billing\DwsBillingServiceReportAggregateCategory;
use Domain\Billing\DwsBillingServiceReportAggregateGroup;
use Infrastructure\Common\CastsDecimal;
use Infrastructure\Model;
use Lib\Arrays;

/**
 * サービス提供実績記録票：合計 Eloquent モデル基底クラス.
 *
 * @property int $id サービス提供実績記録票：合計ID
 * @property int $dws_billing_service_report_id サービス提供実績記録票ID
 * @property \Domain\Billing\DwsBillingServiceReportAggregateGroup $group サービス提供実績記録票：合計区分グループ
 * @property \Domain\Billing\DwsBillingPaymentCategory $category サービス提供実績記録票：合計区分カテゴリー
 * @property \Domain\Common\Decimal $value 合計時間
 * @property int $sort_order 並び順
 */
abstract class DwsBillingServiceReportAggregate extends Model
{
    /**
     * ドメインモデル・Eloquent モデル共通の属性.
     */
    private const ATTRIBUTES = [
        'id',
        'group',
        'category',
        'value',
    ];

    /** {@inheritdoc} */
    protected $fillable = [
        'dws_billing_service_report_id',
        'sort_order',
        ...self::ATTRIBUTES,
    ];

    /** {@inheritdoc} */
    protected $casts = [
        'group' => CastsDwsBillingServiceReportAggregateGroup::class,
        'category' => CastsDwsBillingServiceReportAggregateCategory::class,
        'value' => CastsDecimal::class,
    ];

    /**
     * Create an array of instances from domain model.
     *
     * @param \Domain\Billing\DwsBillingServiceReportAggregate $domain
     * @param int $reportId
     * @return array
     */
    public static function fromDomain(DomainAggregate $domain, int $reportId): array
    {
        return Arrays::generate(function () use ($domain, $reportId): iterable {
            $sortOrder = 0;
            foreach ($domain->toAssoc() as $groupValue => $item) {
                $group = DwsBillingServiceReportAggregateGroup::from($groupValue);
                foreach ($item as $categoryValue => $value) {
                    $category = DwsBillingServiceReportAggregateCategory::from($categoryValue);
                    $keys = [
                        'dws_billing_service_report_id' => $reportId,
                        'sort_order' => $sortOrder++,
                    ];
                    $attrs = compact('group', 'category', 'value') + $keys;
                    yield static::firstOrNew($keys, $attrs)->fill($attrs);
                }
            }
        });
    }
}

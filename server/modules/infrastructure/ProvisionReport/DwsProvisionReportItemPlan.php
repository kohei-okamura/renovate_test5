<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\ProvisionReport;

use Domain\ProvisionReport\DwsProvisionReportItem as DomainDwsProvisionReportItem;
use Domain\Shift\ServiceOption;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Infrastructure\Common\ScheduleHolder;
use Infrastructure\Domainable;
use Infrastructure\Model;
use Infrastructure\Project\CastsDwsProjectServiceCategory;
use Infrastructure\Shift\SyncServiceOptions;

/**
 * 障害福祉サービス：予実：要素：予定 Eloquent モデル.
 *
 * @property int $id 障害福祉サービス：予実：要素：予定ID
 * @property int $dws_provision_report_id 障害福祉サービス：予実ID
 * @property int $sort_order 並び順
 * @property-read \Domain\Common\Schedule $schedule スケジュール
 * @property \Domain\Project\DwsProjectServiceCategory $category サービス区分
 * @property int $headcount 提供人数
 * @property int $moving_duration_minutes 移動介護時間数
 * @property null|int $own_expense_program_id 自費サービス情報 ID
 * @property-read \Domain\Shift\ServiceOption[] $options サービスオプション
 * @property string $note 備考
 */
final class DwsProvisionReportItemPlan extends Model implements Domainable
{
    use ScheduleHolder;
    use SyncServiceOptions;

    /**
     * テーブル名.
     */
    public const TABLE = 'dws_provision_report_item_plan';

    /**
     * ドメインモデル・Eloquent モデル共通の属性.
     */
    private const ATTRIBUTES = [
        'schedule',
        'category',
        'headcount',
        'moving_duration_minutes',
        'own_expense_program_id',
        'note',
    ];

    /**
     * ドメインモデルに定義されている属性.
     */
    private const DOMAIN_ATTRIBUTES = [
        ...self::ATTRIBUTES,
        'options',
    ];

    /** {@inheritdoc} */
    protected $table = self::TABLE;

    /** {@inheritdoc} */
    protected $fillable = [
        'id',
        'dws_provision_report_id',
        ...self::ATTRIBUTES,
        'sort_order',
    ];

    /** {@inheritdoc} */
    protected $casts = [
        'schedule_start' => 'datetime',
        'schedule_end' => 'datetime',
        'schedule_date' => 'date',
        'category' => CastsDwsProjectServiceCategory::class,
    ];

    /**
     * HasMany: {@link \Infrastructure\ProvisionReport\DwsProvisionReportItemPlanServiceOption}.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function options(): HasMany
    {
        return $this->hasMany(DwsProvisionReportItemPlanServiceOption::class);
    }

    /**
     * Create an instance from domain model.
     *
     * @param \Domain\ProvisionReport\DwsProvisionReportItem $domain
     * @param int $reportId
     * @param int $sortOrder
     * @return \Infrastructure\ProvisionReport\DwsProvisionReportItemPlan
     */
    public static function fromDomain(DomainDwsProvisionReportItem $domain, int $reportId, int $sortOrder): self
    {
        $keys = [
            'dws_provision_report_id' => $reportId,
            'sort_order' => $sortOrder,
        ];
        $attrs = self::getDomainValues($domain, self::ATTRIBUTES);
        return self::firstOrNew($keys, $attrs)->fill($attrs);
    }

    /** {@inheritdoc} */
    public function toDomain(): DomainDwsProvisionReportItem
    {
        $attrs = $this->toDomainAttributes(self::DOMAIN_ATTRIBUTES);
        return DomainDwsProvisionReportItem::create($attrs);
    }

    /**
     * Get mutator for options.
     *
     * @return \Domain\Shift\ServiceOption[]
     * @noinspection PhpUnused
     */
    protected function getOptionsAttribute(): array
    {
        return $this->mapRelation(
            'options',
            fn (DwsProvisionReportItemPlanServiceOption $option): ServiceOption => ServiceOption::from(
                $option->service_option
            )
        );
    }
}

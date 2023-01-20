<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\Billing;

use Domain\Billing\DwsBillingServiceReport as DomainReport;
use Domain\Billing\DwsBillingServiceReportAggregate as DomainAggregate;
use Domain\Billing\DwsBillingServiceReportItem as DomainItem;
use Domain\Common\Decimal;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Infrastructure\Domainable;
use Infrastructure\Model;
use ScalikePHP\Seq;

/**
 * サービス提供実績記録票 Eloquent モデル.
 *
 * @property int $id サービス提供実績記録票ID
 * @property int $dws_billing_id 請求ID
 * @property int $dws_billing_bundle_id 請求単位ID
 * @property \Domain\Billing\DwsBillingUser $user 利用者（支給決定者）
 * @property \Domain\Billing\DwsBillingServiceReportFormat $format 様式種別番号
 * @property DomainAggregate $plan 予定（計画）
 * @property DomainAggregate $result 実績
 * @property int $emergency_count 提供実績の合計2：緊急時対応加算（回）
 * @property int $first_time_count 提供実績の合計2：初回加算（回）
 * @property int $welfare_specialist_cooperation_count 提供実績の合計2：福祉専門職員等連携加算（回）
 * @property int $behavioral_disorder_support_cooperation_count 提供実績の合計2：行動障害支援連携加算（回）
 * @property int $moving_care_support_count 提供実績の合計3：移動介護緊急時支援加算（回）
 * @property array|\Domain\Billing\DwsBillingServiceReportItem[] $items 明細
 * @property \Domain\Billing\DwsBillingStatus $status 状態
 * @property \Domain\Common\Carbon $fixed_at 確定日時
 * @property \Domain\Common\Carbon $created_at 登録日時
 * @property \Domain\Common\Carbon $updated_at 更新日時
 */
final class DwsBillingServiceReport extends Model implements Domainable
{
    use DwsBillingUserHolder;

    /**
     * テーブル名.
     */
    public const TABLE = 'dws_billing_service_report';

    /**
     * ドメインモデル・Eloquent モデル共通の属性.
     */
    private const ATTRIBUTES = [
        'id',
        'dws_billing_id',
        'dws_billing_bundle_id',
        'user',
        'format',
        'emergency_count',
        'first_time_count',
        'welfare_specialist_cooperation_count',
        'behavioral_disorder_support_cooperation_count',
        'moving_care_support_count',
        'status',
        'fixed_at',
        'created_at',
        'updated_at',
    ];

    /**
     * ドメインモデルに定義されている属性.
     */
    private const DOMAIN_ATTRIBUTES = [
        ...self::ATTRIBUTES,
        'plan',
        'result',
        'items',
    ];

    /** {@inheritdoc} */
    protected $table = self::TABLE;

    /** {@inheritdoc} */
    protected $fillable = self::ATTRIBUTES;

    /** {@inheritdoc} */
    protected $casts = [
        'format' => CastsDwsBillingServiceReportFormat::class,
        'status' => CastsDwsBillingStatus::class,
        'fixed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /** {@inheritdoc} */
    protected $with = [
        'items',
        'plans',
        'results',
    ];

    /**
     * Create an instance from domain model.
     *
     * @param \Domain\Billing\DwsBillingServiceReport $domain
     * @return \Infrastructure\Billing\DwsBillingServiceReport
     */
    public static function fromDomain(DomainReport $domain): self
    {
        $attrs = self::getDomainValues($domain, self::ATTRIBUTES);
        return self::findOrNew($domain->id, ['id'])->fill($attrs);
    }

    /** {@inheritdoc} */
    public function toDomain(): DomainReport
    {
        $attrs = $this->toDomainAttributes(self::DOMAIN_ATTRIBUTES);
        return DomainReport::create($attrs);
    }

    /**
     * HasMany: {@link \Infrastructure\Billing\DwsBillingServiceReportItem}
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function items(): HasMany
    {
        return $this->hasMany(DwsBillingServiceReportItem::class);
    }

    /**
     * HasMany: {@link \Infrastructure\Billing\DwsBillingServiceReportPlan}
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function plans(): HasMany
    {
        return $this->hasMany(DwsBillingServiceReportPlan::class);
    }

    /**
     * HasMany: {@link \Infrastructure\Billing\DwsBillingServiceReportResult}
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function results(): HasMany
    {
        return $this->hasMany(DwsBillingServiceReportResult::class);
    }

    /**
     * Get mutator for items attribute.
     *
     * @return array|\Domain\Billing\DwsBillingServiceReportItem[]
     * @noinspection PhpUnused
     */
    protected function getItemsAttribute(): array
    {
        return $this->mapRelation('items', fn (DwsBillingServiceReportItem $x): DomainItem => $x->toDomain());
    }

    /**
     * Get mutator for plan attribute.
     *
     * @throws \Exception
     * @return \Domain\Billing\DwsBillingServiceReportAggregate
     * @noinspection PhpUnused
     */
    protected function getPlanAttribute(): DomainAggregate
    {
        return $this->getAggregate('plans');
    }

    /**
     * Get mutator for result attribute.
     *
     * @throws \Exception
     * @return \Domain\Billing\DwsBillingServiceReportAggregate
     * @noinspection PhpUnused
     */
    protected function getResultAttribute(): DomainAggregate
    {
        return $this->getAggregate('results');
    }

    /**
     * Get mutator for plan attribute.
     *
     * @param string $relation
     * @throws \Exception
     * @noinspection PhpUnused
     * @return \Domain\Billing\DwsBillingServiceReportAggregate
     */
    private function getAggregate(string $relation): DomainAggregate
    {
        $assoc = Seq::from(...$this->getRelationValue($relation))
            ->groupBy(fn (DwsBillingServiceReportAggregate $x): int => $x->group->value())
            ->mapValues(function (Seq $xs): array {
                return $xs
                    ->toMap(fn (DwsBillingServiceReportAggregate $x): int => $x->category->value())
                    ->mapValues(fn (DwsBillingServiceReportAggregate $x): Decimal => $x->value)
                    ->toAssoc();
            })
            ->toAssoc();
        return DomainAggregate::fromAssoc($assoc);
    }
}

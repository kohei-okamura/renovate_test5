<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\ProvisionReport;

use Domain\ProvisionReport\DwsProvisionReport as DomainDwsProvisionReport;
use Domain\ProvisionReport\DwsProvisionReportItem as DomainDwsProvisionReportItem;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Infrastructure\Domainable;
use Infrastructure\Model;

/**
 * 障害福祉サービス：予実 Eloquent モデル.
 *
 * @property int $id 障害福祉サービス：予実ID
 * @property int $user_id 事業所ID
 * @property int $office_id 事業所ID
 * @property int $contract_id 契約ID
 * @property \Domain\Common\Carbon $provided_in サービス提供年月
 * @property-read \Domain\ProvisionReport\DwsProvisionReportStatus $status 状態
 * @property null|\Domain\Common\Carbon $fixed_at 確定日時
 * @property \Domain\Common\Carbon $created_at 登録日時
 * @property \Domain\Common\Carbon $updated_at 更新日時
 * @property-read \Illuminate\Database\Eloquent\Collection|\Infrastructure\ProvisionReport\DwsProvisionReportItemPlan[] $plans 予定
 * @property-read \Illuminate\Database\Eloquent\Collection|\Infrastructure\ProvisionReport\DwsProvisionReportItemResult[] $results 実績
 */
final class DwsProvisionReport extends Model implements Domainable
{
    /**
     * テーブル名.
     */
    public const TABLE = 'dws_provision_report';

    /** {@inheritdoc} */
    protected $table = self::TABLE;

    /** {@inheritdoc} */
    protected $fillable = [
        'id',
        'user_id',
        'office_id',
        'contract_id',
        'provided_in',
        'status',
        'fixed_at',
        'created_at',
        'updated_at',
    ];

    /** {@inheritdoc} */
    protected $casts = [
        'provided_in' => 'date',
        'fixed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'status' => CastsDwsProvisionReportStatus::class,
    ];

    /** {@inheritdoc} */
    protected $with = [
        'plans',
        'plans.options',
        'results',
        'results.options',
    ];

    /**
     * HasMany: {@link \Infrastructure\ProvisionReport\DwsProvisionReportItemPlan}.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     * @codeCoverageIgnore リレーションの定義のため
     */
    public function plans(): HasMany
    {
        return $this->hasMany(DwsProvisionReportItemPlan::class);
    }

    /**
     * HasMany: {@link \Infrastructure\ProvisionReport\DwsProvisionReportItemResult}.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     * @codeCoverageIgnore リレーションの定義のため
     */
    public function results(): HasMany
    {
        return $this->hasMany(DwsProvisionReportItemResult::class);
    }

    /**
     * Create an instance from domain model.
     *
     * @param \Domain\ProvisionReport\DwsProvisionReport $domain
     * @return \Infrastructure\ProvisionReport\DwsProvisionReport
     */
    public static function fromDomain(DomainDwsProvisionReport $domain): self
    {
        $keys = [
            'id',
            'user_id',
            'office_id',
            'contract_id',
            'provided_in',
            'plans',
            'results',
            'status',
            'fixed_at',
            'created_at',
            'updated_at',
        ];
        $values = self::getDomainValues($domain, $keys);
        return self::findOrNew($domain->id, ['id'])->fill($values);
    }

    /** {@inheritdoc} */
    public function toDomain(): DomainDwsProvisionReport
    {
        $hasGetMutatorAttrs = [
            'plans',
            'results',
        ];
        return DomainDwsProvisionReport::create($this->only($hasGetMutatorAttrs) + $this->toDomainValues());
    }

    /**
     * Get mutator for plans.
     *
     * @return array
     * @noinspection PhpUnused
     */
    protected function getPlansAttribute(): array
    {
        return $this->mapSortRelation(
            'plans',
            'sort_order',
            fn (DwsProvisionReportItemPlan $x): DomainDwsProvisionReportItem => $x->toDomain()
        );
    }

    /**
     * Get mutator for results.
     *
     * @return array
     * @noinspection PhpUnused
     */
    protected function getResultsAttribute(): array
    {
        return $this->mapSortRelation(
            'results',
            'sort_order',
            fn (DwsProvisionReportItemResult $x): DomainDwsProvisionReportItem => $x->toDomain()
        );
    }
}

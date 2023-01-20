<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\Contract;

use Domain\Common\Carbon;
use Domain\Contract\Contract as DomainContract;
use Domain\Contract\ContractPeriod;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Infrastructure\Billing\CastsLtcsExpiredReason;
use Infrastructure\Common\CastsServiceSegment;
use Infrastructure\Model;
use Lib\Arrays;

/**
 * 契約属性 Eloquent モデル.
 *
 * @property int $id 属性 ID
 * @property int $contract_id 契約 ID
 * @property \Domain\Common\ServiceSegment $service_segment 事業領域
 * @property \Domain\Contract\ContractStatus $contract_status 契約状態
 * @property null|\Domain\Common\Carbon $contracted_on 契約日
 * @property null|\Domain\Common\Carbon $terminated_on 解約日
 * @property \Domain\Contract\ContractPeriod $ltcs_period 介護保険サービス提供期間
 * @property \Domain\Billing\LtcsExpiredReason $expired_reason 介護保険サービス中止理由
 * @property string $note 備考
 * @property bool $is_enabled 有効フラグ
 * @property int $version バージョン
 * @property \Illuminate\Support\Carbon $updated_at 更新日時
 * @property-read \Domain\Contract\ContractPeriod[] $dws_periods 障害福祉サービス提供期間
 * @mixin \Eloquent
 */
final class ContractAttr extends Model
{
    /**
     * テーブル名.
     */
    public const TABLE = 'contract_attr';

    /**
     * ドメインモデル・Eloquent モデル共通の属性.
     */
    private const ATTRIBUTES = [
        'service_segment',
        'status',
        'contracted_on',
        'terminated_on',
        'ltcs_period',
        'expired_reason',
        'note',
        'is_enabled',
        'version',
        'updated_at',
    ];

    /**
     * ドメインモデルに定義されている属性.
     */
    private const DOMAIN_ATTRIBUTES = [
        ...self::ATTRIBUTES,
        'dws_periods',
    ];

    /** {@inheritdoc} */
    protected $table = self::TABLE;

    /** {@inheritdoc} */
    protected $fillable = [
        'id',
        'contract_id',
        ...self::ATTRIBUTES,
    ];

    /** {@inheritdoc} */
    protected $casts = [
        'service_segment' => CastsServiceSegment::class,
        'status' => CastsContractStatus::class,
        'contracted_on' => 'date',
        'terminated_on' => 'date',
        'expired_reason' => CastsLtcsExpiredReason::class,
        'is_enabled' => 'boolean',
        'updated_at' => 'datetime',
    ];

    /**
     * BelongsTo: {@link \Infrastructure\Contract\Contract}.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     * @codeCoverageIgnore リレーションの定義のため
     */
    public function contract(): BelongsTo
    {
        return $this->belongsTo(Contract::class);
    }

    /**
     * HasOne: {@link \Infrastructure\Contract\ContractAttrDwsPeriod}.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function dwsPeriods(): HasMany
    {
        return $this->hasMany(ContractAttrDwsPeriod::class);
    }

    /** {@inheritdoc} */
    public function toDomainValues(): array
    {
        return $this->toDomainAttributes(self::DOMAIN_ATTRIBUTES);
    }

    /**
     * Create an instance from domain model.
     *
     * @param \Domain\Contract\Contract $domain
     * @return \Infrastructure\Contract\ContractAttr
     */
    public static function fromDomain(DomainContract $domain): self
    {
        $attrs = self::getDomainValues($domain, self::ATTRIBUTES);
        return self::newModelInstance($attrs);
    }

    /**
     * Get mutator for ltcs_period attribute.
     *
     * @return \Domain\Contract\ContractPeriod
     * @noinspection PhpUnused
     */
    protected function getLtcsPeriodAttribute(): ContractPeriod
    {
        return ContractPeriod::create([
            'start' => empty($this->attributes['ltcs_period_start'])
                ? null
                : Carbon::parse($this->attributes['ltcs_period_start']),
            'end' => empty($this->attributes['ltcs_period_end'])
                ? null
                : Carbon::parse($this->attributes['ltcs_period_end']),
        ]);
    }

    /**
     * Set mutator for ltcs_period attribute.
     *
     * @param \Domain\Contract\ContractPeriod $value
     * @noinspection PhpUnused
     */
    protected function setLtcsPeriodAttribute(ContractPeriod $value): void
    {
        $this->attributes['ltcs_period_start'] = $value->start;
        $this->attributes['ltcs_period_end'] = $value->end;
    }

    /**
     * Get mutator for dws_periods attribute.
     *
     * @return \Domain\Contract\ContractPeriod[]
     * @noinspection PhpUnused
     */
    protected function getDwsPeriodsAttribute(): array
    {
        /** @var \Illuminate\Database\Eloquent\Collection|\Infrastructure\Contract\ContractAttrDwsPeriod[] $xs */
        $xs = $this->getRelationValue('dwsPeriods');
        return $xs === null
            ? []
            : Arrays::generate(function () use ($xs): iterable {
                foreach ($xs as $x) {
                    yield $x->service_division_code->value() => $x->toDomain();
                }
            });
    }
}

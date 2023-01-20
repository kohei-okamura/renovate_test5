<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\Billing;

use Domain\Billing\CopayCoordinationResult;
use Domain\Billing\DwsBillingOffice;
use Domain\Billing\DwsBillingStatement as DomainStatement;
use Domain\Billing\DwsBillingStatementAggregate as DomainStatementAggregate;
use Domain\Billing\DwsBillingStatementContract as DomainStatementContract;
use Domain\Billing\DwsBillingStatementCopayCoordination;
use Domain\Billing\DwsBillingStatementItem as DomainStatementItem;
use Domain\Common\Addr;
use Domain\Common\Prefecture;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Infrastructure\Domainable;
use Infrastructure\Model;

/**
 * 障害福祉サービス明細書 Eloquent モデル.
 *
 * @property int $id 障害福祉サービス明細書ID
 * @property int $dwsBillingId 障害福祉サービスID
 * @property int $dws_billing_bundle_id 障害福祉サービス請求単位ID
 * @property int $dws_area_grade_id 障害福祉サービス請求：利用者ID
 * @property int $dws_billing_id 障害福祉サービス請求ID
 * @property \Domain\Billing\DwsBillingUser $user 利用者（支給決定者）
 * @property \Domain\Billing\DwsBillingStatementCopayCoordination $copay_coordination 上限管理事業所
 * @property null|string $subsidy_city_code 助成自治体番号
 * @property int $total_score 請求額集計欄：合計：給付単位数
 * @property int $total_fee 請求額集計欄：合計：総費用額
 * @property int $total_capped_copay 請求額集計欄：合計：上限月額調整
 * @property null|int $total_adjusted_copay 請求額集計欄：合計：調整後利用者負担額
 * @property null|int $total_coordinated_copay 請求額集計欄：合計：上限管理後利用者負担額
 * @property int $total_copay 請求額集計欄：合計：決定利用者負担額
 * @property int $total_benefit 請求額集計欄：合計：請求額：給付費
 * @property null|int $total_subsidy 請求額集計欄：合計：自治体助成分請求額
 * @property \Domain\Billing\DwsBillingStatementCopayCoordinationStatus $copay_coordination_status 上限管理区分
 * @property \Domain\Billing\DwsBillingStatus $status 状態
 * @property null|\Domain\Common\Carbon $fixedAt 確定日時
 * @property \Domain\Common\Carbon $created_at 登録日時
 * @property \Domain\Common\Carbon $updated_at 更新日時
 * @property-read \Illuminate\Database\Eloquent\Collection|\Infrastructure\Billing\DwsBillingStatementAggregate[] $aggregates 集計
 * @property-read \Illuminate\Database\Eloquent\Collection|\Infrastructure\Billing\DwsBillingStatementContract[] $contracts 契約
 * @property-read \Illuminate\Database\Eloquent\Collection|\Infrastructure\Billing\DwsBillingStatementItem[] $items 明細
 * @method static \Illuminate\Database\Eloquent\Builder|DwsBillingStatement newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DwsBillingStatement newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DwsBillingStatement query()
 * @method static \Illuminate\Database\Eloquent\Builder|DwsBillingStatement whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DwsBillingStatement whereDwsBillingId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DwsBillingStatement whereDwsBillingBundleId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DwsBillingStatement whereSubsidyCityCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DwsBillingStatement whereDwsBillingUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DwsBillingStatement whereDwsAreaGradeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DwsBillingStatement whereTotalScore($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DwsBillingStatement whereTotalFee($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DwsBillingStatement whereTotalCappedCopay($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DwsBillingStatement whereTotalAdjustedCopay($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DwsBillingStatement whereTotalCoordinatedCopay($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DwsBillingStatement whereTotalCopay($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DwsBillingStatement whereTotalBenefit($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DwsBillingStatement whereIsCopayCoordinated($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DwsBillingStatement whereIsCopayCoordinationRequired($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DwsBillingStatement whereCopayCoordinationId($value)
 */
final class DwsBillingStatement extends Model implements Domainable
{
    use DwsBillingUserHolder;

    /**
     * テーブル名.
     */
    public const TABLE = 'dws_billing_statement';

    /**
     * ドメインモデル・Eloquent モデル共通の属性.
     */
    private const ATTRIBUTES = [
        'id',
        'dws_billing_id',
        'dws_billing_bundle_id',
        'subsidy_city_code',
        'user',
        'dws_area_grade_name',
        'dws_area_grade_code',
        'copay_limit',
        'total_score',
        'total_fee',
        'total_capped_copay',
        'total_adjusted_copay',
        'total_coordinated_copay',
        'total_copay',
        'total_benefit',
        'total_subsidy',
        'is_provided',
        'copay_coordination',
        'copay_coordination_status',
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
        'aggregates',
        'contracts',
        'items',
    ];

    /** {@inheritdoc} */
    protected $table = self::TABLE;

    /** {@inheritdoc} */
    protected $fillable = self::ATTRIBUTES;

    /** {@inheritdoc} */
    protected $casts = [
        'is_provided' => 'boolean',
        'copay_coordination_status' => CastsDwsBillingStatementCopayCoordinationStatus::class,
        'status' => CastsDwsBillingStatus::class,
        'fixed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /** {@inheritdoc} */
    protected $with = [
        'aggregates',
        'items',
        'contracts',
    ];

    /**
     * Create an instance from domain model.
     *
     * @param \Domain\Billing\DwsBillingStatement $domain
     * @return \Infrastructure\Billing\DwsBillingStatement
     */
    public static function fromDomain(DomainStatement $domain): self
    {
        $attrs = self::getDomainValues($domain, self::ATTRIBUTES);
        return self::findOrNew($domain->id, ['id'])->fill($attrs);
    }

    /** {@inheritdoc} */
    public function toDomain(): DomainStatement
    {
        $attrs = $this->toDomainAttributes(self::DOMAIN_ATTRIBUTES);
        return DomainStatement::create($attrs);
    }

    /**
     * HasMany: DwsBillingStatementAggregate.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function aggregates(): HasMany
    {
        return $this->hasMany(DwsBillingStatementAggregate::class)->orderBy('sort_order');
    }

    /**
     * HasMany: DwsBillingStatementContract.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function contracts(): HasMany
    {
        return $this->hasMany(DwsBillingStatementContract::class)->orderBy('index_number');
    }

    /**
     * HasMany: DwsBillingStatementContract.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function items(): HasMany
    {
        return $this->hasMany(DwsBillingStatementItem::class)->orderBy('sort_order');
    }

    /**
     * Get mutator for result attribute.
     *
     * @return null|\Domain\Billing\DwsBillingStatementCopayCoordination
     * @noinspection PhpUnused
     */
    protected function getCopayCoordinationAttribute(): ?DwsBillingStatementCopayCoordination
    {
        if (!isset($this->attributes['office_id'])) {
            return null;
        }
        return DwsBillingStatementCopayCoordination::create([
            'office' => DwsBillingOffice::create([
                'officeId' => $this->attributes['office_id'],
                'code' => $this->attributes['office_code'],
                'name' => $this->attributes['office_name'],
                'abbr' => $this->attributes['office_abbr'],
                'addr' => new Addr(
                    postcode: $this->attributes['office_addr_postcode'],
                    prefecture: Prefecture::from($this->attributes['office_addr_prefecture']),
                    city: $this->attributes['office_addr_city'],
                    street: $this->attributes['office_addr_street'],
                    apartment: $this->attributes['office_addr_apartment'],
                ),
                'tel' => $this->attributes['office_tel'],
            ]),
            'result' => CopayCoordinationResult::from($this->attributes['result']),
            'amount' => $this->attributes['amount'],
        ]);
    }

    /**
     * Set mutator for result attribute.
     *
     * @param null|\Domain\Billing\DwsBillingStatementCopayCoordination $copayCoordination
     * @noinspection PhpUnused
     */
    protected function setCopayCoordinationAttribute(?DwsBillingStatementCopayCoordination $copayCoordination): void
    {
        if ($copayCoordination !== null) {
            $this->attributes['office_id'] = $copayCoordination->office->officeId;
            $this->attributes['office_code'] = $copayCoordination->office->code;
            $this->attributes['office_name'] = $copayCoordination->office->name;
            $this->attributes['office_abbr'] = $copayCoordination->office->abbr;
            $this->attributes['office_addr_postcode'] = $copayCoordination->office->addr->postcode;
            $this->attributes['office_addr_prefecture'] = $copayCoordination->office->addr->prefecture->value();
            $this->attributes['office_addr_city'] = $copayCoordination->office->addr->city;
            $this->attributes['office_addr_street'] = $copayCoordination->office->addr->street;
            $this->attributes['office_addr_apartment'] = $copayCoordination->office->addr->apartment;
            $this->attributes['office_tel'] = $copayCoordination->office->tel;
            $this->attributes['result'] = $copayCoordination->result->value();
            $this->attributes['amount'] = $copayCoordination->amount;
        } else {
            $this->attributes['office_id'] = null;
            $this->attributes['office_code'] = '';
            $this->attributes['office_name'] = '';
            $this->attributes['office_abbr'] = '';
            $this->attributes['office_addr_postcode'] = '';
            $this->attributes['office_addr_prefecture'] = null;
            $this->attributes['office_addr_city'] = '';
            $this->attributes['office_addr_street'] = '';
            $this->attributes['office_addr_apartment'] = '';
            $this->attributes['office_tel'] = '';
            $this->attributes['result'] = null;
            $this->attributes['amount'] = 0;
        }
    }

    /**
     * Get mutator for aggregates attribute.
     *
     * @return array
     * @noinspection PhpUnused
     */
    protected function getAggregatesAttribute(): array
    {
        return $this->mapRelation(
            'aggregates',
            fn (DwsBillingStatementAggregate $x): DomainStatementAggregate => $x->toDomain()
        );
    }

    /**
     * Get mutator for contracts attribute.
     *
     * @return array
     * @noinspection PhpUnused
     */
    protected function getContractsAttribute(): array
    {
        return $this->mapRelation(
            'contracts',
            fn (DwsBillingStatementContract $x): DomainStatementContract => $x->toDomain()
        );
    }

    /**
     * Get mutator for items attribute.
     *
     * @return array
     * @noinspection PhpUnused
     */
    protected function getItemsAttribute(): array
    {
        return $this->mapRelation(
            'items',
            fn (DwsBillingStatementItem $x): DomainStatementItem => $x->toDomain()
        );
    }
}

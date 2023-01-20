<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\Billing;

use Domain\Billing\LtcsBillingStatementItem as DomainBillingStatementItem;
use Domain\Billing\LtcsBillingStatementItemSubsidy as DomainBillingStatementItemSubsidy;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Infrastructure\Domainable;
use Infrastructure\Model;
use Infrastructure\ServiceCode\ServiceCodeHolder;
use Infrastructure\ServiceCodeDictionary\CastsLtcsServiceCodeCategory;

/**
 * 介護保険サービス：明細書：明細 Eloquent モデル.
 *
 * @property int $id 明細 ID
 * @property int $statement_id 明細書 ID
 * @property string $service_code サービスコード
 * @property \Domain\ServiceCodeDictionary\LtcsServiceCodeCategory $service_code_category サービスコード区分
 * @property int $unit_score 単位数
 * @property int $count 日数・回数
 * @property int $total_score サービス単位数
 * @property string $note 摘要
 * @property int $sort_order 並び順
 * @property-read \Domain\Billing\LtcsBillingStatementItemSubsidy[] $subsidies 公費
 * @method static \Illuminate\Database\Eloquent\Builder|static whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|static whereStatementId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|static whereUnitScore($value)
 * @method static \Illuminate\Database\Eloquent\Builder|static whereCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|static whereTotalScore($value)
 * @method static \Illuminate\Database\Eloquent\Builder|static whereNote($value)
 */
final class LtcsBillingStatementItem extends Model implements Domainable
{
    use ServiceCodeHolder;

    /**
     * テーブル名.
     */
    public const TABLE = 'ltcs_billing_statement_item';

    /**
     * ドメインモデル・Eloquent モデル共通の属性.
     */
    private const ATTRIBUTES = [
        'service_code',
        'service_code_category',
        'unit_score',
        'count',
        'total_score',
        'note',
    ];

    /**
     * ドメインモデルに定義されている属性.
     */
    private const DOMAIN_ATTRIBUTES = [
        ...self::ATTRIBUTES,
        'subsidies',
    ];

    /** {@inheritdoc} */
    protected $table = self::TABLE;

    /** {@inheritdoc} */
    protected $fillable = [
        'id',
        'statement_id',
        ...self::ATTRIBUTES,
        'sort_order',
    ];

    /** {@inheritdoc} */
    protected $with = [
        'subsidies',
    ];

    /** {@inheritdoc} */
    protected $casts = [
        'service_code_category' => CastsLtcsServiceCodeCategory::class,
    ];

    /**
     * ドメインモデルからインスタンスを生成する.
     *
     * @param \Domain\Billing\LtcsBillingStatementItem $domain
     * @param int $statementId
     * @param int $sortOrder
     * @return static
     */
    public static function fromDomain(DomainBillingStatementItem $domain, int $statementId, int $sortOrder): self
    {
        $keys = [
            'statement_id' => $statementId,
            'sort_order' => $sortOrder,
        ];
        $attrs = self::getDomainValues($domain, self::ATTRIBUTES);
        return self::firstOrNew($keys, $attrs)->fill($attrs);
    }

    /** {@inheritdoc} */
    public function toDomain(): DomainBillingStatementItem
    {
        $attrs = $this->toDomainAttributes(self::DOMAIN_ATTRIBUTES);
        return DomainBillingStatementItem::fromAssoc($attrs);
    }

    /**
     * HasMany: {@link \Infrastructure\Billing\LtcsBillingStatementItemSubsidy}
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function subsidies(): HasMany
    {
        return $this
            ->hasMany(LtcsBillingStatementItemSubsidy::class, 'statement_item_id')
            ->orderBy('sort_order');
    }

    /**
     * Get mutator for subsidies attribute.
     *
     * @return array|\Domain\Billing\LtcsBillingStatementItemSubsidy[]
     * @noinspection PhpUnused
     */
    protected function getSubsidiesAttribute(): array
    {
        return $this->mapRelation(
            'subsidies',
            fn (LtcsBillingStatementItemSubsidy $x): DomainBillingStatementItemSubsidy => $x->toDomain()
        );
    }
}

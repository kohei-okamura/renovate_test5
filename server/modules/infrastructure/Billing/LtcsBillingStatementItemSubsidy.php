<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\Billing;

use Domain\Billing\LtcsBillingStatementItemSubsidy as DomainBillingStatementItemSubsidy;
use Infrastructure\Domainable;
use Infrastructure\Model;

/**
 * 介護保険サービス：明細書：明細：公費 Eloquent モデル.
 *
 * @property int $id 公費 ID
 * @property int $statement_item_id 明細 ID
 * @property int $count 日数・回数
 * @property int $total_score サービス単位数
 * @property int $sort_order 並び順
 * @method static \Illuminate\Database\Eloquent\Builder|static whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|static whereCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|static whereTotalScore($value)
 */
final class LtcsBillingStatementItemSubsidy extends Model implements Domainable
{
    /**
     * テーブル名.
     */
    public const TABLE = 'ltcs_billing_statement_item_subsidy';

    /**
     * ドメインモデル・Eloquent モデル共通の属性.
     */
    private const ATTRIBUTES = [
        'count',
        'total_score',
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
        'statement_item_id',
        ...self::ATTRIBUTES,
        'sort_order',
    ];

    /**
     * ドメインモデルからインスタンスを生成する.
     *
     * @param \Domain\Billing\LtcsBillingStatementItemSubsidy $domain
     * @param int $itemId
     * @param int $sortOrder
     * @return static
     */
    public static function fromDomain(DomainBillingStatementItemSubsidy $domain, int $itemId, int $sortOrder): self
    {
        $keys = [
            'statement_item_id' => $itemId,
            'sort_order' => $sortOrder,
        ];
        $attrs = self::getDomainValues($domain, self::ATTRIBUTES);
        return self::firstOrNew($keys, $attrs)->fill($attrs);
    }

    /** {@inheritdoc} */
    public function toDomain(): DomainBillingStatementItemSubsidy
    {
        $attrs = $this->toDomainAttributes(self::DOMAIN_ATTRIBUTES);
        return DomainBillingStatementItemSubsidy::fromAssoc($attrs);
    }
}

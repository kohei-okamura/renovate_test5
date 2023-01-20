<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\OwnExpenseProgram;

use Domain\Common\Expense;
use Domain\Common\TaxCategory;
use Domain\Common\TaxType;
use Domain\OwnExpenseProgram\OwnExpenseProgram as DomainOwnExpenseProgram;
use Infrastructure\Model;

/**
 * 自費サービス情報属性 Eloquent モデル.
 *
 * @property int $id 自費サービス情報属性ID
 * @property string $name 名称
 * @property int $duration_minutes 単位時間数
 * @property string $note 備考
 * @property bool $is_enabled 有効フラグ
 * @property int $version バージョン
 * @property \Domain\Common\Carbon $updated_at 更新日時
 * @property-read \Domain\Common\Expense $fee 費用
 *
 * @mixin \Eloquent
 */
final class OwnExpenseProgramAttr extends Model
{
    /**
     * テーブル名.
     */
    public const TABLE = 'own_expense_program_attr';

    /** {@inheritdoc} */
    protected $table = self::TABLE;

    /** {@inheritdoc} */
    protected $fillable = [
        'id',
        'name',
        'duration_minutes',
        'fee',
        'note',
        'is_enabled',
        'version',
        'updated_at',
    ];

    /** {@inheritdoc} */
    protected $casts = [
        'is_enabled' => 'boolean',
        'updated_at' => 'datetime',
    ];

    /** {@inheritdoc} */
    public function toDomainValues(): array
    {
        $hasGetMutatorAttrs = [
            'fee',
        ];
        return $this->only($hasGetMutatorAttrs) + parent::toDomainValues();
    }

    /**
     * Create an instance from domain model.
     *
     * @param \Domain\OwnExpenseProgram\OwnExpenseProgram $domain
     * @return \Infrastructure\OwnExpenseProgram\OwnExpenseProgramAttr
     */
    public static function fromDomain(DomainOwnExpenseProgram $domain): self
    {
        $keys = [
            'name',
            'duration_minutes',
            'fee',
            'note',
            'is_enabled',
            'version',
            'updated_at',
        ];
        $attrs = self::getDomainValues($domain, $keys);
        return self::newModelInstance($attrs);
    }

    /**
     * Get mutator for fee attribute.
     *
     * @return \Domain\Common\Expense
     * @noinspection PhpUnused
     */
    protected function getFeeAttribute(): Expense
    {
        return Expense::create([
            'taxExcluded' => $this->fee_tax_excluded,
            'taxIncluded' => $this->fee_tax_included,
            'taxType' => TaxType::from($this->fee_tax_type),
            'taxCategory' => TaxCategory::from($this->fee_tax_category),
        ]);
    }

    /**
     * Set mutator for fee attribute.
     *
     * @param \Domain\Common\Expense $fee
     * @return void
     * @noinspection PhpUnused
     */
    protected function setFeeAttribute(Expense $fee): void
    {
        $this->attributes['fee_tax_excluded'] = $fee->taxExcluded;
        $this->attributes['fee_tax_included'] = $fee->taxIncluded;
        $this->attributes['fee_tax_type'] = $fee->taxType->value();
        $this->attributes['fee_tax_category'] = $fee->taxCategory->value();
    }
}

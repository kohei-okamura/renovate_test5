<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\OwnExpenseProgram;

use Domain\OwnExpenseProgram\OwnExpenseProgram as DomainOwnExpenseProgram;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Infrastructure\Domainable;
use Infrastructure\Model;
use Infrastructure\Organization\BelongsToOrganization;

/**
 * 自費サービス情報 Eloquent モデル.
 *
 * @property int $id 自費サービス情報ID
 * @property int $organization_id 事業者ID
 * @property null|int $office_id 事業所ID
 * @property \Domain\Common\Carbon $created_at 登録日時
 * @property-read \Infrastructure\OwnExpenseProgram\OwnExpenseProgramAttr $attr
 * @mixin \Eloquent
 */
final class OwnExpenseProgram extends Model implements Domainable
{
    use BelongsToOrganization;

    /**
     * テーブル名.
     */
    public const TABLE = 'own_expense_program';

    /** {@inheritdoc} */
    protected $table = self::TABLE;

    /** {@inheritdoc} */
    protected $fillable = [
        'id',
        'organization_id',
        'office_id',
        'created_at',
    ];

    /** {@inheritdoc} */
    protected $casts = [
        'created_at' => 'datetime',
    ];

    /** {@inheritdoc} */
    protected $with = ['attr'];

    /**
     * HasOne: {@link \Infrastructure\Office\OfficeAttr}.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function attr(): HasOne
    {
        return $this->hasAttribute(OwnExpenseProgramAttr::class);
    }

    /** {@inheritdoc} */
    public function toDomain(): DomainOwnExpenseProgram
    {
        return DomainOwnExpenseProgram::create($this->toDomainValues() + $this->attr->toDomainValues());
    }

    /**
     * Create an instance from domain model.
     *
     * @param \Domain\OwnExpenseProgram\OwnExpenseProgram $domain
     * @return \Infrastructure\OwnExpenseProgram\OwnExpenseProgram
     */
    public static function fromDomain(DomainOwnExpenseProgram $domain): self
    {
        $keys = [
            'id',
            'organization_id',
            'office_id',
            'created_at',
        ];
        $values = self::getDomainValues($domain, $keys);
        return self::findOrNew($domain->id, ['id'])->fill($values);
    }
}

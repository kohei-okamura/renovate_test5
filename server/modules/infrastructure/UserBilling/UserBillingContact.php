<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\UserBilling;

use Domain\Common\Contact as DomainContact;
use Infrastructure\Common\CastsContactRelationship;
use Infrastructure\Domainable;
use Infrastructure\Model;

/**
 * 利用者請求：利用者：連絡先電話番号 Eloquent モデル.
 *
 * @property int $id 利用者請求：利用者：連絡先電話番号 ID
 * @property int $user_billing_id 利用者請求 ID
 * @property int $sort_order 表示順
 * @property string $tel 電話番号
 * @property \Domain\Common\ContactRelationship $relationship 続柄・関係
 * @property string $name 名前
 */
final class UserBillingContact extends Model implements Domainable
{
    /**
     * テーブル名.
     */
    public const TABLE = 'user_billing_contact';

    /** {@inheritdoc} */
    protected $table = self::TABLE;

    /** {@inheritdoc} */
    protected $fillable = [
        'id',
        'user_billing_id',
        'sort_order',
        'tel',
        'relationship',
        'name',
    ];

    /** {@inheritdoc} */
    protected $casts = [
        'relationship' => CastsContactRelationship::class,
    ];

    /**
     * Create an instance from domain model.
     *
     * @param \Domain\Common\Contact $domain
     * @param array $additional
     * @return \Infrastructure\UserBilling\UserBillingContact
     */
    public static function fromDomain(DomainContact $domain, array $additional): self
    {
        $keys = [
            'tel',
            'relationship',
            'name',
        ];
        $values = self::getDomainValues($domain, $keys);
        return self::firstOrNew($additional, $additional + $values)->fill($additional + $values);
    }

    /** {@inheritdoc} */
    public function toDomain(): DomainContact
    {
        $hasGetMutatorAttrs = [
            'tel',
            'relationship',
            'name',
        ];
        return DomainContact::create($this->only($hasGetMutatorAttrs) + $this->toDomainValues());
    }
}

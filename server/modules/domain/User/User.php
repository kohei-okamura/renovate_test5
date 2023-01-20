<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\User;

use Domain\Entity;
use Domain\Versionable;

/**
 * 利用者.
 *
 * @property-read int $organizationId 組織ID
 * @property-read \Domain\Common\StructuredName $name 氏名
 * @property-read \Domain\Common\Sex $sex 性別
 * @property-read \Domain\Common\Carbon $birthday 生年月日
 * @property-read \Domain\Common\Addr $addr 住所
 * @property-read \Domain\Common\Location $location 位置情報
 * @property-read \Domain\Common\Contact[] $contacts 連絡先電話番号
 * @property-read int $bankAccountId 銀行口座ID
 * @property-read \Domain\User\UserBillingDestination $billingDestination 請求先情報
 * @property-read bool $isEnabled 有効フラグ
 * @property-read int $version バージョン
 * @property-read \Domain\Common\Carbon $createdAt 登録日時
 * @property-read \Domain\Common\Carbon $updatedAt 更新日時
 */
final class User extends Entity
{
    use Versionable;

    /** {@inheritdoc} */
    protected function attrs(): array
    {
        return [
            ...parent::attrs(),
            'organizationId',
            'name',
            'sex',
            'birthday',
            'addr',
            'location',
            'contacts',
            'bankAccountId',
            'billingDestination',
            'isEnabled',
            'version',
            'createdAt',
            'updatedAt',
        ];
    }

    /** {@inheritdoc} */
    protected function jsonables(): array
    {
        return [
            'id' => true,
            'organizationId' => false,
            'name' => true,
            'sex' => true,
            'birthday' => true,
            'addr' => true,
            'location' => true,
            'contacts' => true,
            'bankAccountId' => true,
            'billingDestination' => true,
            'isEnabled' => true,
            'version' => false,
            'createdAt' => true,
            'updatedAt' => true,
        ];
    }
}

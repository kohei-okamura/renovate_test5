<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\Organization;

use Domain\Entity;
use Domain\Versionable;

/**
 * 事業者.
 *
 * @property-read string $code 事業者コード
 * @property-read string $name 事業者名
 * @property-read \Domain\Common\Addr $addr 住所
 * @property-read string $tel 電話番号
 * @property-read string $fax FAX番号
 * @property-read bool $isEnabled 有効フラグ
 * @property-read int $version バージョン
 * @property-read \Domain\Common\Carbon $createdAt 登録日時
 * @property-read \Domain\Common\Carbon $updatedAt 更新日時
 */
final class Organization extends Entity
{
    use Versionable;

    /** {@inheritdoc} */
    protected function attrs(): array
    {
        return [
            ...parent::attrs(),
            'code',
            'name',
            'addr',
            'tel',
            'fax',
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
            'id' => false,
            'code' => false,
            'name' => false,
            'addr' => false,
            'tel' => false,
            'fax' => false,
            'isEnabled' => false,
            'version' => false,
            'createdAt' => false,
            'updatedAt' => false,
        ];
    }
}

<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\Common;

use Domain\Model;

/**
 * 連絡先電話番号.
 *
 * @property-read string $tel 電話番号
 * @property-read \Domain\Common\ContactRelationship $relationship 続柄・関係
 * @property-read string $name 名前
 */
final class Contact extends Model
{
    /** {@inheritdoc} */
    protected function attrs(): array
    {
        return [
            'tel',
            'relationship',
            'name',
        ];
    }

    /** {@inheritdoc} */
    protected function jsonables(): array
    {
        return [
            'tel' => true,
            'relationship' => true,
            'name' => true,
        ];
    }
}

<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\UserBilling;

use Domain\Model;
use Domain\Office\Office;

/**
 * 利用者請求：事業所.
 *
 * @property-read string $name 事業所名
 * @property-read string $corporationName 法人名
 * @property-read \Domain\Common\Addr $addr 住所
 * @property-read string $tel 電話番号
 */
final class UserBillingOffice extends Model
{
    /**
     * 利用者請求：事業所 ドメインモデルを作成する.
     *
     * @param \Domain\Office\Office $office
     * @return static
     */
    public static function from(Office $office): self
    {
        return self::create([
            'name' => $office->name,
            'corporationName' => $office->corporationName,
            'addr' => $office->addr,
            'tel' => $office->tel,
        ]);
    }

    /** {@inheritdoc} */
    protected function attrs(): array
    {
        return [
            'name',
            'corporationName',
            'addr',
            'tel',
        ];
    }

    /** {@inheritdoc} */
    protected function jsonables(): array
    {
        return [
            'name' => true,
            'corporationName' => true,
            'addr' => true,
            'tel' => true,
        ];
    }
}

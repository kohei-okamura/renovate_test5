<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\Billing;

use Domain\Model;
use Domain\Office\Office;

/**
 * 障害福祉サービス請求：事業所.
 *
 * @property-read int $officeId 事業所ID
 * @property-read string $code 事業所番号
 * @property-read string $name 事業所名
 * @property-read string $abbr 事業所名（略称）
 * @property-read \Domain\Common\Addr $addr 所在地
 * @property-read string $tel 電話番号
 */
final class DwsBillingOffice extends Model
{
    /**
     * 事業所からインスタンスを生成する.
     *
     * @param \Domain\Office\Office $office
     * @return static
     */
    public static function from(Office $office): self
    {
        return self::create([
            'officeId' => $office->id,
            'code' => $office->dwsGenericService->code,
            'name' => $office->name,
            'abbr' => $office->abbr,
            'addr' => $office->addr,
            'tel' => $office->tel,
        ]);
    }

    /** {@inheritdoc} */
    protected function attrs(): array
    {
        return [
            'officeId',
            'code',
            'name',
            'abbr',
            'addr',
            'tel',
        ];
    }

    /** {@inheritdoc} */
    protected function jsonables(): array
    {
        return [
            'officeId' => true,
            'code' => true,
            'name' => true,
            'abbr' => true,
            'addr' => true,
            'tel' => true,
        ];
    }
}

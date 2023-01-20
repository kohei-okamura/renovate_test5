<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\DwsAreaGrade;

use Domain\Entity;

/**
 * 障害福祉サービス：地域区分.
 *
 * @property-read string $code 障害地域区分コード
 * @property-read string $name 障害地域区分名
 */
final class DwsAreaGrade extends Entity
{
    /** {@inheritdoc} */
    protected function attrs(): array
    {
        return [
            ...parent::attrs(),
            'code',
            'name',
        ];
    }

    /** {@inheritdoc} */
    protected function jsonables(): array
    {
        return [
            'id' => true,
            'code' => true,
            'name' => true,
        ];
    }
}

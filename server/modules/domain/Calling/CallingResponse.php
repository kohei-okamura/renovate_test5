<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\Calling;

use Domain\Entity;

/**
 * 出勤確認応答.
 *
 * @property-read int $callingId 出勤確認ID
 * @property-read \Domain\Common\Carbon $createdAt 登録日時
 */
final class CallingResponse extends Entity
{
    /** {@inheritdoc} */
    protected function attrs(): array
    {
        return [
            ...parent::attrs(),
            'callingId',
            'createdAt',
        ];
    }

    /** {@inheritdoc} */
    protected function jsonables(): array
    {
        return [
            'id' => true,
            'callingId' => false,
            'createdAt' => true,
        ];
    }
}

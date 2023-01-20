<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\Calling;

use Domain\Entity;

/**
 * 出勤確認送信履歴.
 *
 * @property-read int $id id
 * @property-read int $callingId 出勤確認ID
 * @property-read \Domain\Calling\CallingType $callingType 送信タイプ
 * @property-read bool $isSucceeded 送信成功フラグ
 * @property-read \Domain\Common\Carbon $createdAt 登録日時
 */
final class CallingLog extends Entity
{
    /** {@inheritdoc} */
    protected function attrs(): array
    {
        return [
            ...parent::attrs(),
            'callingId',
            'callingType',
            'isSucceeded',
            'createdAt',
        ];
    }

    /** {@inheritdoc} */
    protected function jsonables(): array
    {
        return [
            'id' => true,
            'callingId' => true,
            'callingType' => true,
            'isSucceeded' => true,
            'createdAt' => true,
        ];
    }
}

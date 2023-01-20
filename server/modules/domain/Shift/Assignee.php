<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\Shift;

use Domain\Model;

/**
 * 勤務スタッフ.
 *
 * @property-read null|int $staffId スタッフID
 * @property-read bool $isUndecided 未定フラグ
 * @property-read bool $isTraining 研修フラグ
 */
final class Assignee extends Model
{
    /**
     * Return name of attrs.
     *
     * @return array|string[]
     */
    protected function attrs(): array
    {
        return [
            'staffId',
            'isUndecided',
            'isTraining',
        ];
    }

    /** {@inheritdoc} */
    protected function jsonables(): array
    {
        return [
            'staffId' => true,
            'isUndecided' => true,
            'isTraining' => true,
        ];
    }
}

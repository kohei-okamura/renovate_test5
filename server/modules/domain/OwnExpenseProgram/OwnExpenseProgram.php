<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\OwnExpenseProgram;

use Domain\Entity;
use Domain\Versionable;

/**
 * 自費サービス情報.
 *
 * @property-read int $organizationId 事業者ID
 * @property-read null|int $officeId 事業所ID
 * @property-read string $name 名称
 * @property-read int $durationMinutes 単位時間数
 * @property-read \Domain\Common\Expense $fee 費用
 * @property-read string $note 備考
 * @property-read bool $isEnabled 有効フラグ
 * @property-read int $version バージョン
 * @property-read \Domain\Common\Carbon $createdAt 登録日時
 * @property-read \Domain\Common\Carbon $updatedAt 更新日時
 */
final class OwnExpenseProgram extends Entity
{
    use Versionable;

    /**
     * 全事業所向けの自費サービスかを返す.
     *
     * @return bool
     */
    public function isForAllOffices(): bool
    {
        return is_null($this->officeId);
    }

    /** {@inheritdoc} */
    protected function attrs(): array
    {
        return [
            ...parent::attrs(),
            'organizationId',
            'officeId',
            'name',
            'durationMinutes',
            'fee',
            'note',
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
            'officeId' => true,
            'name' => true,
            'durationMinutes' => true,
            'fee' => true,
            'note' => true,
            'isEnabled' => false,
            'version' => false,
            'createdAt' => true,
            'updatedAt' => true,
        ];
    }
}

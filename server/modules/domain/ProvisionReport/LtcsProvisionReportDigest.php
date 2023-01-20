<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\ProvisionReport;

use Domain\Model;

/**
 * 介護保険サービス：予実：概要.
 *
 * @property-read int $userId 利用者ID
 * @property-read \Domain\Common\StructuredName $name 利用者氏名
 * @property-read string $insNumber 被保険者番号
 * @property-read bool $isEnabled 利用者の状態
 * @property-read \Domain\ProvisionReport\LtcsProvisionReportStatus $status 予実の状態
 */
final class LtcsProvisionReportDigest extends Model
{
    protected function attrs(): array
    {
        return [
            'userId',
            'name',
            'insNumber',
            'isEnabled',
            'status',
        ];
    }

    /** {@inheritdoc} */
    protected function jsonables(): array
    {
        return [
            'userId' => true,
            'name' => true,
            'insNumber' => true,
            'isEnabled' => true,
            'status' => true,
        ];
    }
}

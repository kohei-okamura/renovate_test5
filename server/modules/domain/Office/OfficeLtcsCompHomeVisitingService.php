<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\Office;

use Domain\Model;

/**
 * 事業所：介護保険サービス：訪問型サービス（総合事業）.
 *
 * @property-read string $code 事業所番号
 * @property-read null|\Domain\Common\Carbon $openedOn 開設日
 * @property-read null|\Domain\Common\Carbon $designationExpiredOn 指定更新期日
 */
final class OfficeLtcsCompHomeVisitingService extends Model
{
    /** {@inheritdoc} */
    protected function attrs(): array
    {
        return [
            'code',
            'openedOn',
            'designationExpiredOn',
        ];
    }

    /** {@inheritdoc} */
    protected function jsonables(): array
    {
        return [
            'code' => true,
            'openedOn' => true,
            'designationExpiredOn' => true,
        ];
    }
}

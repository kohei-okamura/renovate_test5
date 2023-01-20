<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\Office;

use Domain\Model;

/**
 * 事業所：障害福祉サービス.
 *
 * @property-read string $code 事業所番号
 * @property-read null|\Domain\Common\Carbon $openedOn 開設日
 * @property-read null|\Domain\Common\Carbon $designationExpiredOn 指定更新期日
 * @property-read null|int $dwsAreaGradeId 障害地域区分ID
 */
final class OfficeDwsGenericService extends Model
{
    /** {@inheritdoc} */
    protected function attrs(): array
    {
        return [
            'code',
            'openedOn',
            'designationExpiredOn',
            'dwsAreaGradeId',
        ];
    }

    /** {@inheritdoc} */
    protected function jsonables(): array
    {
        return [
            'code' => true,
            'openedOn' => true,
            'designationExpiredOn' => true,
            'dwsAreaGradeId' => true,
        ];
    }
}

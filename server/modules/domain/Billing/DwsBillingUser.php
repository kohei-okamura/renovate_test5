<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\Billing;

use Domain\DwsCertification\DwsCertification;
use Domain\Model;
use Domain\User\User;

/**
 * 障害福祉サービス請求：利用者.
 *
 * @property-read int $userId 利用者ID
 * @property-read int $dwsCertificationId 障害福祉サービス受給者証ID
 * @property-read string $dwsNumber 受給者番号
 * @property-read \Domain\Common\StructuredName $name 氏名
 * @property-read \Domain\Common\StructuredName $childName 児童氏名
 * @property-read int $copayLimit 利用者負担上限月額
 */
final class DwsBillingUser extends Model
{
    /**
     * 利用者モデル＆障害福祉サービス受給者証モデルからインスタンスを生成する.
     *
     * @param \Domain\User\User $user
     * @param \Domain\DwsCertification\DwsCertification $certification
     * @return static
     */
    public static function from(User $user, DwsCertification $certification): self
    {
        return self::create([
            'userId' => $user->id,
            'dwsCertificationId' => $certification->id,
            'dwsNumber' => $certification->dwsNumber,
            'name' => $user->name,
            'childName' => $certification->child->name,
            'copayLimit' => $certification->copayLimit,
        ]);
    }

    /** {@inheritdoc} */
    protected function attrs(): array
    {
        return [
            'userId',
            'dwsCertificationId',
            'dwsNumber',
            'name',
            'childName',
            'copayLimit',
        ];
    }

    /** {@inheritdoc} */
    protected function jsonables(): array
    {
        return [
            'userId' => true,
            'dwsCertificationId' => true,
            'dwsNumber' => true,
            'name' => true,
            'childName' => true,
            'copayLimit' => true,
        ];
    }
}

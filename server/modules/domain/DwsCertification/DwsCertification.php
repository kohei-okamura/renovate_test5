<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\DwsCertification;

use Domain\Entity;
use Domain\Versionable;

/**
 * 障害福祉サービス受給者証.
 *
 * @property-read int userId 利用者ID
 * @property-read \Domain\Common\Carbon $effectivatedOn 適用日
 * @property-read \Domain\DwsCertification\DwsCertificationStatus $status 障害福祉サービス認定区分
 * @property-read string $dwsNumber 受給者証番号
 * @property-read array|\Domain\DwsCertification\DwsType[] $dwsTypes 障害種別
 * @property-read \Domain\Common\Carbon $issuedOn 交付日
 * @property-read string $cityName 市区町村名
 * @property-read string $cityCode 市区町村番号
 * @property-read \Domain\DwsCertification\DwsLevel $dwsLevel 障害程度区分
 * @property-read bool $isSubjectOfComprehensiveSupport 重度障害者等包括支援対象フラグ
 * @property-read \Domain\Common\Carbon $activatedOn 認定の有効期間（開始）
 * @property-read \Domain\Common\Carbon $deactivatedOn 認定の有効期間（終了）
 * @property-read array|\Domain\DwsCertification\DwsCertificationGrant[] $grants 支給量
 * @property-read \Domain\DwsCertification\Child $child 児童情報
 * @property-read int $copayRate 利用者負担割合（原則）
 * @property-read int $copayLimit 負担上限月額
 * @property-read \Domain\Common\Carbon $copayActivatedOn 利用者負担適用期間（開始）
 * @property-read \Domain\Common\Carbon $copayDeactivatedOn 利用者負担適用期間（終了）
 * @property-read \Domain\DwsCertification\CopayCoordination $copayCoordination 上限管理情報
 * @property-read array|\Domain\DwsCertification\DwsCertificationAgreement[] $agreements 訪問系サービス事業者記入欄
 * @property-read bool $isEnabled 有効フラグ
 * @property-read int $version バージョン
 * @property-read \Domain\Common\Carbon $createdAt 登録日時
 * @property-read \Domain\Common\Carbon $updatedAt 更新日時
 */
final class DwsCertification extends Entity
{
    use Versionable;

    /** {@inheritdoc} */
    protected function attrs(): array
    {
        return [
            ...parent::attrs(),
            'userId',
            'effectivatedOn',
            'status',
            'dwsNumber',
            'dwsTypes',
            'issuedOn',
            'cityName',
            'cityCode',
            'dwsLevel',
            'isSubjectOfComprehensiveSupport',
            'activatedOn',
            'deactivatedOn',
            'grants',
            'child',
            'copayRate',
            'copayLimit',
            'copayActivatedOn',
            'copayDeactivatedOn',
            'copayCoordination',
            'agreements',
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
            'userId' => true,
            'effectivatedOn' => true,
            'status' => true,
            'dwsNumber' => true,
            'dwsTypes' => true,
            'issuedOn' => true,
            'cityName' => true,
            'cityCode' => true,
            'dwsLevel' => true,
            'isSubjectOfComprehensiveSupport' => true,
            'activatedOn' => true,
            'deactivatedOn' => true,
            'grants' => true,
            'child' => true,
            'copayRate' => true,
            'copayLimit' => true,
            'copayActivatedOn' => true,
            'copayDeactivatedOn' => true,
            'copayCoordination' => true,
            'agreements' => true,
            'isEnabled' => false,
            'version' => false,
            'createdAt' => true,
            'updatedAt' => true,
        ];
    }
}

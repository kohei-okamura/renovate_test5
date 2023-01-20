<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\Office;

use Domain\Entity;
use Domain\Versionable;

/**
 * 事業所.
 *
 * @property-read int $organizationId 事業者ID
 * @property-read string $name 事業所名
 * @property-read string $abbr 事業所名：略称
 * @property-read string $phoneticName 事業所名：フリガナ
 * @property-read string $corporationName 法人名
 * @property-read string $phoneticCorporationName 法人名：フリガナ
 * @property-read \Domain\Office\Purpose $purpose 事業者区分
 * @property-read \Domain\Common\Addr $addr 住所
 * @property-read \Domain\Common\Location $location 位置情報
 * @property-read string $tel 電話番号
 * @property-read string $fax FAX番号
 * @property-read string $email メールアドレス
 * @property-read \Domain\Office\OfficeQualification[] $qualifications 指定区分
 * @property-read null|int $officeGroupId 事業所グループID
 * @property-read null|\Domain\Office\OfficeDwsGenericService $dwsGenericService 事業所：障害福祉サービス
 * @property-read null|\Domain\Office\OfficeDwsCommAccompanyService $dwsCommAccompanyService 事業所：障害福祉サービス（地域生活支援事業・移動支援）
 * @property-read null|\domain\Office\OfficeLtcsCareManagementService $ltcsCareManagementService 事業所：介護保険サービス：居宅介護支援
 * @property-read null|\Domain\Office\OfficeLtcsHomeVisitLongTermCareService $ltcsHomeVisitLongTermCareService 事業所：介護保険サービス：訪問介護
 * @property-read null|\Domain\Office\OfficeLtcsCompHomeVisitingService $ltcsCompHomeVisitingService 事業所：介護保険サービス：訪問型サービス（総合事業）
 * @property-read null|\domain\Office\OfficeLtcsPreventionService $ltcsPreventionService 事業所：介護保険サービス：介護予防支援
 * @property-read \Domain\Office\OfficeStatus $status 状態
 * @property-read int $isEnabled 有効フラグ
 * @property-read int $version バージョン
 * @property-read \Domain\Common\Carbon $createdAt 登録日時
 * @property-read \Domain\Common\Carbon $updatedAt 更新日時
 */
final class Office extends Entity
{
    use Versionable;

    /** {@inheritdoc} */
    protected function attrs(): array
    {
        return [
            ...parent::attrs(),
            'organizationId',
            'name',
            'abbr',
            'phoneticName',
            'corporationName',
            'phoneticCorporationName',
            'purpose',
            'addr',
            'location',
            'tel',
            'fax',
            'email',
            'qualifications',
            'officeGroupId',
            'dwsGenericService',
            'dwsCommAccompanyService',
            'ltcsCareManagementService',
            'ltcsHomeVisitLongTermCareService',
            'ltcsCompHomeVisitingService',
            'ltcsPreventionService',
            'status',
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
            'name' => true,
            'abbr' => true,
            'phoneticName' => true,
            'corporationName' => true,
            'phoneticCorporationName' => true,
            'purpose' => true,
            'addr' => true,
            'location' => true,
            'tel' => true,
            'fax' => true,
            'email' => true,
            'qualifications' => true,
            'officeGroupId' => true,
            'dwsGenericService' => true,
            'dwsCommAccompanyService' => true,
            'ltcsCareManagementService' => true,
            'ltcsHomeVisitLongTermCareService' => true,
            'ltcsCompHomeVisitingService' => true,
            'ltcsPreventionService' => true,
            'status' => true,
            'isEnabled' => true,
            'version' => false,
            'createdAt' => true,
            'updatedAt' => true,
        ];
    }
}

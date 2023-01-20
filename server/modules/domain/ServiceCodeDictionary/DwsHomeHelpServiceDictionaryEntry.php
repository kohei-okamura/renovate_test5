<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\ServiceCodeDictionary;

use Domain\Entity;

/**
 * サービスコード辞書エントリ（障害：居宅介護）.
 *
 * @property-read int $id エントリID
 * @property-read int $dwsHomeHelpServiceDictionaryId 辞書ID
 * @property-read \Domain\ServiceCode\ServiceCode $serviceCode サービスコード
 * @property-read string $name 名称
 * @property-read \Domain\ServiceCodeDictionary\DwsServiceCodeCategory $category サービスコード区分
 * @property-read bool $isExtra 増分
 * @property-read bool $isSecondary 2人（2人目の居宅介護従業者による場合）
 * @property-read \Domain\ServiceCodeDictionary\DwsHomeHelpServiceProviderType $providerType 提供者区分
 * @property-read bool $isPlannedByNovice 初計（初任者研修課程修了者が作成した居宅介護計画に基づき提供する場合）
 * @property-read \Domain\ServiceCodeDictionary\DwsHomeHelpServiceBuildingType $buildingType 障害居宅介護建物区分
 * @property-read int $score 単位数
 * @property-read \Domain\Common\IntRange $daytimeDuration 時間数（日中）
 * @property-read \Domain\Common\IntRange $morningDuration 時間数（早朝）
 * @property-read \Domain\Common\IntRange $nightDuration 時間数（夜間）
 * @property-read \Domain\Common\IntRange $midnightDuration1 時間数（深夜1）
 * @property-read \Domain\Common\IntRange $midnightDuration2 時間数（深夜2）
 * @property-read \Domain\Common\Carbon $createdAt 登録日時
 * @property-read \Domain\Common\Carbon $updatedAt 更新日時
 */
final class DwsHomeHelpServiceDictionaryEntry extends Entity
{
    /** {@inheritdoc} */
    protected function attrs(): array
    {
        return [
            ...parent::attrs(),
            'dwsHomeHelpServiceDictionaryId',
            'serviceCode',
            'name',
            'category',
            'isExtra',
            'isSecondary',
            'providerType',
            'isPlannedByNovice',
            'buildingType',
            'score',
            'daytimeDuration',
            'morningDuration',
            'nightDuration',
            'midnightDuration1',
            'midnightDuration2',
            'createdAt',
            'updatedAt',
        ];
    }

    /** {@inheritdoc} */
    protected function jsonables(): array
    {
        return [
            'id' => false,
            'dwsHomeHelpServiceDictionaryId' => false,
            'serviceCode' => false,
            'name' => false,
            'category' => false,
            'isExtra' => false,
            'isSecondary' => false,
            'providerType' => false,
            'isPlannedByNovice' => false,
            'buildingType' => false,
            'score' => false,
            'daytimeDuration' => false,
            'morningDuration' => false,
            'nightDuration' => false,
            'midnightDuration1' => false,
            'midnightDuration2' => false,
            'createdAt' => false,
            'updatedAt' => false,
        ];
    }
}

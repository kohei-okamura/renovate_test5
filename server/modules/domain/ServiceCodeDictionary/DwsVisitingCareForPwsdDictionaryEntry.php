<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\ServiceCodeDictionary;

use Domain\Entity;

/**
 * サービスコード辞書エントリ（障害：重度訪問介護）.
 *
 * @property-read int $id
 * @property-read int $dwsVisitingCareForPwsdDictionaryId 障害福祉サービス：重度訪問介護：サービスコード辞書ID
 * @property-read \Domain\ServiceCode\ServiceCode $serviceCode サービスコード
 * @property-read string $name 名称
 * @property-read \Domain\ServiceCodeDictionary\DwsServiceCodeCategory $category サービスコード区分
 * @property-read bool $isSecondary 2人（2人目の重度訪問介護従業者による場合）
 * @property-read bool $isCoaching 同行（熟練従業者が同行して支援を行う場合）
 * @property-read bool $isHospitalized 入院（病院等に入院又は入所中に利用した場合）
 * @property-read bool $isLongHospitalized 90日（90日以上利用減算）
 * @property-read int $score 単位数
 * @property-read \Domain\ServiceCodeDictionary\Timeframe $timeframe 時間帯
 * @property-read \Domain\Common\IntRange $duration 時間数
 * @property-read int $unit 単位
 * @property-read \Domain\Common\Carbon $createdAt 登録日時
 * @property-read \Domain\Common\Carbon $updatedAt 更新日時
 */
final class DwsVisitingCareForPwsdDictionaryEntry extends Entity
{
    /** {@inheritdoc} */
    protected function attrs(): array
    {
        return [
            ...parent::attrs(),
            'dwsVisitingCareForPwsdDictionaryId',
            'serviceCode',
            'name',
            'category',
            'isSecondary',
            'isCoaching',
            'isHospitalized',
            'isLongHospitalized',
            'score',
            'timeframe',
            'duration',
            'unit',
            'createdAt',
            'updatedAt',
        ];
    }

    /** {@inheritdoc} */
    protected function jsonables(): array
    {
        return [
            'id' => false,
            'dwsVisitingCareForPwsdDictionaryId' => false,
            'serviceCode' => false,
            'name' => false,
            'category' => false,
            'isSecondary' => false,
            'isCoaching' => false,
            'isHospitalized' => false,
            'isLongHospitalized' => false,
            'score' => false,
            'timeframe' => false,
            'duration' => false,
            'unit' => false,
            'createdAt' => false,
            'updatedAt' => false,
        ];
    }
}

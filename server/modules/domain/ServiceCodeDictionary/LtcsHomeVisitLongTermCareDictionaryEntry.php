<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\ServiceCodeDictionary;

use Domain\Entity;

/**
 * 介護保険サービス：訪問介護：サービスコード辞書エントリ.
 *
 * @property-read int $dictionaryId 辞書 ID
 * @property-read \Domain\ServiceCode\ServiceCode $serviceCode サービスコード
 * @property-read string $name 名称
 * @property-read LtcsServiceCodeCategory $category サービスコード区分
 * @property-read int $headcount 提供人数
 * @property-read LtcsCompositionType $compositionType 合成識別区分
 * @property-read \Domain\Office\HomeVisitLongTermCareSpecifiedOfficeAddition $specifiedOfficeAddition 特定事業所加算
 * @property-read LtcsNoteRequirement $noteRequirement 摘要欄記載要件
 * @property-read bool $isLimited 支給限度額対象
 * @property-read bool $isBulkSubtractionTarget 同一建物減算対象
 * @property-read bool $isSymbioticSubtractionTarget 共生型減算対象
 * @property-read \Domain\ServiceCodeDictionary\LtcsCalcScore $score 算定単位数
 * @property-read \Domain\ServiceCodeDictionary\LtcsCalcExtraScore $extraScore きざみ単位数
 * @property-read Timeframe $timeframe 時間帯
 * @property-read \Domain\Common\IntRange $totalMinutes 合計時間数
 * @property-read \Domain\Common\IntRange $physicalMinutes 身体時間数
 * @property-read \Domain\Common\IntRange $houseworkMinutes 生活時間数
 * @property-read \Domain\Common\Carbon $createdAt 登録日時
 * @property-read \Domain\Common\Carbon $updatedAt 更新日時
 */
final class LtcsHomeVisitLongTermCareDictionaryEntry extends Entity
{
    /** {@inheritdoc} */
    protected function attrs(): array
    {
        return [
            ...parent::attrs(),
            'dictionaryId',
            'serviceCode',
            'name',
            'category',
            'headcount',
            'compositionType',
            'specifiedOfficeAddition',
            'noteRequirement',
            'isLimited',
            'isBulkSubtractionTarget',
            'isSymbioticSubtractionTarget',
            'score',
            'extraScore',
            'timeframe',
            'totalMinutes',
            'physicalMinutes',
            'houseworkMinutes',
            'createdAt',
            'updatedAt',
        ];
    }

    /** {@inheritdoc} */
    protected function jsonables(): array
    {
        return [
            'id' => true,
            'dictionaryId' => true,
            'serviceCode' => true,
            'name' => true,
            'category' => true,
            'headcount' => true,
            'compositionType' => true,
            'specifiedOfficeAddition' => true,
            'noteRequirement' => true,
            'isLimited' => true,
            'isBulkSubtractionTarget' => true,
            'isSymbioticSubtractionTarget' => true,
            'score' => true,
            'extraScore' => true,
            'timeframe' => true,
            'totalMinutes' => true,
            'physicalMinutes' => true,
            'houseworkMinutes' => true,
            'createdAt' => true,
            'updatedAt' => true,
        ];
    }
}

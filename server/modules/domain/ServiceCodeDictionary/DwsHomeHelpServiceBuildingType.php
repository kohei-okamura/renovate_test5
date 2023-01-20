<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 *
 * THIS CODE IS AUTO GENERATED. DO NOT EDIT DIRECTLY.
 */
declare(strict_types=1);

namespace Domain\ServiceCodeDictionary;

use Domain\Enum;

/**
 * 障害福祉サービス：居宅介護：建物区分.
 *
 * @method static DwsHomeHelpServiceBuildingType none() 下記に該当しない
 * @method static DwsHomeHelpServiceBuildingType over20() 事業所と同一建物の利用者又はこれ以外の同一建物の利用者20人以上にサービスを行う場合
 * @method static DwsHomeHelpServiceBuildingType over50() 事業所と同一建物の利用者50人以上にサービスを行う場合
 */
final class DwsHomeHelpServiceBuildingType extends Enum
{
    /** {@inheritdoc} */
    protected static array $values = [
        'none' => 0,
        'over20' => 1,
        'over50' => 2,
    ];
}

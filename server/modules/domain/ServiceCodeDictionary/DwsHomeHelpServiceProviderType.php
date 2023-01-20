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
 * 障害福祉サービス：居宅介護：提供者区分.
 *
 * @method static DwsHomeHelpServiceProviderType none() 下記に該当しない
 * @method static DwsHomeHelpServiceProviderType beginner() 基（基礎研修課程修了者等により行われる場合）
 * @method static DwsHomeHelpServiceProviderType careWorkerForPwsd() 重研（重度訪問介護研修修了者による場合）
 */
final class DwsHomeHelpServiceProviderType extends Enum
{
    use DwsHomeHelpServiceProviderTypeSupport;

    /** {@inheritdoc} */
    protected static array $values = [
        'none' => 0,
        'beginner' => 1,
        'careWorkerForPwsd' => 2,
    ];
}

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
 * 介護保険サービス：摘要欄記載条件.
 *
 * @method static LtcsNoteRequirement durationMinutes() 所要時間
 * @method static LtcsNoteRequirement none() 空白（記載不要）
 */
final class LtcsNoteRequirement extends Enum
{
    /** {@inheritdoc} */
    protected static array $values = [
        'durationMinutes' => 1,
        'none' => 99,
    ];
}

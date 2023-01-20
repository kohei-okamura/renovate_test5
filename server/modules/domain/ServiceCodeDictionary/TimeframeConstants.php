<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\ServiceCodeDictionary;

/**
 * 時間帯関連の定数定義.
 */
interface TimeframeConstants
{
    public const START_OF_DAY = 0;
    public const START_OF_MORNING = 6;
    public const START_OF_DAYTIME = 8;
    public const START_OF_NIGHT = 18;
    public const START_OF_MIDNIGHT = 22;
}

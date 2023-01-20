<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 *
 * THIS CODE IS AUTO GENERATED. DO NOT EDIT DIRECTLY.
 */
declare(strict_types=1);

namespace Domain\Office;

use Domain\Enum;

/**
 * 事業内容.
 *
 * @method static Business backOffice() バックオフィス
 * @method static Business homeVisitCare() 訪問介護
 * @method static Business homeVisitNursing() 訪問看護
 * @method static Business dayCare() デイサービス
 * @method static Business homeCareSupport() 居宅介護支援
 * @method static Business college() カレッジ
 * @method static Business massage() マッサージ
 */
final class Business extends Enum
{
    /** {@inheritdoc} */
    protected static array $values = [
        'backOffice' => 1,
        'homeVisitCare' => 2,
        'homeVisitNursing' => 3,
        'dayCare' => 4,
        'homeCareSupport' => 5,
        'college' => 6,
        'massage' => 7,
    ];
}

<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 *
 * THIS CODE IS AUTO GENERATED. DO NOT EDIT DIRECTLY.
 */
declare(strict_types=1);

namespace Domain\ProvisionReport;

use Domain\Enum;

/**
 * 障害福祉サービス：予実：状態.
 *
 * @method static DwsProvisionReportStatus notCreated() 未作成
 * @method static DwsProvisionReportStatus inProgress() 作成中
 * @method static DwsProvisionReportStatus fixed() 確定済
 */
final class DwsProvisionReportStatus extends Enum
{
    /** {@inheritdoc} */
    protected static array $values = [
        'notCreated' => 1,
        'inProgress' => 2,
        'fixed' => 3,
    ];
}

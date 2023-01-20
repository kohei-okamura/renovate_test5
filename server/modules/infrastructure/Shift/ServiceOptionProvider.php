<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\Shift;

/**
 * {@link \Domain\Shift\ServiceOption} Provider.
 *
 * ※このインターフェースをクラスに実装する場合は {@link \Infrastructure\Shift\ServiceOptionHolder} を追加すること.
 *
 * @property \Domain\Shift\ServiceOption $service_option サービスオプション（勤務シフト・勤務実績）ID
 */
interface ServiceOptionProvider
{
}

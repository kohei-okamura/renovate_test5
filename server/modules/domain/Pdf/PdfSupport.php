<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\Pdf;

use Domain\Common\Carbon;

/**
 * PDF に出力する値の作成サポート.
 */
trait PdfSupport
{
    /**
     * 日付を和暦、年、月、日にする.
     *
     * @param null|\Domain\Common\Carbon $carbon
     * @return array
     */
    public static function localized(?Carbon $carbon): array
    {
        return [
            'japaneseCalender' => $carbon !== null ? $carbon->toEraName() : '  ',
            'year' => $carbon !== null ? sprintf('%02s', $carbon->toJapaneseYear()) : '  ',
            'month' => $carbon !== null ? $carbon->format('m') : '  ',
            'day' => $carbon !== null ? $carbon->format('d') : '  ',
        ];
    }
}

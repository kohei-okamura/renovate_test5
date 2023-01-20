<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Console\Commands;

use Domain\Common\Carbon;
use Lib\Exceptions\InvalidArgumentException;

/**
 * Batchコマンド用 date,time オプションサポート.
 *
 * @mixin \Illuminate\Console\Command
 */
trait BatchDateTimeCommandOptionSupport
{
    /**
     * コマンドオプションの条件から、対象の日時を算出する.
     *
     * @return \Domain\Common\Carbon
     */
    protected function getTargetDatetime(): Carbon
    {
        return $this->option('batch')
            ? Carbon::now()->startOfMinute()
            : $this->buildTargetFromCommandOptions();
    }

    /**
     * コマンドオプションからの時刻組み立て.
     *
     * @return \Domain\Common\Carbon
     */
    private function buildTargetFromCommandOptions(): Carbon
    {
        $time = $this->option('time');
        if ($time === null || preg_match('/\A(?:[0-1][0-9]|2[0-3])[0-5][0-9]\z/', $time) !== 1) {
            throw new InvalidArgumentException();
        }
        $date = $this->option('date') ?? Carbon::now()->format('Ymd');
        return Carbon::createFromFormat('Ymd Hi', "{$date} {$time}");
    }
}

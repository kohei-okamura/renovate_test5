<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\Common;

use Illuminate\Support\Arr;
use Lib\Arrays;
use Lib\Exceptions\InvalidArgumentException;

/**
 * Time Range.
 *
 * @property-read string $start
 * @property-read string $end
 */
final class TimeRange extends Range
{
    private const TIME_PATTERN = '/\A(?:0[0-9]|1[0-9]|2[0-3]):[0-5][0-9]\z/';

    /**
     * start ~ end の時間数を分で返す。
     *
     * ただし end の時間が start 以前の場合は翌日として扱う。
     *
     * @return int
     */
    public function toMinutes(): int
    {
        $start = Carbon::parse($this->start);
        $end = Carbon::parse($this->end);
        return $start > $end ? $end->addDay()->diffInMinutes($start) : $start->diffInMinutes($end);
    }

    /** {@inheritdoc} */
    protected function computedAttrs(array $values): array
    {
        return Arrays::generate(function () use ($values) {
            foreach ($this->attrs() as $key) {
                $time = Arr::get($values, $key, '');
                if (is_string($time)) {
                    $this->ensure($time);
                    yield $key => $time;
                } else {
                    throw new InvalidArgumentException('Not in the specified format for TimeRange');
                }
            }
        });
    }

    /**
     * 値が時刻形式の文字列であることを検証する.
     *
     * @param string $value
     * @return void
     */
    private function ensure(string $value): void
    {
        if (!preg_match(self::TIME_PATTERN, $value)) {
            throw new InvalidArgumentException('Not in the specified format for TimeRange');
        }
    }
}

<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\Common;

use Carbon\CarbonImmutable;
use Lib\Exceptions\LogicException;
use ScalikePHP\Option;
use Yasumi\Yasumi;

/**
 * Immutable date and time.
 *
 * **FYI**
 * {@link \Carbon\CarbonImmutable} の phpdoc がイケてないせいで警告がでることを防ぐため
 * 同クラスから一部のコメントをコピーして書き換えている.
 *
 * @method static years(int $value) Set current instance year to the given value.
 * @method static year(int $value) Set current instance year to the given value.
 * @method static setYears(int $value) Set current instance year to the given value.
 * @method static setYear(int $value) Set current instance year to the given value.
 * @method static months(int $value) Set current instance month to the given value.
 * @method static month(int $value) Set current instance month to the given value.
 * @method static setMonths(int $value) Set current instance month to the given value.
 * @method static setMonth(int $value) Set current instance month to the given value.
 * @method static days(int $value) Set current instance day to the given value.
 * @method static day(int $value) Set current instance day to the given value.
 * @method static setDays(int $value) Set current instance day to the given value.
 * @method static setDay(int $value) Set current instance day to the given value.
 * @method static hours(int $value) Set current instance hour to the given value.
 * @method static hour(int $value) Set current instance hour to the given value.
 * @method static setHours(int $value) Set current instance hour to the given value.
 * @method static setHour(int $value) Set current instance hour to the given value.
 * @method static minutes(int $value) Set current instance minute to the given value.
 * @method static minute(int $value) Set current instance minute to the given value.
 * @method static setMinutes(int $value) Set current instance minute to the given value.
 * @method static setMinute(int $value) Set current instance minute to the given value.
 * @method static seconds(int $value) Set current instance second to the given value.
 * @method static second(int $value) Set current instance second to the given value.
 * @method static setSeconds(int $value) Set current instance second to the given value.
 * @method static setSecond(int $value) Set current instance second to the given value.
 * @method static millis(int $value) Set current instance millisecond to the given value.
 * @method static milli(int $value) Set current instance millisecond to the given value.
 * @method static setMillis(int $value) Set current instance millisecond to the given value.
 * @method static setMilli(int $value) Set current instance millisecond to the given value.
 * @method static milliseconds(int $value) Set current instance millisecond to the given value.
 * @method static millisecond(int $value) Set current instance millisecond to the given value.
 * @method static setMilliseconds(int $value) Set current instance millisecond to the given value.
 * @method static setMillisecond(int $value) Set current instance millisecond to the given value.
 * @method static micros(int $value) Set current instance microsecond to the given value.
 * @method static micro(int $value) Set current instance microsecond to the given value.
 * @method static setMicros(int $value) Set current instance microsecond to the given value.
 * @method static setMicro(int $value) Set current instance microsecond to the given value.
 * @method static microseconds(int $value) Set current instance microsecond to the given value.
 * @method static microsecond(int $value) Set current instance microsecond to the given value.
 * @method static setMicroseconds(int $value) Set current instance microsecond to the given value.
 * @method static setMicrosecond(int $value) Set current instance microsecond to the given value.
 * @method static addYears(int $value = 1)                                                             Add years (the $value count passed in) to the instance (using date interval).
 * @method static addYear() Add one year to the instance (using date interval).
 * @method static subYears(int $value = 1)                                                             Sub years (the $value count passed in) to the instance (using date interval).
 * @method static subYear() Sub one year to the instance (using date interval).
 * @method static addYearsWithOverflow(int $value = 1)                                                 Add years (the $value count passed in) to the instance (using date interval) with overflow explicitly allowed.
 * @method static addYearWithOverflow()                                                                Add one year to the instance (using date interval) with overflow explicitly allowed.
 * @method static subYearsWithOverflow(int $value = 1)                                                 Sub years (the $value count passed in) to the instance (using date interval) with overflow explicitly allowed.
 * @method static subYearWithOverflow()                                                                Sub one year to the instance (using date interval) with overflow explicitly allowed.
 * @method static addYearsWithoutOverflow(int $value = 1)                                              Add years (the $value count passed in) to the instance (using date interval) with overflow explicitly forbidden.
 * @method static addYearWithoutOverflow()                                                             Add one year to the instance (using date interval) with overflow explicitly forbidden.
 * @method static subYearsWithoutOverflow(int $value = 1)                                              Sub years (the $value count passed in) to the instance (using date interval) with overflow explicitly forbidden.
 * @method static subYearWithoutOverflow()                                                             Sub one year to the instance (using date interval) with overflow explicitly forbidden.
 * @method static addYearsWithNoOverflow(int $value = 1)                                               Add years (the $value count passed in) to the instance (using date interval) with overflow explicitly forbidden.
 * @method static addYearWithNoOverflow()                                                              Add one year to the instance (using date interval) with overflow explicitly forbidden.
 * @method static subYearsWithNoOverflow(int $value = 1)                                               Sub years (the $value count passed in) to the instance (using date interval) with overflow explicitly forbidden.
 * @method static subYearWithNoOverflow()                                                              Sub one year to the instance (using date interval) with overflow explicitly forbidden.
 * @method static addYearsNoOverflow(int $value = 1)                                                   Add years (the $value count passed in) to the instance (using date interval) with overflow explicitly forbidden.
 * @method static addYearNoOverflow()                                                                  Add one year to the instance (using date interval) with overflow explicitly forbidden.
 * @method static subYearsNoOverflow(int $value = 1)                                                   Sub years (the $value count passed in) to the instance (using date interval) with overflow explicitly forbidden.
 * @method static subYearNoOverflow()                                                                  Sub one year to the instance (using date interval) with overflow explicitly forbidden.
 * @method static addMonths(int $value = 1)                                                            Add months (the $value count passed in) to the instance (using date interval).
 * @method static addMonth() Add one month to the instance (using date interval).
 * @method static subMonths(int $value = 1)                                                            Sub months (the $value count passed in) to the instance (using date interval).
 * @method static subMonth() Sub one month to the instance (using date interval).
 * @method static addMonthsWithOverflow(int $value = 1)                                                Add months (the $value count passed in) to the instance (using date interval) with overflow explicitly allowed.
 * @method static addMonthWithOverflow()                                                               Add one month to the instance (using date interval) with overflow explicitly allowed.
 * @method static subMonthsWithOverflow(int $value = 1)                                                Sub months (the $value count passed in) to the instance (using date interval) with overflow explicitly allowed.
 * @method static subMonthWithOverflow()                                                               Sub one month to the instance (using date interval) with overflow explicitly allowed.
 * @method static addMonthsWithoutOverflow(int $value = 1)                                             Add months (the $value count passed in) to the instance (using date interval) with overflow explicitly forbidden.
 * @method static addMonthWithoutOverflow()                                                            Add one month to the instance (using date interval) with overflow explicitly forbidden.
 * @method static subMonthsWithoutOverflow(int $value = 1)                                             Sub months (the $value count passed in) to the instance (using date interval) with overflow explicitly forbidden.
 * @method static subMonthWithoutOverflow()                                                            Sub one month to the instance (using date interval) with overflow explicitly forbidden.
 * @method static addMonthsWithNoOverflow(int $value = 1)                                              Add months (the $value count passed in) to the instance (using date interval) with overflow explicitly forbidden.
 * @method static addMonthWithNoOverflow()                                                             Add one month to the instance (using date interval) with overflow explicitly forbidden.
 * @method static subMonthsWithNoOverflow(int $value = 1)                                              Sub months (the $value count passed in) to the instance (using date interval) with overflow explicitly forbidden.
 * @method static subMonthWithNoOverflow()                                                             Sub one month to the instance (using date interval) with overflow explicitly forbidden.
 * @method static addMonthsNoOverflow(int $value = 1)                                                  Add months (the $value count passed in) to the instance (using date interval) with overflow explicitly forbidden.
 * @method static addMonthNoOverflow()                                                                 Add one month to the instance (using date interval) with overflow explicitly forbidden.
 * @method static subMonthsNoOverflow(int $value = 1)                                                  Sub months (the $value count passed in) to the instance (using date interval) with overflow explicitly forbidden.
 * @method static subMonthNoOverflow()                                                                 Sub one month to the instance (using date interval) with overflow explicitly forbidden.
 * @method static addDays(int $value = 1)                                                              Add days (the $value count passed in) to the instance (using date interval).
 * @method static addDay() Add one day to the instance (using date interval).
 * @method static subDays(int $value = 1)                                                              Sub days (the $value count passed in) to the instance (using date interval).
 * @method static subDay() Sub one day to the instance (using date interval).
 * @method static addHours(int $value = 1)                                                             Add hours (the $value count passed in) to the instance (using date interval).
 * @method static addHour() Add one hour to the instance (using date interval).
 * @method static subHours(int $value = 1)                                                             Sub hours (the $value count passed in) to the instance (using date interval).
 * @method static subHour() Sub one hour to the instance (using date interval).
 * @method static addMinutes(int $value = 1)                                                           Add minutes (the $value count passed in) to the instance (using date interval).
 * @method static addMinute() Add one minute to the instance (using date interval).
 * @method static subMinutes(int $value = 1)                                                           Sub minutes (the $value count passed in) to the instance (using date interval).
 * @method static subMinute() Sub one minute to the instance (using date interval).
 * @method static addSeconds(int $value = 1)                                                           Add seconds (the $value count passed in) to the instance (using date interval).
 * @method static addSecond() Add one second to the instance (using date interval).
 * @method static subSeconds(int $value = 1)                                                           Sub seconds (the $value count passed in) to the instance (using date interval).
 * @method static subSecond() Sub one second to the instance (using date interval).
 * @method static addMillis(int $value = 1)                                                            Add milliseconds (the $value count passed in) to the instance (using date interval).
 * @method static addMilli() Add one millisecond to the instance (using date interval).
 * @method static subMillis(int $value = 1)                                                            Sub milliseconds (the $value count passed in) to the instance (using date interval).
 * @method static subMilli() Sub one millisecond to the instance (using date interval).
 * @method static addMilliseconds(int $value = 1)                                                      Add milliseconds (the $value count passed in) to the instance (using date interval).
 * @method static addMillisecond() Add one millisecond to the instance (using date interval).
 * @method static subMilliseconds(int $value = 1)                                                      Sub milliseconds (the $value count passed in) to the instance (using date interval).
 * @method static subMillisecond() Sub one millisecond to the instance (using date interval).
 * @method static addMicros(int $value = 1)                                                            Add microseconds (the $value count passed in) to the instance (using date interval).
 * @method static addMicro() Add one microsecond to the instance (using date interval).
 * @method static subMicros(int $value = 1)                                                            Sub microseconds (the $value count passed in) to the instance (using date interval).
 * @method static subMicro() Sub one microsecond to the instance (using date interval).
 * @method static addMicroseconds(int $value = 1)                                                      Add microseconds (the $value count passed in) to the instance (using date interval).
 * @method static addMicrosecond() Add one microsecond to the instance (using date interval).
 * @method static subMicroseconds(int $value = 1)                                                      Sub microseconds (the $value count passed in) to the instance (using date interval).
 * @method static subMicrosecond() Sub one microsecond to the instance (using date interval).
 * @method static addMillennia(int $value = 1)                                                         Add millennia (the $value count passed in) to the instance (using date interval).
 * @method static addMillennium() Add one millennium to the instance (using date interval).
 * @method static subMillennia(int $value = 1)                                                         Sub millennia (the $value count passed in) to the instance (using date interval).
 * @method static subMillennium() Sub one millennium to the instance (using date interval).
 * @method static addMillenniaWithOverflow(int $value = 1)                                             Add millennia (the $value count passed in) to the instance (using date interval) with overflow explicitly allowed.
 * @method static addMillenniumWithOverflow()                                                          Add one millennium to the instance (using date interval) with overflow explicitly allowed.
 * @method static subMillenniaWithOverflow(int $value = 1)                                             Sub millennia (the $value count passed in) to the instance (using date interval) with overflow explicitly allowed.
 * @method static subMillenniumWithOverflow()                                                          Sub one millennium to the instance (using date interval) with overflow explicitly allowed.
 * @method static addMillenniaWithoutOverflow(int $value = 1)                                          Add millennia (the $value count passed in) to the instance (using date interval) with overflow explicitly forbidden.
 * @method static addMillenniumWithoutOverflow()                                                       Add one millennium to the instance (using date interval) with overflow explicitly forbidden.
 * @method static subMillenniaWithoutOverflow(int $value = 1)                                          Sub millennia (the $value count passed in) to the instance (using date interval) with overflow explicitly forbidden.
 * @method static subMillenniumWithoutOverflow()                                                       Sub one millennium to the instance (using date interval) with overflow explicitly forbidden.
 * @method static addMillenniaWithNoOverflow(int $value = 1)                                           Add millennia (the $value count passed in) to the instance (using date interval) with overflow explicitly forbidden.
 * @method static addMillenniumWithNoOverflow()                                                        Add one millennium to the instance (using date interval) with overflow explicitly forbidden.
 * @method static subMillenniaWithNoOverflow(int $value = 1)                                           Sub millennia (the $value count passed in) to the instance (using date interval) with overflow explicitly forbidden.
 * @method static subMillenniumWithNoOverflow()                                                        Sub one millennium to the instance (using date interval) with overflow explicitly forbidden.
 * @method static addMillenniaNoOverflow(int $value = 1)                                               Add millennia (the $value count passed in) to the instance (using date interval) with overflow explicitly forbidden.
 * @method static addMillenniumNoOverflow()                                                            Add one millennium to the instance (using date interval) with overflow explicitly forbidden.
 * @method static subMillenniaNoOverflow(int $value = 1)                                               Sub millennia (the $value count passed in) to the instance (using date interval) with overflow explicitly forbidden.
 * @method static subMillenniumNoOverflow()                                                            Sub one millennium to the instance (using date interval) with overflow explicitly forbidden.
 * @method static addCenturies(int $value = 1)                                                         Add centuries (the $value count passed in) to the instance (using date interval).
 * @method static addCentury() Add one century to the instance (using date interval).
 * @method static subCenturies(int $value = 1)                                                         Sub centuries (the $value count passed in) to the instance (using date interval).
 * @method static subCentury() Sub one century to the instance (using date interval).
 * @method static addCenturiesWithOverflow(int $value = 1)                                             Add centuries (the $value count passed in) to the instance (using date interval) with overflow explicitly allowed.
 * @method static addCenturyWithOverflow()                                                             Add one century to the instance (using date interval) with overflow explicitly allowed.
 * @method static subCenturiesWithOverflow(int $value = 1)                                             Sub centuries (the $value count passed in) to the instance (using date interval) with overflow explicitly allowed.
 * @method static subCenturyWithOverflow()                                                             Sub one century to the instance (using date interval) with overflow explicitly allowed.
 * @method static addCenturiesWithoutOverflow(int $value = 1)                                          Add centuries (the $value count passed in) to the instance (using date interval) with overflow explicitly forbidden.
 * @method static addCenturyWithoutOverflow()                                                          Add one century to the instance (using date interval) with overflow explicitly forbidden.
 * @method static subCenturiesWithoutOverflow(int $value = 1)                                          Sub centuries (the $value count passed in) to the instance (using date interval) with overflow explicitly forbidden.
 * @method static subCenturyWithoutOverflow()                                                          Sub one century to the instance (using date interval) with overflow explicitly forbidden.
 * @method static addCenturiesWithNoOverflow(int $value = 1)                                           Add centuries (the $value count passed in) to the instance (using date interval) with overflow explicitly forbidden.
 * @method static addCenturyWithNoOverflow()                                                           Add one century to the instance (using date interval) with overflow explicitly forbidden.
 * @method static subCenturiesWithNoOverflow(int $value = 1)                                           Sub centuries (the $value count passed in) to the instance (using date interval) with overflow explicitly forbidden.
 * @method static subCenturyWithNoOverflow()                                                           Sub one century to the instance (using date interval) with overflow explicitly forbidden.
 * @method static addCenturiesNoOverflow(int $value = 1)                                               Add centuries (the $value count passed in) to the instance (using date interval) with overflow explicitly forbidden.
 * @method static addCenturyNoOverflow()                                                               Add one century to the instance (using date interval) with overflow explicitly forbidden.
 * @method static subCenturiesNoOverflow(int $value = 1)                                               Sub centuries (the $value count passed in) to the instance (using date interval) with overflow explicitly forbidden.
 * @method static subCenturyNoOverflow()                                                               Sub one century to the instance (using date interval) with overflow explicitly forbidden.
 * @method static addDecades(int $value = 1)                                                           Add decades (the $value count passed in) to the instance (using date interval).
 * @method static addDecade() Add one decade to the instance (using date interval).
 * @method static subDecades(int $value = 1)                                                           Sub decades (the $value count passed in) to the instance (using date interval).
 * @method static subDecade() Sub one decade to the instance (using date interval).
 * @method static addDecadesWithOverflow(int $value = 1)                                               Add decades (the $value count passed in) to the instance (using date interval) with overflow explicitly allowed.
 * @method static addDecadeWithOverflow()                                                              Add one decade to the instance (using date interval) with overflow explicitly allowed.
 * @method static subDecadesWithOverflow(int $value = 1)                                               Sub decades (the $value count passed in) to the instance (using date interval) with overflow explicitly allowed.
 * @method static subDecadeWithOverflow()                                                              Sub one decade to the instance (using date interval) with overflow explicitly allowed.
 * @method static addDecadesWithoutOverflow(int $value = 1)                                            Add decades (the $value count passed in) to the instance (using date interval) with overflow explicitly forbidden.
 * @method static addDecadeWithoutOverflow()                                                           Add one decade to the instance (using date interval) with overflow explicitly forbidden.
 * @method static subDecadesWithoutOverflow(int $value = 1)                                            Sub decades (the $value count passed in) to the instance (using date interval) with overflow explicitly forbidden.
 * @method static subDecadeWithoutOverflow()                                                           Sub one decade to the instance (using date interval) with overflow explicitly forbidden.
 * @method static addDecadesWithNoOverflow(int $value = 1)                                             Add decades (the $value count passed in) to the instance (using date interval) with overflow explicitly forbidden.
 * @method static addDecadeWithNoOverflow()                                                            Add one decade to the instance (using date interval) with overflow explicitly forbidden.
 * @method static subDecadesWithNoOverflow(int $value = 1)                                             Sub decades (the $value count passed in) to the instance (using date interval) with overflow explicitly forbidden.
 * @method static subDecadeWithNoOverflow()                                                            Sub one decade to the instance (using date interval) with overflow explicitly forbidden.
 * @method static addDecadesNoOverflow(int $value = 1)                                                 Add decades (the $value count passed in) to the instance (using date interval) with overflow explicitly forbidden.
 * @method static addDecadeNoOverflow()                                                                Add one decade to the instance (using date interval) with overflow explicitly forbidden.
 * @method static subDecadesNoOverflow(int $value = 1)                                                 Sub decades (the $value count passed in) to the instance (using date interval) with overflow explicitly forbidden.
 * @method static subDecadeNoOverflow()                                                                Sub one decade to the instance (using date interval) with overflow explicitly forbidden.
 * @method static addQuarters(int $value = 1)                                                          Add quarters (the $value count passed in) to the instance (using date interval).
 * @method static addQuarter() Add one quarter to the instance (using date interval).
 * @method static subQuarters(int $value = 1)                                                          Sub quarters (the $value count passed in) to the instance (using date interval).
 * @method static subQuarter() Sub one quarter to the instance (using date interval).
 * @method static addQuartersWithOverflow(int $value = 1)                                              Add quarters (the $value count passed in) to the instance (using date interval) with overflow explicitly allowed.
 * @method static addQuarterWithOverflow()                                                             Add one quarter to the instance (using date interval) with overflow explicitly allowed.
 * @method static subQuartersWithOverflow(int $value = 1)                                              Sub quarters (the $value count passed in) to the instance (using date interval) with overflow explicitly allowed.
 * @method static subQuarterWithOverflow()                                                             Sub one quarter to the instance (using date interval) with overflow explicitly allowed.
 * @method static addQuartersWithoutOverflow(int $value = 1)                                           Add quarters (the $value count passed in) to the instance (using date interval) with overflow explicitly forbidden.
 * @method static addQuarterWithoutOverflow()                                                          Add one quarter to the instance (using date interval) with overflow explicitly forbidden.
 * @method static subQuartersWithoutOverflow(int $value = 1)                                           Sub quarters (the $value count passed in) to the instance (using date interval) with overflow explicitly forbidden.
 * @method static subQuarterWithoutOverflow()                                                          Sub one quarter to the instance (using date interval) with overflow explicitly forbidden.
 * @method static addQuartersWithNoOverflow(int $value = 1)                                            Add quarters (the $value count passed in) to the instance (using date interval) with overflow explicitly forbidden.
 * @method static addQuarterWithNoOverflow()                                                           Add one quarter to the instance (using date interval) with overflow explicitly forbidden.
 * @method static subQuartersWithNoOverflow(int $value = 1)                                            Sub quarters (the $value count passed in) to the instance (using date interval) with overflow explicitly forbidden.
 * @method static subQuarterWithNoOverflow()                                                           Sub one quarter to the instance (using date interval) with overflow explicitly forbidden.
 * @method static addQuartersNoOverflow(int $value = 1)                                                Add quarters (the $value count passed in) to the instance (using date interval) with overflow explicitly forbidden.
 * @method static addQuarterNoOverflow()                                                               Add one quarter to the instance (using date interval) with overflow explicitly forbidden.
 * @method static subQuartersNoOverflow(int $value = 1)                                                Sub quarters (the $value count passed in) to the instance (using date interval) with overflow explicitly forbidden.
 * @method static subQuarterNoOverflow()                                                               Sub one quarter to the instance (using date interval) with overflow explicitly forbidden.
 * @method static addWeeks(int $value = 1)                                                             Add weeks (the $value count passed in) to the instance (using date interval).
 * @method static addWeek() Add one week to the instance (using date interval).
 * @method static subWeeks(int $value = 1)                                                             Sub weeks (the $value count passed in) to the instance (using date interval).
 * @method static subWeek() Sub one week to the instance (using date interval).
 * @method static addWeekdays(int $value = 1)                                                          Add weekdays (the $value count passed in) to the instance (using date interval).
 * @method static addWeekday() Add one weekday to the instance (using date interval).
 * @method static subWeekdays(int $value = 1)                                                          Sub weekdays (the $value count passed in) to the instance (using date interval).
 * @method static subWeekday() Sub one weekday to the instance (using date interval).
 * @method static addRealMicros(int $value = 1)                                                        Add microseconds (the $value count passed in) to the instance (using timestamp).
 * @method static addRealMicro() Add one microsecond to the instance (using timestamp).
 * @method static subRealMicros(int $value = 1)                                                        Sub microseconds (the $value count passed in) to the instance (using timestamp).
 * @method static subRealMicro() Sub one microsecond to the instance (using timestamp).
 * @method static addRealMicroseconds(int $value = 1)                                                  Add microseconds (the $value count passed in) to the instance (using timestamp).
 * @method static addRealMicrosecond() Add one microsecond to the instance (using timestamp).
 * @method static subRealMicroseconds(int $value = 1)                                                  Sub microseconds (the $value count passed in) to the instance (using timestamp).
 * @method static subRealMicrosecond() Sub one microsecond to the instance (using timestamp).
 * @method static addRealMillis(int $value = 1)                                                        Add milliseconds (the $value count passed in) to the instance (using timestamp).
 * @method static addRealMilli() Add one millisecond to the instance (using timestamp).
 * @method static subRealMillis(int $value = 1)                                                        Sub milliseconds (the $value count passed in) to the instance (using timestamp).
 * @method static subRealMilli() Sub one millisecond to the instance (using timestamp).
 * @method static addRealMilliseconds(int $value = 1)                                                  Add milliseconds (the $value count passed in) to the instance (using timestamp).
 * @method static addRealMillisecond() Add one millisecond to the instance (using timestamp).
 * @method static subRealMilliseconds(int $value = 1)                                                  Sub milliseconds (the $value count passed in) to the instance (using timestamp).
 * @method static subRealMillisecond() Sub one millisecond to the instance (using timestamp).
 * @method static addRealSeconds(int $value = 1)                                                       Add seconds (the $value count passed in) to the instance (using timestamp).
 * @method static addRealSecond() Add one second to the instance (using timestamp).
 * @method static subRealSeconds(int $value = 1)                                                       Sub seconds (the $value count passed in) to the instance (using timestamp).
 * @method static subRealSecond() Sub one second to the instance (using timestamp).
 * @method static addRealMinutes(int $value = 1)                                                       Add minutes (the $value count passed in) to the instance (using timestamp).
 * @method static addRealMinute() Add one minute to the instance (using timestamp).
 * @method static subRealMinutes(int $value = 1)                                                       Sub minutes (the $value count passed in) to the instance (using timestamp).
 * @method static subRealMinute() Sub one minute to the instance (using timestamp).
 * @method static addRealHours(int $value = 1)                                                         Add hours (the $value count passed in) to the instance (using timestamp).
 * @method static addRealHour() Add one hour to the instance (using timestamp).
 * @method static subRealHours(int $value = 1)                                                         Sub hours (the $value count passed in) to the instance (using timestamp).
 * @method static subRealHour() Sub one hour to the instance (using timestamp).
 * @method static addRealDays(int $value = 1)                                                          Add days (the $value count passed in) to the instance (using timestamp).
 * @method static addRealDay() Add one day to the instance (using timestamp).
 * @method static subRealDays(int $value = 1)                                                          Sub days (the $value count passed in) to the instance (using timestamp).
 * @method static subRealDay() Sub one day to the instance (using timestamp).
 * @method static addRealWeeks(int $value = 1)                                                         Add weeks (the $value count passed in) to the instance (using timestamp).
 * @method static addRealWeek() Add one week to the instance (using timestamp).
 * @method static subRealWeeks(int $value = 1)                                                         Sub weeks (the $value count passed in) to the instance (using timestamp).
 * @method static subRealWeek() Sub one week to the instance (using timestamp).
 * @method static addRealMonths(int $value = 1)                                                        Add months (the $value count passed in) to the instance (using timestamp).
 * @method static addRealMonth() Add one month to the instance (using timestamp).
 * @method static subRealMonths(int $value = 1)                                                        Sub months (the $value count passed in) to the instance (using timestamp).
 * @method static subRealMonth() Sub one month to the instance (using timestamp).
 * @method static addRealQuarters(int $value = 1)                                                      Add quarters (the $value count passed in) to the instance (using timestamp).
 * @method static addRealQuarter() Add one quarter to the instance (using timestamp).
 * @method static subRealQuarters(int $value = 1)                                                      Sub quarters (the $value count passed in) to the instance (using timestamp).
 * @method static subRealQuarter() Sub one quarter to the instance (using timestamp).
 * @method static addRealYears(int $value = 1)                                                         Add years (the $value count passed in) to the instance (using timestamp).
 * @method static addRealYear() Add one year to the instance (using timestamp).
 * @method static subRealYears(int $value = 1)                                                         Sub years (the $value count passed in) to the instance (using timestamp).
 * @method static subRealYear() Sub one year to the instance (using timestamp).
 * @method static addRealDecades(int $value = 1)                                                       Add decades (the $value count passed in) to the instance (using timestamp).
 * @method static addRealDecade() Add one decade to the instance (using timestamp).
 * @method static subRealDecades(int $value = 1)                                                       Sub decades (the $value count passed in) to the instance (using timestamp).
 * @method static subRealDecade() Sub one decade to the instance (using timestamp).
 * @method static addRealCenturies(int $value = 1)                                                     Add centuries (the $value count passed in) to the instance (using timestamp).
 * @method static addRealCentury() Add one century to the instance (using timestamp).
 * @method static subRealCenturies(int $value = 1)                                                     Sub centuries (the $value count passed in) to the instance (using timestamp).
 * @method static subRealCentury() Sub one century to the instance (using timestamp).
 * @method static addRealMillennia(int $value = 1)                                                     Add millennia (the $value count passed in) to the instance (using timestamp).
 * @method static addRealMillennium() Add one millennium to the instance (using timestamp).
 * @method static subRealMillennia(int $value = 1)                                                     Sub millennia (the $value count passed in) to the instance (using timestamp).
 * @method static subRealMillennium() Sub one millennium to the instance (using timestamp).
 * @method static roundYear(float $precision = 1, string $function = "round") Round the current instance year with given precision using the given function.
 * @method static roundYears(float $precision = 1, string $function = "round") Round the current instance year with given precision using the given function.
 * @method static floorYear(float $precision = 1) Truncate the current instance year with given precision.
 * @method static floorYears(float $precision = 1) Truncate the current instance year with given precision.
 * @method static ceilYear(float $precision = 1) Ceil the current instance year with given precision.
 * @method static ceilYears(float $precision = 1) Ceil the current instance year with given precision.
 * @method static roundMonth(float $precision = 1, string $function = "round") Round the current instance month with given precision using the given function.
 * @method static roundMonths(float $precision = 1, string $function = "round") Round the current instance month with given precision using the given function.
 * @method static floorMonth(float $precision = 1) Truncate the current instance month with given precision.
 * @method static floorMonths(float $precision = 1) Truncate the current instance month with given precision.
 * @method static ceilMonth(float $precision = 1) Ceil the current instance month with given precision.
 * @method static ceilMonths(float $precision = 1) Ceil the current instance month with given precision.
 * @method static roundDay(float $precision = 1, string $function = "round") Round the current instance day with given precision using the given function.
 * @method static roundDays(float $precision = 1, string $function = "round") Round the current instance day with given precision using the given function.
 * @method static floorDay(float $precision = 1) Truncate the current instance day with given precision.
 * @method static floorDays(float $precision = 1) Truncate the current instance day with given precision.
 * @method static ceilDay(float $precision = 1) Ceil the current instance day with given precision.
 * @method static ceilDays(float $precision = 1) Ceil the current instance day with given precision.
 * @method static roundHour(float $precision = 1, string $function = "round") Round the current instance hour with given precision using the given function.
 * @method static roundHours(float $precision = 1, string $function = "round") Round the current instance hour with given precision using the given function.
 * @method static floorHour(float $precision = 1) Truncate the current instance hour with given precision.
 * @method static floorHours(float $precision = 1) Truncate the current instance hour with given precision.
 * @method static ceilHour(float $precision = 1) Ceil the current instance hour with given precision.
 * @method static ceilHours(float $precision = 1) Ceil the current instance hour with given precision.
 * @method static roundMinute(float $precision = 1, string $function = "round") Round the current instance minute with given precision using the given function.
 * @method static roundMinutes(float $precision = 1, string $function = "round") Round the current instance minute with given precision using the given function.
 * @method static floorMinute(float $precision = 1) Truncate the current instance minute with given precision.
 * @method static floorMinutes(float $precision = 1) Truncate the current instance minute with given precision.
 * @method static ceilMinute(float $precision = 1) Ceil the current instance minute with given precision.
 * @method static ceilMinutes(float $precision = 1) Ceil the current instance minute with given precision.
 * @method static roundSecond(float $precision = 1, string $function = "round") Round the current instance second with given precision using the given function.
 * @method static roundSeconds(float $precision = 1, string $function = "round") Round the current instance second with given precision using the given function.
 * @method static floorSecond(float $precision = 1) Truncate the current instance second with given precision.
 * @method static floorSeconds(float $precision = 1) Truncate the current instance second with given precision.
 * @method static ceilSecond(float $precision = 1) Ceil the current instance second with given precision.
 * @method static ceilSeconds(float $precision = 1) Ceil the current instance second with given precision.
 * @method static roundMillennium(float $precision = 1, string $function = "round") Round the current instance millennium with given precision using the given function.
 * @method static roundMillennia(float $precision = 1, string $function = "round") Round the current instance millennium with given precision using the given function.
 * @method static floorMillennium(float $precision = 1) Truncate the current instance millennium with given precision.
 * @method static floorMillennia(float $precision = 1) Truncate the current instance millennium with given precision.
 * @method static ceilMillennium(float $precision = 1) Ceil the current instance millennium with given precision.
 * @method static ceilMillennia(float $precision = 1) Ceil the current instance millennium with given precision.
 * @method static roundCentury(float $precision = 1, string $function = "round") Round the current instance century with given precision using the given function.
 * @method static roundCenturies(float $precision = 1, string $function = "round") Round the current instance century with given precision using the given function.
 * @method static floorCentury(float $precision = 1) Truncate the current instance century with given precision.
 * @method static floorCenturies(float $precision = 1) Truncate the current instance century with given precision.
 * @method static ceilCentury(float $precision = 1) Ceil the current instance century with given precision.
 * @method static ceilCenturies(float $precision = 1) Ceil the current instance century with given precision.
 * @method static roundDecade(float $precision = 1, string $function = "round") Round the current instance decade with given precision using the given function.
 * @method static roundDecades(float $precision = 1, string $function = "round") Round the current instance decade with given precision using the given function.
 * @method static floorDecade(float $precision = 1) Truncate the current instance decade with given precision.
 * @method static floorDecades(float $precision = 1) Truncate the current instance decade with given precision.
 * @method static ceilDecade(float $precision = 1) Ceil the current instance decade with given precision.
 * @method static ceilDecades(float $precision = 1) Ceil the current instance decade with given precision.
 * @method static roundQuarter(float $precision = 1, string $function = "round") Round the current instance quarter with given precision using the given function.
 * @method static roundQuarters(float $precision = 1, string $function = "round") Round the current instance quarter with given precision using the given function.
 * @method static floorQuarter(float $precision = 1) Truncate the current instance quarter with given precision.
 * @method static floorQuarters(float $precision = 1) Truncate the current instance quarter with given precision.
 * @method static ceilQuarter(float $precision = 1) Ceil the current instance quarter with given precision.
 * @method static ceilQuarters(float $precision = 1) Ceil the current instance quarter with given precision.
 * @method static roundMillisecond(float $precision = 1, string $function = "round") Round the current instance millisecond with given precision using the given function.
 * @method static roundMilliseconds(float $precision = 1, string $function = "round") Round the current instance millisecond with given precision using the given function.
 * @method static floorMillisecond(float $precision = 1) Truncate the current instance millisecond with given precision.
 * @method static floorMilliseconds(float $precision = 1) Truncate the current instance millisecond with given precision.
 * @method static ceilMillisecond(float $precision = 1) Ceil the current instance millisecond with given precision.
 * @method static ceilMilliseconds(float $precision = 1) Ceil the current instance millisecond with given precision.
 * @method static roundMicrosecond(float $precision = 1, string $function = "round") Round the current instance microsecond with given precision using the given function.
 * @method static roundMicroseconds(float $precision = 1, string $function = "round") Round the current instance microsecond with given precision using the given function.
 * @method static floorMicrosecond(float $precision = 1) Truncate the current instance microsecond with given precision.
 * @method static floorMicroseconds(float $precision = 1) Truncate the current instance microsecond with given precision.
 * @method static ceilMicrosecond(float $precision = 1) Ceil the current instance microsecond with given precision.
 * @method static ceilMicroseconds(float $precision = 1) Ceil the current instance microsecond with given precision.
 */
final class Carbon extends CarbonImmutable
{
    protected static $serializer = self::ISO8601;
    protected static $toStringFormat = self::ISO8601;

    /**
     * テスト用の時間指定を解除する.
     *
     * @return void
     */
    public static function clearTestNow(): void
    {
        self::setTestNow();
    }

    /** {@inheritdoc} */
    public static function parse($time = null, $tz = null)
    {
        if ($time === null || $time === '') {
            throw new LogicException('time should specify');
        }
        return parent::parse($time, $tz);
    }

    /**
     * 引数が「空」の場合に None を返す `parse` 関数.
     *
     * @param null|string $time
     * @return \ScalikePHP\Option|self[]
     */
    public static function parseOption(?string $time): Option
    {
        return empty($time) ? Option::none() : Option::some(self::parse($time));
    }

    /**
     * 元号に変換する.
     *
     * @return string
     */
    public function toEraName(): string
    {
        return mb_substr($this->formatLocalized('%EC%Ey'), 0, 2);
    }

    /**
     * 和暦の「年」に変換する.
     *
     * @return string
     */
    public function toJapaneseYear(): string
    {
        return mb_substr($this->formatLocalized('%EC%Ey'), 2);
    }

    /**
     * 和暦の「年」に変換する.
     *
     * @return string
     */
    public function toJapaneseYearWithEra(): string
    {
        return $this->formatLocalized('%EC%Ey');
    }

    /**
     * 和暦の日付に変換する.
     *
     * @return string
     */
    public function toJapaneseDate(): string
    {
        $year = $this->toJapaneseYearWithEra();
        $month = $this->format('n');
        $day = $this->format('j');
        return "{$year}年{$month}月{$day}日";
    }

    /**
     * 和暦の日付に変換する.
     *
     * @return string
     */
    public function toJapaneseYearMonth(): string
    {
        $year = $this->toJapaneseYearWithEra();
        $month = $this->format('n');
        return "{$year}年{$month}月";
    }

    /**
     * 翌営業日を取得する.
     *
     * @return \Domain\Common\Carbon
     */
    public function getNextBusinessDay(): CarbonImmutable
    {
        $holidayProvider = Yasumi::create('Japan', $this->year);
        for ($i = 1;; ++$i) {
            $date = $this->addDays($i);
            if ($holidayProvider->isWorkingDay($date)) {
                return $date;
            }
        }
    }
}

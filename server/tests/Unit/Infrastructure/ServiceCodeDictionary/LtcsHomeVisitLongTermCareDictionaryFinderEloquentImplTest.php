<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Infrastructure\ServiceCodeDictionary;

use Closure;
use Domain\Common\Carbon;
use Domain\ServiceCodeDictionary\LtcsHomeVisitLongTermCareDictionary;
use Infrastructure\ServiceCodeDictionary\LtcsHomeVisitLongTermCareDictionaryFinderEloquentImpl;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\DatabaseMixin;
use Tests\Unit\Test;

/**
 * {@link \Infrastructure\ServiceCodeDictionary\LtcsHomeVisitLongTermCareDictionaryFinderEloquentImpl} Test.
 */
class LtcsHomeVisitLongTermCareDictionaryFinderEloquentImplTest extends Test
{
    use DatabaseMixin;
    use ExamplesConsumer;
    use UnitSupport;

    private LtcsHomeVisitLongTermCareDictionaryFinderEloquentImpl $finder;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (LtcsHomeVisitLongTermCareDictionaryFinderEloquentImplTest $self): void {
            $self->finder = app(LtcsHomeVisitLongTermCareDictionaryFinderEloquentImpl::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_find(): void
    {
        $effectivatedOn = Carbon::parse('2020-01-03');
        $examples = [
            'specified effectivatedBefore' => [
                ['effectivatedBefore' => $effectivatedOn],
                fn (LtcsHomeVisitLongTermCareDictionary $x): bool => $x->effectivatedOn <= $effectivatedOn,
            ],
        ];
        $this->should(
            'return a FinderResult of LtcsHomeVisitLongTermCareDictionary with given parameters',
            function (array $condition, Closure $assert): void {
                $result = $this->finder->find($condition, ['all' => true, 'sortBy' => 'id']);

                $this->assertNotEmpty($result);
                $this->assertNotEmpty($result->list);
                $this->assertForAll(
                    $result->list,
                    fn (LtcsHomeVisitLongTermCareDictionary $x): bool => $assert($x),
                );
                $this->assertExists(
                    $this->examples->ltcsHomeVisitLongTermCareDictionaries,
                    fn (LtcsHomeVisitLongTermCareDictionary $x): bool => !$assert($x)
                );
            },
            compact('examples')
        );
        $this->should(
            'return a FinderResult of LtcsHomeVisitLongTermCareDictionary with unknown parameter',
            function () {
                $result = $this->finder->find(
                    ['invalid' => 'value'],
                    ['all' => true, 'sortBy' => 'id'],
                );
                $this->assertNotEmpty($result);
                $this->assertNotEmpty($result->list);
                $this->assertArrayStrictEquals(
                    $this->examples->ltcsHomeVisitLongTermCareDictionaries,
                    $result->list->toArray()
                );
            }
        );
    }
}

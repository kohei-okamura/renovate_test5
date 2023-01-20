<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Infrastructure\ServiceCodeDictionary;

use Closure;
use Domain\Common\Carbon;
use Domain\ServiceCodeDictionary\DwsVisitingCareForPwsdDictionary;
use Infrastructure\ServiceCodeDictionary\DwsVisitingCareForPwsdDictionaryFinderEloquentImpl;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\DatabaseMixin;
use Tests\Unit\Test;

/**
 * {@link \Infrastructure\ServiceCodeDictionary\DwsVisitingCareForPwsdDictionaryFinderEloquentImpl} Test.
 */
class DwsVisitingCareForPwsdDictionaryFinderEloquentImplTest extends Test
{
    use DatabaseMixin;
    use ExamplesConsumer;
    use UnitSupport;

    private DwsVisitingCareForPwsdDictionaryFinderEloquentImpl $finder;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (DwsVisitingCareForPwsdDictionaryFinderEloquentImplTest $self): void {
            $self->finder = app(DwsVisitingCareForPwsdDictionaryFinderEloquentImpl::class);
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
                fn (DwsVisitingCareForPwsdDictionary $x): bool => $x->effectivatedOn <= $effectivatedOn,
                fn (DwsVisitingCareForPwsdDictionary $x): bool => $x->effectivatedOn > $effectivatedOn,
            ],
        ];
        $this->should(
            'return a FinderResult of DwsVisitingCareForPwsdDictionary with given parameters',
            function (array $condition, Closure $assert, Closure $others): void {
                $result = $this->finder->find($condition, ['all' => true, 'sortBy' => 'id']);

                $this->assertNotEmpty($result);
                $this->assertNotEmpty($result->list);
                $this->assertForAll(
                    $result->list,
                    fn (DwsVisitingCareForPwsdDictionary $x): bool => $assert($x),
                );

                $this->assertExists(
                    $this->examples->dwsVisitingCareForPwsdDictionaries,
                    fn (DwsVisitingCareForPwsdDictionary $x): bool => $others($x)
                );
            },
            compact('examples')
        );

        $this->should('return a FinderResult of DwsVisitingCareForPwsdDictionary with unknown parameter', function () {
            $result = $this->finder->find(
                ['invalid' => 'value'],
                ['all' => true, 'sortBy' => 'id'],
            );
            $this->assertNotEmpty($result);
            $this->assertNotEmpty($result->list);
            $this->assertArrayStrictEquals(
                $this->examples->dwsVisitingCareForPwsdDictionaries,
                $result->list->toArray()
            );
        });

        $this->should('return FinderResult with sorted list', function (string $key, Closure $sortBy): void {
            $expects = Seq::fromArray($this->examples->dwsVisitingCareForPwsdDictionaries)
                ->sortBy($sortBy);

            $result = $this->finder->find([], ['sortBy' => $key, 'desc' => false, 'all' => true]);

            $this->assertNotEmpty($result->list);
            $this->assertArrayStrictEquals($expects->toArray(), $result->list->toArray());
        }, [
            'examples' => [
                'sortBy date' => [
                    'date',
                    fn (DwsVisitingCareForPwsdDictionary $x): Carbon => $x->createdAt,
                ],
                'sortBy effectivatedOn' => [
                    'effectivatedOn',
                    fn (DwsVisitingCareForPwsdDictionary $x): Carbon => $x->effectivatedOn,
                ],
                'sortBy id' => [
                    'id',
                    fn (DwsVisitingCareForPwsdDictionary $x): int => $x->id,
                ],
            ],
        ]);
    }
}

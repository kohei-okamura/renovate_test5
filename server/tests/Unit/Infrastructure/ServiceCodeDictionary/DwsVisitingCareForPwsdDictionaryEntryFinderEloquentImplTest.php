<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Infrastructure\ServiceCodeDictionary;

use Closure;
use Domain\ServiceCodeDictionary\DwsServiceCodeCategory;
use Domain\ServiceCodeDictionary\DwsVisitingCareForPwsdDictionaryEntry;
use Domain\ServiceCodeDictionary\DwsVisitingCareForPwsdDictionaryEntry as Entry;
use Domain\ServiceCodeDictionary\Timeframe;
use Infrastructure\ServiceCodeDictionary\DwsVisitingCareForPwsdDictionaryEntryFinderEloquentImpl;
use Lib\Exceptions\SetupException;
use ScalikePHP\None;
use ScalikePHP\Some;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\DatabaseMixin;
use Tests\Unit\Test;

/**
 * {@link \Infrastructure\ServiceCodeDictionary\DwsVisitingCareForPwsdDictionaryEntryFinderEloquentImpl} Test.
 */
final class DwsVisitingCareForPwsdDictionaryEntryFinderEloquentImplTest extends Test
{
    use DatabaseMixin;
    use ExamplesConsumer;
    use UnitSupport;

    private array $dictionaries;
    private array $entries;

    private DwsVisitingCareForPwsdDictionaryEntryFinderEloquentImpl $finder;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachTest(function (self $self): void {
            $self->dictionaries = $self->examples->dwsVisitingCareForPwsdDictionaries;
            $self->entries = $self->examples->dwsVisitingCareForPwsdDictionaryEntries;
        });
        self::beforeEachSpec(function (self $self): void {
            $self->finder = app(DwsVisitingCareForPwsdDictionaryEntryFinderEloquentImpl::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_find(): void
    {
        // TODO: サービスコードAPIに差し替えたのでとりあえずスキップ
        self::markTestSkipped();
        $examples = [
            'specified category' => [
                ['category' => DwsServiceCodeCategory::visitingCareForPwsd1()],
                fn (Entry $x): bool => $x->category === DwsServiceCodeCategory::visitingCareForPwsd1(),
            ],
            'specified secondary' => [
                ['isSecondary' => false],
                fn (Entry $x): bool => !$x->isSecondary,
            ],
            'specified coaching' => [
                ['isCoaching' => false],
                fn (Entry $x): bool => !$x->isCoaching,
            ],
            'specified hospitalized' => [
                ['isHospitalized' => true],
                fn (Entry $x): bool => $x->isHospitalized,
            ],
            'specified long_hospitalized' => [
                ['isLongHospitalized' => false],
                fn (Entry $x): bool => !$x->isLongHospitalized,
            ],
            'specified timeframe' => [
                ['timeframe' => Timeframe::daytime()],
                fn (Entry $x): bool => $x->timeframe === Timeframe::daytime(),
            ],
            'specified dwsVisitingCareForPwsdDictionaryId' => [
                ['dwsVisitingCareForPwsdDictionaryId' => $this->dictionaries[1]->id],
                fn (Entry $x): bool => $x->dwsVisitingCareForPwsdDictionaryId === $this->dictionaries[1]->id,
            ],
        ];
        $this->should(
            'return a FinderResult of DwsVisitingCareForPwsdDictionaryEntries with given parameters',
            function (array $filterParams, Closure $assert): void {
                $paginationParams = [
                    'all' => true,
                    'sortBy' => 'id',
                ];
                $actual = $this->finder->find($filterParams, $paginationParams);

                $this->assertNotEmpty($actual);
                $this->assertNotEmpty($actual->list);
                $this->assertForAll($actual->list, $assert);
                $this->assertExists($this->entries, $this->invert($assert));
            },
            compact('examples')
        );
        $this->should(
            'return a FinderResult of DwsVisitingCareForPwsdDictionaryEntries with invalid filter keyword',
            function (): void {
                $actual = $this->finder->find(['dummy' => 'eustylelab'], ['sortBy' => 'id', 'all' => true]);

                $this->assertNotEmpty($actual->list);
                $this->assertCount(count($this->entries), $actual->list);
                $this->assertEach(
                    function ($a, $b): void {
                        $this->assertModelStrictEquals($a, $b);
                    },
                    $actual->list->toArray(),
                    $this->entries
                );
            }
        );
    }

    /**
     * @test
     * @return void
     */
    public function describe_findByCategory(): void
    {
        // TODO: サービスコードAPIに差し替えたのでとりあえずスキップ
        self::markTestSkipped();
        $this->should('return DwsVisitingCareForPwsdDictionaryEntry when it found', function (): void {
            $dictionary = $this->dictionaries[0];
            $category = DwsServiceCodeCategory::copayCoordinationAddition();
            $assert = fn (Entry $x): bool => $x->dwsVisitingCareForPwsdDictionaryId === $dictionary->id
                && $x->category === $category;
            $this->assertExists($this->entries, $assert);
            $this->assertExists($this->entries, $this->invert($assert));

            $actual = $this->finder->findByCategory($dictionary, $category);

            $this->assertInstanceOf(DwsVisitingCareForPwsdDictionaryEntry::class, $actual);
            $this->assertSame($category, $actual->category);
        });
        $this->should('throw NotFoundException when it does not found', function (): void {
            $dictionary = $this->dictionaries[0];
            $category = DwsServiceCodeCategory::welfareSpecialistCooperationAddition();
            $this->assertForAll(
                $this->entries,
                fn (Entry $x): bool => $x->dwsVisitingCareForPwsdDictionaryId !== $dictionary->id
                    || $x->category !== $category
            );

            $this->assertThrows(SetupException::class, function () use ($dictionary, $category): void {
                $this->finder->findByCategory($dictionary, $category);
            });
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_findByCategoryOption(): void
    {
        // TODO: サービスコードAPIに差し替えたのでとりあえずスキップ
        self::markTestSkipped();
        $this->should('return Some of DwsVisitingCareForPwsdDictionaryEntry when it found', function (): void {
            $dictionary = $this->dictionaries[0];
            $category = DwsServiceCodeCategory::copayCoordinationAddition();
            $assert = fn (Entry $x): bool => $x->dwsVisitingCareForPwsdDictionaryId === $dictionary->id
                && $x->category === $category;
            $this->assertExists($this->entries, $assert);
            $this->assertExists($this->entries, $this->invert($assert));

            $actual = $this->finder->findByCategoryOption($dictionary, $category);

            $this->assertInstanceOf(Some::class, $actual);
            $this->assertSame($category, $actual->get()->category);
        });
        $this->should('return None when it does not found', function (): void {
            $dictionary = $this->dictionaries[0];
            $category = DwsServiceCodeCategory::welfareSpecialistCooperationAddition();
            $this->assertForAll(
                $this->entries,
                fn (Entry $x): bool => $x->dwsVisitingCareForPwsdDictionaryId !== $dictionary->id
                    || $x->category !== $category
            );

            $actual = $this->finder->findByCategoryOption($dictionary, $category);

            $this->assertInstanceOf(None::class, $actual);
        });
    }
}

<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Infrastructure\ServiceCodeDictionary;

use Closure;
use Domain\ServiceCode\ServiceCode;
use Domain\ServiceCodeDictionary\DwsHomeHelpServiceDictionaryEntry as Entry;
use Domain\ServiceCodeDictionary\DwsHomeHelpServiceProviderType;
use Domain\ServiceCodeDictionary\DwsServiceCodeCategory;
use Infrastructure\ServiceCodeDictionary\DwsHomeHelpServiceDictionaryEntryFinderEloquentImpl;
use Lib\Exceptions\SetupException;
use ScalikePHP\None;
use ScalikePHP\Some;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\DatabaseMixin;
use Tests\Unit\Test;

/**
 * {@link \Infrastructure\ServiceCodeDictionary\DwsHomeHelpServiceDictionaryEntryFinderEloquentImpl} Test.
 */
final class DwsHomeHelpServiceDictionaryEntryFinderEloquentImplTest extends Test
{
    use DatabaseMixin;
    use ExamplesConsumer;
    use UnitSupport;

    private array $dictionaries;
    private array $entries;

    private DwsHomeHelpServiceDictionaryEntryFinderEloquentImpl $finder;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachTest(function (self $self): void {
            $self->dictionaries = $self->examples->dwsHomeHelpServiceDictionaries;
            $self->entries = $self->examples->dwsHomeHelpServiceDictionaryEntries;
        });
        self::beforeEachSpec(function (self $self): void {
            $self->finder = app(DwsHomeHelpServiceDictionaryEntryFinderEloquentImpl::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_find(): void
    {
        $examples = [
            'specified dwsHomeHelpServiceDictionaryId' => [
                ['dwsHomeHelpServiceDictionaryId' => $this->dictionaries[0]->id],
                fn (Entry $x): bool => $x->dwsHomeHelpServiceDictionaryId === $this->dictionaries[0]->id,
            ],
            'specified category' => [
                ['category' => DwsServiceCodeCategory::physicalCare()],
                fn (Entry $x): bool => $x->category === DwsServiceCodeCategory::physicalCare(),
            ],
            'specified isSecondary' => [
                ['isSecondary' => false],
                fn (Entry $x): bool => !$x->isSecondary,
            ],
            'specified isExtra' => [
                ['isExtra' => false],
                fn (Entry $x): bool => !$x->isExtra,
            ],
            'specified isPlannedByNovice' => [
                ['isPlannedByNovice' => true],
                fn (Entry $x): bool => $x->isPlannedByNovice,
            ],
            'specified providerType' => [
                ['providerType' => DwsHomeHelpServiceProviderType::careWorkerForPwsd()],
                fn (Entry $x): bool => $x->providerType === DwsHomeHelpServiceProviderType::careWorkerForPwsd(),
            ],
            'specified morningDuration' => [
                ['morningDuration' => 3],
                fn (Entry $x): bool => $x->morningDuration->contains(3),
            ],
            'specified dayTimeDuration' => [
                ['daytimeDuration' => 3],
                fn (Entry $x): bool => $x->daytimeDuration->contains(3),
            ],
            'specified nightDuration' => [
                ['nightDuration' => 3],
                fn (Entry $x): bool => $x->nightDuration->contains(3),
            ],
            'specified midnightDuration1' => [
                ['midnightDuration1' => 3],
                fn (Entry $x): bool => $x->midnightDuration1->contains(3),
            ],
            'specified midnightDuration2' => [
                ['midnightDuration2' => 3],
                fn (Entry $x): bool => $x->midnightDuration2->contains(3),
            ],
            'specified serviceCodes' => [
                ['serviceCodes' => ['115010']],
                fn (Entry $x): bool => ServiceCode::fromString('115010')->equals($x->serviceCode),
            ],
        ];
        $this->should(
            'return a FinderResult of DwsHomeHelpServiceDictionaryEntries with given parameters',
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
            'return a FinderResult of DwsHomeHelpServiceDictionaryEntries with invalid filter keyword',
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
        $this->should('return DwsHomeHelpServiceDictionaryEntry when it found', function (): void {
            $dictionary = $this->dictionaries[0];
            $category = DwsServiceCodeCategory::copayCoordinationAddition();
            $assert = fn (Entry $x): bool => $x->dwsHomeHelpServiceDictionaryId === $dictionary->id
                && $x->category === $category;
            $this->assertExists($this->entries, $assert);
            $this->assertExists($this->entries, $this->invert($assert));

            $actual = $this->finder->findByCategory($dictionary, $category);

            $this->assertInstanceOf(Entry::class, $actual);
            $this->assertSame($category, $actual->category);
        });
        $this->should('throw NotFoundException when it does not found', function (): void {
            $dictionary = $this->dictionaries[0];
            $category = DwsServiceCodeCategory::behavioralDisorderSupportCooperationAddition();
            $this->assertForAll(
                $this->entries,
                fn (Entry $x): bool => $x->dwsHomeHelpServiceDictionaryId !== $dictionary->id
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
        $this->should('return Some of DwsHomeHelpServiceDictionaryEntry when it found', function (): void {
            $dictionary = $this->dictionaries[0];
            $category = DwsServiceCodeCategory::copayCoordinationAddition();
            $assert = fn (Entry $x): bool => $x->dwsHomeHelpServiceDictionaryId === $dictionary->id
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
                fn (Entry $x): bool => $x->dwsHomeHelpServiceDictionaryId !== $dictionary->id
                    || $x->category !== $category
            );

            $actual = $this->finder->findByCategoryOption($dictionary, $category);

            $this->assertInstanceOf(None::class, $actual);
        });
    }
}

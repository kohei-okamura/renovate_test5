<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Infrastructure\ServiceCodeDictionary;

use Closure;
use Domain\Office\HomeVisitLongTermCareSpecifiedOfficeAddition;
use Domain\ServiceCode\ServiceCode;
use Domain\ServiceCodeDictionary\LtcsHomeVisitLongTermCareDictionaryEntry;
use Domain\ServiceCodeDictionary\LtcsServiceCodeCategory;
use Domain\ServiceCodeDictionary\Timeframe;
use Infrastructure\ServiceCodeDictionary\LtcsHomeVisitLongTermCareDictionaryEntryFinderEloquentImpl;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\DatabaseMixin;
use Tests\Unit\Test;

/**
 * {@link \Infrastructure\ServiceCodeDictionary\LtcsHomeVisitLongTermCareDictionaryEntryFinderEloquentImpl} のテスト.
 */
final class LtcsHomeVisitLongTermCareDictionaryEntryFinderEloquentImplTest extends Test
{
    use CarbonMixin;
    use DatabaseMixin;
    use ExamplesConsumer;
    use UnitSupport;

    private LtcsHomeVisitLongTermCareDictionaryEntryFinderEloquentImpl $finder;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachSpec(function (LtcsHomeVisitLongTermCareDictionaryEntryFinderEloquentImplTest $self): void {
            $self->finder = app(LtcsHomeVisitLongTermCareDictionaryEntryFinderEloquentImpl::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_find(): void
    {
        $dictionaryId = $this->examples->ltcsHomeVisitLongTermCareDictionaries[3]->id;
        $specifiedOfficeAddition = HomeVisitLongTermCareSpecifiedOfficeAddition::addition1();
        $examples = [
            'specified category' => [
                ['category' => LtcsServiceCodeCategory::physicalCare()],
                function (LtcsHomeVisitLongTermCareDictionaryEntry $x): bool {
                    return $x->category === LtcsServiceCodeCategory::physicalCare();
                },
            ],
            'specified dictionaryId' => [
                ['dictionaryId' => $dictionaryId],
                fn (LtcsHomeVisitLongTermCareDictionaryEntry $x): bool => $x->dictionaryId === $dictionaryId,
            ],
            'specified headcount' => [
                ['headcount' => 1],
                fn (LtcsHomeVisitLongTermCareDictionaryEntry $x): bool => $x->headcount === 1,
            ],
            'specified houseworkMinutes' => [
                ['houseworkMinutes' => 90],
                fn (LtcsHomeVisitLongTermCareDictionaryEntry $x): bool => $x->houseworkMinutes->contains(90),
            ],
            'specified physicalMinutes' => [
                ['physicalMinutes' => 30],
                fn (LtcsHomeVisitLongTermCareDictionaryEntry $x): bool => $x->physicalMinutes->contains(30),
            ],
            'specified q' => [
                ['q' => '111'],
                fn (LtcsHomeVisitLongTermCareDictionaryEntry $x): bool => preg_match('/^111[0-9A-Z]{3}$/', $x->serviceCode->toString()) === 1,
            ],
            'specified serviceCodes' => [
                ['serviceCodes' => ['116275']],
                fn (LtcsHomeVisitLongTermCareDictionaryEntry $x): bool => ServiceCode::fromString('116275')->equals($x->serviceCode),
            ],
            'specified specifiedOfficeAddition' => [
                ['specifiedOfficeAddition' => $specifiedOfficeAddition],
                function (LtcsHomeVisitLongTermCareDictionaryEntry $x) use ($specifiedOfficeAddition): bool {
                    return $x->specifiedOfficeAddition === $specifiedOfficeAddition;
                },
            ],
            'specified timeframe' => [
                ['timeframe' => Timeframe::daytime()],
                fn (LtcsHomeVisitLongTermCareDictionaryEntry $x): bool => $x->timeframe === Timeframe::daytime(),
            ],
            'specified totalMinutes' => [
                ['totalMinutes' => 360],
                fn (LtcsHomeVisitLongTermCareDictionaryEntry $x): bool => $x->totalMinutes->contains(360),
            ],
        ];
        $this->should(
            'return a FinderResult of LtcsHomeVisitLongTermCareDictionaryEntries with given parameters',
            function (array $condition, Closure $assert): void {
                $result = $this->finder->find($condition, ['all' => true, 'sortBy' => 'id']);

                $this->assertNotEmpty($result);
                $this->assertNotEmpty($result->list);
                $this->assertForAll(
                    $result->list,
                    fn (LtcsHomeVisitLongTermCareDictionaryEntry $x): bool => $assert($x)
                );
                $this->assertExists(
                    $this->examples->ltcsHomeVisitLongTermCareDictionaryEntries,
                    fn (LtcsHomeVisitLongTermCareDictionaryEntry $x): bool => !$assert($x)
                );
            },
            compact('examples')
        );
        $this->should(
            'return a FinderResult of LtcsHomeVisitLongTermCareDictionaryEntries with invalid filter keyword',
            function (): void {
                $result = $this->finder->find(['dummy' => 'eustylelab'], ['sortBy' => 'id']);

                $this->assertNotEmpty($result->list);
                $this->assertCount(count($this->examples->ltcsHomeVisitLongTermCareDictionaryEntries), $result->list);
                $this->assertEach(
                    function ($a, $b): void {
                        $this->assertModelStrictEquals($a, $b);
                    },
                    $result->list->toArray(),
                    Seq::fromArray($this->examples->ltcsHomeVisitLongTermCareDictionaryEntries)->toArray()
                );
            }
        );
    }
}

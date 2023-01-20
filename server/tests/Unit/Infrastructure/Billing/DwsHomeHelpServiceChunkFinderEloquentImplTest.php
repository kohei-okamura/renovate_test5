<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Infrastructure\Billing;

use Closure;
use Domain\Billing\DwsHomeHelpServiceChunk;
use Domain\Common\Pagination;
use Domain\Entity;
use Domain\FinderResult;
use Domain\ServiceCodeDictionary\DwsHomeHelpServiceBuildingType;
use Domain\ServiceCodeDictionary\DwsServiceCodeCategory;
use Infrastructure\Billing\DwsHomeHelpServiceChunkFinderEloquentImpl;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Fixtures\DwsHomeHelpServiceChunkFixture;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\DatabaseMixin;
use Tests\Unit\Test;

/**
 * {@link \Infrastructure\Billing\DwsHomeHelpServiceChunkFinderEloquentImpl} test.
 */
class DwsHomeHelpServiceChunkFinderEloquentImplTest extends Test
{
    use CarbonMixin;
    use DatabaseMixin;
    use ExamplesConsumer;
    use DwsHomeHelpServiceChunkFixture;
    use UnitSupport;

    private DwsHomeHelpServiceChunkFinderEloquentImpl $finder;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachTest(function (DwsHomeHelpServiceChunkFinderEloquentImplTest $self): void {
            $self->createDwsHomeHelpServiceChunk(); // TODO DEV-3849
            $self->finder = app(DwsHomeHelpServiceChunkFinderEloquentImpl::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_find(): void
    {
        $this->should('return a FinderResult of DwsHomeHelpServiceChunk', function (): void {
            $result = $this->finder->find([], ['sortBy' => 'date']);

            $this->assertInstanceOf(FinderResult::class, $result);
            $this->assertInstanceOf(Seq::class, $result->list);
            $this->assertInstanceOf(Pagination::class, $result->pagination);
            $this->assertNotEmpty($result->list);
            $this->assertForAll($result->list, fn (Entity $x): bool => $x instanceof DwsHomeHelpServiceChunk);
        });
        $this->should(
            'return a paginated FinderResult when param `all` not given or falsy',
            function ($all): void {
                $itemsPerPage = 2;
                $page = 2;
                $count = count($this->examples->dwsHomeHelpServiceChunks);
                $pages = (int)ceil($count / $itemsPerPage);
                $result = $this->finder->find(
                    [],
                    $all + [
                        'sortBy' => 'date',
                        'itemsPerPage' => $itemsPerPage,
                        'page' => $page,
                    ]
                );

                $this->assertNotEmpty($this->examples->dwsHomeHelpServiceChunks);
                $this->assertNotEmpty($result->list);
                $this->assertSame($itemsPerPage, $result->pagination->itemsPerPage);
                $this->assertSame($page, $result->pagination->page);
                $this->assertSame($pages, $result->pagination->pages);
                $this->assertSame($count, $result->pagination->count);
            },
            [
                'examples' => [
                    'all is not given' => [
                        [],
                    ],
                    'all is false' => [
                        ['all' => false],
                    ],
                    'all is 0' => [
                        ['all' => 0],
                    ],
                ],
            ]
        );
        $this->should(
            'return a FinderResult with only one page when param `all` given and truthy',
            function ($all): void {
                $itemsPerPage = 2;
                $page = 2;
                $count = count($this->examples->dwsHomeHelpServiceChunks);
                $result = $this->finder->find(
                    [],
                    $all + [
                        'sortBy' => 'id',
                        'itemsPerPage' => $itemsPerPage,
                        'page' => $page,
                    ]
                );

                $this->assertNotEmpty($this->examples->dwsHomeHelpServiceChunks);
                $this->assertNotEmpty($result->list);
                $this->assertSame($count, $result->pagination->count);
                $this->assertSame($count, $result->pagination->itemsPerPage);
                $this->assertSame(1, $result->pagination->page);
                $this->assertSame(1, $result->pagination->pages);
            },
            [
                'examples' => [
                    'all is true' => [
                        ['all' => true],
                    ],
                    'all is 1' => [
                        ['all' => 1],
                    ],
                ],
            ]
        );
        $this->should(
            'return a FindResult with specified filter params',
            function (array $filter, Closure $f): void {
                $this->assertExists($this->examples->dwsHomeHelpServiceChunks, $this->invert($f));
                $finderResult = $this->finder->find(
                    $filter,
                    [
                        'all' => true,
                        'sortBy' => 'id',
                    ]
                );

                $this->assertNotEmpty($finderResult);
                $this->assertNotEmpty($finderResult->list);
                $this->assertForAll($finderResult->list, $f);
            },
            [
                'examples' => [
                    'userId specified' => [
                        ['userId' => $this->examples->users[1]->id],
                        fn (DwsHomeHelpServiceChunk $x): bool => $x->userId === $this->examples->users[1]->id,
                    ],
                    'category specified' => [
                        ['category' => DwsServiceCodeCategory::housework()],
                        fn (DwsHomeHelpServiceChunk $x): bool => $x->category === DwsServiceCodeCategory::housework(),
                    ],
                    'buildingType' => [
                        ['buildingType' => DwsHomeHelpServiceBuildingType::over20()],
                        fn (DwsHomeHelpServiceChunk $x): bool => $x->buildingType === DwsHomeHelpServiceBuildingType::over20(),
                    ],
                    'isEmergency' => [
                        ['isEmergency' => false],
                        fn (DwsHomeHelpServiceChunk $x): bool => !$x->isEmergency,
                    ],
                    'isPlannedByNovice' => [
                        ['isPlannedByNovice' => false],
                        fn (DwsHomeHelpServiceChunk $x): bool => !$x->isPlannedByNovice,
                    ],
                    'rangeStartBefore' => [
                        ['rangeStartBefore' => $this->examples->dwsHomeHelpServiceChunks[1]->range->start->addHour()],
                        fn (DwsHomeHelpServiceChunk $x): bool => $x->range->start <= $this->examples->dwsHomeHelpServiceChunks[1]->range->start->addHour(),
                    ],
                    'rangeEndAfter' => [
                        ['rangeEndAfter' => $this->examples->dwsHomeHelpServiceChunks[1]->range->end->subHour()],
                        fn (DwsHomeHelpServiceChunk $x): bool => $x->range->end > $this->examples->dwsHomeHelpServiceChunks[1]->range->end->subHour(),
                    ],
                    'isFirst' => [
                        ['isFirst' => false],
                        fn (DwsHomeHelpServiceChunk $x): bool => !$x->isFirst,
                    ],
                    'isWelfareSpecialistCooperation' => [
                        ['isWelfareSpecialistCooperation' => false],
                        fn (DwsHomeHelpServiceChunk $x): bool => !$x->isWelfareSpecialistCooperation,
                    ],
                ],
            ],
        );
        $this->should(
            'return a FinderResult of non-filtered DwsHomeHelpServiceChunks when filter param is unsupported',
            function (): void {
                $result = $this->finder->find(
                    ['filter' => 'value'],
                    ['sortBy' => 'date']
                );

                $this->assertInstanceOf(FinderResult::class, $result);
                $this->assertInstanceOf(Seq::class, $result->list);
                $this->assertInstanceOf(Pagination::class, $result->pagination);
                $this->assertNotEmpty($result->list);
                $this->assertCount(count($this->examples->dwsHomeHelpServiceChunks), $result->list);
            }
        );
    }
}

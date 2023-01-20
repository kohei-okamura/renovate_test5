<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Infrastructure\Billing;

use Domain\Billing\DwsVisitingCareForPwsdChunk as Chunk;
use Domain\Common\Carbon;
use Domain\Common\Pagination;
use Domain\Entity;
use Domain\FinderResult;
use Domain\ServiceCodeDictionary\DwsServiceCodeCategory;
use Infrastructure\Billing\DwsVisitingCareForPwsdChunkFinderEloquentImpl;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Fixtures\DwsVisitingCareForPwsdChunkFixture;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\DatabaseMixin;
use Tests\Unit\Test;

/**
 * {@link \Infrastructure\Billing\DwsVisitingCareForPwsdChunkFinderEloquentImpl} Test.
 */
final class DwsVisitingCareForPwsdChunkFinderEloquentImplTest extends Test
{
    use DatabaseMixin;
    use DwsVisitingCareForPwsdChunkFixture;
    use ExamplesConsumer;
    use UnitSupport;

    private DwsVisitingCareForPwsdChunkFinderEloquentImpl $finder;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachTest(function (DwsVisitingCareForPwsdChunkFinderEloquentImplTest $self): void {
            // TODO: DEV-3849 temporary への exampleデータのセットをFixtureで行うとe2eテストで失敗する
            $self->createDwsVisitingCareForPwsdChunk();
            $self->finder = app(DwsVisitingCareForPwsdChunkFinderEloquentImpl::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_find(): void
    {
        $this->should('return a FinderResult of DwsVisitingCareForPwsdChunk', function (): void {
            $actual = $this->finder->find([], ['sortBy' => 'date']);

            $this->assertInstanceOf(FinderResult::class, $actual);
            $this->assertInstanceOf(Seq::class, $actual->list);
            $this->assertInstanceOf(Pagination::class, $actual->pagination);
            $this->assertNotEmpty($actual->list);
            $this->assertForAll($actual->list, fn (Entity $x): bool => $x instanceof Chunk);
        });
        $this->should(
            'return a paginated FinderResult when param `all` not given or falsy',
            function ($all): void {
                $itemsPerPage = 2;
                $page = 2;
                $count = count($this->examples->dwsVisitingCareForPwsdChunks);
                $pages = (int)ceil($count / $itemsPerPage);
                $actual = $this->finder->find(
                    [],
                    $all + [
                        'sortBy' => 'date',
                        'itemsPerPage' => $itemsPerPage,
                        'page' => $page,
                    ]
                );

                $this->assertNotEmpty($this->examples->dwsVisitingCareForPwsdChunks);
                $this->assertNotEmpty($actual->list);
                $this->assertSame($itemsPerPage, $actual->pagination->itemsPerPage);
                $this->assertSame($page, $actual->pagination->page);
                $this->assertSame($pages, $actual->pagination->pages);
                $this->assertSame($count, $actual->pagination->count);
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
                $count = count($this->examples->dwsVisitingCareForPwsdChunks);
                $actual = $this->finder->find(
                    [],
                    $all + [
                        'sortBy' => 'id',
                        'itemsPerPage' => $itemsPerPage,
                        'page' => $page,
                    ]
                );

                $this->assertNotEmpty($this->examples->dwsVisitingCareForPwsdChunks);
                $this->assertNotEmpty($actual->list);
                $this->assertSame($count, $actual->pagination->count);
                $this->assertSame($count, $actual->pagination->itemsPerPage);
                $this->assertSame(1, $actual->pagination->page);
                $this->assertSame(1, $actual->pagination->pages);
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
            function ($filter, $assert): void {
                $actual = $this->finder->find(
                    $filter,
                    [
                        'all' => true,
                        'sortBy' => 'id',
                    ]
                );

                $this->assertExists($this->examples->dwsVisitingCareForPwsdChunks, $this->invert($assert));
                $this->assertNotEmpty($actual);
                $this->assertNotEmpty($actual->list);
                $this->assertForAll($actual->list, $assert);
            },
            [
                'examples' => [
                    'userId specified' => [
                        ['userId' => $this->examples->users[1]->id],
                        fn (Chunk $x): bool => $x->userId === $this->examples->users[1]->id,
                    ],
                    'category specified' => [
                        ['category' => DwsServiceCodeCategory::visitingCareForPwsd1()],
                        fn (Chunk $x): bool => $x->category === DwsServiceCodeCategory::visitingCareForPwsd1(),
                    ],
                    'providedOn specified' => [
                        ['providedOn' => Carbon::create(2021, 2, 10)],
                        fn (Chunk $x): bool => $x->providedOn->eq(Carbon::create(2021, 2, 10)),
                    ],
                ],
            ],
        );
        $this->should(
            'return a FinderResult of non-filtered DwsVisitingCareForPwsdChunks when filter param is unsupported',
            function (): void {
                $actual = $this->finder->find(
                    ['filter' => 'value'],
                    ['sortBy' => 'date']
                );

                $this->assertInstanceOf(FinderResult::class, $actual);
                $this->assertInstanceOf(Seq::class, $actual->list);
                $this->assertInstanceOf(Pagination::class, $actual->pagination);
                $this->assertNotEmpty($actual->list);
                $this->assertCount(count($this->examples->dwsVisitingCareForPwsdChunks), $actual->list);
            }
        );
    }
}

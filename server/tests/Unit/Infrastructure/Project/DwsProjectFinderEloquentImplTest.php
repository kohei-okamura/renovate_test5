<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Infrastructure\Project;

use Closure;
use Domain\Common\Carbon;
use Domain\Common\Pagination;
use Domain\FinderResult;
use Domain\Project\DwsProject;
use Infrastructure\Project\DwsProjectFinderEloquentImpl;
use Lib\Exceptions\InvalidArgumentException;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\DatabaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;

/**
 * DwsProjectFinderEloquentImpl のテスト
 */
class DwsProjectFinderEloquentImplTest extends Test
{
    use DatabaseMixin;
    use ExamplesConsumer;
    use MockeryMixin;
    use UnitSupport;

    private DwsProjectFinderEloquentImpl $finder;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachTest(function (DwsProjectFinderEloquentImplTest $self): void {
            $self->finder = app(DwsProjectFinderEloquentImpl::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_find(): void
    {
        $this->should('return FinderResult on DwsProject', function (): void {
            $result = $this->finder->find([], ['sortBy' => 'date']);

            $this->assertInstanceOf(FinderResult::class, $result);
            $this->assertInstanceOf(Seq::class, $result->list);
            $this->assertInstanceOf(Pagination::class, $result->pagination);
            $this->assertNotEmpty($result->list);
            foreach ($result->list as $item) {
                $this->assertInstanceOf(DwsProject::class, $item);
            }
        });

        $this->should('return DwsProject Entity', function (): void {
            $result = $this->finder->find([], ['sortBy' => 'id', 'itemsPerPage' => 1, 'desc' => false]);

            $this->assertCount(1, $result->list);
            $this->assertModelStrictEquals($this->examples->dwsProjects[0], $result->list->head());
        });

        $this->should('return FinderResult with sorted list', function (string $key, Closure $sortBy): void {
            $expects = Seq::fromArray($this->examples->dwsProjects)
                ->sortBy($sortBy);

            $result = $this->finder->find([], ['sortBy' => $key, 'desc' => false, 'all' => true]);

            $this->assertNotEmpty($result->list);
            $this->assertArrayStrictEquals($expects->toArray(), $result->list->toArray());
        }, [
            'examples' => [
                'sortBy date' => [
                    'date',
                    fn (DwsProject $x): Carbon => $x->createdAt,
                ],
                'sortBy id' => [
                    'id',
                    fn (DwsProject $x): int => $x->id,
                ],
            ],
        ]);

        $this->should(
            'return a FinderResult with only one page when param `all` given and truthy',
            function ($all): void {
                $itemsPerPage = 2;
                $page = 2;
                $count = count($this->examples->dwsProjects);
                $result = $this->finder->find(
                    [],
                    $all + [
                        'sortBy' => 'date',
                        'itemsPerPage' => $itemsPerPage,
                        'page' => $page,
                    ]
                );

                $this->assertNotEmpty($this->examples->dwsProjects);
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
            function ($filter, $f): void {
                $result = $this->finder->find(
                    $filter,
                    [
                        'all' => true,
                        'sortBy' => 'date',
                    ]
                );

                $this->assertNotEmpty($result);
                $this->assertNotEmpty($result->list);
                $this->assertForAll($result->list, $f);
            },
            [
                'examples' => [
                    'organizationId specified' => [
                        ['organizationId' => $this->examples->organizations[0]->id],
                        fn (DwsProject $x): bool => $x->organizationId === $this->examples->organizations[0]->id,
                    ],
                    'keyword specified' => [
                        ['q' => 'A'],
                        fn ($x) => true, // 処理は何もしない
                    ],
                ],
            ]
        );

        $this->should(
            'return a FinderResult of DwsProjects with given `userId`',
            function (): void {
                $result = $this->finder->find(
                    ['userId' => $this->examples->users[0]->id],
                    [
                        'all' => true,
                        'sortBy' => 'date',
                    ]
                );

                $this->assertNotEmpty($this->examples->dwsProjects);
                $this->assertNotEmpty($result->list);
                $this->assertForAll(
                    $result->list,
                    fn (DwsProject $x): bool => $x->userId === $this->examples->users[0]->id
                );
                $this->assertExists(
                    $this->examples->dwsProjects,
                    fn (DwsProject $x): bool => $x->userId === $this->examples->users[0]->id
                );
            }
        );

        $this->should(
            'return a FinderResult of DwsProjects with given `officeIds`',
            function (): void {
                $officeIds = [
                    $this->examples->offices[0]->id,
                    $this->examples->offices[1]->id,
                ];
                $result = $this->finder->find(
                    ['officeIds' => $officeIds],
                    [
                        'all' => true,
                        'sortBy' => 'id',
                    ]
                );

                $this->assertNotEmpty($result);
                $this->assertNotEmpty($result->list);
                $this->assertForAll(
                    $result->list,
                    function (DwsProject $x) use ($officeIds): bool {
                        return in_array($x->officeId, $officeIds, true);
                    }
                );
                $this->assertTrue(Seq::fromArray($this->examples->dwsProjects)->exists(
                    fn (DwsProject $x): bool => !in_array($x->officeId, $officeIds, true)
                ));
            }
        );

        $this->should(
            'throw InvalidArgumentException when `sortBy` not given or empty',
            function (): void {
                $this->assertThrows(
                    InvalidArgumentException::class,
                    function (): void {
                        $this->finder->find(
                            [],
                            ['all' => true]
                        );
                    }
                );
            }
        );
    }
}

<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Infrastructure\DwsCertification;

use Closure;
use Domain\Common\Carbon;
use Domain\Common\Pagination;
use Domain\DwsCertification\DwsCertification;
use Domain\DwsCertification\DwsCertificationStatus;
use Domain\FinderResult;
use Infrastructure\DwsCertification\DwsCertificationFinderEloquentImpl;
use Lib\Exceptions\InvalidArgumentException;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\DatabaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;

/**
 * {@link \Infrastructure\DwsCertification\DwsCertificationFinderEloquentImpl} のテスト.
 */
final class DwsCertificationFinderEloquentImplTest extends Test
{
    use CarbonMixin;
    use DatabaseMixin;
    use ExamplesConsumer;
    use MockeryMixin;
    use UnitSupport;

    private DwsCertificationFinderEloquentImpl $finder;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachTest(function (DwsCertificationFinderEloquentImplTest $self): void {
            $self->finder = app(DwsCertificationFinderEloquentImpl::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_find(): void
    {
        $this->should('return a FinderResult of DwsCertification', function (): void {
            $result = $this->finder->find([], ['sortBy' => 'date']);

            $this->assertInstanceOf(FinderResult::class, $result);
            $this->assertInstanceOf(Seq::class, $result->list);
            $this->assertInstanceOf(Pagination::class, $result->pagination);
            $this->assertNotEmpty($result->list);
            foreach ($result->list as $item) {
                $this->assertInstanceOf(DwsCertification::class, $item);
            }
        });
        $this->should('return Entity of DwsCertification', function (): void {
            $result = $this->finder->find([], ['sortBy' => 'id', 'desc' => false, 'itemsPerPage' => 1]);

            $this->assertCount(1, $result->list);
            $this->assertModelStrictEquals($this->examples->dwsCertifications[0], $result->list->head());
        });
        $this->should(
            'return a paginated FinderResult when param `all` not given or falsy',
            function ($all): void {
                $itemsPerPage = 2;
                $page = 1;
                $count = count($this->examples->dwsCertifications);
                $pages = (int)ceil($count / $itemsPerPage);
                $result = $this->finder->find(
                    [],
                    $all + [
                        'sortBy' => 'date',
                        'itemsPerPage' => $itemsPerPage,
                        'page' => $page,
                    ]
                );

                $this->assertNotEmpty($this->examples->dwsCertifications);
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
                $page = 3;
                $count = count($this->examples->dwsCertifications);
                $result = $this->finder->find(
                    [],
                    $all + [
                        'sortBy' => 'date',
                        'itemsPerPage' => $itemsPerPage,
                        'page' => $page,
                    ]
                );

                $this->assertNotEmpty($this->examples->dwsCertifications);
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
                $this->assertExists($this->examples->dwsCertifications, $this->invert($f));
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
                    'activateOnBefore specified' => [
                        ['activatedOnBefore' => Carbon::create(2020, 11, 1)],
                        fn (DwsCertification $x): bool => $x->activatedOn <= Carbon::create(2020, 11, 1),
                    ],
                    'deactivateOnAfter specified' => [
                        ['deactivatedOnAfter' => Carbon::create(2020, 11, 1)],
                        fn (DwsCertification $x): bool => $x->deactivatedOn >= Carbon::create(2020, 11, 1),
                    ],
                    'effectivatedBefore specified' => [
                        ['effectivatedBefore' => Carbon::create(2021, 2, 10)],
                        fn (DwsCertification $x): bool => $x->effectivatedOn <= Carbon::create(2021, 2, 10),
                    ],
                    'userIds specified' => [
                        ['userIds' => [$this->examples->users[2]->id]],
                        fn (DwsCertification $x): bool => $x->userId === $this->examples->users[2]->id,
                    ],
                    'status specified' => [
                        ['status' => DwsCertificationStatus::approved()],
                        fn (DwsCertification $x): bool => $x->status === DwsCertificationStatus::approved(),
                    ],
                    'userId specified' => [
                        ['userId' => $this->examples->users[2]->id],
                        fn (DwsCertification $x): bool => $x->userId === $this->examples->users[2]->id,
                    ],
                ],
            ]
        );
        $this->should('return all examples data with unsupported filter param.', function () {
            $result = $this->finder->find(
                ['q' => 'A'],
                ['all' => true, 'sortBy' => 'id'],
            );

            $this->assertNotEmpty($result);
            $this->assertNotEmpty($result->list);
            $expected = Seq::fromArray($this->examples->dwsCertifications)
                ->sortBy(fn (DwsCertification $x): int => $x->id)->toArray();
            $this->assertArrayStrictEquals(
                $expected,
                $result->list->toArray()
            );
        });
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
        $this->should(
            'sort DwsCertifications using given param `sortBy` and `desc`',
            function (): void {
                $expected = Seq::fromArray($this->examples->dwsCertifications)
                    ->filter(function (DwsCertification $dwsCertification): bool {
                        return $dwsCertification->userId === $this->examples->users[3]->id;
                    })
                    ->sortBy(fn (DwsCertification $dwsCertification): int => $dwsCertification->createdAt->unix())
                    ->reverse()
                    ->toArray();
                $paginationParams = [
                    'all' => true,
                    'desc' => true,
                    'sortBy' => 'date',
                ];
                $filterParams = ['userId' => $this->examples->users[3]->id];
                $actual = $this->finder->find($filterParams, $paginationParams);
                $this->assertEach(
                    function ($a, $b) {
                        $this->assertModelStrictEquals($a, $b);
                    },
                    $expected,
                    $actual->list->toArray()
                );
            }
        );
        $this->should('return a FinderResult of dwsCertifications with given `userId`', function (): void {
            $result = $this->finder->find(['userId' => $this->examples->users[3]->id], ['sortBy' => 'date']);

            $this->assertNotEmpty($result->list);
            $this->assertForAll(
                $result->list,
                fn (DwsCertification $x): bool => $x->userId === $this->examples->users[3]->id
            );
            $this->assertExists(
                $this->examples->dwsCertifications,
                fn (DwsCertification $x): bool => $x->userId !== $this->examples->users[3]->id
            );
        });
        $this->should('return FinderResult with sorted list', function (string $key, Closure $sortBy): void {
            $sortKey1 = [];
            $sortKey2 = [];
            $expects = $this->examples->dwsCertifications;
            foreach ($expects as $value) {
                $sortKey1[] = $sortBy($value);
                $sortKey2[] = $value->id;
            }
            // sortBy に使用した値が同じだった場合の並び順を保証するために id を第2キーにする（finder の戻り値が降順だったのであわせる）
            array_multisort($sortKey1, \SORT_ASC, \SORT_REGULAR, $sortKey2, \SORT_ASC, \SORT_NUMERIC, $expects);

            $result = $this->finder->find([], ['sortBy' => $key, 'desc' => false, 'all' => true]);

            $this->assertNotEmpty($result->list);
            $this->assertArrayStrictEquals($expects, $result->list->toArray());
        }, [
            'examples' => [
                'sortBy updatedAt' => [
                    'updatedAt',
                    fn (DwsCertification $x): Carbon => $x->updatedAt,
                ],
                'sortBy effectivatedOn' => [
                    'effectivatedOn',
                    fn (DwsCertification $x): Carbon => $x->effectivatedOn,
                ],
                'sortBy id' => [
                    'id',
                    fn (DwsCertification $x): int => $x->id,
                ],
            ],
        ]);
    }
}

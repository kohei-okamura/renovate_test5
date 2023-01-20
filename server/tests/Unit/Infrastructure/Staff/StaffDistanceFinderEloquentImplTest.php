<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Infrastructure\Staff;

use Domain\Common\Distance;
use Domain\Common\Location;
use Domain\Common\Pagination;
use Domain\Common\Sex;
use Domain\FinderResult;
use Illuminate\Database\Eloquent\Collection;
use Infrastructure\Staff\Staff as InfrastructureStaff;
use Infrastructure\Staff\StaffDistanceFinderEloquentImpl;
use Lib\Exceptions\InvalidArgumentException;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\DatabaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\StaffFinderMixin;
use Tests\Unit\Test;

/**
 * StaffDistanceFinderEloquentImpl のテスト.
 */
class StaffDistanceFinderEloquentImplTest extends Test
{
    use DatabaseMixin;
    use ExamplesConsumer;
    use MockeryMixin;
    use StaffFinderMixin;
    use UnitSupport;

    /**
     * デフォルトのSRIDを定義.
     *
     * @link https://qiita.com/boiledorange73/items/b98d3d1ef3abf7299aba
     */
    protected const DEFAULT_SRID = 4326;

    private StaffDistanceFinderEloquentImpl $finder;

    private Seq $staffDistances;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachTest(function (StaffDistanceFinderEloquentImplTest $self): void {
            $self->staffDistances = $self->makeStaffDistances($self->examples->users[15]->location);
            $self->finder = app(StaffDistanceFinderEloquentImpl::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_find(): void
    {
        $this->should('throw an exception when argument is invalid', function (): void {
            $this->assertThrows(
                InvalidArgumentException::class,
                function (): void {
                    $this->finder->find(
                        [],
                        ['sortBy' => 'distance']
                    );
                }
            );
            $this->assertThrows(
                InvalidArgumentException::class,
                function (): void {
                    $this->finder->find(
                        [
                            'location' => '',
                            'range' => 20000,
                        ],
                        ['sortBy' => 'distance']
                    );
                }
            );
            $this->assertThrows(
                InvalidArgumentException::class,
                function (): void {
                    $this->finder->find(
                        [
                            'location' => $this->examples->users[15]->location,
                            'range' => null,
                        ],
                        ['sortBy' => 'distance']
                    );
                }
            );
        });
        $this->should('return a FinderResult of Distance', function (): void {
            $result = $this->finder->find(
                [
                    'location' => $this->examples->users[15]->location,
                    'range' => 10000,
                ],
                ['sortBy' => 'distance']
            );

            $this->assertInstanceOf(FinderResult::class, $result);
            $this->assertInstanceOf(Seq::class, $result->list);
            $this->assertInstanceOf(Pagination::class, $result->pagination);
            $this->assertNotEmpty($result->list);
            foreach ($result->list as $item) {
                $this->assertInstanceOf(Distance::class, $item);
            }
        });
        $this->should(
            'return a paginated FinderResult when param `all` not given or falsy',
            function ($all): void {
                $itemsPerPage = 2;
                $page = 3;
                $range = 10000;
                $count = $this->countStaffWithinRange($range);
                $pages = (int)ceil($count / $itemsPerPage);
                $result = $this->finder->find(
                    [
                        'location' => $this->examples->users[15]->location,
                        'range' => $range,
                    ],
                    $all + [
                        'sortBy' => 'distance',
                        'itemsPerPage' => $itemsPerPage,
                        'page' => $page,
                    ]
                );

                $this->assertNotEmpty($this->staffDistances);
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
                $range = 10000;
                $count = $this->countStaffWithinRange($range);
                $result = $this->finder->find(
                    [
                        'location' => $this->examples->users[15]->location,
                        'range' => $range,
                    ],
                    $all + [
                        'sortBy' => 'distance',
                        'itemsPerPage' => $itemsPerPage,
                        'page' => $page,
                    ]
                );

                $this->assertNotEmpty($this->staffDistances);
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
            'return a FinderResult of Distance with given `organizationId`',
            function (): void {
                $organizationId = $this->examples->organizations[0]->id;
                $result = $this->finder->find(
                    [
                        'location' => $this->examples->users[15]->location,
                        'organizationId' => $organizationId,
                        'range' => 10000,
                    ],
                    [
                        'all' => true,
                        'sortBy' => 'distance',
                    ]
                );

                $this->assertNotEmpty($this->staffDistances);
                $this->assertNotEmpty($result->list);
                $this->assertForAll(
                    $result->list,
                    fn (Distance $x): bool => $x->destination->organizationId === $organizationId
                );
                $this->assertExists(
                    $this->staffDistances,
                    fn (Distance $x): bool => $x->destination->organizationId !== $organizationId
                );
            }
        );
        $this->should(
            'return a FinderResult of Distance with given `sex`',
            function (): void {
                $sex = Sex::male()->value();
                $result = $this->finder->find(
                    [
                        'location' => $this->examples->users[15]->location,
                        'sex' => $sex,
                        'range' => 10000,
                    ],
                    [
                        'all' => true,
                        'sortBy' => 'distance',
                    ]
                );

                $this->assertNotEmpty($this->staffDistances);
                $this->assertNotEmpty($result->list);
                $this->assertForAll(
                    $result->list,
                    fn (Distance $x): bool => $x->destination->sex->value() === $sex
                );
                $this->assertExists(
                    $this->staffDistances,
                    fn (Distance $x): bool => $x->destination->sex->value() !== $sex
                );
            }
        );
        $this->should(
            'return a FinderResult of Distance fit to `range`',
            function (): void {
                $range = 500;
                $result = $this->finder->find(
                    [
                        'location' => $this->examples->users[15]->location,
                        'range' => $range,
                    ],
                    [
                        'all' => true,
                        'sortBy' => 'distance',
                    ]
                );

                $this->assertNotEmpty($this->staffDistances);
                $this->assertNotEmpty($result->list);
                $this->assertForAll(
                    $result->list,
                    fn (Distance $x): bool => $x->distance <= $range
                );
                $this->assertExists(
                    $this->staffDistances,
                    fn (Distance $x): bool => $x->distance > $range
                );
            }
        );
        $this->should(
            'set condition for all of given params when multiple params given',
            function (): void {
                $filterParams = [
                    'location' => $this->examples->users[15]->location,
                    'organizationId' => $this->examples->organizations[0]->id,
                    'sex' => Sex::male(),
                    'range' => 20000,
                ];
                $result = $this->finder->find(
                    $filterParams,
                    [
                        'all' => true,
                        'sortBy' => 'distance',
                    ]
                );
                // 狙いのデータが取れていること
                $this->assertForAll(
                    $result->list,
                    fn (Distance $x) => $x->destination->organizationId === $filterParams['organizationId']
                        && $x->destination->sex === $filterParams['sex']
                        && $x->distance <= $filterParams['range']
                );
                // フィルタされたデータが元から存在していること
                $this->assertExists(
                    $this->staffDistances,
                    fn (Distance $x) => $x->destination->organizationId !== $filterParams['organizationId']
                        || $x->destination->sex !== $filterParams['sex']
                        || $x->distance > $filterParams['range']
                );
                // フィルタされていない（狙いのデータ）が元から存在していること
                $this->assertExists(
                    $this->staffDistances,
                    fn (Distance $x) => $x->destination->organizationId === $filterParams['organizationId']
                        && $x->destination->sex === $filterParams['sex']
                        && $x->distance <= $filterParams['range']
                );
            }
        );
        $this->should(
            'throw InvalidArgumentException when `sortBy` not given or empty',
            function (): void {
                $filterParams = [
                    'location' => $this->examples->users[15]->location,
                    'organizationId' => $this->examples->organizations[0]->id,
                    'sex' => Sex::male(),
                    'range' => 20000,
                ];
                $this->assertThrows(
                    InvalidArgumentException::class,
                    function () use ($filterParams): void {
                        $this->finder->find($filterParams, ['all' => true]);
                    }
                );
                $this->assertThrows(
                    InvalidArgumentException::class,
                    function () use ($filterParams): void {
                        $this->finder->find($filterParams, ['all' => true, 'sortBy' => '']);
                    }
                );
            }
        );
        $this->should(
            'sort Distances using given param `sortBy` and `desc`',
            function (): void {
                $range = 20000;
                $expected = Seq::fromArray($this->staffDistances)
                    ->filter(fn (Distance $distance) => $distance->distance <= $range)
                    ->sortBy(fn (Distance $distance) => $distance->distance)
                    ->reverse()
                    ->toArray();

                $filterParams = [
                    'location' => $this->examples->users[15]->location,
                    'range' => $range,
                ];
                $paginationParams = [
                    'all' => true,
                    'desc' => true,
                    'sortBy' => 'distance',
                ];
                $result = $this->finder->find($filterParams, $paginationParams);
                foreach ($result->list as $index => $distance) {
                    $this->assertModelStrictEquals($expected[$index], $distance);
                }
            }
        );
    }

    /**
     * スタッフの距離情報一覧を生成する.
     *
     * @param \Domain\Common\Location $location
     * @return \ScalikePHP\Seq
     */
    private function makeStaffDistances(Location $location): Seq
    {
        $distances = $this->fetchDistances($location);
        return Seq::fromArray($distances->map(fn (InfrastructureStaff $x): Distance => Distance::create([
            'distance' => $x['distance'],
            'destination' => $x->toDomain(),
        ])));
    }

    /**
     * 距離を取得する.
     *
     * @param \Domain\Common\Location $location
     * @return \Illuminate\Database\Eloquent\Collection
     */
    private function fetchDistances(Location $location): Collection
    {
        $query = InfrastructureStaff::query()
            ->with('attr')
            ->join('staff_to_attr', 'staff_to_attr.staff_id', '=', 'staff.id')
            ->join('staff_attr', 'staff_attr.id', '=', 'staff_to_attr.staff_attr_id');
        $bindValues = [sprintf('POINT(%f %f)', $location->lat, $location->lng), self::DEFAULT_SRID];
        $select = 'ST_Distance_Sphere(ST_GeomFromText(?, ?), staff_attr.location) AS distance';
        return $query
            ->select('staff.*')
            ->selectRaw($select)
            ->addBinding($bindValues, 'select')
            ->get();
    }

    /**
     * 指定範囲に存在するスタッフをカウントする.
     *
     * @param int $range
     * @return int
     */
    private function countStaffWithinRange($range): int
    {
        $targets = Seq::fromArray($this->staffDistances)->filter(fn (
            Distance $distance
        ) => $distance->distance <= $range);
        return count($targets);
    }
}

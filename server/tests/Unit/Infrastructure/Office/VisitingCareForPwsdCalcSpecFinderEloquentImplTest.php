<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Infrastructure\Office;

use Closure;
use Domain\Common\Carbon;
use Domain\Common\Pagination;
use Domain\FinderResult;
use Domain\Office\VisitingCareForPwsdCalcSpec;
use Infrastructure\Office\VisitingCareForPwsdCalcSpecFinderEloquentImpl;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\DatabaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;

/**
 * {@link \Infrastructure\Office\VisitingCareForPwsdCalcSpecFinderEloquentImpl} のテスト.
 */
final class VisitingCareForPwsdCalcSpecFinderEloquentImplTest extends Test
{
    use DatabaseMixin;
    use ExamplesConsumer;
    use MockeryMixin;
    use UnitSupport;

    /** @var array|\Domain\Office\VisitingCareForPwsdCalcSpec[] */
    private array $specs;
    private VisitingCareForPwsdCalcSpecFinderEloquentImpl $finder;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachTest(function (self $self): void {
            $self->specs = $self->examples->visitingCareForPwsdCalcSpecs;
        });
        self::beforeEachSpec(function (self $self): void {
            $self->finder = app(VisitingCareForPwsdCalcSpecFinderEloquentImpl::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_find(): void
    {
        $this->should('return a FinderResult of VisitingCareForPwsdCalcSpec', function (): void {
            $result = $this->finder->find([], ['sortBy' => 'date']);

            $this->assertInstanceOf(FinderResult::class, $result);
            $this->assertInstanceOf(Seq::class, $result->list);
            $this->assertInstanceOf(Pagination::class, $result->pagination);
            $this->assertNotEmpty($result->list);
            foreach ($result->list as $item) {
                $this->assertInstanceOf(VisitingCareForPwsdCalcSpec::class, $item);
            }
        });
        $this->should(
            'return a paginated FinderResult when param `all` not given or falsy',
            function ($all): void {
                $itemsPerPage = 2;
                $page = 1;
                $count = count($this->specs);
                $pages = (int)ceil($count / $itemsPerPage);
                $result = $this->finder->find(
                    [],
                    $all + [
                        'sortBy' => 'date',
                        'itemsPerPage' => $itemsPerPage,
                        'page' => $page,
                    ]
                );

                $this->assertNotEmpty($this->specs);
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
                $count = count($this->specs);
                $result = $this->finder->find(
                    [],
                    $all + [
                        'sortBy' => 'date',
                        'itemsPerPage' => $itemsPerPage,
                        'page' => $page,
                    ]
                );

                $this->assertNotEmpty($this->specs);
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
            function (array $filter, Closure $assert): void {
                $result = $this->finder->find(
                    $filter,
                    [
                        'all' => true,
                        'sortBy' => 'date',
                    ]
                );

                $this->assertNotEmpty($result);
                $this->assertNotEmpty($result->list);
                $this->assertForAll($result->list, $assert);
                $this->assertExists($this->specs, $this->invert($assert));
            },
            [
                'examples' => [
                    'officeId specified' => [
                        ['officeId' => $this->specs[2]->officeId],
                        fn (VisitingCareForPwsdCalcSpec $x): bool => $x->officeId === $this->specs[2]->officeId,
                    ],
                    'period specified' => [
                        ['period' => Carbon::create(2021, 2, 13)],
                        fn (VisitingCareForPwsdCalcSpec $x): bool => $x->period->contains(Carbon::create(2021, 2, 13)),
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
            $expected = Seq::fromArray($this->specs)
                ->sortBy(fn (VisitingCareForPwsdCalcSpec $x): int => $x->id)->toArray();
            $this->assertArrayStrictEquals(
                $expected,
                $result->list->toArray()
            );
        });
        $this->should(
            'sort VisitingCareForPwsdCalcSpecs using given param `sortBy` and `desc`',
            function (): void {
                $expected = Seq::fromArray($this->specs)
                    ->filter(fn (VisitingCareForPwsdCalcSpec $x) => $x->officeId === $this->specs[2]->officeId)
                    ->sortBy(fn (VisitingCareForPwsdCalcSpec $x) => $x->createdAt->unix())
                    ->reverse()
                    ->toArray();
                $paginationParams = [
                    'all' => true,
                    'desc' => true,
                    'sortBy' => 'date',
                ];
                $filterParams = ['officeId' => $this->specs[2]->officeId];
                $actual = $this->finder->find($filterParams, $paginationParams);
                $this->assertArrayStrictEquals(
                    $expected,
                    $actual->list->toArray()
                );
            }
        );
        $this->should(
            'sort VisitingCareForPwsdCalcSpecs using default sorting rules',
            function (): void {
                $officeId = $this->examples->offices[25]->id;
                $expected = [
                    $this->examples->visitingCareForPwsdCalcSpecs[7],
                    $this->examples->visitingCareForPwsdCalcSpecs[8],
                    $this->examples->visitingCareForPwsdCalcSpecs[5],
                    $this->examples->visitingCareForPwsdCalcSpecs[6],
                ];
                $paginationParams = ['all' => true];
                $filterParams = ['officeId' => $officeId];
                $actual = $this->finder->find($filterParams, $paginationParams);
                $this->assertArrayStrictEquals(
                    $expected,
                    $actual->list->toArray()
                );
            }
        );
    }
}

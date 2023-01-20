<?php
/**
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Infrastructure\User;

use Closure;
use Domain\Common\Carbon;
use Domain\Common\Pagination;
use Domain\FinderResult;
use Domain\User\UserLtcsCalcSpec;
use Infrastructure\User\UserLtcsCalcSpecFinderEloquentImpl;
use Lib\Exceptions\InvalidArgumentException;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\DatabaseMixin;
use Tests\Unit\Test;

/**
 * {@link \Infrastructure\User\UserLtcsCalcSpecFinderEloquentImpl} のテスト.
 */
final class UserLtcsCalcSpecFinderEloquentImplTest extends Test
{
    use CarbonMixin;
    use DatabaseMixin;
    use ExamplesConsumer;
    use UnitSupport;

    private UserLtcsCalcSpecFinderEloquentImpl $finder;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachTest(function (self $self): void {
            $self->finder = app(UserLtcsCalcSpecFinderEloquentImpl::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_find(): void
    {
        $this->should('return FinderResult on UserLtcsCalcSpec', function (): void {
            $actual = $this->finder->find([], ['sortBy' => 'date']);

            $this->assertInstanceOf(FinderResult::class, $actual);
            $this->assertInstanceOf(Seq::class, $actual->list);
            $this->assertInstanceOf(Pagination::class, $actual->pagination);
            $this->assertNotEmpty($actual->list);
            $this->assertForAll($actual->list, fn ($x): bool => $x instanceof UserLtcsCalcSpec);
        });
        $this->should(
            'return a FinderResult with only one page when param `all` given and truthy',
            function ($all): void {
                $itemsPerPage = 2;
                $page = 2;
                $count = count($this->examples->userLtcsCalcSpecs);
                $paginationParams = [
                    'sortBy' => 'date',
                    'itemsPerPage' => $itemsPerPage,
                    'page' => $page,
                ];

                $actual = $this->finder->find([], $all + $paginationParams);

                $this->assertNotEmpty($this->examples->userLtcsCalcSpecs);
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
            function (array $filterParams, Closure $assert): void {
                $paginationParams = [
                    'all' => true,
                    'sortBy' => 'date',
                ];

                $actual = $this->finder->find($filterParams, $paginationParams);

                $this->assertNotEmpty($actual);
                $this->assertNotEmpty($actual->list);
                $this->assertForAll($actual->list, $assert);
                $this->assertExists($this->examples->userLtcsCalcSpecs, $this->invert($assert));
            },
            [
                'examples' => [
                    'userId specified' => [
                        ['userId' => $this->examples->users[0]->id],
                        fn (UserLtcsCalcSpec $x): bool => $x->userId === $this->examples->users[0]->id,
                    ],
                    'effectivatedOnBefore specified' => [
                        ['effectivatedOnBefore' => Carbon::create(2020, 2, 10)],
                        fn (UserLtcsCalcSpec $x): bool => $x->effectivatedOn->lte(Carbon::create(2020, 2, 10)),
                    ],
                ],
            ]
        );
        $this->should(
            'return ordered list specified `sortBy` params',
            function (string $key, string $parameter): void {
                $expectedList = Seq::fromArray($this->examples->userLtcsCalcSpecs)
                    ->sortBy(fn (UserLtcsCalcSpec $x): Carbon => $x->{$parameter});

                $actual = $this->finder->find([], ['all' => true, 'sortBy' => $key]);

                $this->assertNotEmpty($actual);
                $this->assertNotEmpty($actual->list);
                $this->assertArrayStrictEquals($expectedList->toArray(), $actual->list->toArray());
            },
            [
                'examples' => [
                    'sort by `date`' => ['date', 'createdAt'],
                    'sort by `effectivatedOn`' => ['effectivatedOn', 'effectivatedOn'],
                    'sort by `updatedAt`' => ['updatedAt', 'updatedAt'],
                ],
            ]
        );
        $this->should('throw InvalidArgumentException when `sortBy` not given or empty', function (): void {
            $this->assertThrows(InvalidArgumentException::class, function (): void {
                $this->finder->find([], ['all' => true]);
            });
        });
    }
}

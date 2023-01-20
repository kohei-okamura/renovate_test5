<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Infrastructure\Billing;

use Closure;
use Domain\Billing\DwsBillingStatement;
use Domain\Common\Pagination;
use Domain\Entity;
use Domain\FinderResult;
use Infrastructure\Billing\DwsBillingStatementFinderEloquentImpl;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\DatabaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;

/**
 * {@link \Infrastructure\Billing\DwsBillingStatementFinderEloquentImpl} のテスト.
 */
class DwsBillingStatementFinderEloquentImplTest extends Test
{
    use DatabaseMixin;
    use ExamplesConsumer;
    use MockeryMixin;
    use UnitSupport;

    private DwsBillingStatementFinderEloquentImpl $finder;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachTest(function (DwsBillingStatementFinderEloquentImplTest $self): void {
            $self->finder = app(DwsBillingStatementFinderEloquentImpl::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_find(): void
    {
        $this->should('return a FinderResult of DwsBillingStatement', function (): void {
            $result = $this->finder->find([], ['sortBy' => 'date']);

            $this->assertInstanceOf(FinderResult::class, $result);
            $this->assertInstanceOf(Seq::class, $result->list);
            $this->assertInstanceOf(Pagination::class, $result->pagination);
            $this->assertNotEmpty($result->list);
            $this->assertForAll($result->list, fn (Entity $x): bool => $x instanceof DwsBillingStatement);
        });
        $this->should('return a FinderResult of dwsBillingStatements with invalid parameters', function (): void {
            $result = $this->finder->find(
                ['invalid' => 1],
                ['all' => true, 'sortBy' => 'id']
            );

            $this->assertNotEmpty($result->list);
            $this->assertForAll($result->list, fn (Entity $x): bool => $x instanceof DwsBillingStatement);
        });
        $this->should(
            'return a paginated FinderResult when param `all` not given or falsy',
            function ($all): void {
                $itemsPerPage = 2;
                $page = 2;
                $count = count($this->examples->dwsBillingStatements);
                $pages = (int)ceil($count / $itemsPerPage);
                $result = $this->finder->find(
                    [],
                    $all + [
                        'sortBy' => 'date',
                        'itemsPerPage' => $itemsPerPage,
                        'page' => $page,
                    ]
                );

                $this->assertNotEmpty($this->examples->dwsBillingStatements);
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
                $count = count($this->examples->dwsBillingStatements);
                $result = $this->finder->find(
                    [],
                    $all + [
                        'sortBy' => 'date',
                        'itemsPerPage' => $itemsPerPage,
                        'page' => $page,
                    ]
                );

                $this->assertNotEmpty($this->examples->dwsBillingStatements);
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
            'return a FinderResult of dwsBillingStatements with given parameters',
            function (array $filter, Closure $f): void {
                $this->assertExists(
                    $this->examples->dwsBillingStatements,
                    $this->invert($f)
                );

                $result = $this->finder->find($filter, ['sortBy' => 'date']);

                $this->assertNotEmpty($this->examples->dwsBillingStatements);
                $this->assertNotEmpty($result->list);
                $this->assertForAll($result->list, $f);
            },
            ['examples' => [
                'with `userId`' => [
                    ['userId' => $this->examples->dwsBillingStatements[0]->user->userId],
                    fn (DwsBillingStatement $dwsBillingStatement): bool => $dwsBillingStatement->user->userId === $this->examples->dwsBillingStatements[0]->user->userId,
                ],
                'with `dwsBillingBundleId`' => [
                    ['dwsBillingBundleId' => $this->examples->dwsBillingStatements[0]->dwsBillingBundleId],
                    fn (DwsBillingStatement $dwsBillingStatement): bool => $dwsBillingStatement->dwsBillingBundleId === $this->examples->dwsBillingStatements[0]->dwsBillingBundleId,
                ],
                'with `dwsBillingBundleIds`' => [
                    ['dwsBillingBundleIds' => [
                        $this->examples->dwsBillingStatements[0]->dwsBillingBundleId,
                        $this->examples->dwsBillingStatements[1]->dwsBillingBundleId,
                    ]],
                    fn (DwsBillingStatement $dwsBillingStatement): bool => in_array($dwsBillingStatement->dwsBillingBundleId, [
                        $this->examples->dwsBillingStatements[0]->dwsBillingBundleId,
                        $this->examples->dwsBillingStatements[1]->dwsBillingBundleId,
                    ], true),
                ],
                'with `dwsCertificationId`' => [
                    ['dwsCertificationId' => $this->examples->dwsBillingStatements[0]->user->dwsCertificationId],
                    fn (DwsBillingStatement $dwsBillingStatement): bool => $dwsBillingStatement->user->dwsCertificationId === $this->examples->dwsBillingStatements[0]->user->dwsCertificationId,
                ],
            ]]
        );
    }
}

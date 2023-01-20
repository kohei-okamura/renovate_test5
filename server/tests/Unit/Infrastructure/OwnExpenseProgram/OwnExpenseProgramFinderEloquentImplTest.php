<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Infrastructure\OwnExpenseProgram;

use Closure;
use Domain\Common\Pagination;
use Domain\FinderResult;
use Domain\OwnExpenseProgram\OwnExpenseProgram;
use Infrastructure\OwnExpenseProgram\OwnExpenseProgramFinderEloquentImpl;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\DatabaseMixin;
use Tests\Unit\Test;

/**
 * {@link \Infrastructure\OwnExpenseProgram\OwnExpenseProgramFinderEloquentImpl} Test.
 */
class OwnExpenseProgramFinderEloquentImplTest extends Test
{
    use DatabaseMixin;
    use ExamplesConsumer;
    use UnitSupport;

    private OwnExpenseProgramFinderEloquentImpl $finder;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachTest(function (OwnExpenseProgramFinderEloquentImplTest $self): void {
            $self->finder = app(OwnExpenseProgramFinderEloquentImpl::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_find(): void
    {
        $this->should('return a FinderResult of OwnExpenseProgram', function (): void {
            $result = $this->finder->find([], ['sortBy' => 'date']);

            $this->assertInstanceOf(FinderResult::class, $result);
            $this->assertInstanceOf(Seq::class, $result->list);
            $this->assertInstanceOf(Pagination::class, $result->pagination);
            $this->assertNotEmpty($result->list);
            foreach ($result->list as $item) {
                $this->assertInstanceOf(OwnExpenseProgram::class, $item);
            }
        });
        $this->should('return a FinderResult when itemsPerPage is 1', function (): void {
            $result = $this->finder->find([], ['sortBy' => 'id', 'itemsPerPage' => 1]);

            $this->assertCount(1, $result->list);
            $this->assertModelStrictEquals($this->examples->ownExpensePrograms[0], $result->list->head());
        });
        $this->should(
            'return a paginated FinderResult when param `all` not given or falsy',
            function ($all): void {
                $itemsPerPage = 2;
                $page = 1;
                $count = count($this->examples->ownExpensePrograms);
                $pages = (int)ceil($count / $itemsPerPage);
                $result = $this->finder->find(
                    [],
                    $all + [
                        'sortBy' => 'date',
                        'itemsPerPage' => $itemsPerPage,
                        'page' => $page,
                    ]
                );

                $this->assertNotEmpty($this->examples->ownExpensePrograms);
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
                $count = count($this->examples->ownExpensePrograms);
                $result = $this->finder->find(
                    [],
                    $all + [
                        'sortBy' => 'date',
                        'itemsPerPage' => $itemsPerPage,
                        'page' => $page,
                    ]
                );

                $this->assertNotEmpty($this->examples->ownExpensePrograms);
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
                $this->assertExists($this->examples->ownExpensePrograms, $this->invert($f));

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
                    'when officeIdOrNull' => [
                        ['officeIdOrNull' => $this->examples->offices[0]->id],
                        fn (OwnExpenseProgram $x): bool => $x->officeId === $this->examples->offices[0]->id
                            || $x->officeId === null,
                    ],
                    'when officeIds' => [
                        ['officeIds' => $this->examples->offices[0]->id],
                        fn (OwnExpenseProgram $x): bool => $x->officeId === $this->examples->offices[0]->id,
                    ],
                    'when officeIdsOrNull' => [
                        ['officeIdsOrNull' => $this->examples->offices[0]->id],
                        fn (OwnExpenseProgram $x): bool => $x->officeId === $this->examples->offices[0]->id
                            || $x->officeId === null,
                    ],
                    'when organizationId' => [
                        ['organizationId' => $this->examples->organizations[0]->id],
                        fn (OwnExpenseProgram $x): bool => $x->organizationId === $this->examples->organizations[0]->id,
                    ],
                    'when q' => [
                        ['q' => '洗濯'],
                        fn (OwnExpenseProgram $x): bool => strpos($x->name, '洗濯') !== false,
                    ],
                ],
            ],
        );
    }
}

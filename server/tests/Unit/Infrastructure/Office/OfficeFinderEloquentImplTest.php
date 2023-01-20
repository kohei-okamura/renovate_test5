<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Infrastructure\Office;

use Domain\Common\Carbon;
use Domain\Common\Pagination;
use Domain\Common\Prefecture;
use Domain\Contract\ContractStatus;
use Domain\FinderResult;
use Domain\Office\Office;
use Domain\Office\OfficeQualification;
use Domain\Office\OfficeStatus;
use Domain\Office\Purpose;
use Infrastructure\Office\OfficeFinderEloquentImpl;
use Lib\Exceptions\InvalidArgumentException;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\DatabaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;
use UnitTester;

/**
 * OfficeFinderEloquentImpl のテスト.
 */
class OfficeFinderEloquentImplTest extends Test
{
    use DatabaseMixin;
    use ExamplesConsumer;
    use MockeryMixin;
    use UnitSupport;

    protected UnitTester $tester;

    private OfficeFinderEloquentImpl $finder;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachTest(function (OfficeFinderEloquentImplTest $self): void {
            $self->finder = app(OfficeFinderEloquentImpl::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_find(): void
    {
        $this->should('return a FinderResult of Office', function (): void {
            $result = $this->finder->find([], ['sortBy' => 'name']);

            $this->assertInstanceOf(FinderResult::class, $result);
            $this->assertInstanceOf(Seq::class, $result->list);
            $this->assertInstanceOf(Pagination::class, $result->pagination);
            $this->assertNotEmpty($result->list);
            foreach ($result->list as $item) {
                $this->assertInstanceOf(Office::class, $item);
            }
        });
        $this->should('return a FinderResult when itemsPerPage is 1', function (): void {
            $result = $this->finder->find([], ['sortBy' => 'id', 'itemsPerPage' => 1]);

            $this->assertCount(1, $result->list);
            $this->assertModelStrictEquals($this->examples->offices[0], $result->list->head());
        });
        $this->should(
            'return a paginated FinderResult when param `all` not given or falsy',
            function ($all): void {
                $itemsPerPage = 2;
                $page = 3;
                $count = count($this->examples->offices);
                $pages = (int)ceil($count / $itemsPerPage);
                $result = $this->finder->find(
                    [],
                    $all + [
                        'sortBy' => 'name',
                        'itemsPerPage' => $itemsPerPage,
                        'page' => $page,
                    ]
                );

                $this->assertNotEmpty($this->examples->offices);
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
                $count = count($this->examples->offices);
                $result = $this->finder->find(
                    [],
                    $all + [
                        'sortBy' => 'name',
                        'itemsPerPage' => $itemsPerPage,
                        'page' => $page,
                    ]
                );

                $this->assertNotEmpty($this->examples->offices);
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
            'return a FinderResult of Offices with given `organizationId`',
            function (): void {
                $result = $this->finder->find(
                    ['organizationId' => $this->examples->organizations[0]->id],
                    [
                        'all' => true,
                        'sortBy' => 'name',
                    ]
                );

                $this->assertNotEmpty($this->examples->offices);
                $this->assertNotEmpty($result->list);
                $this->assertForAll(
                    $result->list,
                    fn (Office $x): bool => $x->organizationId === $this->examples->organizations[0]->id
                );
                $this->assertExists(
                    $this->examples->offices,
                    fn (Office $x): bool => $x->organizationId !== $this->examples->organizations[0]->id
                );
            }
        );
        $this->should(
            'return a FinderResult of Offices with given `organizationId` and empty `q`',
            function (): void {
                $result = $this->finder->find(
                    [
                        'organizationId' => $this->examples->organizations[0]->id,
                        'q' => '',
                    ],
                    [
                        'all' => true,
                        'sortBy' => 'name',
                    ]
                );

                $this->assertNotEmpty($this->examples->offices);
                $this->assertNotEmpty($result->list);
                $this->assertForAll(
                    $result->list,
                    fn (Office $x): bool => $x->organizationId === $this->examples->organizations[0]->id
                );
                $this->assertExists(
                    $this->examples->offices,
                    fn (Office $x): bool => $x->organizationId !== $this->examples->organizations[0]->id
                );
            }
        );
        $this->should(
            'return a FinderResult of Offices with given `organizationId` and invalid keyword',
            function (): void {
                $result = $this->finder->find(
                    [
                        'organizationId' => $this->examples->organizations[0]->id,
                        'invalid' => 'invalid',
                    ],
                    [
                        'all' => true,
                        'sortBy' => 'name',
                    ]
                );

                $this->assertNotEmpty($this->examples->offices);
                $this->assertNotEmpty($result->list);
                $this->assertForAll(
                    $result->list,
                    fn (Office $x): bool => $x->organizationId === $this->examples->organizations[0]->id
                );
                $this->assertExists(
                    $this->examples->offices,
                    fn (Office $x): bool => $x->organizationId !== $this->examples->organizations[0]->id
                );
            }
        );
        $this->should(
            'return a FinderResult of users with name that matches given `q`',
            function ($name): void {
                $result = $this->finder->find(
                    ['q' => $name],
                    [
                        'all' => true,
                        'sortBy' => 'name',
                    ]
                );

                $nameContains = fn (Office $offices, string $needle): bool => strpos($offices->name, $needle) !== false;
                $nameNotContains = fn (Office $offices, string $needle): bool => $nameContains($offices, $needle) === false;
                $this->assertNotEmpty($this->examples->offices);
                $this->assertNotEmpty($result->list);
                $this->assertForAll(
                    $result->list,
                    fn (Office $x): bool => $nameContains($x, $name)
                );
                $this->assertTrue(
                    Seq::fromArray($this->examples->offices)->exists(
                        fn (Office $x): bool => $nameNotContains($x, $name)
                    )
                );
            },
            [
                'examples' => [
                    'name given' => ['事業所テスト'],
                ],
            ]
        );
        $this->should(
            'return a FinderResult of users with abbr that matches given `q`',
            function ($abbr): void {
                $result = $this->finder->find(
                    ['q' => $abbr],
                    [
                        'all' => true,
                        'sortBy' => 'id',
                    ]
                );

                $abbrContains = fn (Office $offices, string $needle): bool => strpos($offices->abbr, $needle) !== false;
                $abbrNotContains = fn (Office $offices, string $needle): bool => $abbrContains($offices, $needle) === false;
                $this->assertNotEmpty($this->examples->offices);
                $this->assertNotEmpty($result->list);
                $this->assertForAll(
                    $result->list,
                    fn (Office $x): bool => $abbrContains($x, $abbr)
                );
                $this->assertTrue(
                    Seq::fromArray($this->examples->offices)->exists(
                        fn (Office $x): bool => $abbrNotContains($x, $abbr)
                    )
                );
            },
            [
                'examples' => [
                    'abbr given' => ['事テス'],
                ],
            ]
        );
        $this->should(
            'return a FinderResult of users with phoneticName that matches given `q`',
            function ($phoneticName): void {
                $result = $this->finder->find(
                    ['q' => $phoneticName],
                    [
                        'all' => true,
                        'sortBy' => 'name',
                    ]
                );

                $phoneticNameContains = fn (Office $offices, string $needle): bool => strpos($offices->phoneticName, $needle) !== false;
                $phoneticNameNotContains = fn (Office $offices, string $needle): bool => $phoneticNameContains($offices, $needle) === false;
                $this->assertNotEmpty($this->examples->offices);
                $this->assertNotEmpty($result->list);
                $this->assertForAll(
                    $result->list,
                    fn (Office $x): bool => $phoneticNameContains($x, $phoneticName)
                );
                $this->assertTrue(
                    Seq::fromArray($this->examples->offices)->exists(
                        fn (Office $x): bool => $phoneticNameNotContains($x, $phoneticName)
                    )
                );
            },
            [
                'examples' => [
                    'phoneticName given' => ['ジギョウショテスト'],
                ],
            ]
        );
        $this->should(
            'Returns a FinderResult of users belonging to the prefecture that matches the specified `prefecture`',
            function ($prefecture): void {
                $result = $this->finder->find(
                    ['prefecture' => "{$prefecture}"],
                    [
                        'all' => true,
                        'sortBy' => 'name',
                    ]
                );

                $prefectureContains = fn (Office $offices, int $prefecture): bool => $offices->addr->prefecture->value() === $prefecture;
                $prefectureNotContains = fn (Office $offices, int $prefecture): bool => $offices->addr->prefecture->value() !== $prefecture;
                $this->assertNotEmpty($this->examples->offices);
                $this->assertNotEmpty($result->list);
                $this->assertForAll(
                    $result->list,
                    fn (Office $x): bool => $prefectureContains($x, $prefecture)
                );
                $this->assertTrue(
                    Seq::fromArray($this->examples->offices)->exists(
                        fn (Office $x): bool => $prefectureNotContains($x, $prefecture)
                    )
                );
            },
            [
                'examples' => [
                    'prefecture given' => [Prefecture::okinawa()->value()],
                ],
            ]
        );
        $this->should('return a FinderResult of offices with given `purpose`', function (): void {
            $result = $this->finder->find(['purpose' => Purpose::external()], ['sortBy' => 'date']);

            $this->assertNotEmpty($this->examples->offices);
            $this->assertNotEmpty($result->list);
            $this->assertForAll(
                $result->list,
                fn (Office $x): bool => $x->purpose === Purpose::external()
            );
            $this->assertExists(
                $this->examples->offices,
                fn (Office $x): bool => $x->purpose !== Purpose::external()
            );
        });
        $this->should('return a FinderResult of offices with the `qualifications`', function (): void {
            $result = $this->finder->find(['qualifications' => Seq::fromArray([OfficeQualification::dwsVisitingCareForPwsd()])], ['sortBy' => 'id']);

            $this->assertNotEmpty($result);
            $this->assertNotEmpty($result->list);
            $this->assertForAll(
                $result->list,
                fn (Office $x): bool => in_array(OfficeQualification::dwsVisitingCareForPwsd(), $x->qualifications, true)
            );
        });
        $this->should(
            'return a FinderResult of Offices with given `officeIds`',
            function (): void {
                $ids = [
                    $this->examples->offices[0]->id,
                    $this->examples->offices[1]->id,
                    $this->examples->offices[2]->id,
                ];
                $result = $this->finder->find(
                    ['officeIds' => $ids],
                    [
                        'all' => true,
                        'sortBy' => 'name',
                    ]
                );

                $this->assertNotEmpty($result);
                $this->assertNotEmpty($result->list);
                $this->assertForAll(
                    $result->list,
                    fn (Office $x): bool => in_array($x->id, $ids, true)
                );
            }
        );
        $this->should(
            'return a FinderResult of Offices with given `officeIdsOrExternal`',
            function (): void {
                $ids = [
                    $this->examples->offices[0]->id,
                    $this->examples->offices[1]->id,
                    $this->examples->offices[2]->id,
                ];
                $result = $this->finder->find(
                    ['officeIdsOrExternal' => $ids],
                    [
                        'all' => true,
                        'sortBy' => 'name',
                    ]
                );

                $this->assertNotEmpty($result);
                $this->assertNotEmpty($result->list);
                $this->assertForAll(
                    $result->list,
                    fn (Office $x): bool => in_array($x->id, $ids, true) || $x->purpose === Purpose::external()
                );
            }
        );
        $this->should(
            'return a FinderResult of Offices with given `statuses`',
            function (): void {
                $statuses = [OfficeStatus::inOperation()];
                $result = $this->finder->find(
                    ['statuses' => [OfficeStatus::inOperation()]],
                    [
                        'all' => true,
                        'sortBy' => 'name',
                    ]
                );

                $this->assertNotEmpty($result);
                $this->assertNotEmpty($result->list);
                $this->assertForAll(
                    $result->list,
                    fn (Office $x): bool => in_array($x->status, $statuses, true)
                );
                $this->assertExists(
                    $this->examples->offices,
                    fn (Office $x): bool => !in_array($x->status, $statuses, true)
                );
            }
        );
        $this->should('return a FinderResult of Offices with given `userId`', function (): void {
            $userId = $this->examples->users[0]->id;
            $result = $this->finder->find(
                ['userId' => $userId],
                [
                    'all' => true,
                    'sortBy' => 'name',
                ]
            );

            $this->assertNotEmpty($result);
            $this->assertNotEmpty($result->list);
            $this->assertForAll(
                $result->list,
                function (Office $x) use ($userId): bool {
                    foreach ($this->examples->contracts as $contract) {
                        if (
                            $contract->officeId === $x->id
                            && $contract->userId === $userId
                            // 「契約状態が仮契約」または「契約状態が本契約かつ契約日が今日以前」
                            && (
                                $contract->status === ContractStatus::provisional()
                                || (
                                    $contract->status === ContractStatus::formal()
                                    && $contract->contractedOn <= Carbon::today()
                                )
                            )
                        ) {
                            return true;
                        }
                    }
                    return false;
                }
            );
            $this->assertExists(
                $this->examples->offices,
                function (Office $x) use ($userId): bool {
                    foreach ($this->examples->contracts as $contract) {
                        if (
                            $contract->officeId === $x->id
                            && (
                                // 利用者が指定された事業所との契約の情報を持たない
                                $contract->userId !== $userId
                                // 「契約状態が仮契約」でない、かつ「契約状態が本契約かつ契約日が今日以前」でない
                                || !(
                                    $contract->status === ContractStatus::provisional()
                                    || (
                                        $contract->status === ContractStatus::formal()
                                        && $contract->contractedOn <= Carbon::today()
                                    )
                                )
                            )
                        ) {
                            return true;
                        }
                    }
                    return false;
                }
            );
        });
        $this->should(
            'set condition for all of given params when multiple params given',
            function (): void {
                $result = $this->finder->find(
                    [
                        'q' => '事業所テスト',
                        'prefecture' => Prefecture::okinawa()->value(),
                        'organizationId' => $this->examples->organizations[0]->id,
                    ],
                    [
                        'all' => true,
                        'sortBy' => 'name',
                    ]
                );
                $this->assertForAll(
                    $result->list,
                    fn (Office $x) => $x->organizationId === $this->examples->organizations[0]->id
                        && strpos($x->name, '事業所テスト') !== false
                        && $x->addr->prefecture->value() === $this->examples->offices[0]->addr->prefecture->value()
                );
                $this->assertExists(
                    $this->examples->offices,
                    fn (Office $x) => $x->organizationId !== $this->examples->organizations[0]->id
                        && strpos($x->name, '事業所テスト') !== false
                        && $x->addr->prefecture->value() === $this->examples->offices[0]->addr->prefecture->value()
                );
                $this->assertExists(
                    $this->examples->offices,
                    fn (Office $x) => $x->organizationId === $this->examples->organizations[0]->id
                        && strpos($x->name, '事業所テスト') === false
                        && $x->addr->prefecture === $this->examples->offices[0]->addr->prefecture
                );
                $this->assertExists(
                    $this->examples->offices,
                    fn (Office $x) => $x->organizationId === $this->examples->organizations[0]->id
                        && strpos($x->name, '事業所テスト') !== false
                        && $x->addr->prefecture !== $this->examples->offices[0]->addr->prefecture
                );
            }
        );
        $this->should(
            'throw InvalidArgumentException when `sortBy` not given or empty',
            function (): void {
                $this->assertThrows(
                    InvalidArgumentException::class,
                    function (): void {
                        $this->finder->find(
                            [
                                'q' => '事業所テスト',
                                'prefecture' => Prefecture::okinawa()->value(),
                                'organizationId' => $this->examples->organizations[0]->id,
                            ],
                            ['all' => true]
                        );
                    }
                );
            }
        );
        $this->should(
            'Returns a FinderResult of Offices belonging to the OfficeGroup that matches the specified `officeGroupIds`',
            function (): void {
                $result = $this->finder->find(
                    ['officeGroupIds' => $this->examples->officeGroups[0]->id],
                    [
                        'all' => true,
                        'sortBy' => 'name',
                    ]
                );

                $this->assertNotEmpty($result);
                $this->assertNotEmpty($result->list);
                $this->assertForAll(
                    $result->list,
                    fn (Office $x): bool => $x->officeGroupId === $this->examples->officeGroups[0]->id,
                );
            }
        );
        $this->should(
            'sort Offices using given param `sortBy` and `desc`',
            function (): void {
                $expected = Seq::fromArray($this->examples->offices)
                    ->filter(fn (Office $offices) => $offices->organizationId === $this->examples->organizations[0]->id)
                    ->sortBy(fn (Office $offices) => $offices->createdAt->unix())
                    ->reverse()
                    ->toArray();
                $paginationParams = [
                    'all' => true,
                    'desc' => true,
                    'sortBy' => 'date',
                ];
                $filterParams = ['organizationId' => $this->examples->organizations[0]->id];
                foreach ($this->finder->find($filterParams, $paginationParams)->list as $index => $offices) {
                    $this->assertModelStrictEquals($expected[$index], $offices);
                }
            }
        );
        $this->should(
            'throw Exception when unknown parameter `sortBy` and `desc`',
            function (): void {
                $this->assertThrows(
                    InvalidArgumentException::class,
                    function (): void {
                        $paginationParams = [
                            'all' => true,
                            'desc' => true,
                            'sortBy' => 'tel',
                        ];
                        $filterParams = ['organizationId' => $this->examples->organizations[0]->id];
                        $this->finder->find($filterParams, $paginationParams);
                    }
                );
            }
        );
        $this->should('return a FinderResult of community general support centers when `isCommunityGeneralSupportCenter`', function (): void {
            $result = $this->finder->find(['isCommunityGeneralSupportCenter' => true], ['sortBy' => 'date']);
            $pattern1 = '/\A\d{2}0\d{2}[01234]\d{4}\z/';
            $pattern2 = '/\A\d{2}0\d{2}(?:(?!0000).)*?\d{1}\z/';

            $this->assertNotEmpty($this->examples->offices);
            $this->assertNotEmpty($result->list);
            $this->assertForAll(
                $result->list,
                fn (Office $x): bool => preg_match($pattern1, $x->ltcsPreventionService->code) === 1
                    && preg_match($pattern2, $x->ltcsPreventionService->code) === 1
            );
            $this->assertExists(
                $this->examples->offices,
                fn (Office $x): bool => preg_match($pattern1, $x->ltcsPreventionService->code) === 0
                    || preg_match($pattern2, $x->ltcsPreventionService->code) === 0
            );
        });
    }
}

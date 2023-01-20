<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Infrastructure\User;

use Domain\Common\Carbon;
use Domain\Common\Pagination;
use Domain\Common\ServiceSegment;
use Domain\Contract\ContractStatus;
use Domain\FinderResult;
use Domain\User\User;
use Infrastructure\User\UserFinderEloquentImpl;
use Lib\Exceptions\InvalidArgumentException;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\DatabaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;

/**
 * UserFinderEloquentImpl のテスト.
 */
class UserFinderEloquentImplTest extends Test
{
    use DatabaseMixin;
    use ExamplesConsumer;
    use MockeryMixin;
    use UnitSupport;

    private UserFinderEloquentImpl $finder;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachTest(function (UserFinderEloquentImplTest $self): void {
            $self->finder = app(UserFinderEloquentImpl::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_find(): void
    {
        $this->should('return User Entity', function (): void {
            $result = $this->finder->find([], ['sortBy' => 'id', 'itemsPerPage' => 1, 'desc' => false]);

            $this->assertCount(1, $result->list);
            $this->assertModelStrictEquals(
                $this->examples->users[0],
                $result->list->head()
            );
        });
        $this->should('return a FinderResult of User', function (): void {
            $result = $this->finder->find([], ['sortBy' => 'name']);

            $this->assertInstanceOf(FinderResult::class, $result);
            $this->assertInstanceOf(Seq::class, $result->list);
            $this->assertInstanceOf(Pagination::class, $result->pagination);
            $this->assertNotEmpty($result->list);
            foreach ($result->list as $item) {
                $this->assertInstanceOf(User::class, $item);
            }
        });
        $this->should(
            'return a paginated FinderResult when param `all` not given or falsy',
            function ($all): void {
                $itemsPerPage = 2;
                $page = 3;
                $count = count($this->examples->users);
                $pages = (int)ceil($count / $itemsPerPage);
                $result = $this->finder->find(
                    [],
                    $all + [
                        'sortBy' => 'name',
                        'itemsPerPage' => $itemsPerPage,
                        'page' => $page,
                    ]
                );

                $this->assertNotEmpty($this->examples->users);
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
                $count = count($this->examples->users);
                $result = $this->finder->find(
                    [],
                    $all + [
                        'sortBy' => 'name',
                        'itemsPerPage' => $itemsPerPage,
                        'page' => $page,
                    ]
                );

                $this->assertNotEmpty($this->examples->users);
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
            'return a FinderResult of users with given `organizationId`',
            function (): void {
                $result = $this->finder->find(
                    ['organizationId' => $this->examples->organizations[0]->id],
                    [
                        'all' => true,
                        'sortBy' => 'name',
                    ]
                );

                $this->assertNotEmpty($this->examples->users);
                $this->assertNotEmpty($result->list);
                $this->assertForAll(
                    $result->list,
                    fn (User $x): bool => $x->organizationId === $this->examples->organizations[0]->id
                );
                $this->assertExists(
                    $this->examples->users,
                    fn (User $x): bool => $x->organizationId !== $this->examples->organizations[0]->id
                );
            }
        );
        $this->should('return a FinderResult of users with given `officeId`', function (): void {
            $officeId = $this->examples->contracts[0]->officeId;
            $result = $this->finder->find(['officeId' => $officeId], ['sortBy' => 'name']);

            $this->assertNotEmpty($this->examples->users);
            $this->assertNotEmpty($result->list);
            $f = function (User $x) use ($officeId): bool {
                foreach ($this->examples->contracts as $contract) {
                    if (
                        $contract->userId === $x->id
                        // 利用者が指定された事業所との契約の情報を持つ
                        && $contract->officeId === $officeId
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
            };
            $this->assertForAll(
                $result->list,
                $f
            );
            $this->assertExists(
                $this->examples->users,
                $this->invert($f)
            );
        });
        $this->should(
            'return a FinderResult of users with name that matches given `q`',
            function ($familyName, $givenName): void {
                $result = $this->finder->find(
                    ['q' => "{$familyName}{$givenName}"],
                    [
                        'all' => true,
                        'sortBy' => 'name',
                    ]
                );

                $nameContains = fn (User $user, string $needle): bool => strpos($user->name->familyName, $needle) !== false
                    || strpos($user->name->givenName, $needle) !== false
                    || strpos($user->name->phoneticFamilyName, $needle) !== false
                    || strpos($user->name->phoneticGivenName, $needle) !== false;
                $nameNotContains = fn (User $user, string $needle): bool => $nameContains($user, $needle) === false;
                $this->assertNotEmpty($this->examples->users);
                $this->assertNotEmpty($result->list);
                $this->assertForAll(
                    $result->list,
                    fn (User $x): bool => ($nameContains($x, $familyName) && $nameContains($x, $givenName))
                    || $nameContains($x, "{$familyName}{$givenName}")
                );
                $this->assertTrue(
                    Seq::fromArray($this->examples->users)->exists(
                        fn (User $x): bool => $nameNotContains($x, $familyName) && $nameNotContains($x, $givenName)
                    )
                );
            },
            [
                'examples' => [
                    'full name given' => ['内藤', '勇介'],
                    'phonetic full name given' => ['ナイトウ', 'ユウスケ'],
                ],
            ]
        );
        $this->should('return a FinderResult of users with given `isEnabled`', function (): void {
            $result = $this->finder->find(['isEnabled' => $this->examples->users[0]->isEnabled], ['sortBy' => 'name']);

            $this->assertNotEmpty($result);
            $this->assertNotEmpty($result->list);
            $this->assertForAll(
                $result->list,
                fn (User $x): bool => $x->isEnabled === $this->examples->users[0]->isEnabled
            );
            $this->assertExists(
                $this->examples->users,
                fn (User $x): bool => $x->isEnabled !== $this->examples->users[0]->isEnabled
            );
        });
        $this->should('return a FinderResult of users with given `sex`', function (): void {
            $result = $this->finder->find(['sex' => $this->examples->users[0]->sex], ['sortBy' => 'name']);

            $this->assertNotEmpty($result);
            $this->assertNotEmpty($result->list);
            $this->assertForAll(
                $result->list,
                fn (User $x): bool => $x->sex === $this->examples->users[0]->sex
            );
        });
        $this->should('return a FinderResult of users with given `officeIds`', function (): void {
            $officeIds = [
                $this->examples->offices[0]->id,
                $this->examples->offices[1]->id,
            ];
            $result = $this->finder->find(
                ['officeIds' => $officeIds],
                [
                    'all' => true,
                    'sortBy' => 'name',
                ]
            );

            $this->assertNotEmpty($result);
            $this->assertNotEmpty($result->list);
            $this->assertForAll(
                $result->list,
                function (User $x) use ($officeIds): bool {
                    foreach ($this->examples->contracts as $contract) {
                        if (
                            $contract->userId === $x->id
                            // 利用者が指定された事業所との契約の情報を持つ
                            && in_array($contract->officeId, $officeIds, true)
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
                $this->examples->users,
                function (User $x) use ($officeIds): bool {
                    foreach ($this->examples->contracts as $contract) {
                        if (
                            $contract->userId === $x->id
                            && (
                                // 利用者が指定された事業所との契約の情報を持たない
                                !in_array($contract->officeId, $officeIds, true)
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
        $this->should('return a FinderResult of users with given `isContracingtWith`', function (): void {
            $month = Carbon::parse('2022-10');
            $result = $this->finder->find(
                [
                    'isContractingWith' => [
                        'officeId' => $this->examples->contracts[0]->officeId,
                        'date' => $month,
                        'serviceSegment' => ServiceSegment::disabilitiesWelfare(),
                    ],
                ],
                [
                    'all' => true,
                    'sortBy' => 'name',
                ]
            );

            $this->assertNotEmpty($result);
            $this->assertNotEmpty($result->list);
            $this->assertForAll(
                $result->list,
                function (User $x) use ($month): bool {
                    foreach ($this->examples->contracts as $contract) {
                        if (
                            $contract->userId === $x->id
                            // 利用者が指定された事業所との契約の情報を持つ
                            && $contract->officeId === $this->examples->contracts[0]->officeId
                            // 「契約状態が仮契約」または「契約状態が本契約または契約終了かつ契約日が今日以前」
                            && (
                                $contract->status === ContractStatus::provisional()
                                || (
                                    (
                                        $contract->status === ContractStatus::formal()
                                        || $contract->status === ContractStatus::terminated()
                                    )
                                    && $contract->contractedOn < $month->endOfMonth()
                                    && ($contract->terminatedOn >= $month->firstOfMonth() || $contract->terminatedOn === null)
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
                $this->examples->users,
                function (User $x) use ($month): bool {
                    foreach ($this->examples->contracts as $contract) {
                        if (
                            $contract->userId === $x->id
                            && (
                                // 利用者が指定された事業所との契約の情報を持たない
                                $contract->officeId !== $this->examples->contracts[0]->officeId
                                // 「契約状態が仮契約」でない、かつ「契約状態が本契約または契約終了かつ契約日が今日以前」でない
                                || !(
                                    $contract->status === ContractStatus::provisional()
                                    || (
                                        (
                                            $contract->status === ContractStatus::formal()
                                            || $contract->status === ContractStatus::terminated()
                                        )
                                        && $contract->contractedOn < $month->endOfMonth()
                                        && ($contract->terminatedOn >= $month->startOfMonth() || $contract->terminatedOn === null)
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
                        'q' => '内藤勇介',
                        'organizationId' => $this->examples->organizations[0]->id,
                        'dummy' => 'eustylelab',
                    ],
                    [
                        'all' => true,
                        'sortBy' => 'name',
                    ]
                );
                $this->assertForAll(
                    $result->list,
                    fn (User $x) => $x->organizationId === $this->examples->organizations[0]->id
                        && strpos($x->name->familyName . $x->name->givenName, '内藤勇介') !== false
                );
                $this->assertExists(
                    $this->examples->users,
                    fn (User $x) => $x->organizationId !== $this->examples->organizations[0]->id
                        && strpos($x->name->familyName . $x->name->givenName, '内藤勇介') !== false
                );
                $this->assertExists(
                    $this->examples->users,
                    fn (User $x) => $x->organizationId === $this->examples->organizations[0]->id
                        && strpos($x->name->familyName . $x->name->givenName, '内藤勇介') === false
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
                            ['q' => '内藤勇介', 'organizationId' => $this->examples->organizations[0]->id],
                            ['all' => true]
                        );
                    }
                );
            }
        );
        $this->should(
            'sort users using given param `sortBy` and `desc`',
            function (): void {
                $expected = Seq::fromArray($this->examples->users)
                    ->filter(fn (User $user) => $user->organizationId === $this->examples->organizations[0]->id)
                    ->sortBy(fn (User $user) => $user->createdAt->unix())
                    ->reverse()
                    ->toArray();
                $paginationParams = [
                    'all' => true,
                    'desc' => true,
                    'sortBy' => 'date',
                ];
                $filterParams = ['organizationId' => $this->examples->organizations[0]->id];
                foreach ($this->finder->find($filterParams, $paginationParams)->list as $index => $user) {
                    $this->assertModelStrictEquals($expected[$index], $user);
                }
            }
        );
    }
}

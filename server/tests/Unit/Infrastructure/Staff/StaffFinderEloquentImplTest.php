<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Infrastructure\Staff;

use Domain\Common\Pagination;
use Domain\Common\Sex;
use Domain\FinderResult;
use Domain\Staff\Staff;
use Domain\Staff\StaffStatus;
use Infrastructure\Staff\StaffFinderEloquentImpl;
use Lib\Exceptions\InvalidArgumentException;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\DatabaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;

/**
 * StaffFinderEloquentImpl のテスト.
 */
class StaffFinderEloquentImplTest extends Test
{
    use DatabaseMixin;
    use ExamplesConsumer;
    use MockeryMixin;
    use UnitSupport;

    private StaffFinderEloquentImpl $finder;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachTest(function (StaffFinderEloquentImplTest $self): void {
            $self->finder = app(StaffFinderEloquentImpl::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_find(): void
    {
        $this->should('return a FinderResult of Staff', function (): void {
            $result = $this->finder->find([], ['sortBy' => 'name']);

            $this->assertInstanceOf(FinderResult::class, $result);
            $this->assertInstanceOf(Seq::class, $result->list);
            $this->assertInstanceOf(Pagination::class, $result->pagination);
            $this->assertNotEmpty($result->list);
            foreach ($result->list as $item) {
                $this->assertInstanceOf(Staff::class, $item);
            }
        });
        $this->should(
            'return a paginated FinderResult when param `all` not given or falsy',
            function ($all): void {
                $itemsPerPage = 2;
                $page = 3;
                $count = count($this->examples->staffs);
                $pages = (int)ceil($count / $itemsPerPage);
                $result = $this->finder->find(
                    [],
                    $all + [
                        'sortBy' => 'name',
                        'itemsPerPage' => $itemsPerPage,
                        'page' => $page,
                    ]
                );

                $this->assertNotEmpty($this->examples->staffs);
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
                $count = count($this->examples->staffs);
                $result = $this->finder->find(
                    [],
                    $all + [
                        'sortBy' => 'name',
                        'itemsPerPage' => $itemsPerPage,
                        'page' => $page,
                    ]
                );

                $this->assertNotEmpty($this->examples->staffs);
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
            'return a FinderResult of staffs with given `organizationId`',
            function (): void {
                $result = $this->finder->find(
                    ['organizationId' => $this->examples->organizations[0]->id],
                    [
                        'all' => true,
                        'sortBy' => 'name',
                    ]
                );

                $this->assertNotEmpty($this->examples->staffs);
                $this->assertNotEmpty($result->list);
                $this->assertForAll(
                    $result->list,
                    fn (Staff $x): bool => $x->organizationId === $this->examples->organizations[0]->id
                );
                $this->assertExists(
                    $this->examples->staffs,
                    fn (Staff $x): bool => $x->organizationId !== $this->examples->organizations[0]->id
                );
            }
        );
        $this->should('return a FinderResult of staffs with given `officeId`', function (): void {
            $result = $this->finder->find(['officeId' => $this->examples->staffs[26]->officeIds[0]], ['sortBy' => 'name']);

            $this->assertNotEmpty($this->examples->staffs);
            $this->assertNotEmpty($result->list);
            $this->assertForAll(
                $result->list,
                fn (Staff $x): bool => in_array($this->examples->staffs[26]->officeIds[0], $x->officeIds, true),
            );
            $this->assertExists(
                $this->examples->staffs,
                fn (Staff $x): bool => !in_array($this->examples->staffs[26]->officeIds[0], $x->officeIds, true),
            );
        });
        $this->should(
            'return a FinderResult of staffs with name that matches given `q`',
            function ($familyName, $givenName): void {
                $result = $this->finder->find(
                    ['q' => "{$familyName}{$givenName}"],
                    [
                        'all' => true,
                        'sortBy' => 'name',
                    ]
                );

                $contains = function (Staff $staff, string $needle): bool {
                    return strpos($staff->name->familyName, $needle) !== false
                        || strpos($staff->name->givenName, $needle) !== false
                        || strpos($staff->name->phoneticFamilyName, $needle) !== false
                        || strpos($staff->name->phoneticGivenName, $needle) !== false;
                };
                $notContains = fn (Staff $staff, string $needle): bool => $contains($staff, $needle) === false;
                $this->assertNotEmpty($this->examples->staffs);
                $this->assertNotEmpty($result->list);
                $this->assertForAll(
                    $result->list,
                    fn (Staff $x): bool => ($contains($x, $familyName) && $contains($x, $givenName))
                        || $contains($x, "{$familyName}{$givenName}")
                );
                $this->assertTrue(
                    Seq::fromArray($this->examples->staffs)->exists(
                        fn (Staff $x): bool => $notContains($x, $familyName) && $notContains($x, $givenName)
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
        $this->should('return a FinderResult of staffs with given `sex`', function (): void {
            $result = $this->finder->find(['sex' => Sex::male()->value()], ['sortBy' => 'name']);

            $this->assertNotEmpty($this->examples->staffs);
            $this->assertNotEmpty($result->list);
            $this->assertForAll(
                $result->list,
                fn (Staff $x): bool => $x->sex === Sex::male(),
            );
            $this->assertExists(
                $this->examples->staffs,
                fn (Staff $x): bool => $x->sex !== Sex::male(),
            );
        });
        $this->should('return a FinderResult of staffs with given `statuses`', function (): void {
            $result = $this->finder->find(['statuses' => [StaffStatus::active()]], ['sortBy' => 'name']);

            $this->assertNotEmpty($this->examples->staffs);
            $this->assertNotEmpty($result->list);
            $this->assertForAll(
                $result->list,
                fn (Staff $x): bool => $x->status === StaffStatus::active(),
            );
            $this->assertExists(
                $this->examples->staffs,
                fn (Staff $x): bool => $x->status !== StaffStatus::active(),
            );
        });
        $this->should(
            'return a FinderResult of Staffs with given `officeIds`',
            function (): void {
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
                    function (Staff $x) use ($officeIds): bool {
                        foreach ($x->officeIds as $officeId) {
                            if (!in_array($officeId, $officeIds, true)) {
                                return false;
                            }
                        }
                        return true;
                    }
                );
                $this->assertTrue(Seq::fromArray($this->examples->staffs)->exists(
                    fn (Staff $x): bool => !in_array($this->examples->offices[0]->id, $x->officeIds, true)
                        && !in_array($this->examples->offices[1]->id, $x->officeIds, true)
                ));
            }
        );
        $this->should(
            'return a FinderResult of Staffs with given `email`',
            function (): void {
                $email = $this->examples->staffs[1]->email;
                $result = $this->finder->find(
                    ['email' => $email],
                    [
                        'all' => true,
                        'sortBy' => 'name',
                    ]
                );

                $this->assertNotEmpty($result);
                $this->assertNotEmpty($result->list);
                $this->assertForAll(
                    $result->list,
                    fn (Staff $x): bool => $x->email === $email
                );
                $this->assertExists(
                    $this->examples->staffs,
                    fn (Staff $x): bool => $x->email !== $email
                );
            }
        );
        $this->should(
            'return a FinderResult of Staffs with given `isEnabled`',
            function (): void {
                $result = $this->finder->find(
                    ['isEnabled' => true],
                    [
                        'all' => true,
                        'sortBy' => 'name',
                    ]
                );

                $this->assertNotEmpty($result);
                $this->assertNotEmpty($result->list);
                $this->assertForAll(
                    $result->list,
                    fn (Staff $x): bool => $x->isEnabled === true
                );
                $this->assertExists(
                    $this->examples->staffs,
                    fn (Staff $x): bool => $x->isEnabled !== true
                );
            }
        );
        $this->should(
            'set condition for all of given params when multiple params given',
            function (): void {
                $result = $this->finder->find(
                    ['q' => '内藤勇介', 'organizationId' => $this->examples->organizations[0]->id],
                    [
                        'all' => true,
                        'sortBy' => 'name',
                    ]
                );
                $this->assertForAll(
                    $result->list,
                    fn (Staff $x) => $x->organizationId === $this->examples->organizations[0]->id
                        && strpos($x->name->familyName . $x->name->givenName, '内藤勇介') !== false
                );
                $this->assertExists(
                    $this->examples->staffs,
                    fn (Staff $x) => $x->organizationId !== $this->examples->organizations[0]->id
                        && strpos($x->name->familyName . $x->name->givenName, '内藤勇介') !== false
                );
                $this->assertExists(
                    $this->examples->staffs,
                    fn (Staff $x) => $x->organizationId === $this->examples->organizations[0]->id
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
            'sort staffs using given param `sortBy` and `desc`',
            function (): void {
                $expected = Seq::fromArray($this->examples->staffs)
                    ->filter(fn (Staff $staff) => $staff->organizationId === $this->examples->organizations[0]->id)
                    ->sortBy(fn (Staff $staff) => $staff->createdAt->unix())
                    ->reverse()
                    ->toArray();
                $paginationParams = [
                    'all' => true,
                    'desc' => true,
                    'sortBy' => 'date',
                ];
                $filterParams = ['organizationId' => $this->examples->organizations[0]->id];
                foreach ($this->finder->find($filterParams, $paginationParams)->list as $index => $staff) {
                    $this->assertModelStrictEquals($expected[$index], $staff);
                }
            }
        );
    }
}

<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Infrastructure\Contract;

use Closure;
use Domain\Common\Carbon;
use Domain\Common\Pagination;
use Domain\Common\ServiceSegment;
use Domain\Contract\Contract;
use Domain\FinderResult;
use Infrastructure\Contract\ContractFinderEloquentImpl;
use Lib\Exceptions\InvalidArgumentException;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\DatabaseMixin;
use Tests\Unit\Test;

/**
 * {@link \Infrastructure\Contract\ContractFinderEloquentImpl} のテスト.
 */
final class ContractFinderEloquentImplTest extends Test
{
    use CarbonMixin;
    use DatabaseMixin;
    use ExamplesConsumer;
    use UnitSupport;

    private ContractFinderEloquentImpl $finder;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachTest(function (self $self): void {
            $self->finder = app(ContractFinderEloquentImpl::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_find(): void
    {
        $this->should('return FinderResult on Contract', function (): void {
            $actual = $this->finder->find([], ['sortBy' => 'date']);

            $this->assertInstanceOf(FinderResult::class, $actual);
            $this->assertInstanceOf(Seq::class, $actual->list);
            $this->assertInstanceOf(Pagination::class, $actual->pagination);
            $this->assertNotEmpty($actual->list);
            $this->assertForAll($actual->list, fn ($x): bool => $x instanceof Contract);
        });
        $this->should(
            'return a FinderResult with only one page when param `all` given and truthy',
            function ($all): void {
                $itemsPerPage = 2;
                $page = 2;
                $count = count($this->examples->contracts);
                $paginationParams = [
                    'sortBy' => 'date',
                    'itemsPerPage' => $itemsPerPage,
                    'page' => $page,
                ];

                $actual = $this->finder->find([], $all + $paginationParams);

                $this->assertNotEmpty($this->examples->contracts);
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
                $this->assertExists($this->examples->contracts, $this->invert($assert));
            },
            [
                'examples' => [
                    'contractedOnAfter specified' => [
                        ['contractedOnAfter' => Carbon::now()],
                        fn (Contract $x): bool => $x->contractedOn >= Carbon::now(),
                    ],
                    'contractedOnBefore specified' => [
                        ['contractedOnBefore' => Carbon::now()],
                        fn (Contract $x): bool => $x->contractedOn <= Carbon::now(),
                    ],
                    'date specified' => [
                        ['date' => Carbon::now()->subDay()],
                        fn (Contract $x): bool => $x->contractedOn <= Carbon::now()->subDay()->startOfDay(),
                    ],
                    'officeId specified' => [
                        ['officeId' => $this->examples->offices[0]->id],
                        fn (Contract $x): bool => $x->officeId === $this->examples->offices[0]->id,
                    ],
                    'officeIds specified' => [
                        ['officeIds' => [$this->examples->offices[0]->id, $this->examples->offices[1]->id]],
                        fn (Contract $x): bool => in_array($x->officeId, [$this->examples->offices[0]->id, $this->examples->offices[1]->id], true),
                    ],
                    'organizationId specified' => [
                        ['organizationId' => $this->examples->organizations[0]->id],
                        fn (Contract $x): bool => $x->organizationId === $this->examples->organizations[0]->id,
                    ],
                    'serviceSegment specified' => [
                        ['serviceSegment' => ServiceSegment::disabilitiesWelfare()],
                        fn (Contract $x): bool => $x->serviceSegment === ServiceSegment::disabilitiesWelfare(),
                    ],
                    'status specified' => [
                        ['status' => $this->examples->contracts[0]->status],
                        fn (Contract $x): bool => $x->status === $this->examples->contracts[0]->status,
                    ],
                    'terminatedIn specified' => [
                        ['terminatedIn' => [$this->examples->contracts[0]->terminatedOn->subMinute(), $this->examples->contracts[0]->terminatedOn->addMinutes()]],
                        fn (Contract $x): bool => $this->examples->contracts[0]->terminatedOn->subMinute() <= $x->terminatedOn && $x->terminatedOn <= $this->examples->contracts[0]->terminatedOn->addMinutes(),
                    ],
                    'terminatedOnAfter specified' => [
                        ['terminatedOnAfter' => Carbon::now()],
                        fn (Contract $x): bool => $x->terminatedOn === null
                            || $x->terminatedOn >= Carbon::now(),
                    ],
                    'userId specified' => [
                        ['userId' => $this->examples->users[0]->id],
                        fn (Contract $x): bool => $x->userId === $this->examples->users[0]->id,
                    ],
                    'userIds specified' => [
                        ['userIds' => [$this->examples->users[0]->id]],
                        fn (Contract $x): bool => in_array($x->userId, [$this->examples->users[0]->id], true),
                    ],
                ],
            ]
        );
        $this->should(
            'return ordered list specified `sortBy` params',
            function (string $key, string $parameter): void {
                $expectedList = Seq::fromArray($this->examples->contracts)
                    ->sortBy(fn (Contract $x): Carbon => $x->{$parameter});

                $actual = $this->finder->find([], ['all' => true, 'sortBy' => $key]);

                $this->assertNotEmpty($actual);
                $this->assertNotEmpty($actual->list);
                $this->assertArrayStrictEquals($expectedList->toArray(), $actual->list->toArray());
            },
            [
                'examples' => [
                    'sort by `date`' => ['date', 'createdAt'],
                    //                    'sort by `contractedOn`' => ['contractedOn', 'contractedOn'], // ContractedOn で同じ日付になった場合ソートが不定になるので一旦はずす
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

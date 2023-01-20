<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Infrastructure\UserBilling;

use Closure;
use Domain\Common\Carbon;
use Domain\Common\Pagination;
use Domain\FinderResult;
use Domain\User\PaymentMethod;
use Domain\UserBilling\UserBilling;
use Domain\UserBilling\UserBillingResult;
use Domain\UserBilling\WithdrawalResultCode;
use Domain\UserBillingUsedService\UserBillingUsedService;
use Infrastructure\UserBilling\UserBillingFinderEloquentImpl;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\DatabaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;

/**
 * {@link \Infrastructure\UserBilling\UserBillingFinderEloquentImpl} のテスト.
 */
final class UserBillingFinderEloquentImplTest extends Test
{
    use DatabaseMixin;
    use ExamplesConsumer;
    use MockeryMixin;
    use UnitSupport;

    private UserBillingFinderEloquentImpl $finder;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachTest(function (UserBillingFinderEloquentImplTest $self): void {
            $self->finder = app(UserBillingFinderEloquentImpl::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_find(): void
    {
        $this->should('return UserBilling Entity', function (): void {
            $result = $this->finder->find([], ['sortBy' => 'id', 'itemsPerPage' => 1, 'desc' => false]);

            $this->assertCount(1, $result->list);
            $this->assertModelStrictEquals(
                $this->examples->userBillings[0],
                $result->list->head()
            );
        });
        $this->should('return a FinderResult of UserBilling', function (): void {
            $result = $this->finder->find([], ['sortBy' => 'name']);

            $this->assertInstanceOf(FinderResult::class, $result);
            $this->assertInstanceOf(Seq::class, $result->list);
            $this->assertInstanceOf(Pagination::class, $result->pagination);
            $this->assertNotEmpty($result->list);
            foreach ($result->list as $item) {
                $this->assertInstanceOf(UserBilling::class, $item);
            }
        });
        $this->should(
            'return a paginated FinderResult when param `all` not given or falsy',
            function ($all): void {
                $itemsPerPage = 1;
                $page = 2;
                $count = count($this->examples->userBillings);
                $pages = (int)ceil($count / $itemsPerPage);
                $result = $this->finder->find(
                    [],
                    $all + [
                        'sortBy' => 'name',
                        'itemsPerPage' => $itemsPerPage,
                        'page' => $page,
                    ]
                );

                $this->assertNotEmpty($this->examples->userBillings);
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
                $itemsPerPage = 1;
                $page = 2;
                $count = count($this->examples->userBillings);
                $result = $this->finder->find(
                    [],
                    $all + [
                        'sortBy' => 'name',
                        'itemsPerPage' => $itemsPerPage,
                        'page' => $page,
                    ]
                );

                $this->assertNotEmpty($this->examples->userBillings);
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
                $this->assertExists($this->examples->userBillings, $this->invert($f));

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
                    'when contractNumber' => [
                        ['contractNumber' => '0123456789'],
                        fn (UserBilling $x): bool => $x->user->billingDestination->contractNumber === '0123456789',
                    ],
                    'when isTransacted' => [
                        ['isTransacted' => true],
                        fn (UserBilling $x): bool => $x->transactedAt !== null,
                    ],
                    'when providedIn' => [
                        ['providedIn' => Carbon::parse('2020-04')],
                        fn (UserBilling $x): bool => $x->providedIn->firstOfMonth()->eq(Carbon::parse('2020-04-01')),
                    ],
                    'when officeId' => [
                        ['officeId' => $this->examples->offices[0]->id],
                        fn (UserBilling $x): bool => $x->officeId === $this->examples->offices[0]->id,
                    ],
                    'when officeIds' => [
                        ['officeIds' => [$this->examples->offices[0]->id, $this->examples->offices[1]->id]],
                        fn (UserBilling $x): bool => in_array($x->officeId, [$this->examples->offices[0]->id, $this->examples->offices[1]->id], true),
                    ],
                    'when issuedIn' => [
                        ['issuedIn' => Carbon::parse('2020-05')],
                        fn (UserBilling $x): bool => $x->issuedOn->format('Y-m') === '2020-05',
                    ],
                    'when paymentMethod' => [
                        ['paymentMethod' => PaymentMethod::withdrawal()],
                        fn (UserBilling $x): bool => $x->user->billingDestination->paymentMethod === PaymentMethod::withdrawal(),
                    ],
                    'when usedService' => [
                        ['usedService' => UserBillingUsedService::disabilitiesWelfareService()],
                        fn (UserBilling $x): bool => !empty($x->dwsItem),
                    ],
                    'when result' => [
                        ['result' => UserBillingResult::paid()],
                        fn (UserBilling $x): bool => $x->result === UserBillingResult::paid(),
                    ],
                    'when userId' => [
                        ['userId' => $this->examples->users[0]->id],
                        fn (UserBilling $x): bool => $x->userId === $this->examples->users[0]->id,
                    ],
                    'when isDeposited' => [
                        ['isDeposited' => true],
                        fn (UserBilling $x): bool => $x->depositedAt !== null,
                    ],
                    'when withdrawalResultCode' => [
                        ['withdrawalResultCode' => WithdrawalResultCode::other()->value()],
                        fn (UserBilling $x): bool => $x->withdrawalResultCode === WithdrawalResultCode::other(),
                    ],
                ],
            ],
        );
    }
}

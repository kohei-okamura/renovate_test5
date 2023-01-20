<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\UserBilling;

use Domain\Common\Pagination;
use Domain\FinderResult;
use Domain\Permission\Permission;
use ScalikePHP\Option;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\WithdrawalTransactionFinderMixin;
use Tests\Unit\Test;
use UseCase\UserBilling\FindWithdrawalTransactionInteractor;

/**
 * {@link \UseCase\UserBilling\FindWithdrawalTransactionInteractor} のテスト.
 */
final class FindWithdrawalTransactionInteractorTest extends Test
{
    use ContextMixin;
    use ExamplesConsumer;
    use MockeryMixin;
    use UnitSupport;
    use WithdrawalTransactionFinderMixin;

    private FindWithdrawalTransactionInteractor $interactor;

    /**
     * 初期化処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachSpec(function (self $self): void {
            $self->context
                ->allows('getPermittedOffices')
                ->andReturn(Option::from(Seq::from($self->examples->offices[0])))
                ->byDefault();
            $self->interactor = app(FindWithdrawalTransactionInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('find withdrawalTransactions using WithdrawalTransactionFinder', function (): void {
            $filterParams = [];
            $paginationParams = [
                'sortBy' => 'date',
                'itemsPerPage' => 10,
                'page' => 2,
            ];
            $pagination = Pagination::create([
                'sortBy' => 'date',
                'count' => count($this->examples->withdrawalTransactions),
                'itemsPerPage' => $paginationParams['itemsPerPage'],
                'page' => $paginationParams['page'],
            ]);
            $expected = FinderResult::from($this->examples->withdrawalTransactions, $pagination);
            $this->withdrawalTransactionFinder
                ->expects('find')
                ->with(
                    $filterParams + [
                        'organizationId' => $this->context->organization->id,
                        'officeIds' => [$this->examples->offices[0]->id],
                    ],
                    $paginationParams
                )
                ->andReturn($expected);

            $this->assertModelStrictEquals(
                $expected,
                $this->interactor->handle($this->context, Permission::listWithdrawalTransactions(), $filterParams, $paginationParams)
            );
        });
        $this->should('set default sortBy', function (): void {
            $filterParams = [];
            $paginationParams = [
                'itemsPerPage' => 10,
                'page' => 2,
            ];
            $this->withdrawalTransactionFinder
                ->expects('find')
                ->with(
                    $filterParams + [
                        'organizationId' => $this->context->organization->id,
                        'officeIds' => [$this->examples->offices[0]->id],
                    ],
                    ['sortBy' => 'id'] + $paginationParams
                )
                ->andReturn(FinderResult::from([], Pagination::create()));

            $this->interactor->handle($this->context, Permission::listWithdrawalTransactions(), $filterParams, $paginationParams);
        });
    }
}

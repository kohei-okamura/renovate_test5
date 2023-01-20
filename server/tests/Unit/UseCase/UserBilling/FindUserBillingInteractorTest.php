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
use Tests\Unit\Mixins\UserBillingFinderMixin;
use Tests\Unit\Test;
use UseCase\UserBilling\FindUserBillingInteractor;

/**
 * {@link \UseCase\UserBilling\FindUserBillingInteractor} のテスト.
 */
final class FindUserBillingInteractorTest extends Test
{
    use ContextMixin;
    use ExamplesConsumer;
    use MockeryMixin;
    use UnitSupport;
    use UserBillingFinderMixin;

    private FindUserBillingInteractor $interactor;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (FindUserBillingInteractorTest $self): void {
            $self->context
                ->allows('getPermittedOffices')
                ->andReturn(Option::from(Seq::from($self->examples->offices[0])))
                ->byDefault();
            $self->interactor = app(FindUserBillingInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('find userBillings using UserBillingFinder', function (): void {
            $filterParams = [];
            $paginationParams = [
                'sortBy' => 'date',
                'itemsPerPage' => 10,
                'page' => 2,
            ];
            $pagination = Pagination::create([
                'sortBy' => 'date',
                'count' => count($this->examples->userBillings),
                'itemsPerPage' => $paginationParams['itemsPerPage'],
                'page' => $paginationParams['page'],
            ]);
            $expected = FinderResult::from($this->examples->userBillings, $pagination);
            $this->userBillingFinder
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
                $this->interactor->handle($this->context, Permission::listUserBillings(), $filterParams, $paginationParams)
            );
        });
        $this->should('set default sortBy', function (): void {
            $filterParams = [];
            $paginationParams = [
                'itemsPerPage' => 10,
                'page' => 2,
            ];
            $this->userBillingFinder
                ->expects('find')
                ->with(
                    $filterParams + [
                        'organizationId' => $this->context->organization->id,
                        'officeIds' => [$this->examples->offices[0]->id],
                    ],
                    ['sortBy' => 'name'] + $paginationParams
                )
                ->andReturn(FinderResult::from([], Pagination::create()));

            $this->interactor->handle($this->context, Permission::listUserBillings(), $filterParams, $paginationParams);
        });
    }
}

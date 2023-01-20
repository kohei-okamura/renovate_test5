<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\Billing;

use Domain\Common\Carbon;
use Domain\Common\Pagination;
use Domain\FinderResult;
use Domain\Permission\Permission;
use ScalikePHP\Option;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\DwsBillingFinderMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;
use UseCase\Billing\FindDwsBillingInteractor;

/**
 * {@link \UseCase\Billing\FindDwsBillingInteractor} Test.
 */
class FindDwsBillingInteractorTest extends Test
{
    use ContextMixin;
    use DwsBillingFinderMixin;
    use ExamplesConsumer;
    use MockeryMixin;
    use UnitSupport;

    private FindDwsBillingInteractor $interactor;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (FindDwsBillingInteractorTest $self): void {
            $self->context
                ->allows('getPermittedOffices')
                ->andReturn(Option::none());
            $self->interactor = app(FindDwsBillingInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('find entities using Finder', function (): void {
            $filterParams = ['transactedIn' => Carbon::now()];
            $paginationParams = [
                'sortBy' => 'id',
                'all' => true,
            ];
            $pagination = Pagination::create([
                'count' => count($this->examples->dwsBillings),
            ] + $paginationParams);
            $expected = FinderResult::from($this->examples->callings, $pagination);
            $this->dwsBillingFinder
                ->expects('find')
                ->with(
                    equalTo($filterParams + ['organizationId' => $this->context->organization->id]),
                    $paginationParams
                )
                ->andReturn($expected);

            $this->assertModelStrictEquals(
                $expected,
                $this->interactor->handle($this->context, Permission::listBillings(), $filterParams, $paginationParams)
            );
        });
        $this->should('be sorted by id when no specified', function (): void {
            $filterParams = ['transactedIn' => Carbon::now()];
            $paginationParams = [
                'all' => true,
            ];
            $pagination = Pagination::create([
                'count' => count($this->examples->dwsBillings),
            ] + $paginationParams);
            $expected = FinderResult::from($this->examples->dwsBillings, $pagination->copy(['sortBy' => 'id']));
            $this->dwsBillingFinder
                ->expects('find')
                ->with(
                    equalTo($filterParams + ['organizationId' => $this->context->organization->id]),
                    equalTo(['sortBy' => 'id'] + $paginationParams)
                )
                ->andReturn($expected);

            $this->assertModelStrictEquals(
                $expected,
                $this->interactor->handle($this->context, Permission::listShifts(), $filterParams, $paginationParams)
            );
        });
    }
}

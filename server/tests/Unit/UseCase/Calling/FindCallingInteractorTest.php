<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\Calling;

use Domain\Common\Carbon;
use Domain\Common\CarbonRange;
use Domain\Common\Pagination;
use Domain\FinderResult;
use Domain\Permission\Permission;
use ScalikePHP\Option;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CallingFinderMixin;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;
use UseCase\Calling\FindCallingInteractor;

/**
 * {@link \UseCase\Calling\FindCallingInteractor} Test.
 */
class FindCallingInteractorTest extends Test
{
    use ContextMixin;
    use CallingFinderMixin;
    use ExamplesConsumer;
    use MockeryMixin;
    use UnitSupport;

    private FindCallingInteractor $interactor;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (FindCallingInteractorTest $self): void {
            $self->context
                ->allows('getPermittedOffices')
                ->andReturn(Option::none());
            $self->interactor = app(FindCallingInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('find contracts using CallingFinder', function (): void {
            $filterParams = ['expiredRange' => CarbonRange::create(['start' => Carbon::now(), 'end' => Carbon::now()->addSecond()])];
            $paginationParams = [
                'sortBy' => 'id',
                'all' => true,
            ];
            $pagination = Pagination::create([
                'count' => count($this->examples->callings),
            ] + $paginationParams);
            $expected = FinderResult::from($this->examples->callings, $pagination);
            $this->callingFinder
                ->expects('find')
                ->with($filterParams + ['organizationId' => $this->context->organization->id], $paginationParams)
                ->andReturn($expected);

            $this->assertModelStrictEquals(
                $expected,
                $this->interactor->handle($this->context, Permission::listShifts(), $filterParams, $paginationParams)
            );
        });
        $this->should('be sorted by id when no specified', function (): void {
            $filterParams = ['expiredRange' => CarbonRange::create(['start' => Carbon::now(), 'end' => Carbon::now()->addSecond()])];
            $paginationParams = [
                'all' => true,
            ];
            $pagination = Pagination::create([
                'count' => count($this->examples->callings),
            ] + $paginationParams);
            $expected = FinderResult::from($this->examples->callings, $pagination->copy(['sortBy' => 'id']));
            $this->callingFinder
                ->expects('find')
                ->with($filterParams + ['organizationId' => $this->context->organization->id], ['sortBy' => 'id'] + $paginationParams)
                ->andReturn($expected);

            $this->assertModelStrictEquals(
                $expected,
                $this->interactor->handle($this->context, Permission::listShifts(), $filterParams, $paginationParams)
            );
        });
    }
}

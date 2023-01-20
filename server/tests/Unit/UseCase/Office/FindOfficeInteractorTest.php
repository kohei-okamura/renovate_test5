<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\Office;

use Domain\Common\Pagination;
use Domain\FinderResult;
use Domain\Office\Office;
use Domain\Permission\Permission;
use Lib\Exceptions\NotFoundException;
use ScalikePHP\Option;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\OfficeFinderMixin;
use Tests\Unit\Test;
use UseCase\Office\FindOfficeInteractor;

/**
 * \UseCase\Office\FindOfficeInteractor のテスト.
 */
final class FindOfficeInteractorTest extends Test
{
    use ContextMixin;
    use ExamplesConsumer;
    use MockeryMixin;
    use OfficeFinderMixin;
    use UnitSupport;

    private FindOfficeInteractor $interactor;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (FindOfficeInteractorTest $self): void {
            $self->context
                ->allows('getPermittedOffices')
                ->andReturn(Option::none())
                ->byDefault();
            $self->context
                ->allows('isAuthorizedTo')
                ->andReturn(true)
                ->byDefault();
            $self->officeFinder
                ->allows('find')
                ->andReturn(FinderResult::from($self->examples->offices, Pagination::create()))
                ->byDefault();

            $self->interactor = app(FindOfficeInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('find Offices using OfficeFinder', function (): void {
            $filterParams = [];
            $paginationParams = [
                'sortBy' => 'date',
                'itemsPerPage' => 10,
                'page' => 2,
            ];
            $pagination = Pagination::create([
                'sortBy' => 'date',
                'count' => count($this->examples->offices),
                'itemsPerPage' => $paginationParams['itemsPerPage'],
                'page' => $paginationParams['page'],
            ]);
            $expected = FinderResult::from($this->examples->offices, $pagination);
            $this->officeFinder
                ->expects('find')
                ->with(['organizationId' => $this->context->organization->id] + $filterParams, $paginationParams)
                ->andReturn($expected);

            $this->assertModelStrictEquals(
                $expected,
                $this->interactor->handle($this->context, [Permission::listInternalOffices()], $filterParams, $paginationParams)
            );
        });
        $this->should('set default sortBy', function (): void {
            $filterParams = [];
            $paginationParams = [
                'itemsPerPage' => 10,
                'page' => 2,
            ];
            $this->officeFinder
                ->expects('find')
                ->with(
                    ['organizationId' => $this->context->organization->id] + $filterParams,
                    ['sortBy' => 'name'] + $paginationParams
                )
                ->andReturn(FinderResult::from([], Pagination::create()));

            $this->interactor->handle($this->context, [Permission::listInternalOffices()], $filterParams, $paginationParams);
        });
        $this->should('filter office by getPermittedOffices()', function (): void {
            $this->context
                ->allows('isAuthorizedTo')
                ->andReturn(false, true, true);
            $filterParams = [];
            $paginationParams = [
                'sortBy' => 'date',
                'itemsPerPage' => 10,
                'page' => 2,
            ];
            $permittedOffices1 = Seq::from($this->examples->offices[0], $this->examples->offices[1]);
            $permittedOffices2 = Seq::from($this->examples->offices[4]);
            $officeIdFilter = [
                'officeIds' => [
                    ...$permittedOffices1->map(fn (Office $x): int => $x->id),
                    ...$permittedOffices2->map(fn (Office $x): int => $x->id),
                ],
            ];
            $this->context
                ->expects('getPermittedOffices')
                ->andReturn(Option::some($permittedOffices1));
            $this->context
                ->expects('getPermittedOffices')
                ->andReturn(Option::some($permittedOffices2));
            $this->officeFinder
                ->expects('find')
                ->with(
                    ['organizationId' => $this->context->organization->id] + $officeIdFilter + $filterParams,
                    $paginationParams
                )
                ->andReturn(FinderResult::from($this->examples->offices, Pagination::create()));

            $this->interactor->handle($this->context, [Permission::listInternalOffices(), Permission::viewInternalOffices()], $filterParams, $paginationParams);
        });
        $this->should('filter office by getPermittedOffices() with external', function (): void {
            $filterParams = [];
            $paginationParams = [
                'sortBy' => 'date',
                'itemsPerPage' => 10,
                'page' => 2,
            ];
            $permittedOffices1 = Seq::from($this->examples->offices[0], $this->examples->offices[1]);
            $permittedOffices2 = Seq::from($this->examples->offices[4]);
            $officeIdFilter = [
                'officeIdsOrExternal' => [
                    ...$permittedOffices1->map(fn (Office $x): int => $x->id),
                    ...$permittedOffices2->map(fn (Office $x): int => $x->id),
                ],
            ];
            $this->context
                ->expects('getPermittedOffices')
                ->andReturn(Option::some($permittedOffices1));
            $this->context
                ->expects('getPermittedOffices')
                ->andReturn(Option::some($permittedOffices2));
            $this->officeFinder
                ->expects('find')
                ->with(
                    ['organizationId' => $this->context->organization->id] + $officeIdFilter + $filterParams,
                    $paginationParams
                )
                ->andReturn(FinderResult::from($this->examples->offices, Pagination::create()));

            $this->interactor->handle($this->context, [Permission::listInternalOffices(), Permission::viewInternalOffices()], $filterParams, $paginationParams);
        });
        $this->should('not filter office by getPermittedOffices() when all offices are permitted', function (): void {
            $filterParams = [];
            $paginationParams = [
                'sortBy' => 'date',
                'itemsPerPage' => 10,
                'page' => 2,
            ];
            $permittedOffices = Seq::from($this->examples->offices[0], $this->examples->offices[1]);
            $officeIdFilter = [];
            $this->context
                ->expects('getPermittedOffices')
                ->andReturn(Option::some($permittedOffices));
            $this->context
                ->expects('getPermittedOffices')
                ->andReturn(Option::none());
            $this->officeFinder
                ->expects('find')
                ->with(
                    ['organizationId' => $this->context->organization->id] + $officeIdFilter + $filterParams,
                    $paginationParams
                )
                ->andReturn(FinderResult::from($this->examples->offices, Pagination::create()));

            $this->interactor->handle(
                $this->context,
                [Permission::listInternalOffices(), Permission::viewInternalOffices()],
                $filterParams,
                $paginationParams
            );
        });
        $this->should('throw NotFoundException when any offices are not permitted', function (): void {
            $filterParams = [];
            $paginationParams = [
                'sortBy' => 'date',
                'itemsPerPage' => 10,
                'page' => 2,
            ];
            $permittedOffices = Seq::empty();
            $this->context
                ->expects('getPermittedOffices')
                ->andReturn(Option::some($permittedOffices))
                ->twice();

            $this->assertThrows(NotFoundException::class, function () use ($filterParams, $paginationParams): void {
                $this->interactor->handle(
                    $this->context,
                    [Permission::listInternalOffices(), Permission::viewInternalOffices()],
                    $filterParams,
                    $paginationParams
                );
            });
        });
        $this->should('not throw NotFoundException when any offices are permitted', function (): void {
            $filterParams = [];
            $paginationParams = [
                'sortBy' => 'date',
                'itemsPerPage' => 10,
                'page' => 2,
            ];
            $permittedOffices1 = Seq::empty();
            $permittedOffices2 = Seq::from($this->examples->offices[0]);
            $this->context
                ->expects('getPermittedOffices')
                ->andReturn(Option::some($permittedOffices1));
            $this->context
                ->expects('getPermittedOffices')
                ->andReturn(Option::some($permittedOffices2));

            $this->interactor->handle(
                $this->context,
                [Permission::listInternalOffices(), Permission::viewInternalOffices()],
                $filterParams,
                $paginationParams
            );
        });
    }
}

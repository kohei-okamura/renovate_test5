<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\App\Http\Controllers;

use App\Http\Controllers\LtcsAreaGradeController;
use App\Http\Requests\FindLtcsAreaGradeRequest;
use Domain\Common\Pagination;
use Domain\FinderResult;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Lib\Json;
use Mockery;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\FindLtcsAreaGradeUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\OfficeRepositoryMixin;
use Tests\Unit\Mixins\OrganizationResolverMixin;
use Tests\Unit\Mixins\RoleRepositoryMixin;
use Tests\Unit\Mixins\StaffResolverMixin;
use Tests\Unit\Test;

/**
 * LtcsAreaGradeController のテスト.
 */
class LtcsAreaGradeControllerTest extends Test
{
    use ContextMixin;
    use ExamplesConsumer;
    use FindLtcsAreaGradeUseCaseMixin;
    use MockeryMixin;
    use OfficeRepositoryMixin;
    use OrganizationResolverMixin;
    use RoleRepositoryMixin;
    use StaffResolverMixin;
    use UnitSupport;

    public const FILTER_PARAMS = [];
    public const PAGINATION_PARAMS = [
        'sortBy' => 'id',
        'all' => false,
        'desc' => true,
        'itemsPerPage' => 2,
        'page' => 1,
    ];

    private LtcsAreaGradeController $controller;
    private FinderResult $finderResult;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (LtcsAreaGradeControllerTest $self): void {
            $pagination = Pagination::create(self::PAGINATION_PARAMS);
            $self->finderResult = FinderResult::from($self->examples->ltcsAreaGrades, $pagination);
            $self->findLtcsAreaGradeUseCase->allows('handle')->andReturn($self->finderResult)->byDefault();

            $self->officeRepository
                ->allows('lookup')
                ->andReturn(Seq::from($self->examples->offices[0]));
            $self->roleRepository->allows('lookup')->andReturn(Seq::from($self->examples->roles[0]));
            $self->controller = app(LtcsAreaGradeController::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_getIndex(): void
    {
        app()->bind('request', fn () => Request::create(
            '/api/ltcs-area-grades',
            'GET',
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            Json::encode(self::FILTER_PARAMS + self::PAGINATION_PARAMS)
        ));
        app()->bind(FindLtcsAreaGradeRequest::class, function () {
            $request = Mockery::mock(FindLtcsAreaGradeRequest::class)->makePartial();
            $request->allows('context')->andReturn($this->context)->byDefault();
            $request->allows('filterParams')->andReturn(self::FILTER_PARAMS)->byDefault();
            $request->allows('paginationParams')->andReturn(self::PAGINATION_PARAMS)->byDefault();
            return $request;
        });
        $this->should('return a 200 response', function (): void {
            $this->assertSame(
                Response::HTTP_OK,
                app()->call([$this->controller, 'getIndex'])->getStatusCode()
            );
        });
        $this->should('return a JSON of FinderResult', function (): void {
            $this->assertSame(
                $this->finderResult->toJson(),
                app()->call([$this->controller, 'getIndex'])->getContent()
            );
        });
        $this->should('find LtcsAreaGrades using use case', function (): void {
            $this->findLtcsAreaGradeUseCase
                ->expects('handle')
                ->with($this->context, self::FILTER_PARAMS, self::PAGINATION_PARAMS)
                ->andReturn($this->finderResult);
            app()->call([$this->controller, 'getIndex']);
        });
    }
}

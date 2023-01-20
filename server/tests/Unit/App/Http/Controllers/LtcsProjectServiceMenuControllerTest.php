<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\App\Http\Controllers;

use App\Http\Controllers\LtcsProjectServiceMenuController;
use App\Http\Requests\StaffRequest;
use Domain\Common\Pagination;
use Domain\FinderResult;
use Illuminate\Http\Request as LumenRequest;
use Illuminate\Http\Response;
use Mockery;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\GetLtcsProjectServiceMenuListUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\OrganizationResolverMixin;
use Tests\Unit\Mixins\RequestMixin;
use Tests\Unit\Mixins\StaffResolverMixin;
use Tests\Unit\Test;

/**
 * {@link \App\Http\Controllers\LtcsProjectServiceMenuController} のテスト.
 */
class LtcsProjectServiceMenuControllerTest extends Test
{
    use CarbonMixin;
    use ContextMixin;
    use ExamplesConsumer;
    use GetLtcsProjectServiceMenuListUseCaseMixin;
    use MockeryMixin;
    use OrganizationResolverMixin;
    use RequestMixin;
    use StaffResolverMixin;
    use UnitSupport;

    private FinderResult $finderResult;
    private LtcsProjectServiceMenuController $controller;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (LtcsProjectServiceMenuControllerTest $self): void {
            $self->getLtcsProjectServiceMenuListUseCase
                ->allows('handle')
                ->andReturn(FinderResult::from($self->examples->ltcsProjectServiceMenus, Pagination::create()))
                ->byDefault();

            $self->finderResult = FinderResult::from($self->examples->ltcsProjectServiceMenus, Pagination::create());
            $self->controller = app(LtcsProjectServiceMenuController::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_getIndex(): void
    {
        app()->bind('request', fn () => LumenRequest::create(
            '/api/ltcs-project-service-menus',
            'GET',
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
        ));
        app()->bind(StaffRequest::class, function () {
            $request = Mockery::mock(StaffRequest::class)->makePartial();
            $request->allows('context')->andReturn($this->context)->byDefault();
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
        $this->should('find LtcsProjectServiceMenus using use case', function (): void {
            $this->getLtcsProjectServiceMenuListUseCase
                ->expects('handle')
                ->with($this->context, false)
                ->andReturn($this->finderResult);
            app()->call([$this->controller, 'getIndex']);
        });
    }
}

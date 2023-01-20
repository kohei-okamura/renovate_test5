<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\App\Http\Controllers;

use App\Http\Controllers\CallingController;
use App\Http\Requests\StaffRequest;
use Domain\Common\Pagination;
use Domain\FinderResult;
use Illuminate\Http\Request as LumenRequest;
use Illuminate\Http\Response;
use const JSON_UNESCAPED_UNICODE;
use Mockery;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\AcknowledgeStaffAttendanceUseCaseMixin;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\GetShiftsByTokenUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\OfficeRepositoryMixin;
use Tests\Unit\Mixins\OrganizationResolverMixin;
use Tests\Unit\Mixins\RequestMixin;
use Tests\Unit\Mixins\RoleRepositoryMixin;
use Tests\Unit\Mixins\StaffResolverMixin;
use Tests\Unit\Test;

/**
 * CallingController のテスト.
 */
class CallingControllerTest extends Test
{
    use AcknowledgeStaffAttendanceUseCaseMixin;
    use CarbonMixin;
    use ContextMixin;
    use ExamplesConsumer;
    use GetShiftsByTokenUseCaseMixin;
    use MockeryMixin;
    use OfficeRepositoryMixin;
    use OrganizationResolverMixin;
    use RequestMixin;
    use RoleRepositoryMixin;
    use StaffResolverMixin;
    use UnitSupport;

    public const TOKEN = '1234567890abcdefghij1234567890ABCDEFGHIJ12345678901234567890';

    private Seq $callingShifts;
    private FinderResult $finderResult;
    private CallingController $controller;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (CallingControllerTest $self): void {
            $self->callingShifts = Seq::fromArray([
                $self->examples->shifts[0],
                $self->examples->shifts[1],
            ]);
            $pagination = Pagination::create([
                'count' => $self->callingShifts->count(),
                'desc' => false,
                'itemsPerPage' => $self->callingShifts->count(),
                'page' => 1,
                'pages' => 1,
                'sortBy' => 'date',
            ]);
            $self->finderResult = FinderResult::from($self->callingShifts, $pagination);

            $self->acknowledgeStaffAttendanceUseCase
                ->allows('handle')
                ->byDefault();
            $self->getShiftsByTokenUseCase
                ->allows('handle')
                ->andReturn(FinderResult::from($self->callingShifts, $pagination))
                ->byDefault();

            $self->officeRepository
                ->allows('lookup')
                ->andReturn(Seq::from($self->examples->offices[0]));
            $self->roleRepository
                ->allows('lookup')
                ->andReturn(Seq::from($self->examples->roles[0]));

            $self->controller = app(CallingController::class);
        });
    }

    /**
     * @test
     * @returns void
     */
    public function describe_acknowledges(): void
    {
        app()->bind('request', fn () => LumenRequest::create(
            '/api/callings/{token}/acknowledges',
            'POST',
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json']
        ));
        app()->bind(StaffRequest::class, function () {
            $request = Mockery::mock(StaffRequest::class)->makePartial();
            $request->allows('context')->andReturn($this->context)->byDefault();
            return $request;
        });
        $this->should('return a 204 response', function (): void {
            $this->assertSame(
                Response::HTTP_NO_CONTENT,
                app()->call([$this->controller, 'acknowledges'], ['token' => self::TOKEN])->getStatusCode()
            );
        });
        $this->should('return an empty response', function (): void {
            $this->assertSame(
                '',
                app()->call([$this->controller, 'acknowledges'], ['token' => self::TOKEN])->getContent()
            );
        });
        $this->should('create CallingResponse using use case', function (): void {
            $this->acknowledgeStaffAttendanceUseCase
                ->expects('handle')
                ->with($this->context, self::TOKEN);

            app()->call([$this->controller, 'acknowledges'], ['token' => self::TOKEN]);
        });
    }

    /**
     * @test
     * @returns void
     */
    public function describe_shifts(): void
    {
        app()->bind('request', fn () => LumenRequest::create(
            '/api/callings/{token}/shifts',
            'GET',
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json']
        ));
        app()->bind(StaffRequest::class, function () {
            $request = Mockery::mock(StaffRequest::class)->makePartial();
            $request->allows('context')->andReturn($this->context)->byDefault();
            return $request;
        });
        $this->should('return a 200 response', function (): void {
            $this->assertSame(
                Response::HTTP_OK,
                app()->call([$this->controller, 'shifts'], ['token' => self::TOKEN])->getStatusCode()
            );
        });
        $this->should('return a JSON of Shift', function (): void {
            $this->assertSame(
                json_encode($this->finderResult, JSON_UNESCAPED_UNICODE),
                app()->call([$this->controller, 'shifts'], ['token' => self::TOKEN])->getContent()
            );
        });
        $this->should('get Shifts using use case', function (): void {
            $this->getShiftsByTokenUseCase
                ->expects('handle')
                ->with($this->context, self::TOKEN)
                ->andReturn($this->finderResult);

            app()->call([$this->controller, 'shifts'], ['token' => self::TOKEN]);
        });
    }
}

<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\App\Http\Controllers;

use App\Http\Controllers\JobController;
use App\Http\Requests\StaffRequest;
use Illuminate\Http\Request as LumenRequest;
use Illuminate\Http\Response;
use Lib\Exceptions\NotFoundException;
use Lib\Json;
use Mockery;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\LookupJobByTokenUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\OfficeRepositoryMixin;
use Tests\Unit\Mixins\OrganizationResolverMixin;
use Tests\Unit\Mixins\RoleRepositoryMixin;
use Tests\Unit\Mixins\StaffResolverMixin;
use Tests\Unit\Test;

/**
 * {@link \App\Http\Controllers\JobController} のテスト.
 */
class JobControllerTest extends Test
{
    use ContextMixin;
    use ExamplesConsumer;
    use LookupJobByTokenUseCaseMixin;
    use MockeryMixin;
    use OfficeRepositoryMixin;
    use OrganizationResolverMixin;
    use RoleRepositoryMixin;
    use StaffResolverMixin;
    use UnitSupport;

    private JobController $controller;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (JobControllerTest $self): void {
            $self->lookupJobByTokenUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->examples->jobs[0]))
                ->byDefault();
            $self->officeRepository
                ->allows('lookup')
                ->andReturn(Seq::from($self->examples->offices[0]));
            $self->roleRepository
                ->allows('lookup')
                ->andReturn(Seq::from($self->examples->roles[0]));
            $self->controller = app(JobController::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_get(): void
    {
        app()->bind('request', fn () => LumenRequest::create(
            '/api/jobs/{token}',
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
                app()->call([$this->controller, 'get'], ['token' => $this->examples->jobs[0]->token])->getStatusCode()
            );
        });
        $this->should('return a JSON of Job', function (): void {
            $response = app()->call([$this->controller, 'get'], ['token' => $this->examples->jobs[0]->token]);

            $job = $this->examples->jobs[0];
            $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
            $this->assertSame(Json::encode(compact('job')), $response->getContent());
        });
        $this->should('get Job using use case', function (): void {
            $this->lookupJobByTokenUseCase
                ->expects('handle')
                ->with($this->context, $this->examples->jobs[0]->token)
                ->andReturn(Seq::from($this->examples->jobs[0]));

            app()->call([$this->controller, 'get'], ['token' => $this->examples->jobs[0]->token]);
        });
        $this->should('throw a NotFoundException when the id not exists in db', function (): void {
            $this->lookupJobByTokenUseCase
                ->expects('handle')
                ->with($this->context, self::NOT_EXISTING_TOKEN)
                ->andReturn(Seq::emptySeq());

            $this->assertThrows(
                NotFoundException::class,
                function (): void {
                    app()->call([$this->controller, 'get'], ['token' => self::NOT_EXISTING_TOKEN]);
                }
            );
        });
    }
}

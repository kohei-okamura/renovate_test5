<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\App\Http\Controllers;

use App\Http\Controllers\DownloadController;
use App\Http\Requests\StaffRequest;
use Illuminate\Http\Request as LumenRequest;
use Illuminate\Http\Response;
use Lib\Exceptions\NotFoundException;
use Mockery;
use ScalikePHP\Option;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\CreateJobUseCaseMixin;
use Tests\Unit\Mixins\DownloadFileUseCaseMixin;
use Tests\Unit\Mixins\FindUserBillingUseCaseMixin;
use Tests\Unit\Mixins\JobsDispatcherMixin;
use Tests\Unit\Mixins\LookupUserBillingUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\OrganizationResolverMixin;
use Tests\Unit\Mixins\RequestMixin;
use Tests\Unit\Mixins\StaffResolverMixin;
use Tests\Unit\Test;

/**
 * {@link \App\Http\Controllers\DownloadController} のテスト.
 */
final class DownloadControllerTest extends Test
{
    use CarbonMixin;
    use CreateJobUseCaseMixin;
    use ContextMixin;
    use CreateJobUseCaseMixin;
    use DownloadFileUseCaseMixin;
    use ExamplesConsumer;
    use FindUserBillingUseCaseMixin;
    use MockeryMixin;
    use OrganizationResolverMixin;
    use RequestMixin;
    use StaffResolverMixin;
    use UnitSupport;
    use LookupUserBillingUseCaseMixin;
    use JobsDispatcherMixin;

    private const DIR = 'artifacts';
    private const FILENAME = 'dummy.pdf';
    private $resource;

    private DownloadController $controller;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (self $self): void {
            $self->createJobUseCase
                ->allows('handle')
                ->andReturn($self->examples->jobs[0])
                ->byDefault();

            $self->resource = tmpfile();
            $self->downloadFileUseCase
                ->allows('handle')
                ->andReturn(Option::from($self->resource))
                ->byDefault();

            $self->controller = app(DownloadController::class);
        });
    }

    /**
     * @test
     * @returns void
     */
    public function describe_download(): void
    {
        app()->bind('request', fn () => LumenRequest::create(
            '/api/download/{dir}/{filename}',
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
                app()->call(
                    [$this->controller, 'download'],
                    ['dir' => self::DIR, 'filename' => self::FILENAME]
                )->getStatusCode()
            );
        });
        $this->should('return a StreamedResponse', function (): void {
            $response = app()->call(
                [$this->controller, 'download'],
                ['dir' => self::DIR, 'filename' => self::FILENAME]
            );

            $this->assertInstanceOf(StreamedResponse::class, $response);
        });
        $this->should('get Option by using UseCase', function (): void {
            $this->downloadFileUseCase
                ->expects('handle')
                ->with($this->context, self::DIR . '/' . self::FILENAME)
                ->andReturn(Option::from($this->resource));

            app()->call([$this->controller, 'download'], ['dir' => self::DIR, 'filename' => self::FILENAME]);
        });
        $this->should('throw NotFoundException when resource is not found', function (): void {
            $this->downloadFileUseCase
                ->expects('handle')
                ->with($this->context, self::DIR . '/' . self::FILENAME)
                ->andReturn(Option::none());

            $this->assertThrows(
                NotFoundException::class,
                function (): void {
                    app()->call([$this->controller, 'download'], ['dir' => self::DIR, 'filename' => self::FILENAME]);
                }
            );
        });
    }
}

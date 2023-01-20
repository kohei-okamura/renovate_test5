<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\App\Http\Controllers;

use App\Http\Controllers\DwsBillingFileController;
use App\Http\Requests\StaffRequest;
use Illuminate\Http\Request as LumenRequest;
use Illuminate\Http\Response;
use Lib\Json;
use Mockery;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\GetDwsBillingFileInfoUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\OrganizationResolverMixin;
use Tests\Unit\Mixins\StaffResolverMixin;
use Tests\Unit\Test;

/**
 * {@link \App\Http\Controllers\DwsBillingFileController} のテスト.
 */
final class DwsBillingFileControllerTest extends Test
{
    use ContextMixin;
    use ExamplesConsumer;
    use GetDwsBillingFileInfoUseCaseMixin;
    use MatchesSnapshots;
    use MockeryMixin;
    use OrganizationResolverMixin;
    use StaffResolverMixin;
    use UnitSupport;

    public const TEMPORARY_URL = 'temporary-url';

    private DwsBillingFileController $controller;

    /**
     * 初期化処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachSpec(function (self $self): void {
            $self->getDwsBillingFileInfoUseCase
                ->allows('handle')
                ->andReturn(self::TEMPORARY_URL)
                ->byDefault();

            $self->controller = app(DwsBillingFileController::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_get(): void
    {
        app()->bind('request', fn () => LumenRequest::create(
            '/api/dws-billing/{billingId}/files/{token}',
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
                app()->call(
                    [$this->controller, 'get'],
                    [
                        'dwsBillingId' => $this->examples->dwsBillings[0]->id,
                        'token' => $this->examples->dwsBillings[0]->files[0]->token,
                    ]
                )->getStatusCode()
            );
        });
        $this->should('return a JSON', function (): void {
            $response = app()->call(
                [$this->controller, 'get'],
                [
                    'dwsBillingId' => $this->examples->dwsBillings[0]->id,
                    'token' => $this->examples->dwsBillings[0]->files[0]->token,
                ]
            );

            $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
            $this->assertSame(Json::encode(['url' => self::TEMPORARY_URL]), $response->getContent());
        });
        $this->should('get array using use case', function (): void {
            $this->getDwsBillingFileInfoUseCase
                ->expects('handle')
                ->with($this->context, $this->examples->dwsBillings[0]->id, $this->examples->dwsBillings[0]->files[0]->token)
                ->andReturn(self::TEMPORARY_URL);

            app()->call([$this->controller, 'get'], [
                'dwsBillingId' => $this->examples->dwsBillings[0]->id,
                'token' => $this->examples->dwsBillings[0]->files[0]->token,
            ]);
        });
    }
}

<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\App\Http\Controllers;

use App\Http\Controllers\LtcsBillingFileController;
use App\Http\Requests\StaffRequest;
use Illuminate\Http\Request as LumenRequest;
use Illuminate\Http\Response;
use Lib\Json;
use Mockery;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\GetLtcsBillingFileInfoUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\OrganizationResolverMixin;
use Tests\Unit\Mixins\StaffResolverMixin;
use Tests\Unit\Test;

/**
 * {@link \App\Http\Controllers\LtcsBillingFileController} のテスト.
 */
final class LtcsBillingFileControllerTest extends Test
{
    use ContextMixin;
    use ExamplesConsumer;
    use GetLtcsBillingFileInfoUseCaseMixin;
    use MatchesSnapshots;
    use MockeryMixin;
    use OrganizationResolverMixin;
    use StaffResolverMixin;
    use UnitSupport;

    public const TEMPORARY_URL = 'temporary-url';

    private LtcsBillingFileController $controller;

    /**
     * 初期化処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachSpec(function (self $self): void {
            $self->getLtcsBillingFileInfoUseCase
                ->allows('handle')
                ->andReturn(self::TEMPORARY_URL)
                ->byDefault();

            $self->controller = app(LtcsBillingFileController::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_get(): void
    {
        app()->bind('request', fn () => LumenRequest::create(
            '/api/ltcs-billing/{billingId}/files/{token}',
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
                        'id' => $this->examples->ltcsBillings[0]->id,
                        'token' => $this->examples->ltcsBillings[0]->files[0]->token,
                    ]
                )->getStatusCode()
            );
        });
        $this->should('return a JSON', function (): void {
            $response = app()->call(
                [$this->controller, 'get'],
                [
                    'id' => $this->examples->ltcsBillings[0]->id,
                    'token' => $this->examples->ltcsBillings[0]->files[0]->token,
                ]
            );

            $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
            $this->assertSame(Json::encode(['url' => self::TEMPORARY_URL]), $response->getContent());
        });
        $this->should('get array using use case', function (): void {
            $this->getLtcsBillingFileInfoUseCase
                ->expects('handle')
                ->with($this->context, $this->examples->ltcsBillings[0]->id, $this->examples->ltcsBillings[0]->files[0]->token)
                ->andReturn(self::TEMPORARY_URL);

            app()->call([$this->controller, 'get'], [
                'id' => $this->examples->ltcsBillings[0]->id,
                'token' => $this->examples->ltcsBillings[0]->files[0]->token,
            ]);
        });
    }
}

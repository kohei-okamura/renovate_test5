<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\App\Http\Controllers;

use App\Http\Controllers\DwsBillingCopayCoordinationController;
use App\Http\Requests\CreateDwsBillingCopayCoordinationRequest;
use App\Http\Requests\StaffRequest;
use App\Http\Requests\UpdateDwsBillingCopayCoordinationRequest;
use App\Http\Requests\UpdateDwsBillingCopayCoordinationStatusRequest;
use Domain\Billing\CopayCoordinationResult;
use Domain\Billing\DwsBilling;
use Domain\Billing\DwsBillingBundle;
use Domain\Billing\DwsBillingCopayCoordination;
use Domain\Billing\DwsBillingCopayCoordinationExchangeAim;
use Domain\Billing\DwsBillingCopayCoordinationItem;
use Domain\Billing\DwsBillingCopayCoordinationPayment;
use Domain\Billing\DwsBillingCopayCoordinationPdf;
use Domain\Billing\DwsBillingStatus;
use Domain\Common\Pagination;
use Domain\FinderResult;
use Illuminate\Http\Request as LumenRequest;
use Illuminate\Http\Response;
use Lib\Arrays;
use Lib\Json;
use Mockery;
use ScalikePHP\Option;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\ConfigMixin;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\CreateDwsBillingCopayCoordinationUseCaseMixin;
use Tests\Unit\Mixins\DownloadDwsBillingCopayCoordinationUseCaseMixin;
use Tests\Unit\Mixins\DwsBillingStatementFinderMixin;
use Tests\Unit\Mixins\EditDwsBillingCopayCoordinationUseCaseMixin;
use Tests\Unit\Mixins\GetDwsBillingCopayCoordinationInfoUseCaseMixin;
use Tests\Unit\Mixins\GetOfficeListUseCaseMixin;
use Tests\Unit\Mixins\IdentifyDwsCertificationUseCaseMixin;
use Tests\Unit\Mixins\LookupDwsBillingBundleUseCaseMixin;
use Tests\Unit\Mixins\LookupDwsBillingUseCaseMixin;
use Tests\Unit\Mixins\LookupDwsCertificationUseCaseMixin;
use Tests\Unit\Mixins\LookupOfficeUseCaseMixin;
use Tests\Unit\Mixins\LookupUserUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\OrganizationResolverMixin;
use Tests\Unit\Mixins\SnappyMixin;
use Tests\Unit\Mixins\StaffResolverMixin;
use Tests\Unit\Mixins\UpdateDwsBillingCopayCoordinationStatusUseCaseMixin;
use Tests\Unit\Mixins\ValidateCopayCoordinationItemUseCaseMixin;
use Tests\Unit\Test;

/**
 * {@link \App\Http\Controllers\DwsBillingCopayCoordinationController} のテスト.
 */
final class DwsBillingCopayCoordinationControllerTest extends Test
{
    use CarbonMixin;
    use ConfigMixin;
    use ContextMixin;
    use CreateDwsBillingCopayCoordinationUseCaseMixin;
    use DownloadDwsBillingCopayCoordinationUseCaseMixin;
    use DwsBillingStatementFinderMixin;
    use UpdateDwsBillingCopayCoordinationStatusUseCaseMixin;
    use ExamplesConsumer;
    use GetDwsBillingCopayCoordinationInfoUseCaseMixin;
    use GetOfficeListUseCaseMixin;
    use IdentifyDwsCertificationUseCaseMixin;
    use LookupDwsBillingBundleUseCaseMixin;
    use LookupDwsBillingUseCaseMixin;
    use LookupDwsCertificationUseCaseMixin;
    use LookupOfficeUseCaseMixin;
    use LookupUserUseCaseMixin;
    use MockeryMixin;
    use OrganizationResolverMixin;
    use SnappyMixin;
    use StaffResolverMixin;
    use UnitSupport;
    use EditDwsBillingCopayCoordinationUseCaseMixin;
    use ValidateCopayCoordinationItemUseCaseMixin;

    private array $copayCoordinationPdfValues;

    private DwsBilling $billing;
    private DwsBillingBundle $bundle;
    private DwsBillingCopayCoordination $copayCoordination;
    private DwsBillingCopayCoordinationController $controller;
    private Response $pdfResponse;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachTest(function (self $self): void {
            $self->billing = $self->examples->dwsBillings[0]->copy([
                'id' => 14141356,
            ]);
            $self->bundle = $self->examples->dwsBillingBundles[0]->copy([
                'id' => 17320508,
                'dwsBillingId' => 14141356,
            ]);
            $self->copayCoordination = $self->examples->dwsBillingCopayCoordinations[0]->copy([
                'id' => 22360679,
                'dwsBillingId' => 14141356,
                'dwsBillingBundleId' => 17320508,
            ]);
            $self->copayCoordinationPdfValues = [
                'filename' => 'dws_billing_copay_coordination_pdf.pdf',
                'params' => [
                    'bundles' => [
                        [
                            'copayCoordinations' => [
                                DwsBillingCopayCoordinationPdf::from($self->bundle, $self->copayCoordination),
                            ],
                        ],
                    ],
                ],
            ];
            $self->pdfResponse = new Response(
                content: '',
                status: Response::HTTP_OK,
                headers: [
                    'Content-Type' => 'application/pdf',
                    'Content-Disposition' => 'attachment; filename="xxxx.pdf"',
                ]
            );
        });
        self::beforeEachSpec(function (self $self): void {
            $self->snappy
                ->allows('setOption')
                ->andReturnSelf()
                ->byDefault();
            $self->snappy
                ->allows('loadHTML')
                ->andReturnSelf()
                ->byDefault();
            $self->snappy
                ->allows('download')
                ->andReturn($self->pdfResponse)
                ->byDefault();
            $self->config
                ->allows('filename')
                ->andReturn('xxxx.pdf')
                ->byDefault();
            $self->dwsBillingStatementFinder
                ->allows('find')
                ->andReturn(FinderResult::from([], Pagination::create()))
                ->byDefault();
            $self->createDwsBillingCopayCoordinationUseCase
                ->allows('handle')
                ->andReturn([
                    'billing' => $self->billing,
                    'bundle' => $self->bundle,
                    'copayCoordination' => $self->copayCoordination,
                ])
                ->byDefault();
            $self->downloadDwsBillingCopayCoordinationUseCase
                ->allows('handle')
                ->andReturn($self->copayCoordinationPdfValues)
                ->byDefault();
            $self->getDwsBillingCopayCoordinationInfoUseCase
                ->allows('handle')
                ->andReturn([
                    'billing' => $self->billing,
                    'bundle' => $self->bundle,
                    'copayCoordination' => $self->copayCoordination,
                ])
                ->byDefault();
            $self->identifyDwsCertificationUseCase
                ->allows('handle')
                ->andReturn(Option::from($self->examples->dwsCertifications[0]))
                ->byDefault();
            $self->lookupDwsCertificationUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->examples->dwsCertifications[0]))
                ->byDefault();
            $self->lookupDwsBillingUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->examples->dwsBillings[0]))
                ->byDefault();
            $self->lookupDwsBillingBundleUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->examples->dwsBillingBundles[1]))
                ->byDefault();
            $self->lookupOfficeUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->examples->offices[0]))
                ->byDefault();
            $self->lookupUserUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->examples->users[0]))
                ->byDefault();
            $self->editDwsBillingCopayCoordinationUseCase
                ->allows('handle')
                ->andReturn([
                    'billing' => $self->billing,
                    'bundle' => $self->bundle,
                    'copayCoordination' => $self->copayCoordination,
                ])
                ->byDefault();
            $self->updateDwsBillingCopayCoordinationStatusUseCase
                ->allows('handle')
                ->andReturn([
                    'billing' => $self->billing,
                    'bundle' => $self->bundle,
                    'copayCoordination' => $self->copayCoordination,
                ])
                ->byDefault();
            $self->validateCopayCoordinationItemUseCase
                ->allows('handle')
                ->andReturn(true)
                ->byDefault();
            $self->getOfficeListUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->examples->offices[0]))
                ->byDefault();

            $self->controller = app(DwsBillingCopayCoordinationController::class);
        });
    }

    /**
     * @test
     * @returns void
     */
    public function describe_create(): void
    {
        app()->bind('request', fn (): LumenRequest => LumenRequest::create(
            uri: '/api/dws-billings/{dwsBillingId}/bundles/{dwsBundleId}/copay-coordinations',
            method: 'POST',
            parameters: [],
            cookies: [],
            files: [],
            server: ['CONTENT_TYPE' => 'application/json'],
            content: Json::encode($this->input())
        ));
        app()->bind(
            CreateDwsBillingCopayCoordinationRequest::class,
            function (): CreateDwsBillingCopayCoordinationRequest {
                $request = Mockery::mock(CreateDwsBillingCopayCoordinationRequest::class)->makePartial();
                $request->allows('context')->andReturn($this->context)->byDefault();
                return $request;
            }
        );
        $this->specify('201 Created を返す', function (): void {
            $actual = app()
                ->call([$this->controller, 'create'], [
                    'dwsBillingId' => 14141356,
                    'dwsBundleId' => 17320508,
                ])
                ->status();

            $this->assertSame(Response::HTTP_CREATED, $actual);
        });
        $this->specify('DwsBillingCopayCoordination とその関連情報を JSON 形式で返す', function (): void {
            $expected = Json::encode([
                'billing' => $this->billing,
                'bundle' => $this->bundle,
                'copayCoordination' => $this->copayCoordination,
            ]);

            $actual = app()
                ->call([$this->controller, 'create'], [
                    'dwsBillingId' => 14141356,
                    'dwsBundleId' => 17320508,
                ])
                ->content();

            $this->assertSame($expected, $actual);
        });
        $this->specify('利用者負担上限額管理結果票を登録する', function (): void {
            $input = $this->input();
            $items = Arrays::generate(function () use ($input): iterable {
                foreach ($input['items'] as $itemNumber => $item) {
                    yield [
                        'itemNumber' => $itemNumber + 1, // 1〜
                        'officeId' => $item['officeId'],
                        'subtotal' => DwsBillingCopayCoordinationPayment::create([
                            'fee' => $item['subtotal']['fee'],
                            'copay' => $item['subtotal']['copay'],
                            'coordinatedCopay' => $item['subtotal']['coordinatedCopay'],
                        ]),
                    ];
                }
            });
            $this->createDwsBillingCopayCoordinationUseCase
                ->expects('handle')
                ->with(
                    $this->context,
                    14141356,
                    17320508,
                    $input['userId'],
                    CopayCoordinationResult::from($input['result']),
                    DwsBillingCopayCoordinationExchangeAim::from($input['exchangeAim']),
                    equalTo($items)
                )
                ->andReturn([
                    'billing' => $this->examples->dwsBillings[0],
                    'bundle' => $this->examples->dwsBillingBundles[1],
                    'copayCoordination' => $this->examples->dwsBillingCopayCoordinations[0],
                ]);

            app()->call([$this->controller, 'create'], [
                'dwsBillingId' => 14141356,
                'dwsBundleId' => 17320508,
            ]);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_get(): void
    {
        app()->bind('request', fn (): LumenRequest => LumenRequest::create(
            uri: '/api/dws-billings/{dwsBillingId}/bundles/{dwsBillingBundleId}/copay-coordinations/{id}',
            method: 'GET',
        ));
        app()->bind(StaffRequest::class, function (): StaffRequest {
            $request = Mockery::mock(StaffRequest::class)->makePartial();
            $request->allows('context')->andReturn($this->context)->byDefault();
            return $request;
        });
        $this->specify('200 OK を返す', function (): void {
            $actual = app()
                ->call([$this->controller, 'get'], [
                    'dwsBillingId' => 14141356,
                    'dwsBillingBundleId' => 17320508,
                    'id' => 22360679,
                ])
                ->status();

            $this->assertSame(Response::HTTP_OK, $actual);
        });
        $this->specify('利用者負担上限額管理結果票とその関連情報を JSON 形式で返す', function (): void {
            $expected = Json::encode([
                'billing' => $this->billing,
                'bundle' => $this->bundle,
                'copayCoordination' => $this->copayCoordination,
            ]);

            $actual = app()
                ->call([$this->controller, 'get'], [
                    'dwsBillingId' => 14141356,
                    'dwsBillingBundleId' => 17320508,
                    'id' => 22360679,
                ])
                ->content();

            $this->assertSame($expected, $actual);
        });
        $this->specify('DwsBillingCopayCoordination とその関連情報を取得する', function (): void {
            $this->getDwsBillingCopayCoordinationInfoUseCase
                ->expects('handle')
                ->with(
                    $this->context,
                    14141356,
                    17320508,
                    22360679
                )
                ->andReturn([
                    'billing' => $this->billing,
                    'bundle' => $this->bundle,
                    'copayCoordination' => $this->copayCoordination,
                ]);

            app()->call([$this->controller, 'get'], [
                'dwsBillingId' => 14141356,
                'dwsBillingBundleId' => 17320508,
                'id' => 22360679,
            ]);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_update(): void
    {
        app()->bind('request', fn (): LumenRequest => LumenRequest::create(
            uri: '/api/dws-billings/{dwsBillingId}/bundles/{dwsBillingBundleId}/copay-coordinations/{id}',
            method: 'PUT',
            parameters: [],
            cookies: [],
            files: [],
            server: ['CONTENT_TYPE' => 'application/json'],
            content: Json::encode($this->input())
        ));
        app()->bind(
            UpdateDwsBillingCopayCoordinationRequest::class,
            function (): UpdateDwsBillingCopayCoordinationRequest {
                $request = Mockery::mock(UpdateDwsBillingCopayCoordinationRequest::class)->makePartial();
                $request->allows('context')->andReturn($this->context)->byDefault();
                return $request;
            }
        );
        $this->specify('200 OK を返す', function (): void {
            $actual = app()
                ->call([$this->controller, 'update'], [
                    'dwsBillingId' => 14141356,
                    'dwsBillingBundleId' => 17320508,
                    'id' => 22360679,
                ])
                ->status();

            $this->assertSame(Response::HTTP_OK, $actual);
        });
        $this->specify('利用者負担上限額管理結果票とその関連情報を JSON 形式で返す', function (): void {
            $expected = Json::encode([
                'billing' => $this->billing,
                'bundle' => $this->bundle,
                'copayCoordination' => $this->copayCoordination,
            ]);

            $actual = app()
                ->call([$this->controller, 'update'], [
                    'dwsBillingId' => 14141356,
                    'dwsBillingBundleId' => 17320508,
                    'id' => 22360679,
                ])
                ->content();

            $this->assertSame($expected, $actual);
        });
        $this->specify('利用者負担上限額管理結果票を編集する', function (): void {
            $input = $this->input();
            $items = Arrays::generate(function () use ($input): iterable {
                foreach ($input['items'] as $itemNumber => $item) {
                    yield [
                        'itemNumber' => $itemNumber + 1, // 1〜
                        'officeId' => $item['officeId'],
                        'subtotal' => DwsBillingCopayCoordinationPayment::create([
                            'fee' => $item['subtotal']['fee'],
                            'copay' => $item['subtotal']['copay'],
                            'coordinatedCopay' => $item['subtotal']['coordinatedCopay'],
                        ]),
                    ];
                }
            });
            $this->editDwsBillingCopayCoordinationUseCase
                ->expects('handle')
                ->with(
                    $this->context,
                    14141356,
                    17320508,
                    22360679,
                    31415926,
                    CopayCoordinationResult::from($input['result']),
                    DwsBillingCopayCoordinationExchangeAim::from($input['exchangeAim']),
                    equalTo($items)
                )
                ->andReturn([
                    'billing' => $this->billing,
                    'bundle' => $this->bundle,
                    'copayCoordination' => $this->copayCoordination,
                ]);

            app()->call([$this->controller, 'update'], [
                'dwsBillingId' => 14141356,
                'dwsBillingBundleId' => 17320508,
                'id' => 22360679,
            ]);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_status(): void
    {
        app()->bind('request', fn (): LumenRequest => LumenRequest::create(
            uri: '/api/dws-billings/{billingId}/bundles/{billingBundleId}/copay-coordinations/{id}/status',
            method: 'PUT',
            parameters: [],
            cookies: [],
            files: [],
            server: ['CONTENT_TYPE' => 'application/json'],
            content: Json::encode([
                'status' => DwsBillingStatus::fixed(),
            ])
        ));
        app()->bind(
            UpdateDwsBillingCopayCoordinationStatusRequest::class,
            function (): UpdateDwsBillingCopayCoordinationStatusRequest {
                $request = Mockery::mock(UpdateDwsBillingCopayCoordinationStatusRequest::class)->makePartial();
                $request->allows('context')->andReturn($this->context)->byDefault();
                return $request;
            }
        );
        $this->should('return a 200 response', function (): void {
            $actual = app()
                ->call([$this->controller, 'status'], [
                    'dwsBillingId' => 14141356,
                    'dwsBillingBundleId' => 17320508,
                    'id' => 22360679,
                ])
                ->status();

            $this->assertSame(Response::HTTP_OK, $actual);
        });
        $this->specify('利用者負担上限額管理結果票とその関連情報を JSON 形式で返す', function (): void {
            $expected = Json::encode([
                'billing' => $this->billing,
                'bundle' => $this->bundle,
                'copayCoordination' => $this->copayCoordination,
            ]);

            $actual = app()
                ->call([$this->controller, 'status'], [
                    'dwsBillingId' => 14141356,
                    'dwsBillingBundleId' => 17320508,
                    'id' => 22360679,
                ])
                ->content();

            $this->assertSame($expected, $actual);
        });
        $this->should('利用者負担上限額管理結果票の状態を変更する', function (): void {
            $this->updateDwsBillingCopayCoordinationStatusUseCase
                ->expects('handle')
                ->with(
                    $this->context,
                    14141356,
                    17320508,
                    22360679,
                    DwsBillingStatus::fixed()
                )
                ->andReturn([
                    'billing' => $this->billing,
                    'bundle' => $this->bundle,
                    'copayCoordination' => $this->copayCoordination,
                ]);

            app()->call([$this->controller, 'status'], [
                'dwsBillingId' => 14141356,
                'dwsBillingBundleId' => 17320508,
                'id' => 22360679,
            ]);
        });
    }

    /**
     * @test
     * @returns void
     */
    public function describe_download(): void
    {
        app()->bind('request', fn (): LumenRequest => LumenRequest::create(
            uri: '/api/dws-billings/{dwsBillingId}/bundles/{dwsBillingBundleId}/copay-coordinations/{id}.pdf',
            method: 'GET',
        ));
        app()->bind(StaffRequest::class, function (): StaffRequest {
            $request = Mockery::mock(StaffRequest::class)->makePartial();
            $request->allows('context')->andReturn($this->context)->byDefault();
            return $request;
        });
        $this->specify('200 OK を返す', function (): void {
            $actual = app()
                ->call([$this->controller, 'download'], [
                    'dwsBillingId' => 14141356,
                    'dwsBillingBundleId' => 17320508,
                    'id' => 22360679,
                ])
                ->status();

            $this->assertSame(Response::HTTP_OK, $actual);
        });
        $this->should('利用者負担上限額管理結果票を PDF 形式で返す', function (): void {
            $actual = app()->call([$this->controller, 'download'], [
                'dwsBillingId' => 14141356,
                'dwsBillingBundleId' => 17320508,
                'id' => 22360679,
            ]);

            $this->assertSame($this->pdfResponse, $actual);
        });
        $this->should('利用者負担上限額管理結果票 PDF を生成するためのパラメータを取得する', function (): void {
            $this->downloadDwsBillingCopayCoordinationUseCase
                ->expects('handle')
                ->with(
                    $this->context,
                    14141356,
                    17320508,
                    22360679,
                )
                ->andReturn($this->copayCoordinationPdfValues);

            app()->call([$this->controller, 'download'], [
                'dwsBillingId' => 14141356,
                'dwsBillingBundleId' => 17320508,
                'id' => 22360679,
            ]);
        });
    }

    /**
     * 登録/更新用の入力値を返す.
     *
     * @return array
     */
    private function input(): array
    {
        return [
            'dwsBundleId' => 17320508,
            'userId' => 31415926,
            'result' => $this->copayCoordination->result->value(),
            'exchangeAim' => DwsBillingCopayCoordinationExchangeAim::declaration()->value(),
            'isProvided' => true,
            'items' => Seq::fromArray($this->copayCoordination->items)
                ->map(fn (DwsBillingCopayCoordinationItem $item) => [
                    'itemNumber' => $item->itemNumber,
                    'officeId' => $item->office->officeId,
                    'subtotal' => [
                        'fee' => $item->subtotal->fee,
                        'copay' => $item->subtotal->copay,
                        'coordinatedCopay' => $item->subtotal->coordinatedCopay,
                    ],
                ])
                ->toArray(),
        ];
    }

    /**
     * 状態更新リクエストクラスの戻り値を返す.
     *
     * @return DwsBillingStatus
     */
    private function statusPayload(): DwsBillingStatus
    {
        return $this->examples->dwsBillingStatements[0]->status;
    }
}

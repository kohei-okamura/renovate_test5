<?php
/**
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\Office;

use Domain\Common\Pagination;
use Domain\FinderResult;
use Domain\Office\Purpose;
use Domain\Permission\Permission;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\FindOfficeUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;
use UseCase\Office\GetIndexOfficeInteractor;

/**
 * \UseCase\Office\GetIndexOfficeInteractor のテスト.
 */
final class GetIndexOfficeInteractorTest extends Test
{
    use ContextMixin;
    use ExamplesConsumer;
    use FindOfficeUseCaseMixin;
    use MockeryMixin;
    use UnitSupport;

    private const PAGINATION_PARAMS = [
        'all' => false,
        'desc' => true,
        'itemsPerPage' => 2,
        'page' => 1,
        'sortBy' => 'date',
    ];

    private FinderResult $finderResult;
    private GetIndexOfficeInteractor $interactor;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (GetIndexOfficeInteractorTest $self): void {
            $self->finderResult = FinderResult::from($self->examples->offices, Pagination::create(self::PAGINATION_PARAMS));
            $self->context
                ->allows('isAuthorizedTo')
                ->andReturn(true)
                ->byDefault();
            $self->findOfficeUseCase
                ->allows('handle')
                ->andReturn($self->finderResult)
                ->byDefault();

            $self->interactor = app(GetIndexOfficeInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('find Offices using FindOfficeUseCase', function (): void {
            $permissions = [Permission::listInternalOffices(), Permission::listExternalOffices()];
            $filterParams = [];
            $paginationParams = self::PAGINATION_PARAMS;

            $this->findOfficeUseCase
                ->expects('handle')
                ->with(
                    $this->context,
                    $permissions,
                    $filterParams,
                    $paginationParams
                )
                ->andReturn($this->finderResult);

            $this->interactor->handle($this->context, $permissions, $filterParams, $paginationParams);
        });
        $this->should(
            'be filtered by the condition',
            function ($params): void {
                $this->markTestSkipped();
                $permissions = [$params['permission']];
                // フィルターで purpose を指定しても適用されないことを確認する
                $filterParams = ['q' => '新宿', 'purpose' => $params['specified']];
                $paginationParams = self::PAGINATION_PARAMS;

                $this->context
                    ->allows('isAuthorizedTo')
                    ->with($params['notPermitted'])
                    ->andReturn(false);

                $this->findOfficeUseCase
                    ->expects('handle')
                    ->with(
                        $this->context,
                        $permissions,
                        [...$filterParams, ...['purpose' => $params['actual']]],
                        $paginationParams
                    )
                    ->andReturn($this->finderResult);

                $this->interactor->handle($this->context, $permissions, $filterParams, $paginationParams);
            },
            [
                'examples' => [
                    '"purpose is internal." if the staff only has the listInternalOffices permission' => [
                        [
                            'permission' => Permission::listInternalOffices(),
                            'notPermitted' => Permission::listExternalOffices(),
                            'specified' => Purpose::external(),
                            'actual' => Purpose::internal(),
                        ],
                    ],
                    '"purpose is external." if the staff only has the listExternalOffices permission' => [
                        [
                            'permission' => Permission::listExternalOffices(),
                            'notPermitted' => Permission::listInternalOffices(),
                            'specified' => Purpose::internal(),
                            'actual' => Purpose::external(),
                        ],
                    ],
                ],
            ]
        );
        $this->should(
            'be filtered by the specified purpose if the staff has both permissions',
            function ($params): void {
                $permissions = [Permission::listExternalOffices(), Permission::listExternalOffices()];
                $filterParams = ['q' => '新宿', 'purpose' => $params['specified']];
                $paginationParams = self::PAGINATION_PARAMS;

                $this->findOfficeUseCase
                    ->expects('handle')
                    ->with(
                        $this->context,
                        $permissions,
                        $filterParams,
                        $paginationParams
                    )
                    ->andReturn($this->finderResult);

                $this->interactor->handle($this->context, $permissions, $filterParams, $paginationParams);
            },
            [
                'examples' => [
                    'internal was specified' => [
                        [
                            'specified' => Purpose::internal(),
                        ],
                    ],
                    'external was specified' => [
                        [
                            'specified' => Purpose::external(),
                        ],
                    ],
                ],
            ]
        );
    }
}

<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\Contract;

use Domain\Common\Pagination;
use Domain\Common\ServiceSegment;
use Domain\Contract\ContractStatus;
use Domain\FinderResult;
use Domain\Permission\Permission;
use Mockery;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\FindContractUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;
use UseCase\Contract\GetOverlapContractInteractor;

/**
 * {@link \UseCase\Contract\GetOverlapContractInteractor} Test.
 */
final class GetOverlapContractInteractorTest extends Test
{
    use CarbonMixin;
    use ContextMixin;
    use ExamplesConsumer;
    use FindContractUseCaseMixin;
    use MockeryMixin;
    use UnitSupport;

    private GetOverlapContractInteractor $interactor;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachSpec(function (self $self): void {
            $self->findContractUseCase
                ->allows('handle')
                ->andReturn(FinderResult::from(Seq::emptySeq(), Pagination::create()))
                ->byDefault();

            $self->interactor = app(GetOverlapContractInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should(
            'use UseCase with specified parameters according arguments',
            function (): void {
                $permission = Permission::updateDwsContracts();
                $contract = $this->examples->contracts[0];
                $expectedFilterParams = [
                    'userId' => $this->examples->users[0]->id,
                    'officeId' => $this->examples->offices[0]->id,
                    'serviceSegment' => ServiceSegment::disabilitiesWelfare(),
                    'status' => [ContractStatus::provisional(), ContractStatus::formal()],
                ];
                $expectedPaginationParams = [
                    'all' => true,
                    'sortBy' => 'id',
                    'desc' => true,
                ];
                $this->findContractUseCase
                    ->expects('handle')
                    ->with(
                        $this->context,
                        $permission,
                        Mockery::capture($actualFilterParams),
                        Mockery::capture($actualPaginationParams)
                    )
                    ->andReturn(FinderResult::from(Seq::from($contract), Pagination::create()));

                $actual = $this->interactor->handle(
                    $this->context,
                    Permission::updateDwsContracts(),
                    $this->examples->users[0]->id,
                    $this->examples->offices[0]->id,
                    ServiceSegment::disabilitiesWelfare(),
                );

                $this->assertEquals($expectedFilterParams, $actualFilterParams);
                $this->assertSame($expectedPaginationParams, $actualPaginationParams);
                $this->assertArrayStrictEquals([$contract], $actual->toArray());
            }
        );
    }
}

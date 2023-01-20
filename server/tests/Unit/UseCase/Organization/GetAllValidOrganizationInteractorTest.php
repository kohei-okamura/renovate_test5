<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\Organization;

use Domain\Common\Pagination;
use Domain\FinderResult;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\OrganizationFinderMixin;
use Tests\Unit\Test;
use UseCase\Organization\GetAllValidOrganizationInteractor;

/**
 * {@link \UseCase\Organization\GetAllValidOrganizationInteractor} テスト.
 */
class GetAllValidOrganizationInteractorTest extends Test
{
    use ExamplesConsumer;
    use OrganizationFinderMixin;
    use MockeryMixin;
    use UnitSupport;

    private GetAllValidOrganizationInteractor $interactor;

    public static function _setUpSuite(): void
    {
        self::beforeEachSpec(function (GetAllValidOrganizationInteractorTest $self): void {
            $self->interactor = app(GetAllValidOrganizationInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('return Organization of Seq via finder', function (): void {
            $expect = Seq::fromArray($this->examples->organizations);
            $this->organizationFinder
                ->expects('find')
                ->with(
                    ['isEnabled' => true],
                    ['all' => true, 'sortBy' => 'id'],
                )
                ->andReturn(FinderResult::from($expect->toArray(), Pagination::create([])));

            $this->assertEquals(
                $expect,
                $this->interactor->handle()
            );
        });
    }
}

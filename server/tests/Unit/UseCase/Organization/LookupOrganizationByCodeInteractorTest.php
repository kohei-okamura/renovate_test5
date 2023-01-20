<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\Organization;

use ScalikePHP\Option;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\OrganizationRepositoryMixin;
use Tests\Unit\Test;
use UseCase\Organization\LookupOrganizationByCodeInteractor;

/**
 * {@link \UseCase\Organization\LookupOrganizationByCodeInteractor} のテスト.
 */
class LookupOrganizationByCodeInteractorTest extends Test
{
    use ExamplesConsumer;
    use MockeryMixin;
    use OrganizationRepositoryMixin;
    use UnitSupport;

    private LookupOrganizationByCodeInteractor $interactor;

    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (LookupOrganizationByCodeInteractorTest $self): void {
            $self->interactor = app(LookupOrganizationByCodeInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('return Organization of Option via repository', function (): void {
            $code = $this->examples->organizations[0]->code;
            $expect = Option::from($this->examples->organizations[0]);
            $this->organizationRepository
                ->expects('lookupOptionByCode')
                ->with($code)
                ->andReturn($expect);

            $this->assertEquals(
                $expect,
                $this->interactor->handle($code)
            );
        });
    }
}

<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\Organization;

use Domain\Organization\OrganizationSetting;
use Domain\Permission\Permission;
use ScalikePHP\Map;
use ScalikePHP\Option;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\OrganizationSettingRepositoryMixin;
use Tests\Unit\Mixins\TransactionManagerMixin;
use Tests\Unit\Test;
use UseCase\Organization\LookupOrganizationSettingInteractor;

/**
 * {@link \UseCase\Organization\LookupOrganizationSettingInteractor} のテスト.
 */
final class LookupOrganizationSettingInteractorTest extends Test
{
    use ContextMixin;
    use ExamplesConsumer;
    use MockeryMixin;
    use OrganizationSettingRepositoryMixin;
    use TransactionManagerMixin;
    use UnitSupport;

    private OrganizationSetting $organizationSetting;
    private LookupOrganizationSettingInteractor $interactor;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (LookupOrganizationSettingInteractorTest $self): void {
            $self->organizationSettingRepository
                ->allows('lookupByOrganizationId')
                ->andReturn(Map::from([$self->examples->organizationSettings[0]->id => [$self->examples->organizationSettings[0]]]))
                ->byDefault();

            $self->organizationSetting = $self->examples->organizationSettings[0];
            $self->interactor = app(LookupOrganizationSettingInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('return some of OrganizationSetting', function (): void {
            $this->organizationSettingRepository
                ->expects('lookupByOrganizationId')
                ->with($this->context->organization->id)
                ->andReturn(Map::from([$this->organizationSetting->id => [$this->organizationSetting]]));

            $actual = $this->interactor->handle($this->context, Permission::viewOrganizationSettings());
            $this->assertEquals(Option::some($this->organizationSetting), $actual);
            $this->assertModelStrictEquals($this->organizationSetting, $actual->get());
        });
        $this->should('return some of OrganizationSetting when OrganizationSettings is none', function (): void {
            $this->organizationSettingRepository
                ->expects('lookupByOrganizationId')
                ->with($this->context->organization->id)
                ->andReturn(Map::from([]));

            $actual = $this->interactor->handle($this->context, Permission::viewOrganizationSettings());

            $this->assertEquals(Option::some(OrganizationSetting::create([])), $actual);
            $this->assertModelStrictEquals(OrganizationSetting::create(), $actual->get());
        });
    }
}

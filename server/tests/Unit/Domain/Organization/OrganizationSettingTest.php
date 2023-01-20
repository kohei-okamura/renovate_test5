<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Domain\Organization;

use Domain\Common\Carbon;
use Domain\Organization\OrganizationSetting;
use Illuminate\Support\Arr;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Test;

/**
 * {@link \Domain\Organization\OrganizationSetting} のテスト
 */
class OrganizationSettingTest extends Test
{
    use CarbonMixin;
    use UnitSupport;
    use MatchesSnapshots;

    protected OrganizationSetting $organizationSetting;

    protected array $values = [];

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (OrganizationSettingTest $self): void {
            $self->values = [
                'id' => 1,
                'organizationId' => 1,
                'bankingClientCode' => '0123456789',
                'bankingClientName' => 'ｲﾀｸｼｬﾒｲ',
                'createdAt' => Carbon::now(),
                'updatedAt' => Carbon::now(),
            ];
            $self->organizationSetting = OrganizationSetting::create($self->values);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_attrs(): void
    {
        $this->should('have id attribute', function (): void {
            $this->assertSame($this->organizationSetting->get('id'), Arr::get($this->values, 'id'));
        });
        $this->should('have organizationId attribute', function (): void {
            $this->assertSame($this->organizationSetting->get('organizationId'), Arr::get($this->values, 'organizationId'));
        });
        $this->should('have bankingClientCode attribute', function (): void {
            $this->assertSame($this->organizationSetting->get('bankingClientCode'), Arr::get($this->values, 'bankingClientCode'));
        });
        $this->should('have bankingClientName attribute', function (): void {
            $this->assertSame($this->organizationSetting->get('bankingClientName'), Arr::get($this->values, 'bankingClientName'));
        });
        $this->should('have createdAt attribute', function (): void {
            $this->assertSame($this->organizationSetting->get('createdAt'), Arr::get($this->values, 'createdAt'));
        });
        $this->should('have updatedAt attribute', function (): void {
            $this->assertSame($this->organizationSetting->get('updatedAt'), Arr::get($this->values, 'updatedAt'));
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_json(): void
    {
        $this->should('changes to JSON encode results', function (): void {
            $this->assertMatchesJsonSnapshot($this->organizationSetting);
        });
    }
}

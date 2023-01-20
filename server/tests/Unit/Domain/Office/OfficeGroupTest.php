<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Domain\Office;

use Domain\Common\Carbon;
use Domain\Office\OfficeGroup;
use Illuminate\Support\Arr;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Examples\OrganizationExample;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Test;

/**
 * OfficeGroup のテスト
 */
class OfficeGroupTest extends Test
{
    use CarbonMixin;
    use ExamplesConsumer;
    use OrganizationExample;
    use UnitSupport;
    use MatchesSnapshots;

    protected OfficeGroup $officeGroup;

    protected array $values = [];

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (OfficeGroupTest $self): void {
            $self->values = [
                'id' => 1,
                'organizationId' => $self->examples->organizations[0]->id,
                'parentOfficeGroupId' => $self->examples->officeGroups[0]->parentOfficeGroupId,
                'name' => 'テスト事業所',
                'sortOrder' => 1,
                'createdAt' => Carbon::now(),
                'updatedAt' => Carbon::now(),
            ];
            $self->officeGroup = OfficeGroup::create($self->values);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_attrs(): void
    {
        $this->should('have id attribute', function (): void {
            $this->assertSame($this->officeGroup->get('id'), Arr::get($this->values, 'id'));
        });
        $this->should('have organizationId attribute', function (): void {
            $this->assertSame($this->officeGroup->get('organizationId'), Arr::get($this->values, 'organizationId'));
        });
        $this->should('have name attribute', function (): void {
            $this->assertSame($this->officeGroup->get('name'), Arr::get($this->values, 'name'));
        });
        $this->should('have sortOrder attribute', function (): void {
            $this->assertSame($this->officeGroup->get('sortOrder'), Arr::get($this->values, 'sortOrder'));
        });
        $this->should('have createdAt attribute', function (): void {
            $this->assertSame($this->officeGroup->get('createdAt'), Arr::get($this->values, 'createdAt'));
        });
        $this->should('have updatedAt attribute', function (): void {
            $this->assertSame($this->officeGroup->get('updatedAt'), Arr::get($this->values, 'updatedAt'));
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_json(): void
    {
        $this->should('changes to JSON encode results', function (): void {
            $this->assertMatchesJsonSnapshot($this->officeGroup);
        });
    }
}

<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Domain\Permission;

use Domain\Common\Carbon;
use Domain\Permission\Permission;
use Domain\Permission\PermissionGroup;
use Illuminate\Support\Arr;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Examples\OrganizationExample;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Test;

/**
 * PermissionGroup のテスト
 */
class PermissionGroupTest extends Test
{
    use CarbonMixin;
    use ExamplesConsumer;
    use OrganizationExample;
    use UnitSupport;
    use MatchesSnapshots;

    protected PermissionGroup $permissionGroup;

    protected array $values = [];

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (PermissionGroupTest $self): void {
            $self->values = [
                'id' => 1,
                'code' => '123456',
                'name' => '権限グループ名',
                'displayName' => '表示名',
                'permissions' => [Permission::createStaffs()],
                'sortOrder' => 1,
                'createdAt' => Carbon::now(),
            ];
            $self->permissionGroup = PermissionGroup::create($self->values);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_attrs(): void
    {
        $this->should('have id attribute', function (): void {
            $this->assertSame($this->permissionGroup->get('id'), Arr::get($this->values, 'id'));
        });
        $this->should('have code attribute', function (): void {
            $this->assertSame($this->permissionGroup->get('code'), Arr::get($this->values, 'code'));
        });
        $this->should('have name attribute', function (): void {
            $this->assertSame($this->permissionGroup->get('name'), Arr::get($this->values, 'name'));
        });
        $this->should('have displayName attribute', function (): void {
            $this->assertSame($this->permissionGroup->get('displayName'), Arr::get($this->values, 'displayName'));
        });
        $this->should('have permissions attribute', function (): void {
            $this->assertSame($this->permissionGroup->get('permissions'), Arr::get($this->values, 'permissions'));
        });
        $this->should('have sortOrder attribute', function (): void {
            $this->assertSame($this->permissionGroup->get('sortOrder'), Arr::get($this->values, 'sortOrder'));
        });
        $this->should('have createdAt attribute', function (): void {
            $this->assertSame($this->permissionGroup->get('createdAt'), Arr::get($this->values, 'createdAt'));
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_json(): void
    {
        $this->should('changes to JSON encode results', function (): void {
            $this->assertMatchesJsonSnapshot($this->permissionGroup);
        });
    }
}

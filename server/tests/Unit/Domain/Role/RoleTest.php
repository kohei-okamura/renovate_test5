<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Domain\Role;

use Domain\Common\Carbon;
use Domain\Permission\Permission;
use Domain\Role\Role;
use Domain\Role\RoleScope;
use Illuminate\Support\Arr;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Examples\OrganizationExample;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Test;

/**
 * Role のテスト
 */
class RoleTest extends Test
{
    use CarbonMixin;
    use ExamplesConsumer;
    use OrganizationExample;
    use UnitSupport;
    use MatchesSnapshots;

    protected Role $role;

    protected array $values = [];

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (RoleTest $self): void {
            $self->values = [
                'id' => 1,
                'organization_id' => $self->examples->organizations[0]->id,
                'permissions' => [Permission::createStaffs()],
                'name' => '名前',
                'isSystemAdmin' => true,
                'scope' => RoleScope::whole(),
                'sortOrder' => 1,
                'createdAt' => Carbon::now(),
                'updatedAt' => Carbon::now(),
            ];
            $self->role = Role::create($self->values);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_attrs(): void
    {
        $this->should('have id attribute', function (): void {
            $this->assertSame($this->role->get('id'), Arr::get($this->values, 'id'));
        });
        $this->should('have organizationId attribute', function (): void {
            $this->assertSame($this->role->get('organizationId'), Arr::get($this->values, 'organizationId'));
        });
        $this->should('have name attribute', function (): void {
            $this->assertSame($this->role->get('name'), Arr::get($this->values, 'name'));
        });
        $this->should('have isSystemAdmin attribute', function (): void {
            $this->assertSame($this->role->get('isSystemAdmin'), Arr::get($this->values, 'isSystemAdmin'));
        });
        $this->should('have permissions attribute', function (): void {
            $this->assertSame($this->role->get('permissions'), Arr::get($this->values, 'permissions'));
        });
        $this->should('have scope attribute', function (): void {
            $this->assertSame($this->role->get('scope'), Arr::get($this->values, 'scope'));
        });
        $this->should('have sortOrder attribute', function (): void {
            $this->assertSame($this->role->get('sortOrder'), Arr::get($this->values, 'sortOrder'));
        });
        $this->should('have createdAt attribute', function (): void {
            $this->assertSame($this->role->get('createdAt'), Arr::get($this->values, 'createdAt'));
        });
        $this->should('have updatedAt attribute', function (): void {
            $this->assertSame($this->role->get('updatedAt'), Arr::get($this->values, 'updatedAt'));
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_json(): void
    {
        $this->should('changes to JSON encode results', function (): void {
            $this->assertMatchesJsonSnapshot($this->role);
        });
    }
}

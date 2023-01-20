<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Domain\Staff;

use Domain\Common\Carbon;
use Domain\Permission\Permission;
use Domain\Staff\Staff;
use Domain\Staff\StaffStatus;
use Illuminate\Support\Arr;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Examples\OrganizationExample;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Test;

/**
 * Staff のテスト
 */
class StaffTest extends Test
{
    use CarbonMixin;
    use ExamplesConsumer;
    use OrganizationExample;
    use UnitSupport;
    use MatchesSnapshots;

    protected Staff $staff;

    protected array $values = [];

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (StaffTest $self): void {
            $self->values = [
                'id' => 1,
                'organization_id' => $self->examples->organizations[0]->id,
                'permissions' => [Permission::createStaffs()],
                'name' => '名前',
                'sex' => 1,
                'birthday' => '1990-04-30 00:00:00',
                'addr' => [9840056, 4, '仙台市若林区', '成田町16番地の2', 'ロイヤルヒルズ成田町403号'],
                'location' => ['lat' => 35.6969932, 'lng' => 139.6839594],
                'tel' => '012-245-6789',
                'fax' => '123-456-7890',
                'email' => 'sample@example.com',
                'password' => 'test',
                'certifications' => [1, 2],
                'bankAccountId' => $self->examples->bankAccounts[0]->id,
                'roleIds' => [$self->examples->roles[0]->id],
                'officeIds' => [$self->examples->offices[0]->id],
                'officeGroupIds' => [$self->examples->officeGroups[1]->id],
                'isVerified' => 1,
                'status' => StaffStatus::active(),
                'isEnabled' => 1,
                'version' => 1,
                'createdAt' => Carbon::now(),
                'updatedAt' => Carbon::now(),
            ];
            $self->staff = Staff::create($self->values);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_attrs(): void
    {
        $this->should('have id attribute', function (): void {
            $this->assertSame($this->staff->get('id'), Arr::get($this->values, 'id'));
        });
        $this->should('have organizationId attribute', function (): void {
            $this->assertSame($this->staff->get('organizationId'), Arr::get($this->values, 'organizationId'));
        });
        $this->should('have name attribute', function (): void {
            $this->assertSame($this->staff->get('name'), Arr::get($this->values, 'name'));
        });
        $this->should('have sex attribute', function (): void {
            $this->assertSame($this->staff->get('sex'), Arr::get($this->values, 'sex'));
        });
        $this->should('have birthday attribute', function (): void {
            $this->assertSame($this->staff->get('birthday'), Arr::get($this->values, 'birthday'));
        });
        $this->should('have addr attribute', function (): void {
            $this->assertSame($this->staff->get('addr'), Arr::get($this->values, 'addr'));
        });
        $this->should('have location attribute', function (): void {
            $this->assertSame($this->staff->get('location'), Arr::get($this->values, 'location'));
        });
        $this->should('have tel attribute', function (): void {
            $this->assertSame($this->staff->get('tel'), Arr::get($this->values, 'tel'));
        });
        $this->should('have fax attribute', function (): void {
            $this->assertSame($this->staff->get('fax'), Arr::get($this->values, 'fax'));
        });
        $this->should('have email attribute', function (): void {
            $this->assertSame($this->staff->get('email'), Arr::get($this->values, 'email'));
        });
        $this->should('have password attribute', function (): void {
            $this->assertSame($this->staff->get('password'), Arr::get($this->values, 'password'));
        });
        $this->should('have certifications attribute', function (): void {
            $this->assertSame($this->staff->get('certifications'), Arr::get($this->values, 'certifications'));
        });
        $this->should('have bankAccountId attribute', function (): void {
            $this->assertSame($this->staff->get('bankAccountId'), Arr::get($this->values, 'bankAccountId'));
        });
        $this->should('have roleIds attribute', function (): void {
            $this->assertSame($this->staff->get('roleIds'), Arr::get($this->values, 'roleIds'));
        });
        $this->should('have officeIds attribute', function (): void {
            $this->assertSame($this->staff->get('officeIds'), Arr::get($this->values, 'officeIds'));
        });
        $this->should('have officeGroupIds attribute', function (): void {
            $this->assertSame($this->staff->get('officeGroupIds'), Arr::get($this->values, 'officeGroupIds'));
        });
        $this->should('have isVerified attribute', function (): void {
            $this->assertSame($this->staff->get('isVerified'), Arr::get($this->values, 'isVerified'));
        });
        $this->should('have status attribute', function (): void {
            $this->assertSame($this->staff->get('status'), Arr::get($this->values, 'status'));
        });
        $this->should('have isEnabled attribute', function (): void {
            $this->assertSame($this->staff->get('isEnabled'), Arr::get($this->values, 'isEnabled'));
        });
        $this->should('have version attribute', function (): void {
            $this->assertSame($this->staff->get('version'), Arr::get($this->values, 'version'));
        });
        $this->should('have createdAt attribute', function (): void {
            $this->assertSame($this->staff->get('createdAt'), Arr::get($this->values, 'createdAt'));
        });
        $this->should('have updatedAt attribute', function (): void {
            $this->assertSame($this->staff->get('updatedAt'), Arr::get($this->values, 'updatedAt'));
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_json(): void
    {
        $this->should('changes to JSON encode results', function (): void {
            $this->assertMatchesJsonSnapshot($this->staff);
        });
    }
}

<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Domain\Organization;

use Domain\Common\Carbon;
use Domain\Organization\Organization;
use Illuminate\Support\Arr;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Examples\OrganizationExample;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Test;

/**
 * Organization のテスト
 */
class OrganizationTest extends Test
{
    use CarbonMixin;
    use ExamplesConsumer;
    use OrganizationExample;
    use UnitSupport;
    use MatchesSnapshots;

    protected Organization $organization;

    protected array $values = [];

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (OrganizationTest $self): void {
            $self->values = [
                'id' => 1,
                'code' => '0123456789',
                'name' => 'テスト事業所',
                'addr' => [9840056, 4, '仙台市若林区', '成田町16番地の2', 'ロイヤルヒルズ成田町403号'],
                'tel' => '012-345-6789',
                'fax' => '123-456-7890',
                'isEnabled' => 1,
                'version' => 1,
                'createdAt' => Carbon::now(),
                'updatedAt' => Carbon::now(),
            ];
            $self->organization = Organization::create($self->values);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_attrs(): void
    {
        $this->should('have id attribute', function (): void {
            $this->assertSame($this->organization->get('id'), Arr::get($this->values, 'id'));
        });
        $this->should('have code attribute', function (): void {
            $this->assertSame($this->organization->get('code'), Arr::get($this->values, 'code'));
        });
        $this->should('have name attribute', function (): void {
            $this->assertSame($this->organization->get('name'), Arr::get($this->values, 'name'));
        });
        $this->should('have addr attribute', function (): void {
            $this->assertSame($this->organization->get('addr'), Arr::get($this->values, 'addr'));
        });
        $this->should('have tel attribute', function (): void {
            $this->assertSame($this->organization->get('tel'), Arr::get($this->values, 'tel'));
        });
        $this->should('have fax attribute', function (): void {
            $this->assertSame($this->organization->get('fax'), Arr::get($this->values, 'fax'));
        });
        $this->should('have isEnabled attribute', function (): void {
            $this->assertSame($this->organization->get('isEnabled'), Arr::get($this->values, 'isEnabled'));
        });
        $this->should('have version attribute', function (): void {
            $this->assertSame($this->organization->get('version'), Arr::get($this->values, 'version'));
        });
        $this->should('have createdAt attribute', function (): void {
            $this->assertSame($this->organization->get('createdAt'), Arr::get($this->values, 'createdAt'));
        });
        $this->should('have updatedAt attribute', function (): void {
            $this->assertSame($this->organization->get('updatedAt'), Arr::get($this->values, 'updatedAt'));
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_json(): void
    {
        $this->should('changes to JSON encode results', function (): void {
            $this->assertMatchesJsonSnapshot($this->organization);
        });
    }
}

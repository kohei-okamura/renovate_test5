<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Domain\Office;

use Domain\Common\Carbon;
use Domain\Office\Office;
use Domain\Office\OfficeLtcsPreventionService;
use Illuminate\Support\Arr;
use Lib\Exceptions\UndefinedPropertyException;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Examples\OrganizationExample;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Test;

/**
 * Office のテスト
 */
class OfficeTest extends Test
{
    use CarbonMixin;
    use ExamplesConsumer;
    use OrganizationExample;
    use UnitSupport;
    use MatchesSnapshots;

    protected Office $office;

    protected array $values = [];

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (OfficeTest $self): void {
            $self->values = [
                'id' => 1,
                'organizationId' => $self->examples->organizations[0]->id,
                'name' => '事業所テスト',
                'abbr' => '事テス',
                'phoneticName' => 'ツチヤホウモンカイゴジギョウショセンダイ',
                'corporationName' => '事業所テスト',
                'phoneticCorporationName' => 'ジギョウショテスト',
                'purpose' => 0,
                'addr' => [9840056, 4, '仙台市若林区', '成田町16番地の2', 'ロイヤルヒルズ成田町403号'],
                'location' => ['lat' => 35.6969932, 'lng' => 139.6839594],
                'tel' => '012-245-6789',
                'fax' => '123-456-7890',
                'email' => 'sample@example.com',
                'qualifications' => ['1011'],
                'officeGroupId' => 1,
                'dwsGenericService' => [1, '01234567890123456789', '2010-03-30T00:00:00+0900', '2015-08-09T00:00:00+0900'],
                'dwsCommAccompanyService' => ['01234567890123456789', '2010-03-30T00:00:00+0900', '2015-08-09T00:00:00+0900'],
                'ltcsCareManagementService' => [1, '01234567890123456789', '2010-03-30T00:00:00+0900', '2015-08-09T00:00:00+0900'],
                'ltcsHomeVisitLongTermCareService' => [1, '01234567890123456789', '2010-03-30T00:00:00+0900', '2015-08-09T00:00:00+0900'],
                'ltcsCompHomeVisitingService' => ['01234567890123456789', '2010-03-30T00:00:00+0900', '2015-08-09T00:00:00+0900'],
                'ltcsPreventionService' => new OfficeLtcsPreventionService(
                    code: '01234567890123456789',
                    openedOn: Carbon::parse('2010-03-30'),
                    designationExpiredOn: Carbon::parse('2015-08-09')
                ),
                'status' => 2,
                'isEnabled' => 1,
                'version' => 1,
                'createdAt' => Carbon::now(),
                'updatedAt' => Carbon::now(),
            ];
            $self->office = Office::create($self->values);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_attrs(): void
    {
        $this->should('have id attribute', function (): void {
            $this->assertSame($this->office->get('id'), Arr::get($this->values, 'id'));
        });
        $this->should('have organizationId attribute', function (): void {
            $this->assertSame($this->office->get('organizationId'), Arr::get($this->values, 'organizationId'));
        });
        $this->should('have name attribute', function (): void {
            $this->assertSame($this->office->get('name'), Arr::get($this->values, 'name'));
        });
        $this->should('have abbr attribute', function (): void {
            $this->assertSame($this->office->get('abbr'), Arr::get($this->values, 'abbr'));
        });
        $this->should('have phoneticName attribute', function (): void {
            $this->assertSame($this->office->get('phoneticName'), Arr::get($this->values, 'phoneticName'));
        });
        $this->should('have corporationName attribute', function (): void {
            $this->assertSame($this->office->get('corporationName'), Arr::get($this->values, 'corporationName'));
        });
        $this->should('have phoneticCorporationName attribute', function (): void {
            $this->assertSame($this->office->get('phoneticCorporationName'), Arr::get($this->values, 'phoneticCorporationName'));
        });
        $this->should('have purpose attribute', function (): void {
            $this->assertSame($this->office->get('purpose'), Arr::get($this->values, 'purpose'));
        });
        $this->should('have addr attribute', function (): void {
            $this->assertSame($this->office->get('addr'), Arr::get($this->values, 'addr'));
        });
        $this->should('have location attribute', function (): void {
            $this->assertSame($this->office->get('location'), Arr::get($this->values, 'location'));
        });
        $this->should('have tel attribute', function (): void {
            $this->assertSame($this->office->get('tel'), Arr::get($this->values, 'tel'));
        });
        $this->should('have fax attribute', function (): void {
            $this->assertSame($this->office->get('fax'), Arr::get($this->values, 'fax'));
        });
        $this->should('have email attribute', function (): void {
            $this->assertSame($this->office->get('email'), Arr::get($this->values, 'email'));
        });
        $this->should('have qualifications attribute', function (): void {
            $this->assertSame($this->office->get('qualifications'), Arr::get($this->values, 'qualifications'));
        });
        $this->should('have officeGroupId attribute', function (): void {
            $this->assertSame($this->office->get('officeGroupId'), Arr::get($this->values, 'officeGroupId'));
        });
        $this->should('have dwsGenericService attribute', function (): void {
            $this->assertSame($this->office->get('dwsGenericService'), Arr::get($this->values, 'dwsGenericService'));
        });
        $this->should('have dwsCommAccompanyService attribute', function (): void {
            $this->assertSame($this->office->get('dwsCommAccompanyService'), Arr::get($this->values, 'dwsCommAccompanyService'));
        });
        $this->should('have ltcsCareManagementService attribute', function (): void {
            $this->assertSame($this->office->get('ltcsCareManagementService'), Arr::get($this->values, 'ltcsCareManagementService'));
        });
        $this->should('have ltcsHomeVisitLongTermCareService attribute', function (): void {
            $this->assertSame($this->office->get('ltcsHomeVisitLongTermCareService'), Arr::get($this->values, 'ltcsHomeVisitLongTermCareService'));
        });
        $this->should('have ltcsCompHomeVisitingService attribute', function (): void {
            $this->assertSame($this->office->get('ltcsCompHomeVisitingService'), Arr::get($this->values, 'ltcsCompHomeVisitingService'));
        });
        $this->should('have ltcsPreventionService attribute', function (): void {
            $this->assertSame($this->office->get('ltcsPreventionService'), Arr::get($this->values, 'ltcsPreventionService'));
        });
        $this->should('have status attribute', function (): void {
            $this->assertSame($this->office->get('status'), Arr::get($this->values, 'status'));
        });
        $this->should('have isEnabled attribute', function (): void {
            $this->assertSame($this->office->get('isEnabled'), Arr::get($this->values, 'isEnabled'));
        });
        $this->should('have version attribute', function (): void {
            $this->assertSame($this->office->get('version'), Arr::get($this->values, 'version'));
        });
        $this->should('have createdAt attribute', function (): void {
            $this->assertSame($this->office->get('createdAt'), Arr::get($this->values, 'createdAt'));
        });
        $this->should('have updatedAt attribute', function (): void {
            $this->assertSame($this->office->get('updatedAt'), Arr::get($this->values, 'updatedAt'));
        });
        $this->should('throw Exception when get no attribute', function (): void {
            $this->assertThrows(
                UndefinedPropertyException::class,
                function (): void {
                    $this->office->get('unknown');
                }
            );
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_json(): void
    {
        $this->should('changes to JSON encode results', function (): void {
            $this->assertMatchesJsonSnapshot($this->office);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_compare(): void
    {
        $this->should('return false when array length is unmatch', function (): void {
            $that = $this->office->copy(['qualifications' => []]);
            $this->assertFalse($this->office->equals($that));
        });
    }
}

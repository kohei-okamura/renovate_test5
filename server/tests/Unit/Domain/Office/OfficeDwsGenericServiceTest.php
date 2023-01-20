<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Domain\Office;

use Domain\Office\OfficeDwsGenericService;
use Illuminate\Support\Arr;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Examples\OrganizationExample;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Test;

/**
 * OfficeDwsGenericService のテスト
 */
class OfficeDwsGenericServiceTest extends Test
{
    use ExamplesConsumer;
    use OrganizationExample;
    use UnitSupport;
    use MatchesSnapshots;

    protected OfficeDwsGenericService $officeDwsGenericService;

    protected array $values = [];

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (OfficeDwsGenericServiceTest $self): void {
            $self->values = [
                'code' => '01234567890123456789',
                'openedOn' => '2016-05-02 00:00:00',
                'designationExpiredOn' => '2016-05-02 00:00:00',
                'dwsAreaGradeId' => $self->examples->offices[0]->dwsGenericService->dwsAreaGradeId,
            ];
            $self->officeDwsGenericService = OfficeDwsGenericService::create($self->values);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_attrs(): void
    {
        $this->should('have code attribute', function (): void {
            $this->assertSame($this->officeDwsGenericService->get('code'), Arr::get($this->values, 'code'));
        });
        $this->should('have openedOn attribute', function (): void {
            $this->assertSame($this->officeDwsGenericService->get('openedOn'), Arr::get($this->values, 'openedOn'));
        });
        $this->should('have designationExpiredOn attribute', function (): void {
            $this->assertSame($this->officeDwsGenericService->get('designationExpiredOn'), Arr::get($this->values, 'designationExpiredOn'));
        });
        $this->should('have dwsAreaGradeId attribute', function (): void {
            $this->assertSame($this->officeDwsGenericService->get('dwsAreaGradeId'), Arr::get($this->values, 'dwsAreaGradeId'));
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_json(): void
    {
        $this->should('changes to JSON encode results', function (): void {
            $this->assertMatchesJsonSnapshot($this->officeDwsGenericService);
        });
    }
}

<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Domain\Office;

use Domain\Office\OfficeLtcsCareManagementService;
use Illuminate\Support\Arr;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Examples\OrganizationExample;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Test;

/**
 * OfficeLtcsCareManagementService のテスト
 */
class OfficeLtcsCareManagementServiceTest extends Test
{
    use ExamplesConsumer;
    use OrganizationExample;
    use UnitSupport;
    use MatchesSnapshots;

    protected OfficeLtcsCareManagementService $ltcsCareManagementService;

    protected array $values = [];

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (OfficeLtcsCareManagementServiceTest $self): void {
            $self->values = [
                'code' => '01234567890123456789',
                'openedOn' => '2010-03-30T00:00:00+0900',
                'designationExpiredOn' => '2015-08-09T00:00:00+0900',
                'ltcsAreaGradeId' => $self->examples->ltcsAreaGrades[0]->id,
            ];
            $self->ltcsCareManagementService = OfficeLtcsCareManagementService::create($self->values);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_attrs(): void
    {
        $this->should('have code attribute', function (): void {
            $this->assertSame($this->ltcsCareManagementService->get('code'), Arr::get($this->values, 'code'));
        });
        $this->should('have openedOn attribute', function (): void {
            $this->assertSame($this->ltcsCareManagementService->get('openedOn'), Arr::get($this->values, 'openedOn'));
        });
        $this->should('have designationExpiredOn attribute', function (): void {
            $this->assertSame($this->ltcsCareManagementService->get('designationExpiredOn'), Arr::get($this->values, 'designationExpiredOn'));
        });
        $this->should('have ltcsAreaGradeId attribute', function (): void {
            $this->assertSame($this->ltcsCareManagementService->get('ltcsAreaGradeId'), Arr::get($this->values, 'ltcsAreaGradeId'));
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_json(): void
    {
        $this->should('changes to JSON encode results', function (): void {
            $this->assertMatchesJsonSnapshot($this->ltcsCareManagementService);
        });
    }
}

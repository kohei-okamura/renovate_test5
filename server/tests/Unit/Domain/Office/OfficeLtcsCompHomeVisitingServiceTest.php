<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Domain\Office;

use Domain\Office\OfficeLtcsCompHomeVisitingService;
use Illuminate\Support\Arr;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Examples\OrganizationExample;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Test;

/**
 * OfficeLtcsCompHomeVisitingService のテスト
 */
class OfficeLtcsCompHomeVisitingServiceTest extends Test
{
    use ExamplesConsumer;
    use OrganizationExample;
    use UnitSupport;
    use MatchesSnapshots;

    protected OfficeLtcsCompHomeVisitingService $ltcsCompHomeVisitingService;

    protected array $values = [];

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (OfficeLtcsCompHomeVisitingServiceTest $self): void {
            $self->values = [
                'code' => '01234567890123456789',
                'openedOn' => '2010-03-30T00:00:00+0900',
                'designationExpiredOn' => '2015-08-09T00:00:00+0900',
            ];
            $self->ltcsCompHomeVisitingService = OfficeLtcsCompHomeVisitingService::create($self->values);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_attrs(): void
    {
        $this->should('have code attribute', function (): void {
            $this->assertSame($this->ltcsCompHomeVisitingService->get('code'), Arr::get($this->values, 'code'));
        });
        $this->should('have openedOn attribute', function (): void {
            $this->assertSame($this->ltcsCompHomeVisitingService->get('openedOn'), Arr::get($this->values, 'openedOn'));
        });
        $this->should('have designationExpiredOn attribute', function (): void {
            $this->assertSame($this->ltcsCompHomeVisitingService->get('designationExpiredOn'), Arr::get($this->values, 'designationExpiredOn'));
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_json(): void
    {
        $this->should('changes to JSON encode results', function (): void {
            $this->assertMatchesJsonSnapshot($this->ltcsCompHomeVisitingService);
        });
    }
}

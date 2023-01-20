<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Domain\Shift;

use Domain\Common\Carbon;
use Domain\Shift\ShiftImport;
use Illuminate\Support\Arr;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Examples\OrganizationExample;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Test;

/**
 * ShiftImport のテスト
 */
class ShiftImportTest extends Test
{
    use CarbonMixin;
    use ExamplesConsumer;
    use OrganizationExample;
    use UnitSupport;
    use MatchesSnapshots;

    protected ShiftImport $shiftImport;

    protected array $values = [];

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (ShiftImportTest $self): void {
            $self->values = [
                'id' => 1,
                'organization_id' => $self->examples->organizations[0]->id,
                'staffId' => $self->examples->staffs[0]->id,
                'createdAt' => Carbon::now(),
            ];
            $self->shiftImport = ShiftImport::create($self->values);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_attrs(): void
    {
        $this->should('have id attribute', function (): void {
            $this->assertSame($this->shiftImport->get('id'), Arr::get($this->values, 'id'));
        });
        $this->should('have organizationId attribute', function (): void {
            $this->assertSame($this->shiftImport->get('organizationId'), Arr::get($this->values, 'organizationId'));
        });
        $this->should('have staffId attribute', function (): void {
            $this->assertSame($this->shiftImport->get('staffId'), Arr::get($this->values, 'staffId'));
        });
        $this->should('have createdAt attribute', function (): void {
            $this->assertSame($this->shiftImport->get('createdAt'), Arr::get($this->values, 'createdAt'));
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_json(): void
    {
        $this->should('changes to JSON encode results', function (): void {
            $this->assertMatchesJsonSnapshot($this->shiftImport);
        });
    }
}

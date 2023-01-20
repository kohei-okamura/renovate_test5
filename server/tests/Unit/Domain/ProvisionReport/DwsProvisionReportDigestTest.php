<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Domain\ProvisionReport;

use Domain\ProvisionReport\DwsProvisionReportDigest;
use Domain\ProvisionReport\DwsProvisionReportStatus;
use Illuminate\Support\Arr;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Test;

/**
 * {@link \Domain\ProvisionReport\DwsProvisionReportDigest} のテスト
 */
class DwsProvisionReportDigestTest extends Test
{
    use CarbonMixin;
    use UnitSupport;
    use MatchesSnapshots;

    protected DwsProvisionReportDigest $dwsProvisionReportDigest;

    protected array $values = [];

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (DwsProvisionReportDigestTest $self): void {
            $self->values = [
                'userId' => 1,
                'name' => '御名前 氏名',
                'dwsNumber' => '1111',
                'isEnabled' => true,
                'status' => DwsProvisionReportStatus::inProgress(),
            ];
            $self->dwsProvisionReportDigest = DwsProvisionReportDigest::create($self->values);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_attrs(): void
    {
        $examples = [
            'userId' => ['userId'],
            'name' => ['name'],
            'dwsNumber' => ['dwsNumber'],
            'isEnabled' => ['isEnabled'],
            'status' => ['status'],
        ];

        $this->should(
            'have specified attribute',
            function (string $key): void {
                $this->assertSame($this->dwsProvisionReportDigest->get($key), Arr::get($this->values, $key));
            },
            compact('examples')
        );
    }

    /**
     * @test
     * @return void
     */
    public function describe_json(): void
    {
        $this->should('changes to JSON encode results', function (): void {
            $this->assertMatchesJsonSnapshot($this->dwsProvisionReportDigest);
        });
    }
}

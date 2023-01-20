<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Domain\DwsCertification;

use Domain\Common\Carbon;
use Domain\DwsCertification\DwsCertificationGrant;
use Domain\DwsCertification\DwsCertificationServiceType;
use Illuminate\Support\Arr;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Examples\OrganizationExample;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Test;

/**
 * {@link \Domain\DwsCertification\DwsCertificationGrant} のテスト.
 */
class DwsCertificationGrantTest extends Test
{
    use CarbonMixin;
    use ExamplesConsumer;
    use OrganizationExample;
    use UnitSupport;
    use MatchesSnapshots;

    protected DwsCertificationGrant $domain;

    protected array $values = [];

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (DwsCertificationGrantTest $self): void {
            $self->values = [
                'dwsCertificationServiceType' => DwsCertificationServiceType::accompany(),
                'grantedAmount' => 0,
                'activatedOn' => Carbon::now(),
                'deactivatedOn' => Carbon::now(),
            ];
            $self->domain = DwsCertificationGrant::create($self->values);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_attrs(): void
    {
        $this->should('have specified attribute', function (string $key): void {
            $this->assertSame($this->domain->get($key), Arr::get($this->values, $key));
        }, [
            'examples' => [
                'dwsCertificationServiceType' => ['dwsCertificationServiceType'],
                'grantedAmount' => ['grantedAmount'],
                'activatedOn' => ['activatedOn'],
                'deactivatedOn' => ['deactivatedOn'],
            ],
        ]);
    }

    /**
     * @test
     * @return void
     */
    public function describe_json(): void
    {
        $this->should('changes to JSON encode results', function (): void {
            $this->assertMatchesJsonSnapshot($this->domain);
        });
    }
}

<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Domain\LtcsInsCard;

use Domain\LtcsInsCard\LtcsInsCardMaxBenefitQuota;
use Domain\LtcsInsCard\LtcsInsCardServiceType;
use Illuminate\Support\Arr;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Test;

/**
 * {@link \Domain\LtcsInsCard\LtcsInsCardMaxBenefitQuota} Test.
 */
class LtcsInsCardMaxBenefitQuotaTest extends Test
{
    use CarbonMixin;
    use ExamplesConsumer;
    use MatchesSnapshots;
    use UnitSupport;

    protected LtcsInsCardMaxBenefitQuota $domain;
    protected array $values = [];

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (LtcsInsCardMaxBenefitQuotaTest $self): void {
            $self->values = [
                'ltcsInsCardServiceType' => LtcsInsCardServiceType::serviceType1(),
                'maxBenefitQuota' => 10,
            ];
            $self->domain = LtcsInsCardMaxBenefitQuota::create($self->values);
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
                'ltcsInsCardServiceType' => ['ltcsInsCardServiceType'],
                'maxBenefitQuota' => ['maxBenefitQuota'],
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

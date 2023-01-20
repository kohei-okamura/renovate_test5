<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Domain\DwsCertification;

use Domain\Common\Carbon;
use Domain\DwsCertification\DwsCertificationAgreement;
use Domain\DwsCertification\DwsCertificationAgreementType;
use Illuminate\Support\Arr;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Examples\OrganizationExample;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Test;

/**
 * DwsCertification のテスト
 */
class DwsCertificationAgreementTest extends Test
{
    use CarbonMixin;
    use ExamplesConsumer;
    use OrganizationExample;
    use UnitSupport;
    use MatchesSnapshots;

    protected DwsCertificationAgreement $domain;

    protected array $values = [];

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (DwsCertificationAgreementTest $self): void {
            $self->values = [
                'indexNumber' => 1,
                'officeId' => $self->examples->offices[0]->id,
                'dwsCertificationAgreementType' => DwsCertificationAgreementType::accompany(),
                'paymentAmount' => 2,
                'agreedOn' => Carbon::now(),
                'expiredOn' => Carbon::now(),
            ];
            $self->domain = DwsCertificationAgreement::create($self->values);
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
                'indexNumber' => ['indexNumber'],
                'officeId' => ['officeId'],
                'dwsCertificationAgreementType' => ['dwsCertificationAgreementType'],
                'paymentAmount' => ['paymentAmount'],
                'agreedOn' => ['agreedOn'],
                'expiredOn' => ['expiredOn'],
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

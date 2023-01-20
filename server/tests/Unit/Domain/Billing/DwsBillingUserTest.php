<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Domain\Billing;

use Domain\Billing\DwsBillingUser;
use Illuminate\Support\Arr;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Test;

/**
 * {@link \Domain\Billing\DwsBillingUser} のテスト.
 */
final class DwsBillingUserTest extends Test
{
    use CarbonMixin;
    use ExamplesConsumer;
    use UnitSupport;
    use MatchesSnapshots;

    protected DwsBillingUser $dwsBillingUser;

    protected array $values = [];

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (DwsBillingUserTest $self): void {
            $self->values = [
                'userId' => $self->examples->users[0]->id,
                'dwsCertificationId' => $self->examples->dwsCertifications[9]->id,
                'dwsNumber' => '0123456789',
                'name' => '介保大人',
                'childName' => '福祉子供',
                'copayLimit' => 10000,
            ];
            $self->dwsBillingUser = DwsBillingUser::create($self->values);
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
            'dwsCertificationId' => ['dwsCertificationId'],
            'dwsNumber' => ['dwsNumber'],
            'name' => ['name'],
            'childName' => ['childName'],
            'copayLimit' => ['copayLimit'],
        ];

        $this->should(
            'have specified attribute',
            function (string $key): void {
                $this->assertSame($this->dwsBillingUser->get($key), Arr::get($this->values, $key));
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
            $this->assertMatchesJsonSnapshot($this->dwsBillingUser);
        });
    }
}

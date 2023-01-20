<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Domain\Billing;

use Domain\Billing\CopayListSource;
use Domain\Billing\DwsBillingStatement;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;

/**
 * {@link \Domain\Billing\CopayListSource} のテスト.
 */
final class CopayListSourceTest extends Test
{
    use CarbonMixin;
    use ExamplesConsumer;
    use MatchesSnapshots;
    use MockeryMixin;
    use UnitSupport;

    /**
     * 初期化処理.
     */
    public static function _setUpSuite(): void
    {
    }

    /**
     * @test
     * @return void
     */
    public function describe_construct(): void
    {
        $this->should('create CopayListSource', function (): void {
            $actual = new CopayListSource(
                copayCoordinationOfficeName: '上限管理事業所の名前',
                statements: [DwsBillingStatement::create(), DwsBillingStatement::create()]
            );

            $this->assertMatchesModelSnapshot($actual);
        });
    }
}

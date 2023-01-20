<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Domain\ServiceCodeDictionary;

use Domain\Billing\DwsBillingServiceReportProviderType;
use Domain\ServiceCodeDictionary\DwsHomeHelpServiceProviderType;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Test;

/**
 * {@link \Domain\ServiceCodeDictionary\DwsHomeHelpServiceProviderType} のテスト.
 */
final class DwsHomeHelpServiceProviderTypeTest extends Test
{
    use UnitSupport;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (DwsHomeHelpServiceProviderTypeTest $self): void {
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_toDwsBillingServiceReportProviderType()
    {
        $examples = [
            'when DwsBillingServiceReportProviderType is beginner' => [
                DwsBillingServiceReportProviderType::beginner(),
                DwsHomeHelpServiceProviderType::beginner(),
            ],
            'when DwsBillingServiceReportProviderType is careWorkerForPwsd' => [
                DwsBillingServiceReportProviderType::careWorkerForPwsd(),
                DwsHomeHelpServiceProviderType::careWorkerForPwsd(),
            ],
            'when DwsBillingServiceReportProviderType is novice' => [
                DwsBillingServiceReportProviderType::novice(),
                DwsHomeHelpServiceProviderType::none(),
            ],
        ];

        $this->should(
            'return DwsBillingServiceReportProviderType',
            function (DwsBillingServiceReportProviderType $dwsBillingServiceReportProviderType, DwsHomeHelpServiceProviderType $dwsHomeHelpServiceProviderType): void {
                $this->assertSame(
                    $dwsBillingServiceReportProviderType,
                    $dwsHomeHelpServiceProviderType->toDwsBillingServiceReportProviderType(),
                );
            },
            compact('examples')
        );
    }
}

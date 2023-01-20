<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Domain\Billing;

use Domain\Billing\DwsBillingStatementCopayCoordinationStatus;
use Domain\DwsCertification\CopayCoordinationType;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;

/**
 * {@link \Domain\Billing\DwsBillingStatementCopayCoordinationStatus} のテスト.
 */
final class DwsBillingStatementCopayCoordinationStatusTest extends Test
{
    use ExamplesConsumer;
    use MockeryMixin;
    use UnitSupport;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (DwsBillingStatementCopayCoordinationStatusTest $self): void {
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_fromCopayCoordinationType(): void
    {
        $this->should('return unapplicable when CopayCoordinationType is none', function (): void {
            $type = DwsBillingStatementCopayCoordinationStatus::fromCopayCoordinationType(CopayCoordinationType::none());
            $this->assertSame(DwsBillingStatementCopayCoordinationStatus::unapplicable(), $type);
        });
        $this->should('return unapplicable when CopayCoordinationType is unknown', function (): void {
            $type = DwsBillingStatementCopayCoordinationStatus::fromCopayCoordinationType(CopayCoordinationType::unknown());
            $this->assertSame(DwsBillingStatementCopayCoordinationStatus::unapplicable(), $type);
        });
        $this->should('return uncreated when CopayCoordinationType is internal', function (): void {
            $type = DwsBillingStatementCopayCoordinationStatus::fromCopayCoordinationType(CopayCoordinationType::internal());
            $this->assertSame(DwsBillingStatementCopayCoordinationStatus::uncreated(), $type);
        });
        $this->should('return unfilled when CopayCoordinationType is external', function (): void {
            $type = DwsBillingStatementCopayCoordinationStatus::fromCopayCoordinationType(CopayCoordinationType::external());
            $this->assertSame(DwsBillingStatementCopayCoordinationStatus::unfilled(), $type);
        });
    }
}

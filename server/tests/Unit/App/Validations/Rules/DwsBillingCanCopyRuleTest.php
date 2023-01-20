<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\App\Validations\Rules;

use Domain\Billing\DwsBillingStatus;
use ScalikePHP\Seq;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\LookupDwsBillingUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;

/**
 * {@link \App\Validations\Rules\DwsBillingCanCopyRule} のテスト.
 */
final class DwsBillingCanCopyRuleTest extends Test
{
    use LookupDwsBillingUseCaseMixin;
    use MatchesSnapshots;
    use MockeryMixin;
    use RuleTestSupport;
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
    public function describe_validateDwsBillingCanCopy(): void
    {
        $this->should('pass when status is fixed', function (): void {
            $this->lookupDwsBillingUseCase
                ->expects('handle')
                ->andReturn(Seq::from($this->examples->dwsBillings[0]->copy(['status' => DwsBillingStatus::fixed()])));

            $validator = $this->buildCustomValidator(
                ['id' => $this->examples->dwsBillings[0]->id],
                ['id' => 'dws_billing_can_copy']
            );
            $this->assertTrue($validator->passes());
        });
        $this->should('fail when status is not fixed', function (): void {
            $this->lookupDwsBillingUseCase
                ->expects('handle')
                ->andReturn(Seq::from($this->examples->dwsBillings[0]->copy(['status' => DwsBillingStatus::ready()])));

            $validator = $this->buildCustomValidator(
                ['id' => $this->examples->dwsBillings[0]->id],
                ['id' => 'dws_billing_can_copy']
            );
            $this->assertTrue($validator->fails());
        });
        $this->should('fail when billing does not exist', function (): void {
            $this->lookupDwsBillingUseCase
                ->expects('handle')
                ->andReturn(Seq::empty());

            $validator = $this->buildCustomValidator(
                ['id' => $this->examples->dwsBillings[0]->id],
                ['id' => 'dws_billing_can_copy']
            );
            $this->assertTrue($validator->fails());
        });
    }
}

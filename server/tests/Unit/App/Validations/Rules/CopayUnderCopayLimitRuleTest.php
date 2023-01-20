<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\App\Validations\Rules;

use Domain\Common\Pagination;
use Domain\FinderResult;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\DwsBillingStatementFinderMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;

/**
 * {@link \App\Validations\Rules\CopayUnderCopayLimitRule} のテスト.
 */
final class CopayUnderCopayLimitRuleTest extends Test
{
    use DwsBillingStatementFinderMixin;
    use MatchesSnapshots;
    use MockeryMixin;
    use RuleTestSupport;
    use UnitSupport;

    /**
     * 初期化処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachTest(function (self $self): void {
        });
        self::beforeEachSpec(function (self $self): void {
            $self->dwsBillingStatementFinder
                ->allows('find')
                ->andReturn(
                    FinderResult::from(
                        [$self->examples->dwsBillingStatements[0]->copy(['copayLimit' => 1000])],
                        Pagination::create()
                    )
                );
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_validateCopayUnderCoapyLimit(): void
    {
        $this->should(
            'pass when copay is under copay limit',
            function (): void {
                $validator = $this->buildCustomValidator(
                    [
                        'value' => 100,
                        'userId' => $this->examples->users[0]->id,
                        'dwsBillingBundleId' => $this->examples->dwsBillingBundles[0]->id,
                    ],
                    ['value' => 'copay_under_copay_limit:userId']
                );
                $this->assertTrue($validator->passes());
            }
        );
        $this->should(
            'fail when copay is over copay limit',
            function (): void {
                $validator = $this->buildCustomValidator(
                    [
                        'value' => 2000,
                        'userId' => $this->examples->users[0]->id,
                        'dwsBillingBundleId' => $this->examples->dwsBillingBundles[0]->id,
                    ],
                    ['value' => 'copay_under_copay_limit:userId']
                );
                $this->assertFalse($validator->passes());
            }
        );
    }
}

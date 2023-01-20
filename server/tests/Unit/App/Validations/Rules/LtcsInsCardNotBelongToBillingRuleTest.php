<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\App\Validations\Rules;

use Domain\Common\Pagination;
use Domain\FinderResult;
use Domain\Permission\Permission;
use ScalikePHP\Seq;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\LookupLtcsInsCardUseCaseMixin;
use Tests\Unit\Mixins\LtcsBillingStatementFinderMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;

/**
 * {@link \App\Validations\Rules\LtcsInsCardNotBelongToBillingRule} のテスト.
 */
final class LtcsInsCardNotBelongToBillingRuleTest extends Test
{
    use ExamplesConsumer;
    use LookupLtcsInsCardUseCaseMixin;
    use LtcsBillingStatementFinderMixin;
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
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_validateLtcsInsCardNotBelongToBilling(): void
    {
        $this->should('pass when LookupLtcsInsCardUseCase return empty', function (): void {
            $this->lookupLtcsInsCardUseCase
                ->allows('handle')
                ->andReturn(Seq::empty());
            $this->assertTrue(
                $this->buildCustomValidator(
                    ['id' => 1, 'userId' => 1],
                    ['id' => 'ltcs_ins_card_not_belong_to_billing:userId,' . Permission::deleteLtcsInsCards()],
                )->passes()
            );
        });
        $this->should('fail when LtcsBillingStatementFinder return not empty list', function (): void {
            $this->lookupLtcsInsCardUseCase
                ->expects('handle')
                ->andReturn(Seq::from($this->examples->ltcsInsCards[0]));
            $this->ltcsBillingStatementFinder
                ->expects('find')
                ->andReturn(FinderResult::from([$this->examples->ltcsBillingStatements[0]], Pagination::create()));
            $this->assertTrue(
                $this->buildCustomValidator(
                    ['id' => 1, 'userId' => 1],
                    ['id' => 'ltcs_ins_card_not_belong_to_billing:userId,' . Permission::deleteLtcsInsCards()],
                )->fails()
            );
        });
    }
}

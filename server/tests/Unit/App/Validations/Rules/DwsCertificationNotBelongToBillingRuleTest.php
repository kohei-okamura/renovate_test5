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
use Tests\Unit\Mixins\DwsBillingStatementFinderMixin;
use Tests\Unit\Mixins\LookupDwsCertificationUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;

/**
 * {@link \App\Validations\Rules\DwsCertificationNotBelongToBillingRule} のテスト.
 */
final class DwsCertificationNotBelongToBillingRuleTest extends Test
{
    use DwsBillingStatementFinderMixin;
    use ExamplesConsumer;
    use LookupDwsCertificationUseCaseMixin;
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
    public function describe_validateDwsCertificationNotBelongToBilling(): void
    {
        $this->should('pass when LookupDwsCertificationUseCase return empty', function (): void {
            $this->lookupDwsCertificationUseCase
                ->expects('handle')
                ->with($this->context, Permission::deleteDwsCertifications(), 1, 1)
                ->andReturn(Seq::empty());
            $this->dwsBillingStatementFinder
                ->expects('find')
                ->times(0);
            $this->assertTrue(
                $this->buildCustomValidator(
                    ['id' => 1, 'userId' => 1],
                    ['id' => 'dws_certification_not_belong_to_billing:userId,' . Permission::deleteDwsCertifications()],
                )->passes()
            );
        });
        $this->should('pass when DwsBillingStatementFinder return empty list', function (): void {
            $this->lookupDwsCertificationUseCase
                ->expects('handle')
                ->with($this->context, Permission::deleteDwsCertifications(), 1, 1)
                ->andReturn(Seq::from($this->examples->dwsCertifications[0]));
            $this->dwsBillingStatementFinder
                ->expects('find')
                ->with(['dwsCertificationId' => $this->examples->dwsCertifications[0]->id], ['all' => true, 'sortBy' => 'id'])
                ->andReturn(FinderResult::from([], Pagination::create()));
            $this->assertTrue(
                $this->buildCustomValidator(
                    ['id' => 1, 'userId' => 1],
                    ['id' => 'dws_certification_not_belong_to_billing:userId,' . Permission::deleteDwsCertifications()],
                )->passes()
            );
        });
        $this->should('fail when DwsBillingStatementFinder return not empty list', function (): void {
            $this->lookupDwsCertificationUseCase
                ->expects('handle')
                ->with($this->context, Permission::deleteDwsCertifications(), 1, 1)
                ->andReturn(Seq::from($this->examples->dwsCertifications[0]));
            $this->dwsBillingStatementFinder
                ->expects('find')
                ->with(['dwsCertificationId' => $this->examples->dwsCertifications[0]->id], ['all' => true, 'sortBy' => 'id'])
                ->andReturn(FinderResult::from([$this->examples->dwsBillingStatements[0]], Pagination::create()));
            $this->assertTrue(
                $this->buildCustomValidator(
                    ['id' => 1, 'userId' => 1],
                    ['id' => 'dws_certification_not_belong_to_billing:userId,' . Permission::deleteDwsCertifications()],
                )->fails()
            );
        });
    }
}

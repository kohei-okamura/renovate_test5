<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\App\Validations\Rules;

use App\Validations\CustomValidator;
use Domain\Common\Carbon;
use Domain\Common\CarbonRange;
use Domain\Common\Pagination;
use Domain\FinderResult;
use Domain\Project\LtcsProjectServiceCategory;
use Domain\ProvisionReport\LtcsProvisionReportEntry;
use Domain\ProvisionReport\LtcsProvisionReportStatus;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\LtcsProvisionReportFinderMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;

/**
 * {@link \App\Validations\Rules\LtcsProvisionReportContainsLtcsServiceRule} のテスト.
 */
final class LtcsProvisionReportContainsLtcsServiceRuleTest extends Test
{
    use MatchesSnapshots;
    use MockeryMixin;
    use UnitSupport;
    use ContextMixin;
    use ExamplesConsumer;
    use LtcsProvisionReportFinderMixin;

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
    public function describe_validateLtcsProvisionReportContainsLtcsService(): void
    {
        $customValidator = function (): CustomValidator {
            return CustomValidator::make(
                $this->context,
                [
                    'officeId' => $this->examples->offices[2]->id,
                    'transactedIn' => '2021-05',
                ],
                [
                    'officeId' => 'ltcs_provision_report_contains_ltcs_service:transactedIn',
                ],
                [],
                []
            );
        };
        $this->should('pass when the target reports contain ltcs provision report result', function () use ($customValidator): void {
            $transactedIn = Carbon::parse('2021-05');
            $filterParams = [
                'officeId' => $this->examples->offices[2]->id,
                'fixedAt' => CarbonRange::create([
                    'start' => $transactedIn->subMonth()->day(11)->startOfDay(),
                    'end' => $transactedIn->day(10)->endOfDay(),
                ]),
                'status' => LtcsProvisionReportStatus::fixed(),
            ];
            $paginationParams = [
                'all' => true,
                'sortBy' => 'id',
            ];
            $this->ltcsProvisionReportFinder
                ->expects('find')
                ->with($filterParams, $paginationParams)
                ->andReturn(FinderResult::from(
                    [
                        $this->examples->ltcsProvisionReports[0]->copy([
                            'entries' => [
                                LtcsProvisionReportEntry::create([
                                    'category' => LtcsProjectServiceCategory::housework(),
                                    'plans' => [],
                                    'results' => [Carbon::now()],
                                ]),
                                LtcsProvisionReportEntry::create([
                                    'category' => LtcsProjectServiceCategory::ownExpense(),
                                    'plans' => [Carbon::now()],
                                    'results' => [Carbon::now()],
                                ]),
                            ],
                        ]),
                    ],
                    Pagination::create()
                ));
            $this->assertTrue($customValidator()->passes());
        });
        $this->should('fail when the target reports do not contain ltcs provision report result', function () use ($customValidator): void {
            $this->ltcsProvisionReportFinder
                ->expects('find')
                ->andReturn(FinderResult::from(
                    [
                        $this->examples->ltcsProvisionReports[0]->copy([
                            'entries' => [
                                LtcsProvisionReportEntry::create([
                                    'category' => LtcsProjectServiceCategory::housework(),
                                    'plans' => [Carbon::now()],
                                    'results' => [],
                                ]),
                                LtcsProvisionReportEntry::create([
                                    'category' => LtcsProjectServiceCategory::ownExpense(),
                                    'plans' => [Carbon::now()],
                                    'results' => [Carbon::now()],
                                ]),
                            ],
                        ]),
                    ],
                    Pagination::create()
                ));
            $this->assertTrue($customValidator()->fails());
        });
        $this->should('fail when the target reports contain only own expense', function () use ($customValidator): void {
            $this->ltcsProvisionReportFinder
                ->expects('find')
                ->andReturn(FinderResult::from(
                    [
                        $this->examples->ltcsProvisionReports[0]->copy([
                            'entries' => [
                                LtcsProvisionReportEntry::create([
                                    'category' => LtcsProjectServiceCategory::ownExpense(),
                                    'plans' => [Carbon::now()],
                                    'results' => [Carbon::now()],
                                ]),
                            ],
                        ]),
                    ],
                    Pagination::create()
                ));
            $this->assertTrue($customValidator()->fails());
        });
    }
}

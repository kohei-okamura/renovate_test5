<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\App\Validations\Rules;

use Domain\Common\Carbon;
use Domain\Common\Pagination;
use Domain\FinderResult;
use Domain\Office\HomeVisitLongTermCareSpecifiedOfficeAddition;
use Domain\Project\LtcsProjectServiceCategory;
use ScalikePHP\Seq;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\IdentifyLtcsHomeVisitLongTermCareDictionaryUseCaseMixin;
use Tests\Unit\Mixins\LtcsHomeVisitLongTermCareDictionaryEntryFinderMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;

/**
 * {@link \App\Validations\Rules\LtcsServiceCodeSpecifiedOfficeMatchRule} のテスト.
 */
final class LtcsServiceCodeSpecifiedOfficeMatchRuleTest extends Test
{
    use CarbonMixin;
    use ExamplesConsumer;
    use MatchesSnapshots;
    use MockeryMixin;
    use IdentifyLtcsHomeVisitLongTermCareDictionaryUseCaseMixin;
    use LtcsHomeVisitLongTermCareDictionaryEntryFinderMixin;
    use RuleTestSupport;
    use UnitSupport;

    private FinderResult $dictionaryResult;

    /**
     * 初期化処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachSpec(function (self $self): void {
            $self->ltcsHomeVisitLongTermCareDictionaryEntryFinder
                ->allows('find')
                ->andReturn(FinderResult::from(Seq::from($self->examples->ltcsHomeVisitLongTermCareDictionaryEntries[0]), Pagination::create([])))
                ->byDefault();
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_validateLtcsServiceCodeSpecifiedOfficeMatch(): void
    {
        $this->should('pass when entries does not contain LtcsService', function (): void {
            $providedIn = Carbon::now();
            $this->assertTrue(
                $this->buildCustomValidator(
                    [
                        'entries' => [
                            [
                                'serviceCode' => '',
                                'category' => LtcsProjectServiceCategory::ownExpense(),
                            ],
                        ],
                        'specifiedOfficeAddition' => HomeVisitLongTermCareSpecifiedOfficeAddition::addition1()->value(),
                    ],
                    ['entries' => "ltcs_service_code_specified_office_match:specifiedOfficeAddition,{$providedIn}"],
                )
                    ->passes()
            );
        });

        $this->should('pass when serviceCode count equal entry count ', function (): void {
            $providedIn = Carbon::now();
            $filterParams = ['specifiedOfficeAddition' => HomeVisitLongTermCareSpecifiedOfficeAddition::addition1(), 'serviceCodes' => [111211], 'providedIn' => $providedIn];
            $paginationParams = ['all' => true, 'sortBy' => 'id'];
            $this->ltcsHomeVisitLongTermCareDictionaryEntryFinder
                ->expects('find')
                ->with($filterParams, $paginationParams)
                ->andReturn(FinderResult::from(Seq::from($this->examples->ltcsHomeVisitLongTermCareDictionaryEntries[0]), Pagination::create([])))
                ->byDefault();
            $this->assertTrue(
                $this->buildCustomValidator(
                    [
                        'entries' => [
                            [
                                'serviceCode' => '111211',
                                'category' => LtcsProjectServiceCategory::physicalCare(),
                            ],
                        ],
                        'specifiedOfficeAddition' => HomeVisitLongTermCareSpecifiedOfficeAddition::addition1()->value(),
                    ],
                    ['entries' => "ltcs_service_code_specified_office_match:specifiedOfficeAddition,{$providedIn}"],
                )
                    ->passes()
            );
        });
        $this->should('fails when serviceCode count not equal entry count', function (): void {
            $providedIn = Carbon::now();
            $this->ltcsHomeVisitLongTermCareDictionaryEntryFinder
                ->expects('find')
                ->andReturn(
                    FinderResult::from(
                        Seq::from($this->examples->ltcsHomeVisitLongTermCareDictionaryEntries[0], $this->examples->ltcsHomeVisitLongTermCareDictionaryEntries[1]),
                        Pagination::create([])
                    )
                )
                ->byDefault();
            $this->assertTrue(
                $this->buildCustomValidator(
                    [
                        'entries' => [
                            [
                                'serviceCode' => '111211',
                                'category' => LtcsProjectServiceCategory::physicalCare(),
                            ],
                        ],
                        'specifiedOfficeAddition' => HomeVisitLongTermCareSpecifiedOfficeAddition::addition1()->value(),
                    ],
                    ['entries' => "ltcs_service_code_specified_office_match:specifiedOfficeAddition,{$providedIn}"],
                )
                    ->fails()
            );
        });
    }
}

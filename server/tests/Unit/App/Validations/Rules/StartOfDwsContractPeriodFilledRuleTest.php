<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\App\Validations\Rules;

use Domain\Billing\DwsServiceDivisionCode;
use Domain\Common\Carbon;
use Domain\Common\ServiceSegment;
use Domain\Contract\ContractPeriod;
use Domain\Permission\Permission;
use Domain\Project\DwsProjectServiceCategory;
use Domain\ProvisionReport\DwsProvisionReportItem;
use Domain\ProvisionReport\DwsProvisionReportStatus;
use ScalikePHP\Option;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\GetDwsProvisionReportUseCaseMixin;
use Tests\Unit\Mixins\IdentifyContractUseCaseMixin;
use Tests\Unit\Mixins\LookupOfficeUseCaseMixin;
use Tests\Unit\Mixins\LookupUserUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;

/**
 * {@link \App\Validations\Rules\StartOfDwsContractPeriodFilledRule} のテスト.
 */
final class StartOfDwsContractPeriodFilledRuleTest extends Test
{
    use CarbonMixin;
    use ContextMixin;
    use ExamplesConsumer;
    use GetDwsProvisionReportUseCaseMixin;
    use IdentifyContractUseCaseMixin;
    use LookupOfficeUseCaseMixin;
    use LookupUserUseCaseMixin;
    use MockeryMixin;
    use RuleTestSupport;
    use UnitSupport;

    /**
     * @test
     * @return void
     */
    public function describe_validateStartOfDwsContractPeriodFilled(): void
    {
        $this->should('pass when status is not fixed', function (): void {
            $officeId = $this->examples->offices[0]->id;
            $userId = $this->examples->users[0]->id;
            $providedIn = $this->examples->dwsProvisionReports[0]->providedIn;
            $this->lookupOfficeUseCase
                ->expects('handle')
                ->never();
            $this->lookupUserUseCase
                ->expects('handle')
                ->never();
            $this->identifyContractUseCase
                ->expects('handle')
                ->never();
            $this->getDwsProvisionReportUseCase
                ->expects('handle')
                ->never();

            $this->assertTrue(
                $this->buildCustomValidator(
                    ['userId' => $userId, 'status' => DwsProvisionReportStatus::inProgress()->value()],
                    ['userId' => "start_of_dws_contract_period_filled:{$officeId},{$providedIn->format('Y-m')}," . Permission::updateDwsProvisionReports()]
                )
                    ->passes()
            );
        });
        $this->should('pass when officeId does not exist', function (): void {
            $officeId = self::NOT_EXISTING_ID;
            $userId = $this->examples->users[0]->id;
            $providedIn = $this->examples->dwsProvisionReports[0]->providedIn;
            $this->lookupOfficeUseCase
                ->expects('handle')
                ->with($this->context, [Permission::updateDwsProvisionReports()], $officeId)
                ->andReturn(Seq::empty());
            $this->lookupUserUseCase
                ->expects('handle')
                ->never();
            $this->identifyContractUseCase
                ->expects('handle')
                ->never();
            $this->getDwsProvisionReportUseCase
                ->expects('handle')
                ->never();

            $this->assertTrue(
                $this->buildCustomValidator(
                    ['userId' => $userId, 'status' => DwsProvisionReportStatus::fixed()->value()],
                    ['userId' => "start_of_dws_contract_period_filled:{$officeId},{$providedIn->format('Y-m')}," . Permission::updateDwsProvisionReports()]
                )
                    ->passes()
            );
        });
        $this->should('pass when userId does not exist', function (): void {
            $officeId = $this->examples->offices[0]->id;
            $userId = self::NOT_EXISTING_ID;
            $providedIn = $this->examples->dwsProvisionReports[0]->providedIn;
            $this->lookupOfficeUseCase
                ->expects('handle')
                ->with($this->context, [Permission::updateDwsProvisionReports()], $officeId)
                ->andReturn(Seq::from($this->examples->offices[0]));
            $this->lookupUserUseCase
                ->expects('handle')
                ->with($this->context, Permission::updateDwsProvisionReports(), $userId)
                ->andReturn(Seq::empty());
            $this->identifyContractUseCase
                ->expects('handle')
                ->never();
            $this->getDwsProvisionReportUseCase
                ->expects('handle')
                ->never();

            $this->assertTrue(
                $this->buildCustomValidator(
                    ['userId' => $userId, 'status' => DwsProvisionReportStatus::fixed()->value()],
                    ['userId' => "start_of_dws_contract_period_filled:{$officeId},{$providedIn->format('Y-m')}," . Permission::updateDwsProvisionReports()]
                )
                    ->passes()
            );
        });
        $this->should('pass when contract does not exist', function (): void {
            $officeId = $this->examples->offices[0]->id;
            $userId = $this->examples->users[0]->id;
            $providedIn = $this->examples->dwsProvisionReports[0]->providedIn;
            $this->lookupOfficeUseCase
                ->expects('handle')
                ->with($this->context, [Permission::updateDwsProvisionReports()], $officeId)
                ->andReturn(Seq::from($this->examples->offices[0]));
            $this->lookupUserUseCase
                ->expects('handle')
                ->with($this->context, Permission::updateDwsProvisionReports(), $userId)
                ->andReturn(Seq::from($this->examples->users[0]));
            $this->identifyContractUseCase
                ->expects('handle')
                ->with(
                    $this->context,
                    Permission::updateDwsProvisionReports(),
                    $officeId,
                    $userId,
                    ServiceSegment::disabilitiesWelfare(),
                    equalTo(Carbon::now())
                )
                ->andReturn(Option::none());
            $this->getDwsProvisionReportUseCase
                ->expects('handle')
                ->never();

            $this->assertTrue(
                $this->buildCustomValidator(
                    ['userId' => $userId, 'status' => DwsProvisionReportStatus::fixed()->value()],
                    ['userId' => "start_of_dws_contract_period_filled:{$officeId},{$providedIn->format('Y-m')}," . Permission::updateDwsProvisionReports()]
                )
                    ->passes()
            );
        });
        $this->should('pass when provision report does not exist', function (): void {
            $officeId = $this->examples->offices[0]->id;
            $userId = $this->examples->users[0]->id;
            $providedIn = $this->examples->dwsProvisionReports[0]->providedIn;
            $this->lookupOfficeUseCase
                ->expects('handle')
                ->with($this->context, [Permission::updateDwsProvisionReports()], $officeId)
                ->andReturn(Seq::from($this->examples->offices[0]));
            $this->lookupUserUseCase
                ->expects('handle')
                ->with($this->context, Permission::updateDwsProvisionReports(), $userId)
                ->andReturn(Seq::from($this->examples->users[0]));
            $this->identifyContractUseCase
                ->expects('handle')
                ->with(
                    $this->context,
                    Permission::updateDwsProvisionReports(),
                    $officeId,
                    $userId,
                    ServiceSegment::disabilitiesWelfare(),
                    equalTo(Carbon::now())
                )
                ->andReturn(Option::some($this->examples->contracts[0]));
            $this->getDwsProvisionReportUseCase
                ->expects('handle')
                ->with(
                    $this->context,
                    Permission::updateDwsProvisionReports(),
                    $officeId,
                    $userId,
                    equalTo($providedIn)
                )
                ->andReturn(Option::none());

            $this->assertTrue(
                $this->buildCustomValidator(
                    ['userId' => $userId, 'status' => DwsProvisionReportStatus::fixed()->value()],
                    ['userId' => "start_of_dws_contract_period_filled:{$officeId},{$providedIn->format('Y-m')}," . Permission::updateDwsProvisionReports()]
                )
                    ->passes()
            );
        });
        $this->should('fail when start of dws contract home help service period is not filled although the report contains home help service', function (): void {
            $officeId = $this->examples->offices[0]->id;
            $userId = $this->examples->users[0]->id;
            $providedIn = $this->examples->dwsProvisionReports[0]->providedIn;
            $this->lookupOfficeUseCase
                ->expects('handle')
                ->with($this->context, [Permission::updateDwsProvisionReports()], $officeId)
                ->andReturn(Seq::from($this->examples->offices[0]));
            $this->lookupUserUseCase
                ->expects('handle')
                ->with($this->context, Permission::updateDwsProvisionReports(), $userId)
                ->andReturn(Seq::from($this->examples->users[0]));
            $this->identifyContractUseCase
                ->expects('handle')
                ->with(
                    $this->context,
                    Permission::updateDwsProvisionReports(),
                    $officeId,
                    $userId,
                    ServiceSegment::disabilitiesWelfare(),
                    equalTo(Carbon::now())
                )
                ->andReturn(Option::some($this->examples->contracts[0]->copy([
                    'dwsPeriods' => [
                        DwsServiceDivisionCode::homeHelpService()->value() => ContractPeriod::create([
                            'start' => null,
                            'end' => null,
                        ]),
                        DwsServiceDivisionCode::visitingCareForPwsd()->value() => ContractPeriod::create([
                            'start' => Carbon::now(),
                            'end' => null,
                        ]),
                    ],
                ])));
            $this->getDwsProvisionReportUseCase
                ->expects('handle')
                ->with(
                    $this->context,
                    Permission::updateDwsProvisionReports(),
                    $officeId,
                    $userId,
                    equalTo($providedIn)
                )
                ->andReturn(Option::some(
                    $this->examples->dwsProvisionReports[0]->copy([
                        'plans' => [
                            DwsProvisionReportItem::create([
                                'category' => DwsProjectServiceCategory::housework(),
                            ]),
                        ],
                        'results' => [],
                    ]),
                ));

            $this->assertTrue(
                $this->buildCustomValidator(
                    ['userId' => $userId, 'status' => DwsProvisionReportStatus::fixed()->value()],
                    ['userId' => "start_of_dws_contract_period_filled:{$officeId},{$providedIn->format('Y-m')}," . Permission::updateDwsProvisionReports()]
                )
                    ->fails()
            );
        });
        $this->should('pass when start of dws contract visiting care for pwsd period is filled and the report contains visiting care for pwsd', function (): void {
            $officeId = $this->examples->offices[0]->id;
            $userId = $this->examples->users[0]->id;
            $providedIn = $this->examples->dwsProvisionReports[0]->providedIn;
            $this->lookupOfficeUseCase
                ->expects('handle')
                ->with($this->context, [Permission::updateDwsProvisionReports()], $officeId)
                ->andReturn(Seq::from($this->examples->offices[0]));
            $this->lookupUserUseCase
                ->expects('handle')
                ->with($this->context, Permission::updateDwsProvisionReports(), $userId)
                ->andReturn(Seq::from($this->examples->users[0]));
            $this->identifyContractUseCase
                ->expects('handle')
                ->with(
                    $this->context,
                    Permission::updateDwsProvisionReports(),
                    $officeId,
                    $userId,
                    ServiceSegment::disabilitiesWelfare(),
                    equalTo(Carbon::now())
                )
                ->andReturn(Option::some($this->examples->contracts[0]->copy([
                    'dwsPeriods' => [
                        DwsServiceDivisionCode::homeHelpService()->value() => ContractPeriod::create([
                            'start' => Carbon::now(),
                            'end' => null,
                        ]),
                        DwsServiceDivisionCode::visitingCareForPwsd()->value() => ContractPeriod::create([
                            'start' => null,
                            'end' => null,
                        ]),
                    ],
                ])));
            $this->getDwsProvisionReportUseCase
                ->expects('handle')
                ->with(
                    $this->context,
                    Permission::updateDwsProvisionReports(),
                    $officeId,
                    $userId,
                    equalTo($providedIn)
                )
                ->andReturn(Option::some(
                    $this->examples->dwsProvisionReports[0]->copy([
                        'plans' => [
                            DwsProvisionReportItem::create([
                                'category' => DwsProjectServiceCategory::accompany(),
                            ]),
                        ],
                        'results' => [],
                    ]),
                ));

            $this->assertTrue(
                $this->buildCustomValidator(
                    ['userId' => $userId, 'status' => DwsProvisionReportStatus::fixed()->value()],
                    ['userId' => "start_of_dws_contract_period_filled:{$officeId},{$providedIn->format('Y-m')}," . Permission::updateDwsProvisionReports()]
                )
                    ->passes()
            );
        });
        $this->should('fail when start of dws contract visiting care for pwsd period is not filled although the report contains visiting care for pwsd', function (): void {
            $officeId = $this->examples->offices[0]->id;
            $userId = $this->examples->users[0]->id;
            $providedIn = $this->examples->dwsProvisionReports[0]->providedIn;
            $this->lookupOfficeUseCase
                ->expects('handle')
                ->with($this->context, [Permission::updateDwsProvisionReports()], $officeId)
                ->andReturn(Seq::from($this->examples->offices[0]));
            $this->lookupUserUseCase
                ->expects('handle')
                ->with($this->context, Permission::updateDwsProvisionReports(), $userId)
                ->andReturn(Seq::from($this->examples->users[0]));
            $this->identifyContractUseCase
                ->expects('handle')
                ->with(
                    $this->context,
                    Permission::updateDwsProvisionReports(),
                    $officeId,
                    $userId,
                    ServiceSegment::disabilitiesWelfare(),
                    equalTo(Carbon::now())
                )
                ->andReturn(Option::some($this->examples->contracts[0]->copy([
                    'dwsPeriods' => [
                        DwsServiceDivisionCode::homeHelpService()->value() => ContractPeriod::create([
                            'start' => Carbon::now(),
                            'end' => null,
                        ]),
                        DwsServiceDivisionCode::visitingCareForPwsd()->value() => ContractPeriod::create([
                            'start' => null,
                            'end' => null,
                        ]),
                    ],
                ])));
            $this->getDwsProvisionReportUseCase
                ->expects('handle')
                ->with(
                    $this->context,
                    Permission::updateDwsProvisionReports(),
                    $officeId,
                    $userId,
                    equalTo($providedIn)
                )
                ->andReturn(Option::some(
                    $this->examples->dwsProvisionReports[0]->copy([
                        'plans' => [
                            DwsProvisionReportItem::create([
                                'category' => DwsProjectServiceCategory::visitingCareForPwsd(),
                            ]),
                        ],
                        'results' => [],
                    ]),
                ));

            $this->assertTrue(
                $this->buildCustomValidator(
                    ['userId' => $userId, 'status' => DwsProvisionReportStatus::fixed()->value()],
                    ['userId' => "start_of_dws_contract_period_filled:{$officeId},{$providedIn->format('Y-m')}," . Permission::updateDwsProvisionReports()]
                )
                    ->fails()
            );
        });
        $this->should('pass when start of dws contract visiting care for pwsd period is filled and the report contains visiting care for pwsd', function (): void {
            $officeId = $this->examples->offices[0]->id;
            $userId = $this->examples->users[0]->id;
            $providedIn = $this->examples->dwsProvisionReports[0]->providedIn;
            $this->lookupOfficeUseCase
                ->expects('handle')
                ->with($this->context, [Permission::updateDwsProvisionReports()], $officeId)
                ->andReturn(Seq::from($this->examples->offices[0]));
            $this->lookupUserUseCase
                ->expects('handle')
                ->with($this->context, Permission::updateDwsProvisionReports(), $userId)
                ->andReturn(Seq::from($this->examples->users[0]));
            $this->identifyContractUseCase
                ->expects('handle')
                ->with(
                    $this->context,
                    Permission::updateDwsProvisionReports(),
                    $officeId,
                    $userId,
                    ServiceSegment::disabilitiesWelfare(),
                    equalTo(Carbon::now())
                )
                ->andReturn(Option::some($this->examples->contracts[0]->copy([
                    'dwsPeriods' => [
                        DwsServiceDivisionCode::homeHelpService()->value() => ContractPeriod::create([
                            'start' => null,
                            'end' => null,
                        ]),
                        DwsServiceDivisionCode::visitingCareForPwsd()->value() => ContractPeriod::create([
                            'start' => Carbon::now(),
                            'end' => null,
                        ]),
                    ],
                ])));
            $this->getDwsProvisionReportUseCase
                ->expects('handle')
                ->with(
                    $this->context,
                    Permission::updateDwsProvisionReports(),
                    $officeId,
                    $userId,
                    equalTo($providedIn)
                )
                ->andReturn(Option::some(
                    $this->examples->dwsProvisionReports[0]->copy([
                        'plans' => [
                            DwsProvisionReportItem::create([
                                'category' => DwsProjectServiceCategory::visitingCareForPwsd(),
                            ]),
                        ],
                        'results' => [],
                    ]),
                ));

            $this->assertTrue(
                $this->buildCustomValidator(
                    ['userId' => $userId, 'status' => DwsProvisionReportStatus::fixed()->value()],
                    ['userId' => "start_of_dws_contract_period_filled:{$officeId},{$providedIn->format('Y-m')}," . Permission::updateDwsProvisionReports()]
                )
                    ->passes()
            );
        });
        $this->should('fail when start of dws contract visiting care for pwsd is not filled although the report contains both services', function (): void {
            $officeId = $this->examples->offices[0]->id;
            $userId = $this->examples->users[0]->id;
            $providedIn = $this->examples->dwsProvisionReports[0]->providedIn;
            $this->lookupOfficeUseCase
                ->expects('handle')
                ->with($this->context, [Permission::updateDwsProvisionReports()], $officeId)
                ->andReturn(Seq::from($this->examples->offices[0]));
            $this->lookupUserUseCase
                ->expects('handle')
                ->with($this->context, Permission::updateDwsProvisionReports(), $userId)
                ->andReturn(Seq::from($this->examples->users[0]));
            $this->identifyContractUseCase
                ->expects('handle')
                ->with(
                    $this->context,
                    Permission::updateDwsProvisionReports(),
                    $officeId,
                    $userId,
                    ServiceSegment::disabilitiesWelfare(),
                    equalTo(Carbon::now())
                )
                ->andReturn(Option::some($this->examples->contracts[0]->copy([
                    'dwsPeriods' => [
                        DwsServiceDivisionCode::homeHelpService()->value() => ContractPeriod::create([
                            'start' => Carbon::now(),
                            'end' => null,
                        ]),
                        DwsServiceDivisionCode::visitingCareForPwsd()->value() => ContractPeriod::create([
                            'start' => null,
                            'end' => null,
                        ]),
                    ],
                ])));
            $this->getDwsProvisionReportUseCase
                ->expects('handle')
                ->with(
                    $this->context,
                    Permission::updateDwsProvisionReports(),
                    $officeId,
                    $userId,
                    equalTo($providedIn)
                )
                ->andReturn(Option::some(
                    $this->examples->dwsProvisionReports[0]->copy([
                        'plans' => [
                            DwsProvisionReportItem::create([
                                'category' => DwsProjectServiceCategory::accompanyWithPhysicalCare(),
                            ]),
                        ],
                        'results' => [
                            DwsProvisionReportItem::create([
                                'category' => DwsProjectServiceCategory::visitingCareForPwsd(),
                            ]),
                        ],
                    ]),
                ));

            $this->assertTrue(
                $this->buildCustomValidator(
                    ['userId' => $userId, 'status' => DwsProvisionReportStatus::fixed()->value()],
                    ['userId' => "start_of_dws_contract_period_filled:{$officeId},{$providedIn->format('Y-m')}," . Permission::updateDwsProvisionReports()]
                )
                    ->fails()
            );
        });
        $this->should('fail when start of dws contract home help service is not filled although the report contains both services', function (): void {
            $officeId = $this->examples->offices[0]->id;
            $userId = $this->examples->users[0]->id;
            $providedIn = $this->examples->dwsProvisionReports[0]->providedIn;
            $this->lookupOfficeUseCase
                ->expects('handle')
                ->with($this->context, [Permission::updateDwsProvisionReports()], $officeId)
                ->andReturn(Seq::from($this->examples->offices[0]));
            $this->lookupUserUseCase
                ->expects('handle')
                ->with($this->context, Permission::updateDwsProvisionReports(), $userId)
                ->andReturn(Seq::from($this->examples->users[0]));
            $this->identifyContractUseCase
                ->expects('handle')
                ->with(
                    $this->context,
                    Permission::updateDwsProvisionReports(),
                    $officeId,
                    $userId,
                    ServiceSegment::disabilitiesWelfare(),
                    equalTo(Carbon::now())
                )
                ->andReturn(Option::some($this->examples->contracts[0]->copy([
                    'dwsPeriods' => [
                        DwsServiceDivisionCode::homeHelpService()->value() => ContractPeriod::create([
                            'start' => null,
                            'end' => null,
                        ]),
                        DwsServiceDivisionCode::visitingCareForPwsd()->value() => ContractPeriod::create([
                            'start' => Carbon::now(),
                            'end' => null,
                        ]),
                    ],
                ])));
            $this->getDwsProvisionReportUseCase
                ->expects('handle')
                ->with(
                    $this->context,
                    Permission::updateDwsProvisionReports(),
                    $officeId,
                    $userId,
                    equalTo($providedIn)
                )
                ->andReturn(Option::some(
                    $this->examples->dwsProvisionReports[0]->copy([
                        'plans' => [
                            DwsProvisionReportItem::create([
                                'category' => DwsProjectServiceCategory::accompanyWithPhysicalCare(),
                            ]),
                        ],
                        'results' => [
                            DwsProvisionReportItem::create([
                                'category' => DwsProjectServiceCategory::visitingCareForPwsd(),
                            ]),
                        ],
                    ]),
                ));

            $this->assertTrue(
                $this->buildCustomValidator(
                    ['userId' => $userId, 'status' => DwsProvisionReportStatus::fixed()->value()],
                    ['userId' => "start_of_dws_contract_period_filled:{$officeId},{$providedIn->format('Y-m')}," . Permission::updateDwsProvisionReports()]
                )
                    ->fails()
            );
        });
        $this->should('pass when start of dws contract both services period is filled and the report contains both services', function (): void {
            $officeId = $this->examples->offices[0]->id;
            $userId = $this->examples->users[0]->id;
            $providedIn = $this->examples->dwsProvisionReports[0]->providedIn;
            $this->lookupOfficeUseCase
                ->expects('handle')
                ->with($this->context, [Permission::updateDwsProvisionReports()], $officeId)
                ->andReturn(Seq::from($this->examples->offices[0]));
            $this->lookupUserUseCase
                ->expects('handle')
                ->with($this->context, Permission::updateDwsProvisionReports(), $userId)
                ->andReturn(Seq::from($this->examples->users[0]));
            $this->identifyContractUseCase
                ->expects('handle')
                ->with(
                    $this->context,
                    Permission::updateDwsProvisionReports(),
                    $officeId,
                    $userId,
                    ServiceSegment::disabilitiesWelfare(),
                    equalTo(Carbon::now())
                )
                ->andReturn(Option::some($this->examples->contracts[0]->copy([
                    'dwsPeriods' => [
                        DwsServiceDivisionCode::homeHelpService()->value() => ContractPeriod::create([
                            'start' => Carbon::now(),
                            'end' => null,
                        ]),
                        DwsServiceDivisionCode::visitingCareForPwsd()->value() => ContractPeriod::create([
                            'start' => Carbon::now(),
                            'end' => null,
                        ]),
                    ],
                ])));
            $this->getDwsProvisionReportUseCase
                ->expects('handle')
                ->with(
                    $this->context,
                    Permission::updateDwsProvisionReports(),
                    $officeId,
                    $userId,
                    equalTo($providedIn)
                )
                ->andReturn(Option::some(
                    $this->examples->dwsProvisionReports[0]->copy([
                        'plans' => [
                            DwsProvisionReportItem::create([
                                'category' => DwsProjectServiceCategory::accompanyWithPhysicalCare(),
                            ]),
                        ],
                        'results' => [
                            DwsProvisionReportItem::create([
                                'category' => DwsProjectServiceCategory::visitingCareForPwsd(),
                            ]),
                        ],
                    ]),
                ));

            $this->assertTrue(
                $this->buildCustomValidator(
                    ['userId' => $userId, 'status' => DwsProvisionReportStatus::fixed()->value()],
                    ['userId' => "start_of_dws_contract_period_filled:{$officeId},{$providedIn->format('Y-m')}," . Permission::updateDwsProvisionReports()]
                )
                    ->passes()
            );
        });
    }
}

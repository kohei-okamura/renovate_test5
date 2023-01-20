<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\App\Validations\Rules;

use Domain\Common\Carbon;
use Domain\Common\ServiceSegment;
use Domain\Contract\ContractPeriod;
use Domain\Permission\Permission;
use Domain\ProvisionReport\LtcsProvisionReportStatus;
use ScalikePHP\Option;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\IdentifyContractUseCaseMixin;
use Tests\Unit\Mixins\LookupOfficeUseCaseMixin;
use Tests\Unit\Mixins\LookupUserUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;

/**
 * {@link \App\Validations\Rules\StartOfLtcsContractPeriodFilledRule} のテスト.
 */
final class StartOfLtcsContractPeriodFilledRuleTest extends Test
{
    use CarbonMixin;
    use ContextMixin;
    use ExamplesConsumer;
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
    public function describe_validateStartOfLtcsContractPeriodFilled(): void
    {
        $this->should('pass when status is not fixed', function (): void {
            $officeId = $this->examples->offices[0]->id;
            $userId = $this->examples->users[0]->id;
            $this->lookupOfficeUseCase
                ->expects('handle')
                ->never();
            $this->lookupUserUseCase
                ->expects('handle')
                ->never();
            $this->identifyContractUseCase
                ->expects('handle')
                ->never();

            $this->assertTrue(
                $this->buildCustomValidator(
                    ['userId' => $userId, 'status' => LtcsProvisionReportStatus::inProgress()->value()],
                    ['userId' => "start_of_ltcs_contract_period_filled:{$officeId}," . Permission::updateLtcsProvisionReports()]
                )
                    ->passes()
            );
        });
        $this->should('pass when officeId does not exist', function (): void {
            $officeId = self::NOT_EXISTING_ID;
            $userId = $this->examples->users[0]->id;
            $this->lookupOfficeUseCase
                ->expects('handle')
                ->with($this->context, [Permission::updateLtcsProvisionReports()], $officeId)
                ->andReturn(Seq::empty());
            $this->lookupUserUseCase
                ->expects('handle')
                ->never();
            $this->identifyContractUseCase
                ->expects('handle')
                ->never();

            $this->assertTrue(
                $this->buildCustomValidator(
                    ['userId' => $userId, 'status' => LtcsProvisionReportStatus::fixed()->value()],
                    ['userId' => "start_of_ltcs_contract_period_filled:{$officeId}," . Permission::updateLtcsProvisionReports()]
                )
                    ->passes()
            );
        });
        $this->should('pass when userId does not exist', function (): void {
            $officeId = $this->examples->offices[0]->id;
            $userId = self::NOT_EXISTING_ID;
            $this->lookupOfficeUseCase
                ->expects('handle')
                ->with($this->context, [Permission::updateLtcsProvisionReports()], $officeId)
                ->andReturn(Seq::from($this->examples->offices[0]));
            $this->lookupUserUseCase
                ->expects('handle')
                ->with($this->context, Permission::updateLtcsProvisionReports(), $userId)
                ->andReturn(Seq::empty());
            $this->identifyContractUseCase
                ->expects('handle')
                ->never();

            $this->assertTrue(
                $this->buildCustomValidator(
                    ['userId' => $userId, 'status' => LtcsProvisionReportStatus::fixed()->value()],
                    ['userId' => "start_of_ltcs_contract_period_filled:{$officeId}," . Permission::updateLtcsProvisionReports()]
                )
                    ->passes()
            );
        });
        $this->should('true when contract does not exist', function (): void {
            $officeId = $this->examples->offices[0]->id;
            $userId = $this->examples->users[0]->id;
            $this->lookupOfficeUseCase
                ->expects('handle')
                ->with($this->context, [Permission::updateLtcsProvisionReports()], $officeId)
                ->andReturn(Seq::from($this->examples->offices[0]));
            $this->lookupUserUseCase
                ->expects('handle')
                ->with($this->context, Permission::updateLtcsProvisionReports(), $userId)
                ->andReturn(Seq::from($this->examples->users[0]));
            $this->identifyContractUseCase
                ->expects('handle')
                ->with(
                    $this->context,
                    Permission::updateLtcsProvisionReports(),
                    $officeId,
                    $userId,
                    ServiceSegment::longTermCare(),
                    equalTo(Carbon::now())
                )
                ->andReturn(Option::none());

            $this->assertTrue(
                $this->buildCustomValidator(
                    ['userId' => $userId, 'status' => LtcsProvisionReportStatus::fixed()->value()],
                    ['userId' => "start_of_ltcs_contract_period_filled:{$officeId}," . Permission::updateLtcsProvisionReports()]
                )
                    ->passes()
            );
        });
        $this->should('fail when start of ltcs contract period is not filled', function (): void {
            $officeId = $this->examples->offices[0]->id;
            $userId = $this->examples->users[0]->id;
            $this->lookupOfficeUseCase
                ->expects('handle')
                ->with($this->context, [Permission::updateLtcsProvisionReports()], $officeId)
                ->andReturn(Seq::from($this->examples->offices[0]));
            $this->lookupUserUseCase
                ->expects('handle')
                ->with($this->context, Permission::updateLtcsProvisionReports(), $userId)
                ->andReturn(Seq::from($this->examples->users[0]));
            $this->identifyContractUseCase
                ->expects('handle')
                ->with(
                    $this->context,
                    Permission::updateLtcsProvisionReports(),
                    $officeId,
                    $userId,
                    ServiceSegment::longTermCare(),
                    equalTo(Carbon::now())
                )
                ->andReturn(Option::some($this->examples->contracts[1]->copy([
                    'ltcsPeriod' => ContractPeriod::create([
                        'start' => null,
                        'end' => null,
                    ]),
                ])));

            $this->assertTrue(
                $this->buildCustomValidator(
                    ['userId' => $userId, 'status' => LtcsProvisionReportStatus::fixed()->value()],
                    ['userId' => "start_of_ltcs_contract_period_filled:{$officeId}," . Permission::updateLtcsProvisionReports()]
                )
                    ->fails()
            );
        });
        $this->should('pass when start of ltcs contract period is filled', function (): void {
            $officeId = $this->examples->offices[0]->id;
            $userId = $this->examples->users[0]->id;
            $this->lookupOfficeUseCase
                ->expects('handle')
                ->with($this->context, [Permission::updateLtcsProvisionReports()], $officeId)
                ->andReturn(Seq::from($this->examples->offices[0]));
            $this->lookupUserUseCase
                ->expects('handle')
                ->with($this->context, Permission::updateLtcsProvisionReports(), $userId)
                ->andReturn(Seq::from($this->examples->users[0]));
            $this->identifyContractUseCase
                ->expects('handle')
                ->with(
                    $this->context,
                    Permission::updateLtcsProvisionReports(),
                    $officeId,
                    $userId,
                    ServiceSegment::longTermCare(),
                    equalTo(Carbon::now())
                )
                ->andReturn(Option::some($this->examples->contracts[1]->copy([
                    'ltcsPeriod' => ContractPeriod::create([
                        'start' => Carbon::now(),
                        'end' => null,
                    ]),
                ])));

            $this->assertTrue(
                $this->buildCustomValidator(
                    ['userId' => $userId, 'status' => LtcsProvisionReportStatus::fixed()->value()],
                    ['userId' => "start_of_ltcs_contract_period_filled:{$officeId}," . Permission::updateLtcsProvisionReports()]
                )
                    ->passes()
            );
        });
    }
}

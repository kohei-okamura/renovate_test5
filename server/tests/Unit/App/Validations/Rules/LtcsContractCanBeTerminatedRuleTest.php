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
use Domain\Permission\Permission;
use Domain\ProvisionReport\LtcsProvisionReport;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\FindLtcsProvisionReportUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;

/**
 * {@link \App\Validations\Rules\LtcsContractCanBeTerminatedRule} のテスト.
 */
final class LtcsContractCanBeTerminatedRuleTest extends Test
{
    use ExamplesConsumer;
    use FindLtcsProvisionReportUseCaseMixin;
    use MockeryMixin;
    use RuleTestSupport;
    use UnitSupport;

    private LtcsProvisionReport $provisionReport;

    /**
     * 初期化処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachSpec(function (self $self): void {
            $self->provisionReport = $self->examples->ltcsProvisionReports[0];

            $self->findLtcsProvisionReportUseCase
                ->allows('handle')
                ->andReturn(FinderResult::from(
                    [
                        $self->provisionReport->copy(['providedIn' => Carbon::parse('2020-10')]),
                        $self->provisionReport->copy(['providedIn' => Carbon::parse('2020-11')]),
                    ],
                    Pagination::create()
                ))
                ->byDefault();
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_validateLtcsContractCanBeTerminated(): void
    {
        $this->should('pass when terminatedOn is empty', function (): void {
            $validator = $this->buildCustomValidator(
                [
                    'userId' => $this->provisionReport->userId,
                    'officeId' => $this->provisionReport->officeId,
                    'terminatedOn' => null,
                ],
                ['terminatedOn' => 'ltcs_contract_can_be_terminated:userId,officeId,' . Permission::updateLtcsContracts()]
            );
            $this->assertTrue($validator->passes());
        });
        $this->should('pass when terminatedOn is not date', function (): void {
            $validator = $this->buildCustomValidator(
                [
                    'userId' => $this->provisionReport->userId,
                    'officeId' => $this->provisionReport->officeId,
                    'terminatedOn' => 'error',
                ],
                ['terminatedOn' => 'ltcs_contract_can_be_terminated:userId,officeId,' . Permission::updateLtcsContracts()]
            );
            $this->assertTrue($validator->passes());
        });
        $this->should('pass when provision reports with providedIn after terminatedOn do not exist', function (): void {
            $validator = $this->buildCustomValidator(
                [
                    'userId' => $this->provisionReport->userId,
                    'officeId' => $this->provisionReport->officeId,
                    'terminatedOn' => '2020-12-01',
                ],
                ['terminatedOn' => 'ltcs_contract_can_be_terminated:userId,officeId,' . Permission::updateLtcsContracts()]
            );
            $this->assertTrue($validator->passes());
        });
        $this->should('fail when provision reports with providedIn after terminatedOn exist', function (): void {
            $validator = $this->buildCustomValidator(
                [
                    'userId' => $this->provisionReport->userId,
                    'officeId' => $this->provisionReport->officeId,
                    'terminatedOn' => '2020-10-31',
                ],
                ['terminatedOn' => 'ltcs_contract_can_be_terminated:userId,officeId,' . Permission::updateLtcsContracts()]
            );
            $this->assertTrue($validator->fails());
        });
        $this->should('use FindLtcsProvisionReportUseCase', function (): void {
            $this->findLtcsProvisionReportUseCase
                ->expects('handle')
                ->andReturn(FinderResult::from(
                    [
                        $this->provisionReport->copy(['providedIn' => Carbon::parse('2020-10')]),
                        $this->provisionReport->copy(['providedIn' => Carbon::parse('2020-11')]),
                    ],
                    Pagination::create()
                ));

            $validator = $this->buildCustomValidator(
                [
                    'userId' => $this->provisionReport->userId,
                    'officeId' => $this->provisionReport->officeId,
                    'terminatedOn' => '2020-12-01',
                ],
                ['terminatedOn' => 'ltcs_contract_can_be_terminated:userId,officeId,' . Permission::updateLtcsContracts()]
            );
            $validator->validate();
        });
    }
}

<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\ProvisionReport;

use Domain\Billing\LtcsBillingServiceDetail;
use Domain\Billing\LtcsBillingServiceDetailDisposition;
use Domain\Common\Carbon;
use Domain\Common\ServiceSegment;
use Domain\Permission\Permission;
use Domain\ProvisionReport\LtcsBuildingSubtraction;
use Domain\ProvisionReport\LtcsProvisionReport;
use Domain\ProvisionReport\LtcsProvisionReportOverScore;
use Domain\ProvisionReport\LtcsProvisionReportStatus;
use Domain\ServiceCode\ServiceCode;
use Domain\ServiceCodeDictionary\LtcsNoteRequirement;
use Domain\ServiceCodeDictionary\LtcsServiceCodeCategory;
use Mockery;
use ScalikePHP\Option;
use ScalikePHP\Seq;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\BuildLtcsServiceDetailListUseCaseMixin;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\IdentifyContractUseCaseMixin;
use Tests\Unit\Mixins\LookupUserUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;
use UseCase\ProvisionReport\GetLtcsProvisionReportScoreSummaryInteractor;

/**
 * {@link \UseCase\ProvisionReport\GetLtcsProvisionReportScoreSummaryInteractor} のテスト.
 */
final class GetLtcsProvisionReportScoreSummaryInteractorTest extends Test
{
    use BuildLtcsServiceDetailListUseCaseMixin;
    use CarbonMixin;
    use ContextMixin;
    use ExamplesConsumer;
    use IdentifyContractUseCaseMixin;
    use LookupUserUseCaseMixin;
    use MatchesSnapshots;
    use MockeryMixin;
    use UnitSupport;

    private LtcsProvisionReport $ltcsProvisionReport;
    private GetLtcsProvisionReportScoreSummaryInteractor $interactor;
    private LtcsProvisionReportOverScore $plan;
    private LtcsProvisionReportOverScore $result;

    /**
     * 初期化処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachSpec(function (self $self): void {
            $self->plan = new LtcsProvisionReportOverScore(
                maxBenefitExcessScore: 0,
                maxBenefitQuotaExcessScore: 0,
            );
            $self->result = new LtcsProvisionReportOverScore(
                maxBenefitExcessScore: 0,
                maxBenefitQuotaExcessScore: 0,
            );
            $self->identifyContractUseCase
                ->allows('handle')
                ->andReturn(Option::from($self->examples->contracts[0]))
                ->byDefault();
            $self->buildLtcsServiceDetailListUseCase
                ->allows('handle')
                ->with(Mockery::any(), Mockery::any(), Mockery::any(), Mockery::any(), true)
                ->andReturn([
                    $self->serviceDetail(),
                ])
                ->byDefault();
            $self->buildLtcsServiceDetailListUseCase
                ->allows('handle')
                ->with(Mockery::any(), Mockery::any(), Mockery::any(), Mockery::any(), false)
                ->andReturn([
                    $self->serviceDetail(),
                    $self->serviceDetail([
                        'serviceCode' => ServiceCode::fromString('111112'),
                        'unitScore' => 313,
                        'totalScore' => 313,
                    ]),
                ])
                ->byDefault();
            $self->lookupUserUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->examples->users[0]))
                ->byDefault();

            $self->ltcsProvisionReport = LtcsProvisionReport::create(
                [
                    'officeId' => $self->examples->offices[0]->id,
                    'userId' => $self->examples->users[0]->id,
                    'contractId' => $self->examples->contracts[0]->id,
                    'providedIn' => Carbon::parse('2021-10'),
                    'entries' => $self->examples->ltcsProvisionReports[0]->entries,
                    'status' => LtcsProvisionReportStatus::inProgress(),
                    'specifiedOfficeAddition' => $self->examples->homeVisitLongTermCareCalcSpecs[0]->specifiedOfficeAddition,
                    'treatmentImprovementAddition' => $self->examples->homeVisitLongTermCareCalcSpecs[0]->treatmentImprovementAddition,
                    'specifiedTreatmentImprovementAddition' => $self->examples->homeVisitLongTermCareCalcSpecs[0]->specifiedTreatmentImprovementAddition,
                    'baseIncreaseSupportAddition' => $self->examples->homeVisitLongTermCareCalcSpecs[0]->baseIncreaseSupportAddition,
                    'locationAddition' => $self->examples->homeVisitLongTermCareCalcSpecs[0]->locationAddition,
                    'plan' => $self->plan,
                    'result' => $self->result,
                ]
            );
            $self->interactor = app(GetLtcsProvisionReportScoreSummaryInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('return Array of TotalScore of Result and Plan totalScore', function (): void {
            $actual = $this->interactor
                ->handle(
                    $this->context,
                    $this->examples->users[0]->id,
                    $this->examples->offices[0]->id,
                    Carbon::parse('2021-10'),
                    Seq::fromArray($this->examples->ltcsProvisionReports[0]->entries),
                    $this->examples->homeVisitLongTermCareCalcSpecs[0]->specifiedOfficeAddition,
                    $this->examples->homeVisitLongTermCareCalcSpecs[0]->treatmentImprovementAddition,
                    $this->examples->homeVisitLongTermCareCalcSpecs[0]->specifiedTreatmentImprovementAddition,
                    $this->examples->homeVisitLongTermCareCalcSpecs[0]->baseIncreaseSupportAddition,
                    $this->examples->homeVisitLongTermCareCalcSpecs[0]->locationAddition,
                    $this->plan,
                    $this->result
                );
            $this->assertMatchesSnapshot($actual);
        });
        $this->should('use identifyContractUseCase', function (): void {
            $this->identifyContractUseCase
                ->expects('handle')
                ->with(
                    $this->context,
                    Permission::updateLtcsProvisionReports(),
                    $this->examples->offices[0]->id,
                    $this->examples->users[0]->id,
                    ServiceSegment::longTermCare(),
                    equalTo(Carbon::now())
                )
                ->andReturn(Option::from($this->examples->contracts[0]));
            $this->interactor
                ->handle(
                    $this->context,
                    $this->examples->users[0]->id,
                    $this->examples->offices[0]->id,
                    Carbon::parse('2021-10'),
                    Seq::fromArray($this->examples->ltcsProvisionReports[0]->entries),
                    $this->examples->homeVisitLongTermCareCalcSpecs[0]->specifiedOfficeAddition,
                    $this->examples->homeVisitLongTermCareCalcSpecs[0]->treatmentImprovementAddition,
                    $this->examples->homeVisitLongTermCareCalcSpecs[0]->specifiedTreatmentImprovementAddition,
                    $this->examples->homeVisitLongTermCareCalcSpecs[0]->baseIncreaseSupportAddition,
                    $this->examples->homeVisitLongTermCareCalcSpecs[0]->locationAddition,
                    $this->plan,
                    $this->result
                );
        });
        $this->should('use buildLtcsServiceDetailListUseCase for usePlan', function (): void {
            $this->buildLtcsServiceDetailListUseCase
                ->expects('handle')
                ->with(
                    $this->context,
                    equalTo(Carbon::parse('2021-10-01')),
                    equalTo(Seq::from($this->ltcsProvisionReport)),
                    equalTo(Seq::from($this->examples->users[0])),
                    true
                )
                ->andReturn([
                    $this->serviceDetail(),
                ]);
            $this->interactor
                ->handle(
                    $this->context,
                    $this->examples->users[0]->id,
                    $this->examples->offices[0]->id,
                    Carbon::parse('2021-10'),
                    Seq::fromArray($this->examples->ltcsProvisionReports[0]->entries),
                    $this->examples->homeVisitLongTermCareCalcSpecs[0]->specifiedOfficeAddition,
                    $this->examples->homeVisitLongTermCareCalcSpecs[0]->treatmentImprovementAddition,
                    $this->examples->homeVisitLongTermCareCalcSpecs[0]->specifiedTreatmentImprovementAddition,
                    $this->examples->homeVisitLongTermCareCalcSpecs[0]->baseIncreaseSupportAddition,
                    $this->examples->homeVisitLongTermCareCalcSpecs[0]->locationAddition,
                    $this->plan,
                    $this->result
                );
        });
        $this->should('use buildLtcsServiceDetailListUseCase', function (): void {
            $this->buildLtcsServiceDetailListUseCase
                ->expects('handle')
                ->with(
                    $this->context,
                    equalTo(Carbon::parse('2021-10')),
                    equalTo(Seq::from($this->ltcsProvisionReport)),
                    equalTo(Seq::from($this->examples->users[0])),
                    false
                )
                ->andReturn([
                    $this->serviceDetail(),
                ]);
            $this->interactor
                ->handle(
                    $this->context,
                    $this->examples->users[0]->id,
                    $this->examples->offices[0]->id,
                    Carbon::parse('2021-10'),
                    Seq::fromArray($this->examples->ltcsProvisionReports[0]->entries),
                    $this->examples->homeVisitLongTermCareCalcSpecs[0]->specifiedOfficeAddition,
                    $this->examples->homeVisitLongTermCareCalcSpecs[0]->treatmentImprovementAddition,
                    $this->examples->homeVisitLongTermCareCalcSpecs[0]->specifiedTreatmentImprovementAddition,
                    $this->examples->homeVisitLongTermCareCalcSpecs[0]->baseIncreaseSupportAddition,
                    $this->examples->homeVisitLongTermCareCalcSpecs[0]->locationAddition,
                    $this->plan,
                    $this->result
                );
        });
        $this->should('use LookupUserUseCase', function (): void {
            $this->lookupUserUseCase
                ->expects('handle')
                ->with($this->context, Permission::updateLtcsProvisionReports(), $this->examples->users[0]->id)
                ->andReturn(Seq::from($this->examples->users[0]));
            $this->interactor
                ->handle(
                    $this->context,
                    $this->examples->users[0]->id,
                    $this->examples->offices[0]->id,
                    Carbon::parse('2021-10'),
                    Seq::fromArray($this->examples->ltcsProvisionReports[0]->entries),
                    $this->examples->homeVisitLongTermCareCalcSpecs[0]->specifiedOfficeAddition,
                    $this->examples->homeVisitLongTermCareCalcSpecs[0]->treatmentImprovementAddition,
                    $this->examples->homeVisitLongTermCareCalcSpecs[0]->specifiedTreatmentImprovementAddition,
                    $this->examples->homeVisitLongTermCareCalcSpecs[0]->baseIncreaseSupportAddition,
                    $this->examples->homeVisitLongTermCareCalcSpecs[0]->locationAddition,
                    $this->plan,
                    $this->result
                );
        });
    }

    /**
     * サービス詳細を生成する.
     *
     * @param array $overwrites 上書きしたい属性値
     */
    private function serviceDetail(array $overwrites = []): LtcsBillingServiceDetail
    {
        $x = new LtcsBillingServiceDetail(
            userId: $this->examples->users[0]->id,
            disposition: LtcsBillingServiceDetailDisposition::result(),
            providedOn: Carbon::parse('2021-10-01'),
            serviceCode: ServiceCode::fromString('111111'),
            serviceCodeCategory: LtcsServiceCodeCategory::physicalCare(),
            buildingSubtraction: LtcsBuildingSubtraction::none(),
            noteRequirement: LtcsNoteRequirement::none(),
            isAddition: false,
            isLimited: true,
            durationMinutes: 30,
            unitScore: 250,
            count: 1,
            wholeScore: 250,
            maxBenefitQuotaExcessScore: 0,
            maxBenefitExcessScore: 0,
            totalScore: 250,
        );
        return $x->copy($overwrites);
    }
}

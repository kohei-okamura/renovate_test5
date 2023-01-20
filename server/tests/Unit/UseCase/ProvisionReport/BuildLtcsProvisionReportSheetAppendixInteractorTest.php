<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\ProvisionReport;

use Domain\Billing\LtcsBillingServiceDetail;
use Domain\Billing\LtcsBillingServiceDetailDisposition;
use Domain\Common\Carbon;
use Domain\Common\StructuredName;
use Domain\LtcsInsCard\LtcsInsCard;
use Domain\LtcsInsCard\LtcsLevel;
use Domain\Office\Office;
use Domain\ProvisionReport\LtcsBuildingSubtraction;
use Domain\ProvisionReport\LtcsProvisionReport;
use Domain\ProvisionReport\LtcsProvisionReportOverScore;
use Domain\ServiceCode\ServiceCode;
use Domain\ServiceCodeDictionary\LtcsHomeVisitLongTermCareDictionaryEntry;
use Domain\ServiceCodeDictionary\LtcsNoteRequirement;
use Domain\ServiceCodeDictionary\LtcsServiceCodeCategory;
use Domain\User\User;
use Lib\Exceptions\SetupException;
use ScalikePHP\Map;
use ScalikePHP\Option;
use ScalikePHP\Seq;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\IdentifyLtcsAreaGradeFeeUseCaseMixin;
use Tests\Unit\Mixins\IdentifyUserLtcsSubsidyUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;
use UseCase\ProvisionReport\BuildLtcsProvisionReportSheetAppendixInteractor;

/**
 * {@link \UseCase\ProvisionReport\BuildLtcsProvisionReportSheetAppendixInteractor} のテスト.
 */
final class BuildLtcsProvisionReportSheetAppendixInteractorTest extends Test
{
    use ContextMixin;
    use ExamplesConsumer;
    use IdentifyLtcsAreaGradeFeeUseCaseMixin;
    use IdentifyUserLtcsSubsidyUseCaseMixin;
    use MatchesSnapshots;
    use MockeryMixin;
    use UnitSupport;

    private LtcsProvisionReport $report;
    private LtcsInsCard $insCard;
    private Office $office;
    private User $user;
    private Seq $serviceDetails;
    private Map $serviceCodeMap;

    private BuildLtcsProvisionReportSheetAppendixInteractor $interactor;

    /**
     * 初期化処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachTest(function (self $self): void {
            $self->report = $self->examples->ltcsProvisionReports[0]->copy([
                'plan' => new LtcsProvisionReportOverScore(
                    maxBenefitExcessScore: 0,
                    maxBenefitQuotaExcessScore: 0,
                ),
                'result' => new LtcsProvisionReportOverScore(
                    maxBenefitExcessScore: 100,
                    maxBenefitQuotaExcessScore: 200,
                ),
            ]);
            $self->insCard = $self->examples->ltcsInsCards[0]->copy([
                'ltcsLevel' => LtcsLevel::careLevel3(),
                'copayRate' => 20,
            ]);
            $self->office = $self->examples->offices[0]->copy([
                'ltcsHomeVisitLongTermCareService' => $self->examples->offices[0]->ltcsHomeVisitLongTermCareService->copy([
                    'code' => '0123456789',
                ]),
            ]);
            $self->user = $self->examples->users[0]->copy([
                'name' => new StructuredName(
                    familyName: '田中',
                    givenName: '提供太郎',
                    phoneticFamilyName: 'タナカ',
                    phoneticGivenName: 'テイキョウタロウ',
                ),
            ]);
            $self->serviceDetails = Seq::from(
                $self->ltcsBillingServiceDetail(),
                // 身9生3・2人・深・Ⅰ
                $self->ltcsBillingServiceDetail([
                    'serviceCode' => ServiceCode::fromString('112444'),
                    'durationMinutes' => 310,
                    'unitScore' => 1284,
                    'wholeScore' => 1284,
                    'totalScore' => 1284,
                ]),
                // 身9生3・2人・深・Ⅰ
                $self->ltcsBillingServiceDetail([
                    'serviceCode' => ServiceCode::fromString('112444'),
                    'durationMinutes' => 310,
                    'unitScore' => 1443,
                    'wholeScore' => 1443,
                    'totalScore' => 1443,
                ]),
                // 身9生3・2人・深・Ⅰ
                $self->ltcsBillingServiceDetail([
                    'serviceCode' => ServiceCode::fromString('112444'),
                    'durationMinutes' => 310,
                    'unitScore' => 1284,
                    'wholeScore' => 1284,
                    'totalScore' => 1284,
                ]),
                // 訪問介護処遇改善加算Ⅰ
                $self->ltcsBillingServiceDetail([
                    'serviceCode' => ServiceCode::fromString('116275'),
                    'isAddition' => true,
                    'isLimited' => false,
                    'durationMinutes' => 0,
                    'unitScore' => 508,
                    'wholeScore' => 550,
                    'maxBenefitQuotaExcessScore' => 28,
                    'maxBenefitExcessScore' => 14,
                    'totalScore' => 508,
                ]),
            );
            $self->serviceCodeMap = Seq::from(...$self->examples->ltcsHomeVisitLongTermCareDictionaryEntries)
                ->toMap(fn (LtcsHomeVisitLongTermCareDictionaryEntry $x): string => $x->serviceCode->toString())
                ->mapValues(fn (LtcsHomeVisitLongTermCareDictionaryEntry $x): string => $x->name);
        });
        self::beforeEachSpec(function (self $self): void {
            $self->identifyLtcsAreaGradeFeeUseCase
                ->allows('handle')
                ->andReturn(Option::some($self->examples->ltcsAreaGradeFees[0]))
                ->byDefault();

            $self->identifyUserLtcsSubsidyUseCase
                ->allows('handle')
                ->andReturn(Seq::from(
                    Option::some($self->examples->userLtcsSubsidies[0]),
                    Option::none(),
                    Option::none(),
                ))
                ->byDefault();

            $self->interactor = app(BuildLtcsProvisionReportSheetAppendixInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('use IdentifyLtcsAreaGradeFeeUseCase', function (): void {
            $this->identifyLtcsAreaGradeFeeUseCase
                ->expects('handle')
                ->with(
                    $this->context,
                    $this->examples->offices[0]->ltcsHomeVisitLongTermCareService->ltcsAreaGradeId,
                    $this->examples->ltcsProvisionReports[0]->providedIn
                )
                ->andReturn(Option::some($this->examples->ltcsAreaGradeFees[0]));

            $this->interactor->handle(
                context: $this->context,
                report: $this->report,
                insCardAtFirstOfMonth: Option::from($this->insCard),
                insCardAtLastOfMonth: $this->insCard,
                office: $this->office,
                user: $this->user,
                serviceDetails: $this->serviceDetails,
                serviceCodeMap: $this->serviceCodeMap,
            );
        });
        $this->should('throw SetupException when IdentifyLtcsAreaGradeFeeUseCase return none', function (): void {
            $this->identifyLtcsAreaGradeFeeUseCase
                ->expects('handle')
                ->andReturn(Option::none());

            $this->assertThrows(SetupException::class, function (): void {
                $this->interactor->handle(
                    $this->context,
                    $this->report,
                    Option::from($this->insCard),
                    $this->insCard,
                    $this->office,
                    $this->user,
                    $this->serviceDetails,
                    $this->serviceCodeMap,
                );
            });
        });
        $this->should('use IdentifyUserLtcsSubsidyUseCase', function (): void {
            $this->identifyUserLtcsSubsidyUseCase
                ->expects('handle')
                ->with(
                    $this->context,
                    $this->user,
                    $this->report->providedIn
                )
                ->andReturn(Seq::from(
                    Option::some($this->examples->userLtcsSubsidies[0]),
                    Option::none(),
                    Option::none(),
                ));

            $this->interactor->handle(
                context: $this->context,
                report: $this->report,
                insCardAtFirstOfMonth: Option::from($this->insCard),
                insCardAtLastOfMonth: $this->insCard,
                office: $this->office,
                user: $this->user,
                serviceDetails: $this->serviceDetails,
                serviceCodeMap: $this->serviceCodeMap,
            );
        });
        $this->should('return params for ltcs provision report sheet appendix pdf', function (): void {
            $actual = $this->interactor->handle(
                context: $this->context,
                report: $this->report,
                insCardAtFirstOfMonth: Option::from($this->insCard),
                insCardAtLastOfMonth: $this->insCard,
                office: $this->office,
                user: $this->user,
                serviceDetails: $this->serviceDetails,
                serviceCodeMap: $this->serviceCodeMap,
            );

            $this->assertMatchesModelSnapshot($actual);
        });
    }

    /**
     * テスト用のサービス詳細を生成する.
     *
     * @param array $overwrites
     * @return \Domain\Billing\LtcsBillingServiceDetail
     */
    private function ltcsBillingServiceDetail(array $overwrites = []): LtcsBillingServiceDetail
    {
        $x = new LtcsBillingServiceDetail(
            userId: $this->user->id,
            disposition: LtcsBillingServiceDetailDisposition::result(),
            providedOn: Carbon::now()->startOfDay(),
            serviceCode: ServiceCode::fromString('111111'),
            serviceCodeCategory: LtcsServiceCodeCategory::housework(),
            buildingSubtraction: LtcsBuildingSubtraction::none(),
            noteRequirement: LtcsNoteRequirement::none(),
            isAddition: false,
            isLimited: true,
            durationMinutes: 30,
            unitScore: 250,
            count: 1,
            wholeScore: 0,
            maxBenefitQuotaExcessScore: 0,
            maxBenefitExcessScore: 0,
            totalScore: 250,
        );
        return $x->copy($overwrites);
    }
}

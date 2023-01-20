<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\Billing;

use Domain\Billing\CopayCoordinationResult;
use Domain\Billing\DwsBilling;
use Domain\Billing\DwsBillingBundle;
use Domain\Billing\DwsBillingCopayCoordination;
use Domain\Billing\DwsBillingCopayCoordinationExchangeAim;
use Domain\Billing\DwsBillingCopayCoordinationItem;
use Domain\Billing\DwsBillingCopayCoordinationPayment;
use Domain\Billing\DwsBillingInvoice;
use Domain\Billing\DwsBillingOffice;
use Domain\Billing\DwsBillingPaymentCategory;
use Domain\Billing\DwsBillingServiceDetail;
use Domain\Billing\DwsBillingServiceReport;
use Domain\Billing\DwsBillingServiceReportAggregate;
use Domain\Billing\DwsBillingServiceReportAggregateCategory;
use Domain\Billing\DwsBillingServiceReportAggregateGroup;
use Domain\Billing\DwsBillingServiceReportDuration;
use Domain\Billing\DwsBillingServiceReportFormat;
use Domain\Billing\DwsBillingServiceReportItem;
use Domain\Billing\DwsBillingServiceReportSituation;
use Domain\Billing\DwsBillingSource;
use Domain\Billing\DwsBillingStatement as Statement;
use Domain\Billing\DwsBillingStatementAggregate as StatementAggregate;
use Domain\Billing\DwsBillingStatementContract as StatementContract;
use Domain\Billing\DwsBillingStatementCopayCoordinationStatus;
use Domain\Billing\DwsBillingStatementElement as StatementElement;
use Domain\Billing\DwsBillingStatementItem as StatementItem;
use Domain\Billing\DwsBillingStatus;
use Domain\Billing\DwsBillingUser;
use Domain\Billing\DwsGrantedServiceCode;
use Domain\Billing\DwsHomeHelpServiceChunk;
use Domain\Billing\DwsHomeHelpServiceChunkImpl;
use Domain\Billing\DwsHomeHelpServiceFragment;
use Domain\Billing\DwsServiceDivisionCode;
use Domain\Billing\DwsVisitingCareForPwsdChunk;
use Domain\Billing\DwsVisitingCareForPwsdChunkImpl;
use Domain\Billing\DwsVisitingCareForPwsdFragment;
use Domain\Billing\LtcsExpiredReason;
use Domain\Common\Addr;
use Domain\Common\Carbon;
use Domain\Common\CarbonRange;
use Domain\Common\Decimal;
use Domain\Common\Location;
use Domain\Common\Prefecture;
use Domain\Common\Rounding;
use Domain\Common\Schedule;
use Domain\Common\ServiceSegment;
use Domain\Common\Sex;
use Domain\Common\StructuredName;
use Domain\Contract\Contract;
use Domain\Contract\ContractPeriod;
use Domain\Contract\ContractStatus;
use Domain\DwsCertification\Child;
use Domain\DwsCertification\CopayCoordination;
use Domain\DwsCertification\CopayCoordinationType;
use Domain\DwsCertification\DwsCertification;
use Domain\DwsCertification\DwsCertificationAgreement;
use Domain\DwsCertification\DwsCertificationAgreementType;
use Domain\DwsCertification\DwsCertificationGrant;
use Domain\DwsCertification\DwsCertificationServiceType;
use Domain\DwsCertification\DwsCertificationStatus;
use Domain\DwsCertification\DwsLevel;
use Domain\DwsCertification\DwsType;
use Domain\Office\DwsSpecifiedTreatmentImprovementAddition;
use Domain\Office\DwsTreatmentImprovementAddition;
use Domain\Office\HomeHelpServiceCalcSpec;
use Domain\Office\HomeHelpServiceSpecifiedOfficeAddition;
use Domain\Office\Office;
use Domain\Office\OfficeDwsGenericService;
use Domain\Office\OfficeLtcsCareManagementService;
use Domain\Office\OfficeQualification;
use Domain\Office\OfficeStatus;
use Domain\Office\Purpose;
use Domain\Office\VisitingCareForPwsdCalcSpec;
use Domain\Office\VisitingCareForPwsdSpecifiedOfficeAddition;
use Domain\Project\DwsProjectServiceCategory;
use Domain\ProvisionReport\DwsProvisionReport;
use Domain\ProvisionReport\DwsProvisionReportItem;
use Domain\ProvisionReport\DwsProvisionReportStatus;
use Domain\ServiceCode\ServiceCode;
use Domain\ServiceCodeDictionary\DwsHomeHelpServiceBuildingType;
use Domain\ServiceCodeDictionary\DwsHomeHelpServiceDictionary;
use Domain\ServiceCodeDictionary\DwsHomeHelpServiceProviderType;
use Domain\ServiceCodeDictionary\DwsServiceCodeCategory;
use Domain\ServiceCodeDictionary\DwsVisitingCareForPwsdDictionary;
use Domain\User\User;
use Domain\User\UserDwsSubsidy;
use Domain\User\UserDwsSubsidyFactor;
use Domain\User\UserDwsSubsidyType;
use ScalikePHP\Option;
use ScalikePHP\Seq;

/**
 * 障害福祉サービス：請求関連のテスト用のデータ生成関連処理.
 */
trait DwsBillingTestSupport
{
    protected Carbon $providedIn;

    protected DwsHomeHelpServiceDictionary $homeHelpServiceDictionary;
    protected DwsVisitingCareForPwsdDictionary $visitingCareForPwsdDictionary;

    protected Office $office;
    protected HomeHelpServiceCalcSpec $homeHelpServiceCalcSpec;
    protected VisitingCareForPwsdCalcSpec $visitingCareForPwsdCalcSpec;

    /** @var \Domain\User\User[]&\ScalikePHP\Seq */
    protected Seq $users;
    protected User $user;
    protected Contract $contract;
    protected UserDwsSubsidy $userDwsSubsidy;

    /** @var \Domain\DwsCertification\DwsCertification[]&\ScalikePHP\Seq */
    protected Seq $dwsCertifications;
    protected DwsCertification $dwsCertification;

    /** @var \Domain\Billing\DwsBillingCopayCoordination[]&\ScalikePHP\Seq */
    protected Seq $dwsCopayCoordinations;
    protected DwsBillingCopayCoordination $dwsCopayCoordination;

    /** @var \Domain\ProvisionReport\DwsProvisionReport[]&\ScalikePHP\Seq */
    protected Seq $reports;
    protected DwsProvisionReport $report;
    protected Seq $previousReports;

    /** @var \Domain\Billing\DwsBillingSource[]&\ScalikePHP\Seq */
    protected Seq $sources;

    protected DwsBilling $billing;
    protected DwsBillingBundle $bundle;
    protected DwsBillingInvoice $invoice;

    /** @var \Domain\Billing\DwsBillingStatement[]&\ScalikePHP\Seq */
    protected Seq $statements;
    protected Statement $statement;

    /** @var \Domain\Billing\DwsBillingStatementElement[]&\ScalikePHP\Seq */
    protected Seq $statementElements;

    /** @var \Domain\Billing\DwsBillingStatementAggregate[]&\ScalikePHP\Seq */
    protected Seq $statementAggregates;

    /** @var \Domain\Billing\DwsBillingStatementContract[]&\ScalikePHP\Seq */
    protected Seq $statementContracts;

    /** @var \Domain\Billing\DwsBillingServiceReport[]&\ScalikePHP\Seq */
    protected Seq $serviceReports;
    protected DwsBillingServiceReport $serviceReport;

    /** @var \Domain\Billing\DwsBillingServiceReportItem[]&\ScalikePHP\Seq */
    protected Seq $serviceReportItems;

    /** @var \Domain\Billing\DwsHomeHelpServiceChunk[]&\ScalikePHP\Seq */
    protected Seq $dwsHomeHelpServiceChunks;

    /** @var \Domain\Billing\DwsVisitingCareForPwsdChunk[]&\ScalikePHP\Seq */
    protected Seq $dwsVisitingCareForPwsdChunks;

    /**
     * テスト用のデータを生成してプロパティに設定する.
     *
     * @return void
     */
    protected function setupTestData(): void
    {
        $this->providedIn = Carbon::create(2021, 1);

        $this->homeHelpServiceDictionary = $this->homeHelpServiceDictionary();
        $this->visitingCareForPwsdDictionary = $this->visitingCareForPwsdDictionary();

        $this->office = $this->office(604, '1370406140');
        $this->homeHelpServiceCalcSpec = $this->homeHelpServiceCalcSpec($this->office);
        $this->visitingCareForPwsdCalcSpec = $this->visitingCareForPwsdCalcSpec($this->office);

        $this->users = $this->users();
        $this->user = $this->users[0];
        $this->contract = $this->contract();
        $this->userDwsSubsidy = $this->userDwsSubsidy();

        $this->dwsCertifications = $this->dwsCertifications();
        $this->dwsCertification = $this->dwsCertifications[0];

        $this->reports = $this->reports();
        $this->report = $this->reports[0];
        $this->previousReports = Seq::from(...$this->reports())
            ->map(fn (DwsProvisionReport $x): DwsProvisionReport => $x->copy([
                'providedIn' => $x->providedIn->subMonth(),
            ]));

        $this->sources = Seq::from(
            DwsBillingSource::create([
                'certification' => $this->dwsCertifications[0],
                'provisionReport' => $this->reports[0],
                'previousProvisionReport' => Option::some($this->reports[0]->copy([
                    'providedIn' => $this->reports[0]->providedIn->subMonth(),
                ])),
            ]),
            DwsBillingSource::create([
                'certification' => $this->dwsCertifications[1],
                'provisionReport' => $this->reports[1],
                'previousProvisionReport' => Option::some($this->reports[1]->copy([
                    'providedIn' => $this->reports[1]->providedIn->subMonth(),
                ])),
            ]),
            DwsBillingSource::create([
                'certification' => $this->dwsCertifications[2],
                'provisionReport' => $this->reports[2],
                'previousProvisionReport' => Option::some($this->reports[2]->copy([
                    'providedIn' => $this->reports[2]->providedIn->subMonth(),
                ])),
            ]),
        );

        $this->billing = $this->billing();
        $this->bundle = $this->bundle();
        $this->invoice = $this->invoice();

        $this->statementElements = $this->statementElements();
        $this->statementAggregates = $this->statementAggregates(Decimal::fromInt(11_2000));
        $this->statementContracts = Seq::from(
            StatementContract::create([
                'dwsGrantedServiceCode' => DwsGrantedServiceCode::visitingCareForPwsd1(),
                'grantedAmount' => 12345,
                'agreedOn' => Carbon::create(2021, 1, 1),
                'expiredOn' => Carbon::create(2021, 3, 31),
                'indexNumber' => 1,
            ])
        );

        $this->statements = Seq::from(
            $this->statement([
                'user' => DwsBillingUser::from($this->users[0], $this->dwsCertifications[0]),
            ]),
            $this->statement([
                'user' => DwsBillingUser::from($this->users[1], $this->dwsCertifications[1]),
            ]),
        );
        $this->statement = $this->statements[0];

        $this->serviceReportItems = $this->serviceReportItems();

        $this->serviceReports = $this->serviceReports();
        $this->serviceReport = $this->serviceReports[0];

        $this->dwsCopayCoordinations = $this->dwsCopayCoordinations();
        $this->dwsCopayCoordination = $this->dwsCopayCoordinations[0];

        $this->dwsHomeHelpServiceChunks = Seq::from(
            $this->dwsHomeHelpServiceChunk([
                'id' => 1,
                'user_id' => $this->users[0]->id,
            ]),
            $this->dwsHomeHelpServiceChunk([
                'id' => 2,
                'user_id' => $this->users[0]->id,
                'isFirst' => true,
                'range' => CarbonRange::create([
                    'start' => Carbon::create(2021, 1, 3, 12, 0),
                    'end' => Carbon::create(2021, 1, 3, 13, 0),
                ]),
                'category' => DwsServiceCodeCategory::physicalCare(),
                'fragments' => Seq::from(
                    DwsHomeHelpServiceFragment::create([
                        'providerType' => DwsHomeHelpServiceProviderType::none(),
                        'isSecondary' => false,
                        'range' => CarbonRange::create([
                            'start' => Carbon::create(2021, 1, 3, 12, 0),
                            'end' => Carbon::create(2021, 1, 3, 13, 0),
                        ]),
                        'headcount' => 1,
                    ])
                ),
            ]),
            $this->dwsHomeHelpServiceChunk([
                'id' => 3,
                'user_id' => $this->users[0]->id,
                'isEmergency' => true,
                'isWelfareSpecialistCooperation' => true,
                'range' => CarbonRange::create([
                    'start' => Carbon::create(2021, 1, 4, 12, 0),
                    'end' => Carbon::create(2021, 1, 4, 15, 0),
                ]),
                'fragments' => Seq::from(
                    DwsHomeHelpServiceFragment::create([
                        'providerType' => DwsHomeHelpServiceProviderType::none(),
                        'isSecondary' => false,
                        'range' => CarbonRange::create([
                            'start' => Carbon::create(2021, 1, 4, 12, 0),
                            'end' => Carbon::create(2021, 1, 4, 13, 0),
                        ]),
                        'headcount' => 1,
                    ]),
                    DwsHomeHelpServiceFragment::create([
                        'providerType' => DwsHomeHelpServiceProviderType::none(),
                        'isSecondary' => false,
                        'range' => CarbonRange::create([
                            'start' => Carbon::create(2021, 1, 4, 14, 0),
                            'end' => Carbon::create(2021, 1, 4, 15, 0),
                        ]),
                        'headcount' => 1,
                    ]),
                ),
            ])
        );

        $this->dwsVisitingCareForPwsdChunks = Seq::from(
            $this->dwsVisitingCareForPwsdChunk([
                'id' => 1,
                'user_id' => $this->users[0]->id,
            ]),
            $this->dwsVisitingCareForPwsdChunk([
                'id' => 2,
                'user_id' => $this->users[0]->id,
                'category' => DwsServiceCodeCategory::visitingCareForPwsd2(),
                'providedOn' => Carbon::create(2021, 1, 3),
                'isEmergency' => false,
                'isFirst' => true,
                'isBehavioralDisorderSupportCooperation' => false,
                'range' => CarbonRange::create([
                    'start' => Carbon::create(2021, 1, 3, 12, 0),
                    'end' => Carbon::create(2021, 1, 3, 20, 0),
                ]),
                'fragments' => Seq::from(
                    DwsVisitingCareForPwsdFragment::create([
                        'isCoaching' => false,
                        'isMoving' => false,
                        'isSecondary' => false,
                        'movingDurationMinutes' => 0,
                        'range' => CarbonRange::create([
                            'start' => Carbon::create(2021, 1, 3, 12, 0),
                            'end' => Carbon::create(2021, 1, 3, 20, 0),
                        ]),
                        'headcount' => 1,
                    ]),
                ),
            ]),
            $this->dwsVisitingCareForPwsdChunk([
                'id' => 3,
                'user_id' => $this->users[0]->id,
                'category' => DwsServiceCodeCategory::visitingCareForPwsd3(),
                'providedOn' => Carbon::create(2021, 1, 4),
                'range' => CarbonRange::create([
                    'start' => Carbon::create(2021, 1, 4, 12, 0),
                    'end' => Carbon::create(2021, 1, 4, 20, 0),
                ]),
                'fragments' => Seq::from(
                    DwsVisitingCareForPwsdFragment::create([
                        'isCoaching' => false,
                        'isMoving' => false,
                        'isSecondary' => false,
                        'movingDurationMinutes' => 0,
                        'range' => CarbonRange::create([
                            'start' => Carbon::create(2021, 1, 2, 12, 0),
                            'end' => Carbon::create(2021, 1, 2, 20, 0),
                        ]),
                        'headcount' => 2,
                    ]),
                    DwsVisitingCareForPwsdFragment::create([
                        'isCoaching' => false,
                        'isMoving' => false,
                        'isSecondary' => false,
                        'movingDurationMinutes' => 0,
                        'range' => CarbonRange::create([
                            'start' => Carbon::create(2021, 1, 2, 12, 0),
                            'end' => Carbon::create(2021, 1, 2, 20, 0),
                        ]),
                        'headcount' => 2,
                    ]),
                ),
            ]),
            $this->dwsVisitingCareForPwsdChunk([
                'id' => 4,
                'user_id' => $this->users[0]->id,
                'category' => DwsServiceCodeCategory::visitingCareForPwsd3(),
                'providedOn' => Carbon::create(2021, 1, 5),
                'range' => CarbonRange::create([
                    'start' => Carbon::create(2021, 1, 5, 12, 0),
                    'end' => Carbon::create(2021, 1, 5, 20, 0),
                ]),
                'fragments' => Seq::from(
                    DwsVisitingCareForPwsdFragment::create([
                        'isCoaching' => false,
                        'isMoving' => false,
                        'isSecondary' => false,
                        'movingDurationMinutes' => 0,
                        'range' => CarbonRange::create([
                            'start' => Carbon::create(2021, 1, 2, 12, 0),
                            'end' => Carbon::create(2021, 1, 2, 20, 0),
                        ]),
                        'headcount' => 1,
                    ]),
                    DwsVisitingCareForPwsdFragment::create([
                        'isCoaching' => false,
                        'isMoving' => true,
                        'isSecondary' => false,
                        'movingDurationMinutes' => 60,
                        'range' => CarbonRange::create([
                            'start' => Carbon::create(2021, 1, 2, 12, 0),
                            'end' => Carbon::create(2021, 1, 2, 20, 0),
                        ]),
                        'headcount' => 1,
                    ]),
                ),
            ]),
        );
    }

    /**
     * テスト用の事業所を生成する.
     *
     * @param int $id
     * @param string $code
     * @return \Domain\Office\Office
     */
    protected function office(int $id, string $code): Office
    {
        return Office::create([
            'id' => $id,
            'organizationId' => 1,
            'name' => '土屋ケア',
            'abbr' => '土屋ケア',
            'phoneticName' => 'ツチヤケア',
            'purpose' => Purpose::external(),
            'addr' => new Addr(
                postcode: '151-0051',
                prefecture: Prefecture::tokyo(),
                city: '渋谷区',
                street: '千駄ヶ谷2-2-6',
                apartment: '株式会社テイクワン・オフィス',
            ),
            'location' => Location::create(['lat' => 0, 'lng' => 0]),
            'tel' => '03-5474-8581',
            'fax' => '03-5474-8583',
            'email' => 'foo@example.com',
            'officeGroupId' => null,
            'qualifications' => [OfficeQualification::ltcsCareManagement()],
            'dwsGenericService' => OfficeDwsGenericService::create([
                'code' => $code,
                'openedOn' => Carbon::create(2008, 5, 17),
                'designationExpiredOn' => Carbon::create(2022, 12, 31),
                'dwsAreaGradeId' => 1,
            ]),
            'dwsCommAccompanyService' => null,
            'ltcsCareManagementService' => OfficeLtcsCareManagementService::create([
                'code' => $code,
                'openedOn' => Carbon::create(2008, 5, 17),
                'designationExpiredOn' => Carbon::create(2022, 12, 31),
                'ltcsAreaGradeId' => 1,
            ]),
            'ltcsHomeVisitLongTermCareService' => null,
            'ltcsCompHomeVisitingService' => null,
            'status' => OfficeStatus::inOperation(),
            'isEnabled' => true,
            'version' => 1,
            'createdAt' => Carbon::create(2020, 1, 1),
            'updatedAt' => Carbon::create(2020, 1, 1),
        ]);
    }

    /**
     * テスト用の障害福祉サービス：重度訪問介護：算定情報を生成する.
     *
     * @param \Domain\Office\Office $office
     * @param array $attrs
     * @return \Domain\Office\HomeHelpServiceCalcSpec
     */
    protected function homeHelpServiceCalcSpec(Office $office, array $attrs = []): HomeHelpServiceCalcSpec
    {
        $values = [
            'officeId' => $office->id,
            'period' => CarbonRange::create([
                'start' => Carbon::now(),
                'end' => Carbon::now()->addYear(),
            ]),
            'specifiedOfficeAddition' => HomeHelpServiceSpecifiedOfficeAddition::addition1(),
            'treatmentImprovementAddition' => DwsTreatmentImprovementAddition::addition1(),
            'specifiedTreatmentImprovementAddition' => DwsSpecifiedTreatmentImprovementAddition::addition1(),
            'isEnabled' => true,
            'version' => 1,
            'createdAt' => Carbon::now(),
            'updatedAt' => Carbon::now(),
        ];
        return HomeHelpServiceCalcSpec::create($attrs + $values);
    }

    /**
     * テスト用の障害福祉サービス：重度訪問介護：算定情報を生成する.
     *
     * @param \Domain\Office\Office $office
     * @param array $attrs
     * @return \Domain\Office\VisitingCareForPwsdCalcSpec
     */
    protected function visitingCareForPwsdCalcSpec(Office $office, array $attrs = []): VisitingCareForPwsdCalcSpec
    {
        $values = [
            'officeId' => $office->id,
            'period' => CarbonRange::create([
                'start' => Carbon::now(),
                'end' => Carbon::now()->addYear(),
            ]),
            'specifiedOfficeAddition' => VisitingCareForPwsdSpecifiedOfficeAddition::addition1(),
            'treatmentImprovementAddition' => DwsTreatmentImprovementAddition::addition1(),
            'specifiedTreatmentImprovementAddition' => DwsSpecifiedTreatmentImprovementAddition::addition1(),
            'isEnabled' => true,
            'version' => 1,
            'createdAt' => Carbon::now(),
            'updatedAt' => Carbon::now(),
        ];
        return VisitingCareForPwsdCalcSpec::create($attrs + $values);
    }

    /**
     * テスト用の障害福祉サービス：居宅介護：サービスコード辞書を生成する.
     *
     * @return \Domain\ServiceCodeDictionary\DwsHomeHelpServiceDictionary
     */
    protected function homeHelpServiceDictionary(): DwsHomeHelpServiceDictionary
    {
        return DwsHomeHelpServiceDictionary::create([
            'id' => 1,
            'effectivatedOn' => Carbon::create(2020, 1, 1),
            'name' => 'テスト用サービスコード辞書',
            'version' => 1,
            'createdAt' => Carbon::now(),
            'updatedAt' => Carbon::now(),
        ]);
    }

    /**
     * テスト用の障害福祉サービス：重度訪問介護：サービスコード辞書を生成する.
     *
     * @return \Domain\ServiceCodeDictionary\DwsVisitingCareForPwsdDictionary
     */
    protected function visitingCareForPwsdDictionary(): DwsVisitingCareForPwsdDictionary
    {
        return DwsVisitingCareForPwsdDictionary::create([
            'id' => 1,
            'effectivatedOn' => Carbon::create(2020, 1, 1),
            'name' => 'テスト用サービスコード辞書',
            'version' => 1,
            'createdAt' => Carbon::now(),
            'updatedAt' => Carbon::now(),
        ]);
    }

    /**
     * テスト用の利用者一覧を生成する.
     *
     * @return \Domain\User\User[]&\ScalikePHP\Seq
     */
    protected function users(): Seq
    {
        return Seq::from(
            $this->user(['id' => 1]),
            $this->user(['id' => 2]),
            $this->user(['id' => 3]),
        );
    }

    /**
     * テスト用の利用者を生成する.
     *
     * @param array $attrs
     * @return \Domain\User\User
     */
    protected function user(array $attrs = []): User
    {
        $values = [
            'id' => 1,
            'organizationId' => 1,
            'name' => new StructuredName(
                familyName: '高田',
                givenName: '純次',
                phoneticFamilyName: 'タカダ',
                phoneticGivenName: 'ジュンジ',
            ),
            'sex' => Sex::male(),
            'birthday' => Carbon::create(1947, 1, 21),
            'addr' => new Addr(
                postcode: '151-0051',
                prefecture: Prefecture::tokyo(),
                city: '渋谷区',
                street: '千駄ヶ谷2-2-6',
                apartment: '株式会社テイクワン・オフィス',
            ),
            'location' => Location::create(['lat' => 0, 'lng' => 0]),
            'tel' => '03-5474-8581',
            'fax' => '03-5474-8583',
            'bankAccountId' => 1,
            'isEnabled' => true,
            'version' => 1,
            'createdAt' => Carbon::now(),
            'updatedAt' => Carbon::now(),
        ];
        return User::create($attrs + $values);
    }

    /**
     * テスト用の契約を生成する.
     *
     * @param array $attrs
     * @return \Domain\Contract\Contract
     */
    protected function contract(array $attrs = []): Contract
    {
        $values = [
            'id' => 1,
            'organizationId' => 1,
            'userId' => $this->user->id,
            'officeId' => $this->office->id,
            'serviceSegment' => ServiceSegment::disabilitiesWelfare(),
            'status' => ContractStatus::formal(),
            'contractedOn' => Carbon::create(2020, 1, 1),
            'terminatedOn' => null,
            'dwsPeriods' => [
                DwsServiceDivisionCode::homeHelpService()->value() => ContractPeriod::create([
                    'start' => Carbon::create(2020, 1, 1),
                    'end' => Carbon::create(2020, 12, 31),
                ]),
                DwsServiceDivisionCode::visitingCareForPwsd()->value() => ContractPeriod::create([
                    'start' => Carbon::create(2021, 1, 1),
                    'end' => Carbon::create(2021, 12, 31),
                ]),
            ],
            'ltcsPeriod' => ContractPeriod::create([
                'start' => Carbon::create(2019, 1, 1),
                'end' => Carbon::create(2019, 12, 31),
            ]),
            'expiredReason' => LtcsExpiredReason::hospitalized(),
            'note' => 'だるまさんがころんだ',
            'isEnabled' => true,
            'version' => 1,
            'createdAt' => Carbon::create(2020, 1, 2),
            'updatedAt' => Carbon::create(2020, 1, 3),
        ];
        return Contract::create($values + $attrs);
    }

    /**
     * テスト用の利用者：自治体助成情報を生成する.
     *
     * @param array $attrs
     * @return \Domain\User\UserDwsSubsidy
     */
    protected function userDwsSubsidy(array $attrs = []): UserDwsSubsidy
    {
        $values = [
            'id' => 1,
            'userId' => $this->user->id,
            'period' => CarbonRange::create([
                'start' => Carbon::create(2020, 1, 1),
                'end' => Carbon::create(2025, 12, 31),
            ]),
            'cityName' => '荒川区',
            'cityCode' => '131181',
            'subsidyType' => UserDwsSubsidyType::benefitRate(),
            'factor' => UserDwsSubsidyFactor::copay(),
            'benefitRate' => 97,
            'rounding' => Rounding::ceil(),
            'benefitAmount' => 0,
            'copay' => 0,
            'note' => '',
            'isEnabled' => true,
            'version' => 1,
            'createdAt' => Carbon::now(),
            'updatedAt' => Carbon::now(),
        ];
        return UserDwsSubsidy::create($attrs + $values);
    }

    /**
     * テスト用の障害福祉サービス受給者証の一覧を生成する.
     *
     * @return \Domain\DwsCertification\DwsCertification[]&\ScalikePHP\Seq
     */
    protected function dwsCertifications(): Seq
    {
        return Seq::from(
            $this->dwsCertification($this->office, $this->users[0], [
                'id' => 1,
                'cityCode' => '141421',
                'cityName' => '米花市',
            ]),
            $this->dwsCertification($this->office, $this->users[1], [
                'id' => 2,
                'cityCode' => '141421',
                'cityName' => '米花市',
            ]),
            $this->dwsCertification($this->office, $this->users[2], [
                'id' => 3,
                'cityCode' => '173205',
                'cityName' => '古糸市',
            ]),
        );
    }

    /**
     * テスト用の障害福祉サービス受給者証を生成する.
     *
     * @param \Domain\Office\Office $office
     * @param \Domain\User\User $user
     * @param array $attrs
     * @return \Domain\DwsCertification\DwsCertification
     */
    protected function dwsCertification(Office $office, User $user, array $attrs = []): DwsCertification
    {
        $values = [
            'id' => 1,
            'userId' => $user->id,
            'effectivatedOn' => Carbon::create(2022, 2, 1),
            'status' => DwsCertificationStatus::approved(),
            'dwsNumber' => '1234567890',
            'dwsTypes' => [DwsType::physical()],
            'issuedOn' => Carbon::create(2022, 1, 1),
            'cityName' => '杜王町',
            'cityCode' => '123456',
            'dwsLevel' => DwsLevel::level5(),
            'isSubjectOfComprehensiveSupport' => false,
            'activatedOn' => Carbon::create(2022, 1, 1),
            'deactivatedOn' => Carbon::create(2022, 12, 31),
            'grants' => [
                $this->dwsCertificationGrant([
                    'dwsCertificationServiceType' => DwsCertificationServiceType::physicalCare(),
                    'grantedAmount' => '支給量',
                    'activatedOn' => Carbon::create(2022, 1, 1),
                    'deactivatedOn' => Carbon::create(2022, 12, 31),
                ]),
                $this->dwsCertificationGrant([
                    'dwsCertificationServiceType' => DwsCertificationServiceType::housework(),
                    'grantedAmount' => '支給量',
                    'activatedOn' => Carbon::create(2022, 1, 1),
                    'deactivatedOn' => Carbon::create(2022, 12, 31),
                ]),
                $this->dwsCertificationGrant([
                    'dwsCertificationServiceType' => DwsCertificationServiceType::accompanyWithPhysicalCare(),
                    'grantedAmount' => '支給量',
                    'activatedOn' => Carbon::create(2022, 1, 1),
                    'deactivatedOn' => Carbon::create(2022, 12, 31),
                ]),
                $this->dwsCertificationGrant([
                    'dwsCertificationServiceType' => DwsCertificationServiceType::accompany(),
                    'grantedAmount' => '支給量',
                    'activatedOn' => Carbon::create(2022, 1, 1),
                    'deactivatedOn' => Carbon::create(2022, 12, 31),
                ]),
                $this->dwsCertificationGrant([
                    'dwsCertificationServiceType' => DwsCertificationServiceType::visitingCareForPwsd2(),
                    'grantedAmount' => '支給量',
                    'activatedOn' => Carbon::create(2022, 1, 1),
                    'deactivatedOn' => Carbon::create(2022, 12, 31),
                ]),
            ],
            'child' => Child::create([
                'name' => StructuredName::empty(),
                'birthday' => null,
            ]),
            'copayRate' => 10,
            'copayLimit' => 37200,
            'copayActivatedOn' => Carbon::create(2022, 1, 1),
            'copayDeactivatedOn' => Carbon::create(2022, 12, 31),
            'copayCoordination' => CopayCoordination::create([
                'copayCoordinationType' => CopayCoordinationType::none(),
                'officeId' => null,
            ]),
            'agreements' => [
                $this->dwsCertificationAgreement(1, [
                    'officeId' => $office->id,
                    'dwsCertificationAgreementType' => DwsCertificationAgreementType::visitingCareForPwsd1(),
                    'paymentAmount' => 44640,
                    'agreedOn' => Carbon::create(2022, 1, 31),
                    'expiredOn' => Carbon::create(2022, 12, 31),
                ]),
            ],
            'isEnabled' => true,
            'version' => 1,
            'createdAt' => Carbon::now(),
            'updatedAt' => Carbon::now(),
        ];
        return DwsCertification::create($attrs + $values);
    }

    /**
     * テスト用の障害福祉サービス受給者証：訪問系サービス事業者記入欄を生成する.
     *
     * @param int $indexNumber
     * @param array $attrs
     * @return \Domain\DwsCertification\DwsCertificationAgreement
     */
    protected function dwsCertificationAgreement(int $indexNumber, array $attrs = []): DwsCertificationAgreement
    {
        $values = [
            'officeId' => 1,
            'dwsCertificationAgreementType' => DwsCertificationAgreementType::physicalCare(),
            'paymentAmount' => 10 * 24 * 60,
            'agreedOn' => Carbon::create(2021, 1, 1),
            'expiredOn' => null,
        ];
        return DwsCertificationAgreement::create(compact('indexNumber') + $attrs + $values);
    }

    /**
     * テスト用の障害福祉サービス受給者証：支給量等を生成する.
     *
     * @param array $attrs
     * @return \Domain\DwsCertification\DwsCertificationGrant
     */
    protected function dwsCertificationGrant(array $attrs = []): DwsCertificationGrant
    {
        $values = [
            'dwsCertificationServiceType' => DwsCertificationServiceType::physicalCare(),
            'grantedAmount' => '支給量',
            'activatedOn' => Carbon::create(2022, 1, 1),
            'deactivatedOn' => Carbon::create(2022, 12, 31),
        ];
        return DwsCertificationGrant::create($attrs + $values);
    }

    /**
     * テスト用の利用者負担上限額管理結果票の一覧を生成する.
     *
     * @return \Domain\Billing\DwsBillingCopayCoordination[]&\ScalikePHP\Seq
     */
    protected function dwsCopayCoordinations(): Seq
    {
        return Seq::from(
            $this->dwsCopayCoordination(['id' => 1]),
            $this->dwsCopayCoordination([
                'id' => 2,
                'items' => [
                    DwsBillingCopayCoordinationItem::create([
                        'itemNumber' => 1,
                        'office' => DwsBillingOffice::from($this->office),
                        'subtotal' => DwsBillingCopayCoordinationPayment::create([
                            'fee' => 100000,
                            'copay' => 9300,
                            'coordinatedCopay' => 9300,
                        ]),
                    ]),
                ],
            ]),
            $this->dwsCopayCoordination([
                'id' => 3,
                'status' => DwsBillingStatus::ready(),
            ]),
        );
    }

    /**
     * テスト用の利用者負担上限額管理結果票を生成する.
     *
     * @param array $attrs
     * @return \Domain\Billing\DwsBillingCopayCoordination
     */
    protected function dwsCopayCoordination(array $attrs = []): DwsBillingCopayCoordination
    {
        $otherOffice = $this->office(605, '2370406140');
        $values = [
            'dwsBillingId' => $this->billing->id,
            'dwsBillingBundleId' => $this->bundle->id,
            'office' => DwsBillingOffice::from($this->office),
            'user' => DwsBillingUser::from($this->user, $this->dwsCertification),
            'result' => CopayCoordinationResult::appropriated(),
            'exchangeAim' => DwsBillingCopayCoordinationExchangeAim::declaration(),
            'items' => [
                DwsBillingCopayCoordinationItem::create([
                    'itemNumber' => 1,
                    'office' => DwsBillingOffice::from($this->office),
                    'subtotal' => DwsBillingCopayCoordinationPayment::create([
                        'fee' => 100000,
                        'copay' => 9300,
                        'coordinatedCopay' => 9300,
                    ]),
                ]),
                DwsBillingCopayCoordinationItem::create([
                    'itemNumber' => 1,
                    'office' => DwsBillingOffice::from($otherOffice),
                    'subtotal' => DwsBillingCopayCoordinationPayment::create([
                        'fee' => 100000,
                        'copay' => 9300,
                        'coordinatedCopay' => 0,
                    ]),
                ]),
            ],
            'total' => DwsBillingCopayCoordinationPayment::create([
                'fee' => 200000,
                'copay' => 9300,
                'coordinatedCopay' => 9300,
            ]),
            'status' => DwsBillingStatus::fixed(),
            'createdAt' => Carbon::now(),
            'updatedAt' => Carbon::now(),
        ];
        return DwsBillingCopayCoordination::create($attrs + $values);
    }

    /**
     * テスト用の障害福祉サービス：予実の一覧を生成する.
     *
     * @return \Domain\ProvisionReport\DwsProvisionReport[]&\ScalikePHP\Seq
     */
    protected function reports(): Seq
    {
        return Seq::from(
            $this->report([ // 1つ目の市町村：重度訪問介護の例
                'id' => 1,
                'userId' => $this->users[0]->id,
            ]),
            $this->report([ // 1つ目の市町村：居宅介護の例
                'id' => 2,
                'userId' => $this->users[1]->id,
            ]),
            $this->report([ // 2つ目の市町村：重度訪問介護の例
                'id' => 3,
                'userId' => $this->users[2]->id,
            ]),
        );
    }

    /**
     * テスト用の障害福祉サービス：予実の一覧を生成する.
     *
     * @param array $attrs
     * @return \Domain\ProvisionReport\DwsProvisionReport
     */
    protected function report(array $attrs = []): DwsProvisionReport
    {
        $values = [
            'id' => 1,
            'userId' => $this->user->id,
            'officeId' => $this->office->id,
            'contractId' => 1,
            'providedIn' => $this->providedIn,
            'plans' => [
                DwsProvisionReportItem::create([
                    'schedule' => Schedule::create([
                        'date' => Carbon::create(2021, 1, 23),
                        'start' => Carbon::create(2021, 1, 23, 11, 10, 0),
                        'end' => Carbon::create(2021, 1, 23, 12, 10, 0),
                    ]),
                    'category' => DwsProjectServiceCategory::physicalCare(),
                    'headcount' => 1,
                    'options' => [],
                    'note' => '',
                ]),
            ],
            'results' => [
                DwsProvisionReportItem::create([
                    'schedule' => Schedule::create([
                        'date' => Carbon::create(2021, 1, 23),
                        'start' => Carbon::create(2021, 1, 23, 11, 10, 0),
                        'end' => Carbon::create(2021, 1, 23, 12, 10, 0),
                    ]),
                    'category' => DwsProjectServiceCategory::physicalCare(),
                    'headcount' => 1,
                    'options' => [],
                    'note' => '',
                ]),
            ],
            'status' => DwsProvisionReportStatus::fixed(),
            'fixedAt' => Carbon::create(2021, 2, 15),
            'createdAt' => Carbon::now(),
            'updatedAt' => Carbon::now(),
        ];
        return DwsProvisionReport::create($attrs + $values);
    }

    /**
     * テスト用の障害福祉サービス：請求を生成する.
     *
     * @param array $attrs
     * @return \Domain\Billing\DwsBilling
     */
    protected function billing(array $attrs = []): DwsBilling
    {
        $values = [
            'id' => 1,
            'organizationId' => 1,
            'office' => DwsBillingOffice::from($this->office),
            'files' => [],
            'status' => DwsBillingStatus::checking(),
            'transactedIn' => $this->providedIn->addMonth()->startOfMonth(),
            'fixedAt' => null,
            'createdAt' => Carbon::now(),
            'updatedAt' => Carbon::now(),
        ];
        return DwsBilling::create($attrs + $values);
    }

    /**
     * テスト用の障害福祉サービス：請求：サービス詳細を生成する.
     *
     * @param int $userId
     * @param \Domain\Common\Carbon $providedOn
     * @param string $serviceCodeString
     * @param \Domain\ServiceCodeDictionary\DwsServiceCodeCategory $serviceCodeCategory
     * @param int $unitScore
     * @param bool $isAddition
     * @param int $count
     * @return \Domain\Billing\DwsBillingServiceDetail
     */
    protected function serviceDetail(
        int $userId,
        Carbon $providedOn,
        string $serviceCodeString,
        DwsServiceCodeCategory $serviceCodeCategory,
        int $unitScore,
        bool $isAddition = false,
        int $count = 1
    ): DwsBillingServiceDetail {
        return DwsBillingServiceDetail::create([
            'userId' => $userId,
            'providedOn' => $providedOn,
            'serviceCode' => ServiceCode::fromString($serviceCodeString),
            'serviceCodeCategory' => $serviceCodeCategory,
            'unitScore' => $unitScore,
            'isAddition' => $isAddition,
            'count' => $count,
            'totalScore' => $unitScore * $count,
        ]);
    }

    /**
     * テスト用の障害福祉サービス：請求：サービス詳細の一覧を生成する.
     *
     * @param \Domain\Billing\DwsServiceDivisionCode $serviceDivisionCode
     * @param \Domain\Common\Carbon $providedIn
     * @return \Domain\Billing\DwsBillingServiceDetail[]&\ScalikePHP\Seq
     */
    protected function serviceDetails(DwsServiceDivisionCode $serviceDivisionCode, Carbon $providedIn): Seq
    {
        $endOfMonth = $providedIn->endOfMonth()->startOfDay();
        return match ($serviceDivisionCode) {
            DwsServiceDivisionCode::homeHelpService() => Seq::from(
                $this->serviceDetail(
                    userId: $this->users[1]->id,
                    providedOn: $this->providedIn->day(3),
                    serviceCodeString: '111147',
                    serviceCodeCategory: DwsServiceCodeCategory::physicalCare(),
                    unitScore: 1139
                ),
                $this->serviceDetail(
                    userId: $this->users[1]->id,
                    providedOn: $this->providedIn->day(7),
                    serviceCodeString: '117667',
                    serviceCodeCategory: DwsServiceCodeCategory::housework(),
                    unitScore: 438
                ),
                $this->serviceDetail(
                    userId: $this->users[1]->id,
                    providedOn: $endOfMonth,
                    serviceCodeString: '116010',
                    serviceCodeCategory: DwsServiceCodeCategory::specifiedOfficeAddition1(),
                    unitScore: 315,
                    isAddition: true
                ),
            ),
            DwsServiceDivisionCode::visitingCareForPwsd() => Seq::from(
                $this->serviceDetail(
                    userId: $this->users[2]->id,
                    providedOn: $this->providedIn->day(8),
                    serviceCodeString: '124371',
                    serviceCodeCategory: DwsServiceCodeCategory::visitingCareForPwsd3(),
                    unitScore: 276
                ),
                $this->serviceDetail(
                    userId: $this->users[2]->id,
                    providedOn: $this->providedIn->day(8),
                    serviceCodeString: '124381',
                    serviceCodeCategory: DwsServiceCodeCategory::visitingCareForPwsd3(),
                    unitScore: 135
                ),
                $this->serviceDetail(
                    userId: $this->users[2]->id,
                    providedOn: $this->providedIn->day(8),
                    serviceCodeString: '124491',
                    serviceCodeCategory: DwsServiceCodeCategory::visitingCareForPwsd3(),
                    unitScore: 138
                ),
                $this->serviceDetail(
                    userId: $this->users[2]->id,
                    providedOn: $this->providedIn->day(8),
                    serviceCodeString: '124501',
                    serviceCodeCategory: DwsServiceCodeCategory::visitingCareForPwsd3(),
                    unitScore: 137
                ),
                $this->serviceDetail(
                    userId: $this->users[2]->id,
                    providedOn: $this->providedIn->day(8),
                    serviceCodeString: '124511',
                    serviceCodeCategory: DwsServiceCodeCategory::visitingCareForPwsd3(),
                    unitScore: 138
                ),
                $this->serviceDetail(
                    userId: $this->users[2]->id,
                    providedOn: $this->providedIn->day(8),
                    serviceCodeString: '124521',
                    serviceCodeCategory: DwsServiceCodeCategory::visitingCareForPwsd3(),
                    unitScore: 135
                ),
                $this->serviceDetail(
                    userId: $this->users[2]->id,
                    providedOn: $this->providedIn->day(8),
                    serviceCodeString: '124531',
                    serviceCodeCategory: DwsServiceCodeCategory::visitingCareForPwsd3(),
                    unitScore: 138
                ),
                $this->serviceDetail(
                    userId: $this->users[2]->id,
                    providedOn: $this->providedIn->day(8),
                    serviceCodeString: '124321',
                    serviceCodeCategory: DwsServiceCodeCategory::visitingCareForPwsd3(),
                    unitScore: 128,
                    count: 4
                ),
                $this->serviceDetail(
                    userId: $this->users[2]->id,
                    providedOn: $this->providedIn->day(8),
                    serviceCodeString: '122321',
                    serviceCodeCategory: DwsServiceCodeCategory::visitingCareForPwsd3(),
                    unitScore: 106,
                    count: 4
                ),
                $this->serviceDetail(
                    userId: $this->users[2]->id,
                    providedOn: $this->providedIn->day(8),
                    serviceCodeString: '121331',
                    serviceCodeCategory: DwsServiceCodeCategory::visitingCareForPwsd3(),
                    unitScore: 85,
                    count: 8
                ),
                $this->serviceDetail(
                    userId: $this->users[2]->id,
                    providedOn: $this->providedIn->day(8),
                    serviceCodeString: '121341',
                    serviceCodeCategory: DwsServiceCodeCategory::visitingCareForPwsd3(),
                    unitScore: 80,
                    count: 8
                ),
                $this->serviceDetail(
                    userId: $this->users[2]->id,
                    providedOn: $this->providedIn->day(8),
                    serviceCodeString: '121351',
                    serviceCodeCategory: DwsServiceCodeCategory::visitingCareForPwsd3(),
                    unitScore: 86,
                    count: 4
                ),
                $this->serviceDetail(
                    userId: $this->users[2]->id,
                    providedOn: $this->providedIn->day(8),
                    serviceCodeString: '123351',
                    serviceCodeCategory: DwsServiceCodeCategory::visitingCareForPwsd3(),
                    unitScore: 108,
                    count: 4
                ),
                $this->serviceDetail(
                    userId: $this->users[2]->id,
                    providedOn: $this->providedIn->day(8),
                    serviceCodeString: '123361',
                    serviceCodeCategory: DwsServiceCodeCategory::visitingCareForPwsd3(),
                    unitScore: 100,
                    isAddition: false,
                    count: 4
                ),
                $this->serviceDetail(
                    userId: $this->users[2]->id,
                    providedOn: $this->providedIn->day(8),
                    serviceCodeString: '124361',
                    serviceCodeCategory: DwsServiceCodeCategory::visitingCareForPwsd3(),
                    unitScore: 120,
                    count: 4
                ),
                $this->serviceDetail(
                    userId: $this->users[2]->id,
                    providedOn: $this->providedIn->day(23),
                    serviceCodeString: '124171',
                    serviceCodeCategory: DwsServiceCodeCategory::visitingCareForPwsd1(),
                    unitScore: 318
                ),
                $this->serviceDetail(
                    userId: $this->users[2]->id,
                    providedOn: $this->providedIn->day(23),
                    serviceCodeString: '124181',
                    serviceCodeCategory: DwsServiceCodeCategory::visitingCareForPwsd1(),
                    unitScore: 156
                ),
                $this->serviceDetail(
                    userId: $this->users[2]->id,
                    providedOn: $this->providedIn->day(23),
                    serviceCodeString: '124391',
                    serviceCodeCategory: DwsServiceCodeCategory::visitingCareForPwsd1(),
                    unitScore: 159
                ),
                $this->serviceDetail(
                    userId: $this->users[2]->id,
                    providedOn: $this->providedIn->day(23),
                    serviceCodeString: '124401',
                    serviceCodeCategory: DwsServiceCodeCategory::visitingCareForPwsd1(),
                    unitScore: 158
                ),
                $this->serviceDetail(
                    userId: $this->users[2]->id,
                    providedOn: $this->providedIn->day(23),
                    serviceCodeString: '124411',
                    serviceCodeCategory: DwsServiceCodeCategory::visitingCareForPwsd1(),
                    unitScore: 159
                ),
                $this->serviceDetail(
                    userId: $this->users[2]->id,
                    providedOn: $this->providedIn->day(23),
                    serviceCodeString: '124421',
                    serviceCodeCategory: DwsServiceCodeCategory::visitingCareForPwsd1(),
                    unitScore: 156
                ),
                $this->serviceDetail(
                    userId: $this->users[2]->id,
                    providedOn: $this->providedIn->day(23),
                    serviceCodeString: '124431',
                    serviceCodeCategory: DwsServiceCodeCategory::visitingCareForPwsd1(),
                    unitScore: 159
                ),
                $this->serviceDetail(
                    userId: $this->users[2]->id,
                    providedOn: $this->providedIn->day(23),
                    serviceCodeString: '124121',
                    serviceCodeCategory: DwsServiceCodeCategory::visitingCareForPwsd1(),
                    unitScore: 147,
                    count: 4
                ),
                $this->serviceDetail(
                    userId: $this->users[2]->id,
                    providedOn: $this->providedIn->day(23),
                    serviceCodeString: '122121',
                    serviceCodeCategory: DwsServiceCodeCategory::visitingCareForPwsd1(),
                    unitScore: 123,
                    count: 4
                ),
                $this->serviceDetail(
                    userId: $this->users[2]->id,
                    providedOn: $this->providedIn->day(23),
                    serviceCodeString: '121131',
                    serviceCodeCategory: DwsServiceCodeCategory::visitingCareForPwsd1(),
                    unitScore: 98,
                    count: 8
                ),
                $this->serviceDetail(
                    userId: $this->users[2]->id,
                    providedOn: $this->providedIn->day(23),
                    serviceCodeString: '121141',
                    serviceCodeCategory: DwsServiceCodeCategory::visitingCareForPwsd1(),
                    unitScore: 92,
                    count: 8
                ),
                $this->serviceDetail(
                    userId: $this->users[2]->id,
                    providedOn: $this->providedIn->day(23),
                    serviceCodeString: '121151',
                    serviceCodeCategory: DwsServiceCodeCategory::visitingCareForPwsd1(),
                    unitScore: 99,
                    isAddition: false,
                    count: 4
                ),
                $this->serviceDetail(
                    userId: $this->users[2]->id,
                    providedOn: $this->providedIn->day(23),
                    serviceCodeString: '123151',
                    serviceCodeCategory: DwsServiceCodeCategory::visitingCareForPwsd1(),
                    unitScore: 124,
                    count: 4
                ),
                $this->serviceDetail(
                    userId: $this->users[2]->id,
                    providedOn: $this->providedIn->day(23),
                    serviceCodeString: '123161',
                    serviceCodeCategory: DwsServiceCodeCategory::visitingCareForPwsd1(),
                    unitScore: 115,
                    count: 4
                ),
                $this->serviceDetail(
                    userId: $this->users[2]->id,
                    providedOn: $this->providedIn->day(23),
                    serviceCodeString: '124161',
                    serviceCodeCategory: DwsServiceCodeCategory::visitingCareForPwsd1(),
                    unitScore: 138,
                    count: 4
                ),
                $this->serviceDetail(
                    userId: $this->users[2]->id,
                    providedOn: $this->providedIn->day(23),
                    serviceCodeString: '128453',
                    serviceCodeCategory: DwsServiceCodeCategory::outingSupportForPwsd(),
                    unitScore: 100
                ),
                $this->serviceDetail(
                    userId: $this->users[2]->id,
                    providedOn: $this->providedIn->day(23),
                    serviceCodeString: '128457',
                    serviceCodeCategory: DwsServiceCodeCategory::outingSupportForPwsd(),
                    unitScore: 25
                ),
                $this->serviceDetail(
                    userId: $this->users[2]->id,
                    providedOn: $this->providedIn->day(23),
                    serviceCodeString: '128461',
                    serviceCodeCategory: DwsServiceCodeCategory::outingSupportForPwsd(),
                    unitScore: 25
                ),
                $this->serviceDetail(
                    userId: $this->users[2]->id,
                    providedOn: $this->providedIn->day(23),
                    serviceCodeString: '128465',
                    serviceCodeCategory: DwsServiceCodeCategory::outingSupportForPwsd(),
                    unitScore: 25
                ),
                $this->serviceDetail(
                    userId: $this->users[2]->id,
                    providedOn: $this->providedIn->day(23),
                    serviceCodeString: '128469',
                    serviceCodeCategory: DwsServiceCodeCategory::outingSupportForPwsd(),
                    unitScore: 25
                ),
                $this->serviceDetail(
                    userId: $this->users[2]->id,
                    providedOn: $this->providedIn->day(23),
                    serviceCodeString: '128473',
                    serviceCodeCategory: DwsServiceCodeCategory::outingSupportForPwsd(),
                    unitScore: 50
                ),
                $this->serviceDetail(
                    userId: $this->users[2]->id,
                    providedOn: $endOfMonth,
                    serviceCodeString: '126010',
                    serviceCodeCategory: DwsServiceCodeCategory::specifiedOfficeAddition1(),
                    unitScore: 4446,
                    isAddition: true
                ),
            ),
            default => Seq::empty(),
        };
    }

    /**
     * テスト用の障害福祉サービス：請求単位を生成する.
     *
     * @param array $attrs
     * @return \Domain\Billing\DwsBillingBundle
     */
    protected function bundle(array $attrs = []): DwsBillingBundle
    {
        $values = [
            'id' => 1,
            'dwsBillingId' => $this->billing->id,
            'providedIn' => $this->providedIn,
            'cityCode' => '131041',
            'cityName' => '新宿区',
            'details' => [
                ...$this->serviceDetails(DwsServiceDivisionCode::homeHelpService(), $this->providedIn),
                ...$this->serviceDetails(DwsServiceDivisionCode::visitingCareForPwsd(), $this->providedIn),
            ],
            'createdAt' => false,
            'updatedAt' => false,
        ];
        return DwsBillingBundle::create($attrs + $values);
    }

    /**
     * テスト用の障害福祉サービス：請求書を作成する.
     *
     * @param array $attrs
     * @return \Domain\Billing\DwsBillingInvoice
     */
    protected function invoice(array $attrs = []): DwsBillingInvoice
    {
        $values = [
            'id' => 1,
            'dwsBillingBundleId' => 1,
            'claimAmount' => 1847349,
            'dwsPayment' => DwsBillingInvoice::payment([
                'subtotalDetailCount' => 4,
                'subtotalScore' => 174105,
                'subtotalFee' => 1866404,
                'subtotalBenefit' => 1847349,
                'subtotalCopay' => 19055,
                'subtotalSubsidy' => 0,
            ]),
            'highCostDwsPayment' => DwsBillingInvoice::highCostPayment([
                'subtotalDetailCount' => 0,
                'subtotalFee' => 0,
                'subtotalBenefit' => 0,
            ]),
            'totalCount' => 4,
            'totalScore' => 174105,
            'totalFee' => 1866404,
            'totalBenefit' => 1847349,
            'totalCopay' => 19055,
            'totalSubsidy' => 0,
            'items' => [
                DwsBillingInvoice::item([
                    'paymentCategory' => DwsBillingPaymentCategory::category1(),
                    'serviceDivisionCode' => DwsServiceDivisionCode::visitingCareForPwsd(),
                    'subtotalCount' => 4,
                    'subtotalScore' => 174105,
                    'subtotalFee' => 1866404,
                    'subtotalBenefit' => 1847349,
                    'subtotalCopay' => 19055,
                    'subtotalSubsidy' => 0,
                ]),
            ],
            'createdAt' => Carbon::now(),
            'updatedAt' => Carbon::now(),
        ];
        return DwsBillingInvoice::create($attrs + $values);
    }

    /**
     * テスト用の障害福祉サービス：明細書：要素の一覧を生成する.
     *
     * @return \Domain\Billing\DwsBillingStatementElement[]&\ScalikePHP\Seq
     */
    protected function statementElements(): Seq
    {
        return Seq::from(
            $this->statementElement(
                serviceCodeString: '111147',
                serviceCodeCategory: DwsServiceCodeCategory::physicalCare(),
                unitScore: 113,
                count: 1,
                providedOn: [Carbon::create(2021, 1, 3)]
            ),
            $this->statementElement(
                serviceCodeString: '117667',
                serviceCodeCategory: DwsServiceCodeCategory::housework(),
                unitScore: 438,
                count: 1,
                providedOn: [Carbon::create(2021, 1, 7)]
            ),
            $this->statementElement(
                serviceCodeString: '116010',
                serviceCodeCategory: DwsServiceCodeCategory::specifiedOfficeAddition1(),
                unitScore: 315,
                count: 1,
                providedOn: [Carbon::create(2021, 1, 31)],
                isAddition: true
            ),
            $this->statementElement(
                serviceCodeString: '115010',
                serviceCodeCategory: DwsServiceCodeCategory::copayCoordinationAddition(),
                unitScore: 150,
                count: 1,
                providedOn: [Carbon::create(2021, 1, 31)],
                isAddition: true
            ),
            $this->statementElement(
                serviceCodeString: '116715',
                serviceCodeCategory: DwsServiceCodeCategory::treatmentImprovementAddition1(),
                unitScore: 617,
                count: 1,
                providedOn: [Carbon::create(2021, 1, 31)],
                isAddition: true
            ),
            $this->statementElement(
                serviceCodeString: '116772',
                serviceCodeCategory: DwsServiceCodeCategory::specifiedTreatmentImprovementAddition1(),
                unitScore: 151,
                count: 1,
                providedOn: [Carbon::create(2021, 1, 31)],
                isAddition: true
            ),
            $this->statementElement(
                serviceCodeString: '124371',
                serviceCodeCategory: DwsServiceCodeCategory::visitingCareForPwsd3(),
                unitScore: 276,
                count: 1,
                providedOn: [Carbon::create(2021, 1, 8)]
            ),
            $this->statementElement(
                serviceCodeString: '124381',
                serviceCodeCategory: DwsServiceCodeCategory::visitingCareForPwsd3(),
                unitScore: 135,
                count: 1,
                providedOn: [Carbon::create(2021, 1, 8)]
            ),
            $this->statementElement(
                serviceCodeString: '124491',
                serviceCodeCategory: DwsServiceCodeCategory::visitingCareForPwsd3(),
                unitScore: 138,
                count: 1,
                providedOn: [Carbon::create(2021, 1, 8)]
            ),
            $this->statementElement(
                serviceCodeString: '124501',
                serviceCodeCategory: DwsServiceCodeCategory::visitingCareForPwsd3(),
                unitScore: 137,
                count: 1,
                providedOn: [Carbon::create(2021, 1, 8)]
            ),
            $this->statementElement(
                serviceCodeString: '124511',
                serviceCodeCategory: DwsServiceCodeCategory::visitingCareForPwsd3(),
                unitScore: 138,
                count: 1,
                providedOn: [Carbon::create(2021, 1, 8)]
            ),
            $this->statementElement(
                serviceCodeString: '124521',
                serviceCodeCategory: DwsServiceCodeCategory::visitingCareForPwsd3(),
                unitScore: 135,
                count: 1,
                providedOn: [Carbon::create(2021, 1, 8)]
            ),
            $this->statementElement(
                serviceCodeString: '124531',
                serviceCodeCategory: DwsServiceCodeCategory::visitingCareForPwsd3(),
                unitScore: 138,
                count: 1,
                providedOn: [Carbon::create(2021, 1, 8)]
            ),
            $this->statementElement(
                serviceCodeString: '124321',
                serviceCodeCategory: DwsServiceCodeCategory::visitingCareForPwsd3(),
                unitScore: 128,
                count: 4,
                providedOn: [Carbon::create(2021, 1, 8)]
            ),
            $this->statementElement(
                serviceCodeString: '122321',
                serviceCodeCategory: DwsServiceCodeCategory::visitingCareForPwsd3(),
                unitScore: 106,
                count: 4,
                providedOn: [Carbon::create(2021, 1, 8)]
            ),
            $this->statementElement(
                serviceCodeString: '121331',
                serviceCodeCategory: DwsServiceCodeCategory::visitingCareForPwsd3(),
                unitScore: 85,
                count: 8,
                providedOn: [Carbon::create(2021, 1, 8)]
            ),
            $this->statementElement(
                serviceCodeString: '121341',
                serviceCodeCategory: DwsServiceCodeCategory::visitingCareForPwsd3(),
                unitScore: 80,
                count: 8,
                providedOn: [Carbon::create(2021, 1, 8)]
            ),
            $this->statementElement(
                serviceCodeString: '121351',
                serviceCodeCategory: DwsServiceCodeCategory::visitingCareForPwsd3(),
                unitScore: 86,
                count: 4,
                providedOn: [Carbon::create(2021, 1, 8)]
            ),
            $this->statementElement(
                serviceCodeString: '123351',
                serviceCodeCategory: DwsServiceCodeCategory::visitingCareForPwsd3(),
                unitScore: 108,
                count: 4,
                providedOn: [Carbon::create(2021, 1, 8)]
            ),
            $this->statementElement(
                serviceCodeString: '123361',
                serviceCodeCategory: DwsServiceCodeCategory::visitingCareForPwsd3(),
                unitScore: 100,
                count: 4,
                providedOn: [Carbon::create(2021, 1, 8)]
            ),
            $this->statementElement(
                serviceCodeString: '124361',
                serviceCodeCategory: DwsServiceCodeCategory::visitingCareForPwsd3(),
                unitScore: 120,
                count: 4,
                providedOn: [Carbon::create(2021, 1, 8)]
            ),
            $this->statementElement(
                serviceCodeString: '124171',
                serviceCodeCategory: DwsServiceCodeCategory::visitingCareForPwsd1(),
                unitScore: 318,
                count: 1,
                providedOn: [Carbon::create(2021, 1, 23)]
            ),
            $this->statementElement(
                serviceCodeString: '124181',
                serviceCodeCategory: DwsServiceCodeCategory::visitingCareForPwsd1(),
                unitScore: 156,
                count: 1,
                providedOn: [Carbon::create(2021, 1, 23)]
            ),
            $this->statementElement(
                serviceCodeString: '124391',
                serviceCodeCategory: DwsServiceCodeCategory::visitingCareForPwsd1(),
                unitScore: 159,
                count: 1,
                providedOn: [Carbon::create(2021, 1, 23)]
            ),
            $this->statementElement(
                serviceCodeString: '124401',
                serviceCodeCategory: DwsServiceCodeCategory::visitingCareForPwsd1(),
                unitScore: 158,
                count: 1,
                providedOn: [Carbon::create(2021, 1, 23)]
            ),
            $this->statementElement(
                serviceCodeString: '124411',
                serviceCodeCategory: DwsServiceCodeCategory::visitingCareForPwsd1(),
                unitScore: 159,
                count: 1,
                providedOn: [Carbon::create(2021, 1, 23)]
            ),
            $this->statementElement(
                serviceCodeString: '124421',
                serviceCodeCategory: DwsServiceCodeCategory::visitingCareForPwsd1(),
                unitScore: 156,
                count: 1,
                providedOn: [Carbon::create(2021, 1, 23)]
            ),
            $this->statementElement(
                serviceCodeString: '124431',
                serviceCodeCategory: DwsServiceCodeCategory::visitingCareForPwsd1(),
                unitScore: 159,
                count: 1,
                providedOn: [Carbon::create(2021, 1, 23)]
            ),
            $this->statementElement(
                serviceCodeString: '124121',
                serviceCodeCategory: DwsServiceCodeCategory::visitingCareForPwsd1(),
                unitScore: 147,
                count: 4,
                providedOn: [Carbon::create(2021, 1, 23)]
            ),
            $this->statementElement(
                serviceCodeString: '122121',
                serviceCodeCategory: DwsServiceCodeCategory::visitingCareForPwsd1(),
                unitScore: 123,
                count: 4,
                providedOn: [Carbon::create(2021, 1, 23)]
            ),
            $this->statementElement(
                serviceCodeString: '121131',
                serviceCodeCategory: DwsServiceCodeCategory::visitingCareForPwsd1(),
                unitScore: 98,
                count: 8,
                providedOn: [Carbon::create(2021, 1, 23)]
            ),
            $this->statementElement(
                serviceCodeString: '121141',
                serviceCodeCategory: DwsServiceCodeCategory::visitingCareForPwsd1(),
                unitScore: 92,
                count: 8,
                providedOn: [Carbon::create(2021, 1, 23)]
            ),
            $this->statementElement(
                serviceCodeString: '121151',
                serviceCodeCategory: DwsServiceCodeCategory::visitingCareForPwsd1(),
                unitScore: 99,
                count: 4,
                providedOn: [Carbon::create(2021, 1, 23)]
            ),
            $this->statementElement(
                serviceCodeString: '123151',
                serviceCodeCategory: DwsServiceCodeCategory::visitingCareForPwsd1(),
                unitScore: 124,
                count: 4,
                providedOn: [Carbon::create(2021, 1, 23)]
            ),
            $this->statementElement(
                serviceCodeString: '123161',
                serviceCodeCategory: DwsServiceCodeCategory::visitingCareForPwsd1(),
                unitScore: 115,
                count: 4,
                providedOn: [Carbon::create(2021, 1, 23)]
            ),
            $this->statementElement(
                serviceCodeString: '124161',
                serviceCodeCategory: DwsServiceCodeCategory::visitingCareForPwsd1(),
                unitScore: 138,
                count: 4,
                providedOn: [Carbon::create(2021, 1, 23)]
            ),
            $this->statementElement(
                serviceCodeString: '128453',
                serviceCodeCategory: DwsServiceCodeCategory::outingSupportForPwsd(),
                unitScore: 100,
                count: 1,
                providedOn: [Carbon::create(2021, 1, 23)]
            ),
            $this->statementElement(
                serviceCodeString: '128457',
                serviceCodeCategory: DwsServiceCodeCategory::outingSupportForPwsd(),
                unitScore: 25,
                count: 1,
                providedOn: [Carbon::create(2021, 1, 23)]
            ),
            $this->statementElement(
                serviceCodeString: '128461',
                serviceCodeCategory: DwsServiceCodeCategory::outingSupportForPwsd(),
                unitScore: 25,
                count: 1,
                providedOn: [Carbon::create(2021, 1, 23)]
            ),
            $this->statementElement(
                serviceCodeString: '128465',
                serviceCodeCategory: DwsServiceCodeCategory::outingSupportForPwsd(),
                unitScore: 25,
                count: 1,
                providedOn: [Carbon::create(2021, 1, 23)]
            ),
            $this->statementElement(
                serviceCodeString: '128469',
                serviceCodeCategory: DwsServiceCodeCategory::outingSupportForPwsd(),
                unitScore: 25,
                count: 1,
                providedOn: [Carbon::create(2021, 1, 23)]
            ),
            $this->statementElement(
                serviceCodeString: '128473',
                serviceCodeCategory: DwsServiceCodeCategory::outingSupportForPwsd(),
                unitScore: 50,
                count: 1,
                providedOn: [Carbon::create(2021, 1, 23)]
            ),
            $this->statementElement(
                serviceCodeString: '126010',
                serviceCodeCategory: DwsServiceCodeCategory::specifiedOfficeAddition1(),
                unitScore: 4446,
                count: 1,
                providedOn: [Carbon::create(2021, 1, 31)],
                isAddition: true
            ),
            $this->statementElement(
                serviceCodeString: '125010',
                serviceCodeCategory: DwsServiceCodeCategory::copayCoordinationAddition(),
                unitScore: 150,
                count: 1,
                providedOn: [Carbon::create(2021, 1, 31)],
                isAddition: true
            ),
            $this->statementElement(
                serviceCodeString: '126715',
                serviceCodeCategory: DwsServiceCodeCategory::treatmentImprovementAddition1(),
                unitScore: 2984,
                count: 1,
                providedOn: [Carbon::create(2021, 1, 31)],
                isAddition: true
            ),
            $this->statementElement(
                serviceCodeString: '126772',
                serviceCodeCategory: DwsServiceCodeCategory::specifiedTreatmentImprovementAddition1(),
                unitScore: 703,
                count: 1,
                providedOn: [Carbon::create(2021, 1, 31)],
                isAddition: true
            ),
        );
    }

    /**
     * テスト用の障害福祉サービス：明細書：要素を生成する.
     *
     * @param string $serviceCodeString
     * @param int $unitScore
     * @param int $count
     * @param array|\Domain\Common\Carbon[] $providedOn
     * @param bool $isAddition
     * @param DwsServiceCodeCategory $serviceCodeCategory
     * @return \Domain\Billing\DwsBillingStatementElement
     */
    protected function statementElement(
        string $serviceCodeString,
        DwsServiceCodeCategory $serviceCodeCategory,
        int $unitScore,
        int $count,
        array $providedOn,
        bool $isAddition = false
    ): StatementElement {
        return StatementElement::create([
            'serviceCode' => ServiceCode::fromString($serviceCodeString),
            'serviceCodeCategory' => $serviceCodeCategory,
            'unitScore' => $unitScore,
            'isAddition' => $isAddition,
            'count' => $count,
            'providedOn' => $providedOn,
        ]);
    }

    /**
     * テスト用の障害福祉サービス：明細書：集計の一覧を生成する.
     *
     * @param \Domain\Common\Decimal $unitCost
     * @return \Domain\Billing\DwsBillingStatementAggregate[]&\ScalikePHP\Seq
     */
    protected function statementAggregates(Decimal $unitCost): Seq
    {
        return Seq::from(
            new StatementAggregate(
                serviceDivisionCode: DwsServiceDivisionCode::homeHelpService(),
                startedOn: Carbon::create(2020, 1, 1),
                terminatedOn: null,
                serviceDays: 2,
                subtotalScore: 1784,
                unitCost: $unitCost,
                subtotalFee: 19980,
                unmanagedCopay: 1998,
                managedCopay: 1998,
                cappedCopay: 1998,
                adjustedCopay: 1998,
                coordinatedCopay: null,
                subtotalCopay: 1998,
                subtotalBenefit: 17982,
                subtotalSubsidy: 0,
            ),
            new StatementAggregate(
                serviceDivisionCode: DwsServiceDivisionCode::visitingCareForPwsd(),
                startedOn: Carbon::create(2020, 1, 1),
                terminatedOn: null,
                serviceDays: 2,
                subtotalScore: 19311,
                unitCost: $unitCost,
                subtotalFee: 216283,
                unmanagedCopay: 21628,
                managedCopay: 21628,
                cappedCopay: 21628,
                adjustedCopay: 21628,
                coordinatedCopay: null,
                subtotalCopay: 21628,
                subtotalBenefit: 194655,
                subtotalSubsidy: 0,
            )
        );
    }

    /**
     * テスト用の障害福祉サービス：明細書を生成する.
     *
     * @param array $attrs
     * @return \Domain\Billing\DwsBillingStatement
     */
    protected function statement(array $attrs = []): Statement
    {
        $values = [
            'id' => 1,
            'dwsBillingId' => $this->billing->id,
            'dwsBillingBundleId' => $this->bundle->id,
            'subsidyCityCode' => $this->userDwsSubsidy->cityCode,
            'user' => DwsBillingUser::from($this->user, $this->dwsCertification),
            'dwsAreaGradeName' => '一級地',
            'dwsAreaGradeCode' => '01',
            'copayLimit' => $this->dwsCertification->copayLimit,
            'totalScore' => 21095,
            'totalFee' => 236283,
            'totalCappedCopay' => 23626,
            'totalAdjustedCopay' => 23626,
            'totalCoordinatedCopay' => null,
            'totalCopay' => 23626,
            'totalBenefit' => 212637,
            'totalSubsidy' => 16538,
            'isProvided' => true,
            'copayCoordination' => null,
            'copayCoordinationStatus' => DwsBillingStatementCopayCoordinationStatus::uncreated(),
            'aggregates' => [...$this->statementAggregates],
            'contracts' => [...$this->statementContracts],
            'items' => [...$this->statementElements->map(fn (StatementElement $x): StatementItem => $x->toItem())],
            'status' => DwsBillingStatus::checking(),
            'fixedAt' => null,
            'createdAt' => Carbon::now(),
            'updatedAt' => Carbon::now(),
        ];
        return Statement::create($attrs + $values);
    }

    /**
     * テスト用の障害福祉サービス：サービス提供実績記録票：明細を生成する.
     *
     * @return \ScalikePHP\Seq
     */
    protected function serviceReportItems(): Seq
    {
        return Seq::from(
            $this->serviceReportItem(),
            $this->serviceReportItem(
                [
                    'serialNumber' => 2,
                    'providedOn' => Carbon::create(2021, 1, 2),
                    'plan' => DwsBillingServiceReportDuration::create([
                        'period' => CarbonRange::create([
                            'start' => Carbon::create(2021, 1, 2, 13),
                            'end' => Carbon::create(2021, 1, 2, 14, 30),
                        ]),
                        'serviceDurationHours' => Decimal::fromInt(1_5000),
                        'movingDurationHours' => Decimal::zero(),
                    ]),
                    'result' => DwsBillingServiceReportDuration::create([
                        'period' => CarbonRange::create([
                            'start' => Carbon::create(2021, 1, 2, 13),
                            'end' => Carbon::create(2021, 1, 2, 14, 30),
                        ]),
                        'serviceDurationHours' => Decimal::fromInt(1_5000),
                        'movingDurationHours' => Decimal::zero(),
                    ]),
                ]
            ),
            $this->serviceReportItem(
                [
                    'serialNumber' => 3,
                    'providedOn' => Carbon::create(2021, 1, 3),
                    'plan' => DwsBillingServiceReportDuration::create([
                        'period' => CarbonRange::create([
                            'start' => Carbon::create(2021, 1, 3, 13),
                            'end' => Carbon::create(2021, 1, 3, 14, 30),
                        ]),
                        'serviceDurationHours' => Decimal::fromInt(1_5000),
                        'movingDurationHours' => Decimal::zero(),
                    ]),
                    'result' => DwsBillingServiceReportDuration::create([
                        'period' => CarbonRange::create([
                            'start' => Carbon::create(2021, 1, 3, 13),
                            'end' => Carbon::create(2021, 1, 3, 14, 30),
                        ]),
                        'serviceDurationHours' => Decimal::fromInt(1_5000),
                        'movingDurationHours' => Decimal::zero(),
                    ]),
                ]
            ),
            $this->serviceReportItem(
                [
                    'serialNumber' => 4,
                    'providedOn' => Carbon::create(2021, 1, 4),
                    'plan' => DwsBillingServiceReportDuration::create([
                        'period' => CarbonRange::create([
                            'start' => Carbon::create(2021, 1, 4, 13),
                            'end' => Carbon::create(2021, 1, 4, 14, 30),
                        ]),
                        'serviceDurationHours' => Decimal::fromInt(1_5000),
                        'movingDurationHours' => Decimal::zero(),
                    ]),
                    'result' => DwsBillingServiceReportDuration::create([
                        'period' => CarbonRange::create([
                            'start' => Carbon::create(2021, 1, 4, 13),
                            'end' => Carbon::create(2021, 1, 4, 14, 30),
                        ]),
                        'serviceDurationHours' => Decimal::fromInt(1_5000),
                        'movingDurationHours' => Decimal::zero(),
                    ]),
                ]
            ),
        );
    }

    /**
     * テスト用の障害福祉サービス：サービス提供実績記録票：明細を作成する.
     *
     * @param array $attrs
     * @return \Domain\Billing\DwsBillingServiceReportItem
     */
    protected function serviceReportItem(array $attrs = []): DwsBillingServiceReportItem
    {
        $values = [
            'serialNumber' => 1,
            'providedOn' => Carbon::create(2021, 1, 1),
            'serviceType' => DwsGrantedServiceCode::housework(),
            'providerType' => DwsHomeHelpServiceProviderType::none(),
            'situation' => DwsBillingServiceReportSituation::none(),
            'plan' => DwsBillingServiceReportDuration::create([
                'period' => CarbonRange::create([
                    'start' => Carbon::create(2021, 1, 1, 9),
                    'end' => Carbon::create(2021, 1, 1, 11),
                ]),
                'serviceDurationHours' => Decimal::fromInt(2_0000),
                'movingDurationHours' => Decimal::zero(),
            ]),
            'result' => DwsBillingServiceReportDuration::create([
                'period' => CarbonRange::create([
                    'start' => Carbon::create(2021, 1, 1, 9),
                    'end' => Carbon::create(2021, 1, 1, 11),
                ]),
                'serviceDurationHours' => Decimal::fromInt(2_0000),
                'movingDurationHours' => Decimal::zero(),
            ]),
            'serviceCount' => 1,
            'headCount' => 1,
            'isCoaching' => false,
            'isFirstTime' => false,
            'isEmergency' => false,
            'isWelfareSpecialistCooperation' => false,
            'isBehavioralDisorderSupportCooperation' => false,
            'isMovingCareSupport',
            'isDriving' => false,
            'isPreviousMonth' => false,
            'note' => '適当な備考',
        ];
        return DwsBillingServiceReportItem::create($attrs + $values);
    }

    /**
     * テスト用のサービス提供実績記録票を生成する.
     *
     * @param array $attrs
     * @return \Domain\Billing\DwsBillingServiceReport
     */
    protected function serviceReport(array $attrs = []): DwsBillingServiceReport
    {
        $values = [
            'id' => 1,
            'dwsBillingId' => $this->billing->id,
            'dwsBillingBundleId' => $this->bundle->id,
            'user' => DwsBillingUser::from($this->user, $this->dwsCertification),
            'format' => DwsBillingServiceReportFormat::homeHelpService(),
            'plan' => DwsBillingServiceReportAggregate::fromAssoc([
                DwsBillingServiceReportAggregateGroup::physicalCare()->value() => [
                    DwsBillingServiceReportAggregateCategory::category100()->value() => Decimal::fromInt(1_0000),
                ],
            ]),
            'result' => DwsBillingServiceReportAggregate::fromAssoc([
                DwsBillingServiceReportAggregateGroup::physicalCare()->value() => [
                    DwsBillingServiceReportAggregateCategory::category100()->value() => Decimal::fromInt(1_0000),
                ],
            ]),
            'aggregates' => DwsBillingServiceReportAggregate::fromAssoc([
                DwsBillingServiceReportAggregateGroup::physicalCare()->value() => [
                    DwsBillingServiceReportAggregateCategory::category100()->value() => Decimal::zero(),
                    DwsBillingServiceReportAggregateCategory::category70()->value() => Decimal::zero(),
                    DwsBillingServiceReportAggregateCategory::categoryPwsd()->value() => Decimal::zero(),
                    DwsBillingServiceReportAggregateCategory::categoryTotal()->value() => Decimal::fromInt(5_0000),
                ],
            ]),
            'movingDuration' => 0,
            'emergencyCount' => 0,
            'firstTimeCount' => 0,
            'welfareSpecialistCooperationCount' => 0,
            'behavioralDisorderSupportCooperationCount' => 0,
            'movingCareSupportCount' => 0,
            'items' => [...$this->serviceReportItems],
            'status' => DwsBillingStatus::ready(),
            'fixedAt' => null,
            'createdAt' => Carbon::now(),
            'updatedAt' => Carbon::now(),
        ];
        return DwsBillingServiceReport::create($attrs + $values);
    }

    /**
     * テスト用のサービス提供実績記録票一覧を生成する.
     *
     * @return \Domain\Billing\DwsBillingServiceReport[]&\ScalikePHP\Seq
     */
    protected function serviceReports(): Seq
    {
        return Seq::from(
            $this->serviceReport([
                'id' => 1,
                'user' => DwsBillingUser::from($this->users[0], $this->dwsCertifications[0]),
            ]),
            $this->serviceReport([
                'id' => 2,
                'user' => DwsBillingUser::from($this->users[1], $this->dwsCertifications[1]),
                'format' => DwsBillingServiceReportFormat::visitingCareForPwsd(),
                'aggregates' => [],
                'item' => [...$this->serviceReportItems],
            ]),
            $this->serviceReport([
                'id' => 3,
                'user' => DwsBillingUser::from($this->users[2], $this->dwsCertifications[2]),
            ]),
        );
    }

    /**
     * テスト用のサービス単位（居宅）を生成する.
     *
     * @param array $attrs
     * @return \Domain\Billing\DwsHomeHelpServiceChunk
     */
    protected function dwsHomeHelpServiceChunk(array $attrs = []): DwsHomeHelpServiceChunk
    {
        $values = [
            'category' => DwsServiceCodeCategory::housework(),
            'buildingType' => DwsHomeHelpServiceBuildingType::none(),
            'isEmergency' => false,
            'isPlannedByNovice' => false,
            'isFirst' => false,
            'isWelfareSpecialistCooperation' => false,
            'range' => CarbonRange::create([
                'start' => Carbon::create(2021, 1, 2, 12, 0),
                'end' => Carbon::create(2021, 1, 2, 13, 0),
            ]),
            'fragments' => Seq::from(
                DwsHomeHelpServiceFragment::create([
                    'providerType' => DwsHomeHelpServiceProviderType::none(),
                    'isSecondary' => false,
                    'range' => CarbonRange::create([
                        'start' => Carbon::create(2021, 1, 2, 12, 0),
                        'end' => Carbon::create(2021, 1, 2, 13, 0),
                    ]),
                    'headcount' => 1,
                ]),
            ),
        ];
        return DwsHomeHelpServiceChunkImpl::create($attrs + $values);
    }

    /**
     * テスト用のサービス単位（重訪）を生成する.
     *
     * @param array $attrs
     * @return \Domain\Billing\DwsVisitingCareForPwsdChunk
     */
    protected function dwsVisitingCareForPwsdChunk(array $attrs = []): DwsVisitingCareForPwsdChunk
    {
        $values = [
            'category' => DwsServiceCodeCategory::visitingCareForPwsd1(),
            'providedOn' => Carbon::create(2021, 1, 2),
            'isEmergency' => false,
            'isFirst' => false,
            'isBehavioralDisorderSupportCooperation' => false,
            'range' => CarbonRange::create([
                'start' => Carbon::create(2021, 1, 2, 12, 0),
                'end' => Carbon::create(2021, 1, 2, 20, 0),
            ]),
            'fragments' => Seq::from(
                DwsVisitingCareForPwsdFragment::create([
                    'isCoaching' => false,
                    'isMoving' => false,
                    'isSecondary' => false,
                    'movingDurationMinutes' => 0,
                    'range' => CarbonRange::create([
                        'start' => Carbon::create(2021, 1, 2, 12, 0),
                        'end' => Carbon::create(2021, 1, 2, 20, 0),
                    ]),
                    'headcount' => 1,
                ]),
            ),
        ];
        return DwsVisitingCareForPwsdChunkImpl::create($attrs + $values);
    }
}

<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\Billing;

use Domain\Billing\LtcsBillingBundle;
use Domain\Billing\LtcsBillingServiceDetail;
use Domain\Billing\LtcsBillingServiceDetailDisposition;
use Domain\Billing\LtcsExpiredReason;
use Domain\Common\Addr;
use Domain\Common\Carbon;
use Domain\Common\CarbonRange;
use Domain\Common\Decimal;
use Domain\Common\DefrayerCategory;
use Domain\Common\Location;
use Domain\Common\Prefecture;
use Domain\Common\ServiceSegment;
use Domain\Common\Sex;
use Domain\Common\StructuredName;
use Domain\Contract\Contract;
use Domain\Contract\ContractPeriod;
use Domain\Contract\ContractStatus;
use Domain\LtcsAreaGrade\LtcsAreaGradeFee;
use Domain\LtcsInsCard\LtcsCarePlanAuthorType;
use Domain\LtcsInsCard\LtcsInsCard;
use Domain\LtcsInsCard\LtcsInsCardStatus;
use Domain\LtcsInsCard\LtcsLevel;
use Domain\Office\Office;
use Domain\Office\OfficeDwsCommAccompanyService;
use Domain\Office\OfficeDwsGenericService;
use Domain\Office\OfficeLtcsCareManagementService;
use Domain\Office\OfficeLtcsCompHomeVisitingService;
use Domain\Office\OfficeLtcsHomeVisitLongTermCareService;
use Domain\Office\OfficeStatus;
use Domain\Office\Purpose;
use Domain\ProvisionReport\LtcsBuildingSubtraction;
use Domain\ServiceCode\ServiceCode;
use Domain\ServiceCodeDictionary\LtcsNoteRequirement;
use Domain\ServiceCodeDictionary\LtcsServiceCodeCategory;
use Domain\User\User;
use Domain\User\UserLtcsSubsidy;
use Exception;
use Lib\Csv;
use Lib\Exceptions\LogicException;
use ScalikePHP\Seq;

/**
 * 介護保険サービス：請求関連のテスト用のデータ生成関連処理.
 */
trait LtcsBillingTestSupport
{
    protected Seq $statementExamples;
    protected Seq $statementItemExamples;

    protected Seq $insCards;
    protected Seq $users;
    protected Seq $contracts;

    protected LtcsBillingBundle $bundle;
    protected LtcsAreaGradeFee $fee;
    protected Office $office;
    protected Seq $offices;
    protected Seq $subsidies;

    private array $additions = [
        '114000',
        '114001',
        '114002',
        '114003',
        '114114',
        '114115',
        '116271',
        '116272',
        '116273',
        '116274',
        '116275',
        '116278',
        '116279',
        '116361',
        '116362',
        '116363',
        '118000',
        '118100',
        '118110',
    ];

    /**
     * テスト用のデータを生成してプロパティに設定する.
     *
     * @return void
     */
    private function setupTestData(): void
    {
        // 請求時に使用している明細書のフォーマットのCSV
        $this->statementExamples = Csv::read(codecept_data_dir('Billing/ltcs-billing-statement-example.csv'));
        // 請求時に使用している明細書明細のフォーマットのCSV
        $this->statementItemExamples = Csv::read(codecept_data_dir('Billing/ltcs-billing-statement-item-example.csv'));

        $this->office = $this->office(604, '1370406140');
        $this->offices = $this->offices();

        $this->users = $this->users();
        $this->contracts = $this->contracts();
        $this->insCards = $this->insCards();

        $this->bundle = $this->bundle(Carbon::create(2020, 11));
        $this->fee = $this->fee();
        $this->subsidies = $this->subsidies();
    }

    /**
     * テスト用の利用者の一覧を生成する.
     *
     * @return \Domain\Billing\LtcsBillingStatementItem[]&\ScalikePHP\Seq
     */
    private function users(): Seq
    {
        $xs = call_user_func(function (): iterable {
            foreach ($this->statementExamples as $index => $row) {
                yield User::create([
                    'id' => $index + 1,
                    'organizationId' => 1,
                    'name' => new StructuredName(
                        familyName: '高田',
                        givenName: '純次',
                        phoneticFamilyName: 'タカダ',
                        phoneticGivenName: 'ジュンジ',
                    ),
                    'sex' => Sex::from(+$row[15]),
                    'birthday' => Carbon::createFromFormat('Ymd', $row[14]),
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
                ]);
            }
        });
        return Seq::from(...$xs);
    }

    /**
     * テスト用の契約を生成する.
     *
     * @return \Domain\Contract\Contract[]&\ScalikePHP\Seq
     */
    private function contracts(): Seq
    {
        $xs = call_user_func(function (): iterable {
            foreach ($this->statementExamples as $index => $row) {
                $id = $index + 1;
                yield Contract::create([
                    'id' => $id,
                    'organizationId' => $this->office->organizationId,
                    'userId' => $id,
                    'officeId' => $this->office->id,
                    'serviceSegment' => ServiceSegment::longTermCare(),
                    'status' => ContractStatus::terminated(),
                    'contractedOn' => Carbon::create(2008, 5, 17),
                    'terminatedOn' => null,
                    'dwsPeriods' => [],
                    'ltcsPeriod' => ContractPeriod::create([
                        'start' => Carbon::create(2008, 6, 1),
                        'end' => Carbon::create(2021, 12, 31),
                    ]),
                    'expiredReason' => LtcsExpiredReason::hospitalized(),
                    'note' => 'だるまさんがころんだ',
                    'isEnabled' => true,
                    'version' => 1,
                    'createdAt' => Carbon::create(2020, 1, 2),
                    'updatedAt' => Carbon::create(2020, 1, 3),
                ]);
            }
        });
        return Seq::from(...$xs);
    }

    /**
     * テスト用の介護保険サービス：請求単位を生成する.
     *
     * @param \Domain\Common\Carbon $providedIn
     * @return \Domain\Billing\LtcsBillingBundle
     */
    private function bundle(Carbon $providedIn): LtcsBillingBundle
    {
        return LtcsBillingBundle::create([
            'id' => 1,
            'billingId' => 2,
            'providedIn' => $providedIn,
            'details' => $this->details($providedIn)->toArray(),
            'createdAt' => Carbon::now(),
            'updatedAt' => Carbon::now(),
        ]);
    }

    /**
     * テスト用の介護保険サービス：請求：サービス詳細の一覧を生成する.
     *
     * @param \Domain\Common\Carbon $providedIn
     * @return \Domain\Billing\LtcsBillingServiceDetail[]&\ScalikePHP\Seq
     */
    private function details(Carbon $providedIn): Seq
    {
        return $this->statementItemExamples->flatMap(function (array $row) use ($providedIn): iterable {
            /** @var \Domain\LtcsInsCard\LtcsInsCard $insCard */
            $insCard = $this->insCards
                ->find(fn (LtcsInsCard $x): bool => $x->insNumber === $row[7])
                ->getOrElse(function () use ($row): void {
                    throw new Exception("LtcsInsCard(insNumber = {$row[7]}) not found");
                });
            /** @var \Domain\User\User $user */
            $user = $this->users
                ->find(fn (User $x): bool => $x->id === $insCard->userId)
                ->getOrElse(function () use ($row): void {
                    throw new Exception("User({$row[7]} not found");
                });
            $count = +$row[11];
            for ($offset = 0; $offset < $count; ++$offset) {
                $serviceCodeString = $row[8] . $row[9];
                $isLimited = !in_array($serviceCodeString, $this->additions, true)
                    || str_contains($serviceCodeString, '1140');
                $x = new LtcsBillingServiceDetail(
                    userId: $user->id,
                    disposition: LtcsBillingServiceDetailDisposition::result(),
                    providedOn: $providedIn->addDays($offset),
                    serviceCode: ServiceCode::fromString($serviceCodeString),
                    serviceCodeCategory: $this->resolveServiceCodeCategory($serviceCodeString),
                    buildingSubtraction: LtcsBuildingSubtraction::none(),
                    noteRequirement: LtcsNoteRequirement::none(),
                    // テストのためサービスコードのみで判定する
                    isAddition: in_array($serviceCodeString, $this->additions, true),
                    // テストのためサービスコードのみで判定する
                    isLimited: $isLimited,
                    // 摘要欄に記載がない場合はテストのため一律で 60 分とする
                    durationMinutes: empty($row[19]) || !is_numeric($row[19]) ? 60 : +$row[19],
                    unitScore: +$row[10],
                    count: 1,
                    wholeScore: +$row[10],
                    maxBenefitQuotaExcessScore: 0,
                    maxBenefitExcessScore: 0,
                    totalScore: +$row[10],
                );
                yield $x->copy(['disposition' => LtcsBillingServiceDetailDisposition::plan()]);
                yield $x;
            }
        });
    }

    /**
     * テスト用の介護保険サービス：地域区分単価を生成する.
     *
     * @return \Domain\LtcsAreaGrade\LtcsAreaGradeFee
     */
    private function fee(): LtcsAreaGradeFee
    {
        return LtcsAreaGradeFee::create([
            'id' => 1,
            'ltcsAreaGradeId' => 1,
            'effectivatedOn' => Carbon::create(2018, 4, 1),
            'fee' => Decimal::fromInt(11_4000),
        ]);
    }

    /**
     * テスト用の介護保険被保険者証を生成する.
     *
     * @return \Domain\LtcsInsCard\LtcsInsCard[]&\ScalikePHP\Seq
     */
    private function insCards(): Seq
    {
        $xs = call_user_func(function (): iterable {
            foreach ($this->statementExamples as $index => $row) {
                $id = $index + 1;
                yield LtcsInsCard::create([
                    'id' => $id,
                    'userId' => $id,
                    'effectivatedOn' => Carbon::create(2020, 1, 1),
                    'status' => LtcsInsCardStatus::approved(),
                    'insNumber' => $row[7],
                    'issuedOn' => Carbon::create(2020, 1, 1),
                    'insurerNumber' => $row[6],
                    'insurerName' => '杜王町',
                    'ltcsLevel' => LtcsLevel::from(+$row[16]),
                    'certificatedOn' => Carbon::create(2020, 1, 1),
                    'activatedOn' => Carbon::create(2020, 1, 1),
                    'deactivatedOn' => Carbon::create(2022, 12, 31),
                    'maxBenefitQuotas' => [],
                    'copayRate' => 100 - $row[30],
                    'copayActivatedOn' => Carbon::create(2020, 1, 1),
                    'copayDeactivatedOn' => Carbon::create(2022, 12, 31),
                    'carePlanAuthorType' => LtcsCarePlanAuthorType::from(+$row[20]),
                    'carePlanAuthorOfficeId' => $id,
                    'isEnabled' => true,
                    'version' => 1,
                    'createdAt' => Carbon::create(2020, 1, 1),
                    'updatedAt' => Carbon::create(2020, 1, 1),
                ]);
            }
        });
        return Seq::from(...$xs);
    }

    /**
     * テスト用の事業所を生成する.
     *
     * @param int $id
     * @param string $code
     * @return \Domain\Office\Office
     */
    private function office(int $id, string $code): Office
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
            'dwsGenericService' => OfficeDwsGenericService::create([]),
            'dwsCommAccompanyService' => OfficeDwsCommAccompanyService::create([]),
            'ltcsCareManagementService' => OfficeLtcsCareManagementService::create([
                'code' => $code,
                'openedOn' => Carbon::create(2008, 5, 17),
                'designationExpiredOn' => Carbon::create(2022, 12, 31),
                'ltcsAreaGradeId' => 1,
            ]),
            'ltcsHomeVisitLongTermCareService' => OfficeLtcsHomeVisitLongTermCareService::create([
                'code' => $code,
                'openedOn' => Carbon::create(2008, 5, 17),
                'designationExpiredOn' => Carbon::create(2022, 12, 31),
                'ltcsAreaGradeId' => 1,
            ]),
            'ltcsCompHomeVisitingService' => OfficeLtcsCompHomeVisitingService::create([]),
            'status' => OfficeStatus::inOperation(),
            'isEnabled' => true,
            'version' => 1,
            'createdAt' => Carbon::create(2020, 1, 1),
            'updatedAt' => Carbon::create(2020, 1, 1),
        ]);
    }

    /**
     * テスト用の事業所の一覧を生成する.
     *
     * @return \Domain\Office\Office[]&\ScalikePHP\Seq
     */
    private function offices(): Seq
    {
        $xs = call_user_func(function (): iterable {
            foreach ($this->statementExamples as $index => $row) {
                yield $this->office($index + 1, $row[21]);
            }
        });
        return Seq::from(...$xs);
    }

    /**
     * テスト用の利用者：公費情報の一覧を生成する.
     *
     * @return \Domain\User\UserLtcsSubsidy[]&\ScalikePHP\Seq
     */
    private function subsidies(): Seq
    {
        return Seq::from(
            UserLtcsSubsidy::create([
                'id' => 1,
                'userId' => 7,
                'period' => CarbonRange::create([
                    'start' => Carbon::create(2000, 1, 1),
                    'end' => Carbon::create(2022, 12, 13),
                ]),
                'defrayerCategory' => DefrayerCategory::livelihoodProtection(),
                'defrayerNumber' => '12132015',
                'recipientNumber' => '2476729',
                'benefitRate' => 100,
                'copay' => 0,
                'isEnabled' => true,
                'version' => 1,
                'createdAt' => Carbon::create(2000, 1, 1),
                'updatedAt' => Carbon::create(2000, 1, 1),
            ]),
            UserLtcsSubsidy::create([
                'id' => 2,
                'userId' => 17,
                'period' => CarbonRange::create([
                    'start' => Carbon::create(2000, 1, 1),
                    'end' => Carbon::create(2022, 12, 13),
                ]),
                'defrayerCategory' => DefrayerCategory::livelihoodProtection(),
                'defrayerNumber' => '12132015',
                'recipientNumber' => '4608212',
                'benefitRate' => 100,
                'copay' => 0,
                'isEnabled' => true,
                'version' => 1,
                'createdAt' => Carbon::create(2000, 1, 1),
                'updatedAt' => Carbon::create(2000, 1, 1),
            ]),
        );
    }

    /**
     * サービスコードからサービスコード区分を特定する.
     *
     * @param string $serviceCodeString
     * @return \Domain\ServiceCodeDictionary\LtcsServiceCodeCategory
     */
    private function resolveServiceCodeCategory(string $serviceCodeString): LtcsServiceCodeCategory
    {
        return match ($serviceCodeString) {
            '111111',
            '111112',
            '111211',
            '114845',
            '114846' => LtcsServiceCodeCategory::physicalCare(),
            '114111',
            '114112',
            '116111',
            '116135' => LtcsServiceCodeCategory::physicalCareAndHousework(),
            '116275' => LtcsServiceCodeCategory::treatmentImprovementAddition1(),
            '117211',
            '117311' => LtcsServiceCodeCategory::housework(),
            default => throw new LogicException("Unsupported service code: {$serviceCodeString}"),
        };
    }
}

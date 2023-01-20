<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\User;

use Domain\BankAccount\BankAccount;
use Domain\BankAccount\BankAccountRepository;
use Domain\BankAccount\BankAccountType;
use Domain\Billing\DwsServiceDivisionCode;
use Domain\Billing\LtcsExpiredReason;
use Domain\Common\Addr;
use Domain\Common\Carbon;
use Domain\Common\Contact;
use Domain\Common\ContactRelationship;
use Domain\Common\Location;
use Domain\Common\Prefecture;
use Domain\Common\ServiceSegment;
use Domain\Common\Sex;
use Domain\Common\StructuredName;
use Domain\Context\Context;
use Domain\Contract\Contract;
use Domain\Contract\ContractPeriod;
use Domain\Contract\ContractRepository;
use Domain\Contract\ContractStatus;
use Domain\DwsCertification\Child;
use Domain\DwsCertification\CopayCoordination;
use Domain\DwsCertification\CopayCoordinationType;
use Domain\DwsCertification\DwsCertification;
use Domain\DwsCertification\DwsCertificationAgreement;
use Domain\DwsCertification\DwsCertificationAgreementType;
use Domain\DwsCertification\DwsCertificationRepository;
use Domain\DwsCertification\DwsCertificationStatus;
use Domain\DwsCertification\DwsLevel;
use Domain\DwsCertification\DwsType;
use Domain\File\ReadonlyFileStorage;
use Domain\LtcsInsCard\LtcsCarePlanAuthorType;
use Domain\LtcsInsCard\LtcsInsCard;
use Domain\LtcsInsCard\LtcsInsCardRepository;
use Domain\LtcsInsCard\LtcsInsCardStatus;
use Domain\LtcsInsCard\LtcsLevel;
use Domain\Organization\Organization;
use Domain\TransactionManager;
use Domain\TransactionManagerFactory;
use Domain\User\User;
use Domain\User\UserRepository;
use Lib\Csv;
use Lib\Exceptions\NotFoundException;
use ScalikePHP\Seq;

/**
 * 利用者 CSV 一括インポートユースケース実装.
 *
 * @codeCoverageIgnore テスト作業用のため
 */
final class ImportUserInteractor implements ImportUserUseCase
{
    private const COLUMN_FAMILY_NAME = 0;
    private const COLUMN_GIVEN_NAME = 1;
    private const COLUMN_PHONETIC_FAMILY_NAME = 2;
    private const COLUMN_PHONETIC_GIVEN_NAME = 3;
    private const COLUMN_SEX = 4;
    private const COLUMN_BIRTHDAY = 5;
    private const COLUMN_POSTCODE = 6;
    private const COLUMN_PREFECTURE = 7;
    private const COLUMN_CITY = 8;
    private const COLUMN_STREET = 9;
    private const COLUMN_APARTMENT = 10;
    private const COLUMN_TEL1 = 11;
    private const COLUMN_TEL2 = 12;
    private const COLUMN_TEL3 = 13;
    private const COLUMN_LAT = 14;
    private const COLUMN_LNG = 15;

    private const COLUMN_DWS_ENABLED = 16;
    private const COLUMN_LTCS_ENABLED = 17;
    private const COLUMN_MBS_CUSTOMER_CODE = 18;

    private const COLUMN_BANK_CODE = 19;
    private const COLUMN_BANK_NAME = 20;
    private const COLUMN_BANK_BRANCH_CODE = 21;
    private const COLUMN_BANK_BRANCH_NAME = 22;
    private const COLUMN_BANK_ACCOUNT_TYPE = 24;
    private const COLUMN_BANK_ACCOUNT_NUMBER = 25;
    private const COLUMN_BANK_ACCOUNT_HOLDER = 26;

    private const COLUMN_DWS_EFFECTIVATED_ON = 27;
    private const COLUMN_DWS_STATUS = 29;
    private const COLUMN_DWS_NUMBER = 30;
    private const COLUMN_DWS_TYPE_PHYSICAL = 31;
    private const COLUMN_DWS_TYPE_INTELLECTUAL = 32;
    private const COLUMN_DWS_TYPE_MENTAL = 33;
    private const COLUMN_DWS_TYPE_INTRACTABLE_DISEASES = 34;
    private const COLUMN_DWS_ISSUED_ON = 35;
    private const COLUMN_DWS_CITY_NAME = 36;
    private const COLUMN_DWS_CITY_CODE = 37;
    private const COLUMN_DWS_LEVEL = 39;
    private const COLUMN_DWS_ACTIVATED_ON = 40;
    private const COLUMN_DWS_DEACTIVATED_ON = 41;
    private const COLUMN_DWS_COPAY_RATE = 42;
    private const COLUMN_DWS_COPAY_LIMIT = 43;
    private const COLUMN_DWS_COPAY_ACTIVATED_ON = 44;
    private const COLUMN_DWS_COPAY_DEACTIVATED_ON = 45;
    private const COLUMN_DWS_COPAY_COORDINATION_TYPE = 47;
    private const COLUMN_DWS_COPAY_COORDINATION_OFFICE_ID = 49;
    private const COLUMN_DWS_AGREEMENT1_INDEX_NUMBER = 50;
    private const COLUMN_DWS_AGREEMENT1_TYPE = 52;
    private const COLUMN_DWS_AGREEMENT1_PAYMENT_AMOUNT = 53;
    private const COLUMN_DWS_AGREEMENT1_AGREED_ON = 54;
    private const COLUMN_DWS_AGREEMENT1_EXPIRED_ON = 55;
    private const COLUMN_DWS_AGREEMENT2_INDEX_NUMBER = 56;
    private const COLUMN_DWS_AGREEMENT2_TYPE = 58;
    private const COLUMN_DWS_AGREEMENT2_PAYMENT_AMOUNT = 59;
    private const COLUMN_DWS_AGREEMENT2_AGREED_ON = 60;
    private const COLUMN_DWS_AGREEMENT2_EXPIRED_ON = 61;

    private const COLUMN_LTCS_EFFECTIVATED_ON = 62;
    private const COLUMN_LTCS_STATUS = 64;
    private const COLUMN_LTCS_INS_NUMBER = 65;
    private const COLUMN_LTCS_ISSUED_ON = 66;
    private const COLUMN_LTCS_INSURER_NUMBER = 67;
    private const COLUMN_LTCS_INSURER_NAME = 68;
    private const COLUMN_LTCS_LEVEL = 70;
    private const COLUMN_LTCS_CERTIFICATED_ON = 71;
    private const COLUMN_LTCS_ACTIVATED_ON = 72;
    private const COLUMN_LTCS_DEACTIVATED_ON = 73;
    private const COLUMN_LTCS_COPAY_RATE = 74;
    private const COLUMN_LTCS_COPAY_ACTIVATED_ON = 75;
    private const COLUMN_LTCS_COPAY_DEACTIVATED_ON = 76;
    private const COLUMN_LTCS_CARE_PLAN_AUTHOR_TYPE = 78;
    private const COLUMN_LTCS_CARE_PLAN_AUTHOR_OFFICE_ID = 80;

    private const COLUMN_OFFICE_ID = 82;
    private const COLUMN_CONTRACTED_ON = 83;

    private const COLUMN_DWS_HOME_HELP_SERVICE_PERIOD_START = 84;
    private const COLUMN_DWS_HOME_HELP_SERVICE_PERIOD_END = 85;
    private const COLUMN_DWS_VISITING_CARE_FOR_PWSD_PERIOD_START = 86;
    private const COLUMN_DWS_VISITING_CARE_FOR_PWSD_PERIOD_END = 87;
    private const COLUMN_LTCS_PERIOD_START = 88;
    private const COLUMN_LTCS_PERIOD_END = 89;

    private ReadonlyFileStorage $storage;
    private BankAccountRepository $bankAccountRepository;
    private UserRepository $userRepository;
    private ContractRepository $contractRepository;
    private DwsCertificationRepository $dwsCertificationRepository;
    private LtcsInsCardRepository $ltcsInsCardRepository;
    private TransactionManager $transaction;

    /**
     * {@link \UseCase\User\ImportUserInteractor} constructor.
     *
     * @param \Domain\File\ReadonlyFileStorage $storage
     * @param \Domain\BankAccount\BankAccountRepository $bankAccountRepository
     * @param \Domain\User\UserRepository $userRepository
     * @param \Domain\Contract\ContractRepository $contractRepository
     * @param \Domain\DwsCertification\DwsCertificationRepository $dwsCertificationRepository
     * @param \Domain\LtcsInsCard\LtcsInsCardRepository $ltcsInsCardRepository
     * @param \Domain\TransactionManagerFactory $factory
     */
    public function __construct(
        ReadonlyFileStorage $storage,
        BankAccountRepository $bankAccountRepository,
        UserRepository $userRepository,
        ContractRepository $contractRepository,
        DwsCertificationRepository $dwsCertificationRepository,
        LtcsInsCardRepository $ltcsInsCardRepository,
        TransactionManagerFactory $factory
    ) {
        $this->storage = $storage;
        $this->bankAccountRepository = $bankAccountRepository;
        $this->userRepository = $userRepository;
        $this->contractRepository = $contractRepository;
        $this->dwsCertificationRepository = $dwsCertificationRepository;
        $this->ltcsInsCardRepository = $ltcsInsCardRepository;
        $this->transaction = $factory->factory(
            $bankAccountRepository,
            $userRepository,
            $contractRepository,
            $dwsCertificationRepository,
            $ltcsInsCardRepository,
        );
    }

    /** {@inheritdoc} */
    public function handle(Context $context, string $filepath): void
    {
        $this->transaction->run(function () use ($context, $filepath): void {
            $now = Carbon::now();
            $this->seedUsers($context, $filepath, $now);
        });
    }

    /**
     * CSV からデータを読み込んで利用者を登録する.
     *
     * @param \Domain\Context\Context $context
     * @param string $filepath
     * @param \Domain\Common\Carbon $now
     * @return void
     */
    private function seedUsers(Context $context, string $filepath, Carbon $now): void
    {
        /** @var \SplFileInfo $file */
        $file = $this->storage->fetch($filepath)->getOrElse(function () use ($filepath): void {
            throw new NotFoundException("File({$filepath}) not found on storage");
        });
        $csv = Csv::read($file->getPathname())->drop(1); // ヘッダ行を除く
        foreach ($csv as $row) {
            $organization = $context->organization;
            $bankAccount = $this->storeBankAccount($row, $now);
            $user = $this->storeUser($row, $organization, $bankAccount, $now);
            $this->storeContracts($row, $organization, $user, $now);
            $this->storeDwsCertification($row, $user, $now);
            $this->storeLtcsInsCard($row, $user, $now);
        }
    }

    /**
     * 銀行口座を登録する.
     *
     * @param array $row
     * @param \Domain\Common\Carbon $now
     * @return \Domain\BankAccount\BankAccount
     */
    private function storeBankAccount(array $row, Carbon $now): BankAccount
    {
        $bankAccount = BankAccount::create([
            'bankCode' => $row[self::COLUMN_BANK_CODE],
            'bankName' => $row[self::COLUMN_BANK_NAME],
            'bankBranchCode' => $row[self::COLUMN_BANK_BRANCH_CODE],
            'bankBranchName' => $row[self::COLUMN_BANK_BRANCH_NAME],
            'bankAccountType' => empty($row[self::COLUMN_BANK_ACCOUNT_TYPE])
                ? BankAccountType::unknown()
                : BankAccountType::from(+$row[self::COLUMN_BANK_ACCOUNT_TYPE]),
            'bankAccountNumber' => $row[self::COLUMN_BANK_ACCOUNT_NUMBER],
            'bankAccountHolder' => $row[self::COLUMN_BANK_ACCOUNT_HOLDER],
            'version' => 1,
            'createdAt' => $now,
            'updatedAt' => $now,
        ]);
        return $this->bankAccountRepository->store($bankAccount);
    }

    /**
     * 利用者を登録する.
     *
     * @param array $row
     * @param \Domain\Organization\Organization $organization
     * @param \Domain\BankAccount\BankAccount $bankAccount
     * @param \Domain\Common\Carbon $now
     * @return \Domain\User\User
     */
    private function storeUser(array $row, Organization $organization, BankAccount $bankAccount, Carbon $now): User
    {
        $user = User::create([
            'organizationId' => $organization->id,
            'name' => new StructuredName(
                familyName: $row[self::COLUMN_FAMILY_NAME],
                givenName: $row[self::COLUMN_GIVEN_NAME],
                phoneticFamilyName: mb_convert_kana($row[self::COLUMN_PHONETIC_FAMILY_NAME], 'asCKV'),
                phoneticGivenName: mb_convert_kana($row[self::COLUMN_PHONETIC_GIVEN_NAME], 'asCKV'),
            ),
            'sex' => Sex::from(+$row[self::COLUMN_SEX]),
            'birthday' => Carbon::parse($row[self::COLUMN_BIRTHDAY]),
            'addr' => new Addr(
                postcode: $row[self::COLUMN_POSTCODE],
                prefecture: Prefecture::from(+$row[self::COLUMN_PREFECTURE]),
                city: $row[self::COLUMN_CITY],
                street: $row[self::COLUMN_STREET],
                apartment: $row[self::COLUMN_APARTMENT],
            ),
            'contacts' => Seq::from($row[self::COLUMN_TEL1], $row[self::COLUMN_TEL2], $row[self::COLUMN_TEL3])
                ->filter(fn (string $tel): bool => !empty($tel))
                ->map(fn (string $tel): Contact => Contact::create([
                    'tel' => $tel,
                    'relationship' => ContactRelationship::theirself(),
                    'name' => '',
                ]))
                ->toArray(),
            'location' => Location::create([
                'lat' => +$row[self::COLUMN_LAT],
                'lng' => +$row[self::COLUMN_LNG],
            ]),
            'bankAccountId' => $bankAccount->id,
            'isEnabled' => true,
            'version' => 1,
            'createdAt' => $now,
            'updatedAt' => $now,
        ]);
        return $this->userRepository->store($user);
    }

    /**
     * 契約を登録する.
     *
     * @param $row
     * @param \Domain\Organization\Organization $organization
     * @param \Domain\User\User $user
     * @param \Domain\Common\Carbon $now
     * @return void
     */
    private function storeContracts($row, Organization $organization, User $user, Carbon $now): void
    {
        /** @var \Domain\Common\ServiceSegment[] $segments */
        $segments = [
            ...(empty($row[self::COLUMN_DWS_ENABLED]) ? [] : [ServiceSegment::disabilitiesWelfare()]),
            ...(empty($row[self::COLUMN_LTCS_ENABLED]) ? [] : [ServiceSegment::longTermCare()]),
        ];
        foreach ($segments as $segment) {
            $dwsPeriods = $segment === ServiceSegment::disabilitiesWelfare()
                ? [
                    DwsServiceDivisionCode::homeHelpService()->value() => ContractPeriod::create([
                        'start' => Carbon::parseOption($row[self::COLUMN_DWS_HOME_HELP_SERVICE_PERIOD_START])->orNull(),
                        'end' => Carbon::parseOption($row[self::COLUMN_DWS_HOME_HELP_SERVICE_PERIOD_END])->orNull(),
                    ]),
                    DwsServiceDivisionCode::visitingCareForPwsd()->value() => ContractPeriod::create([
                        'start' => Carbon::parseOption($row[self::COLUMN_DWS_VISITING_CARE_FOR_PWSD_PERIOD_START])->orNull(),
                        'end' => Carbon::parseOption($row[self::COLUMN_DWS_VISITING_CARE_FOR_PWSD_PERIOD_END])->orNull(),
                    ]),
                ]
                : [];
            $ltcsPeriod = $segment === ServiceSegment::longTermCare()
                ? ContractPeriod::create([
                    'start' => Carbon::parseOption($row[self::COLUMN_LTCS_PERIOD_START])->orNull(),
                    'end' => Carbon::parseOption($row[self::COLUMN_LTCS_PERIOD_END])->orNull(),
                ])
                : ContractPeriod::create([]);
            $contract = Contract::create([
                'organizationId' => $organization->id,
                'userId' => $user->id,
                'officeId' => +$row[82],
                'serviceSegment' => $segment,
                'status' => ContractStatus::formal(),
                'contractedOn' => Carbon::parse($row[self::COLUMN_CONTRACTED_ON]),
                'terminatedOn' => null,
                'dwsPeriods' => $dwsPeriods,
                'ltcsPeriod' => $ltcsPeriod,
                'expiredReason' => LtcsExpiredReason::unspecified(),
                'note' => '',
                'isEnabled' => true,
                'version' => 1,
                'createdAt' => $now,
                'updatedAt' => $now,
            ]);
            $this->contractRepository->store($contract);
        }
    }

    /**
     * 障害福祉サービス受給者証を登録する.
     *
     * @param array $row
     * @param \Domain\User\User $user
     * @param \Domain\Common\Carbon $now
     * @return void
     */
    private function storeDwsCertification(array $row, User $user, Carbon $now): void
    {
        if (!empty($row[16]) && !empty($row[27])) {
            $certification = DwsCertification::create([
                'userId' => $user->id,
                'effectivatedOn' => Carbon::parse($row[self::COLUMN_DWS_EFFECTIVATED_ON]),
                'status' => DwsCertificationStatus::from(+$row[self::COLUMN_DWS_STATUS]),
                'dwsNumber' => $row[self::COLUMN_DWS_NUMBER],
                'dwsTypes' => [
                    ...(empty($row[self::COLUMN_DWS_TYPE_PHYSICAL]) ? [] : [DwsType::physical()]),
                    ...(empty($row[self::COLUMN_DWS_TYPE_INTELLECTUAL]) ? [] : [DwsType::intellectual()]),
                    ...(empty($row[self::COLUMN_DWS_TYPE_MENTAL]) ? [] : [DwsType::mental()]),
                    ...(empty($row[self::COLUMN_DWS_TYPE_INTRACTABLE_DISEASES]) ? [] : [DwsType::intractableDiseases()]),
                ],
                'issuedOn' => Carbon::parse($row[self::COLUMN_DWS_ISSUED_ON]),
                'cityName' => $row[self::COLUMN_DWS_CITY_NAME],
                'cityCode' => $row[self::COLUMN_DWS_CITY_CODE],
                'dwsLevel' => DwsLevel::from(+$row[self::COLUMN_DWS_LEVEL]),
                'isSubjectOfComprehensiveSupport' => false,
                'activatedOn' => Carbon::parse($row[self::COLUMN_DWS_ACTIVATED_ON]),
                'deactivatedOn' => Carbon::parse($row[self::COLUMN_DWS_DEACTIVATED_ON]),
                'grants' => [],
                'child' => Child::create([
                    'name' => StructuredName::empty(),
                    'birthday' => null,
                ]),
                'copayRate' => +$row[self::COLUMN_DWS_COPAY_RATE],
                'copayLimit' => +$row[self::COLUMN_DWS_COPAY_LIMIT],
                'copayActivatedOn' => Carbon::parse($row[self::COLUMN_DWS_COPAY_ACTIVATED_ON]),
                'copayDeactivatedOn' => Carbon::parse($row[self::COLUMN_DWS_COPAY_DEACTIVATED_ON]),
                'copayCoordination' => CopayCoordination::create([
                    'copayCoordinationType' => CopayCoordinationType::from(+$row[self::COLUMN_DWS_COPAY_COORDINATION_TYPE]),
                    'officeId' => empty($row[self::COLUMN_DWS_COPAY_COORDINATION_OFFICE_ID])
                        ? null
                        : +$row[self::COLUMN_DWS_COPAY_COORDINATION_OFFICE_ID],
                ]),
                'agreements' => [
                    ...(empty($row[self::COLUMN_DWS_AGREEMENT1_INDEX_NUMBER]) ? [] : [
                        DwsCertificationAgreement::create([
                            'indexNumber' => +$row[self::COLUMN_DWS_AGREEMENT1_INDEX_NUMBER],
                            'officeId' => +$row[self::COLUMN_OFFICE_ID],
                            'dwsCertificationAgreementType' => DwsCertificationAgreementType::from(
                                +$row[self::COLUMN_DWS_AGREEMENT1_TYPE]
                            ),
                            'paymentAmount' => +$row[self::COLUMN_DWS_AGREEMENT1_PAYMENT_AMOUNT],
                            'agreedOn' => Carbon::parse($row[self::COLUMN_DWS_AGREEMENT1_AGREED_ON]),
                            'expiredOn' => empty($row[self::COLUMN_DWS_AGREEMENT1_EXPIRED_ON])
                                ? null
                                : Carbon::parse($row[self::COLUMN_DWS_AGREEMENT1_EXPIRED_ON]),
                        ]),
                    ]),
                    ...(empty($row[self::COLUMN_DWS_AGREEMENT2_INDEX_NUMBER]) ? [] : [
                        DwsCertificationAgreement::create([
                            'indexNumber' => +$row[self::COLUMN_DWS_AGREEMENT2_INDEX_NUMBER],
                            'officeId' => +$row[self::COLUMN_OFFICE_ID],
                            'dwsCertificationAgreementType' => DwsCertificationAgreementType::from(
                                +$row[self::COLUMN_DWS_AGREEMENT2_TYPE]
                            ),
                            'paymentAmount' => +$row[self::COLUMN_DWS_AGREEMENT2_PAYMENT_AMOUNT],
                            'agreedOn' => Carbon::parse($row[self::COLUMN_DWS_AGREEMENT2_AGREED_ON]),
                            'expiredOn' => Carbon::parseOption($row[self::COLUMN_DWS_AGREEMENT2_EXPIRED_ON])->orNull(),
                        ]),
                    ]),
                ],
                'isEnabled' => true,
                'version' => 1,
                'createdAt' => $now,
                'updatedAt' => $now,
            ]);
            $this->dwsCertificationRepository->store($certification);
        }
    }

    /**
     * 介護保険被保険者証を登録する.
     *
     * @param array $row
     * @param \Domain\User\User $user
     * @param \Domain\Common\Carbon $now
     * @return void
     */
    private function storeLtcsInsCard(array $row, User $user, Carbon $now): void
    {
        if (!empty($row[self::COLUMN_LTCS_ENABLED]) && !empty($row[self::COLUMN_LTCS_EFFECTIVATED_ON])) {
            $insCard = LtcsInsCard::create([
                'userId' => $user->id,
                'effectivatedOn' => Carbon::parse($row[self::COLUMN_LTCS_EFFECTIVATED_ON]),
                'status' => LtcsInsCardStatus::from(+$row[self::COLUMN_LTCS_STATUS]),
                'insNumber' => $row[self::COLUMN_LTCS_INS_NUMBER],
                'issuedOn' => Carbon::parse($row[self::COLUMN_LTCS_ISSUED_ON]),
                'insurerNumber' => $row[self::COLUMN_LTCS_INSURER_NUMBER],
                'insurerName' => $row[self::COLUMN_LTCS_INSURER_NAME],
                'ltcsLevel' => LtcsLevel::from(+$row[self::COLUMN_LTCS_LEVEL]),
                'certificatedOn' => Carbon::parse($row[self::COLUMN_LTCS_CERTIFICATED_ON]),
                'activatedOn' => Carbon::parse($row[self::COLUMN_LTCS_ACTIVATED_ON]),
                'deactivatedOn' => Carbon::parse($row[self::COLUMN_LTCS_DEACTIVATED_ON]),
                'maxBenefitQuotas' => [],
                'copayRate' => +$row[self::COLUMN_LTCS_COPAY_RATE],
                'copayActivatedOn' => Carbon::parse($row[self::COLUMN_LTCS_COPAY_ACTIVATED_ON]),
                'copayDeactivatedOn' => Carbon::parse($row[self::COLUMN_LTCS_COPAY_DEACTIVATED_ON]),
                'carePlanAuthorType' => LtcsCarePlanAuthorType::from(+$row[self::COLUMN_LTCS_CARE_PLAN_AUTHOR_TYPE]),
                'carePlanAuthorOfficeId' => empty($row[self::COLUMN_LTCS_CARE_PLAN_AUTHOR_OFFICE_ID])
                    ? null
                    : +$row[self::COLUMN_LTCS_CARE_PLAN_AUTHOR_OFFICE_ID],
                'isEnabled' => true,
                'version' => 1,
                'createdAt' => $now,
                'updatedAt' => $now,
            ]);
            $this->ltcsInsCardRepository->store($insCard);
        }
    }
}

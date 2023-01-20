<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Office;

use Domain\Common\Addr;
use Domain\Common\Carbon;
use Domain\Common\CarbonRange;
use Domain\Common\Location;
use Domain\Common\Prefecture;
use Domain\Context\Context;
use Domain\File\ReadonlyFileStorage;
use Domain\Office\DwsSpecifiedTreatmentImprovementAddition;
use Domain\Office\DwsTreatmentImprovementAddition;
use Domain\Office\HomeHelpServiceCalcSpec;
use Domain\Office\HomeHelpServiceCalcSpecRepository;
use Domain\Office\HomeHelpServiceSpecifiedOfficeAddition;
use Domain\Office\HomeVisitLongTermCareCalcSpec;
use Domain\Office\HomeVisitLongTermCareCalcSpecRepository;
use Domain\Office\HomeVisitLongTermCareSpecifiedOfficeAddition;
use Domain\Office\LtcsOfficeLocationAddition;
use Domain\Office\LtcsSpecifiedTreatmentImprovementAddition;
use Domain\Office\LtcsTreatmentImprovementAddition;
use Domain\Office\Office;
use Domain\Office\OfficeDwsCommAccompanyService;
use Domain\Office\OfficeDwsGenericService;
use Domain\Office\OfficeGroup;
use Domain\Office\OfficeGroupRepository;
use Domain\Office\OfficeLtcsCareManagementService;
use Domain\Office\OfficeLtcsCompHomeVisitingService;
use Domain\Office\OfficeLtcsHomeVisitLongTermCareService;
use Domain\Office\OfficeQualification;
use Domain\Office\OfficeRepository;
use Domain\Office\OfficeStatus;
use Domain\Office\Purpose;
use Domain\Office\VisitingCareForPwsdCalcSpec;
use Domain\Office\VisitingCareForPwsdCalcSpecRepository;
use Domain\Office\VisitingCareForPwsdSpecifiedOfficeAddition;
use Domain\TransactionManager;
use Domain\TransactionManagerFactory;
use Lib\Csv;
use Lib\Exceptions\NotFoundException;
use ScalikePHP\Seq;

/**
 * 事業所 CSV 一括インポートユースケース実装.
 */
final class ImportOfficeInteractor implements ImportOfficeUseCase
{
    private ReadonlyFileStorage $storage;
    private OfficeRepository $officeRepository;
    private OfficeGroupRepository $officeGroupRepository;
    private HomeHelpServiceCalcSpecRepository $homeHelpServiceCalcSpecRepository;
    private VisitingCareForPwsdCalcSpecRepository $visitingCareForPwsdCalcSpecRepository;
    private HomeVisitLongTermCareCalcSpecRepository $homeVisitLongTermCareCalcSpecRepository;
    private TransactionManager $transaction;

    /**
     * {@link \UseCase\Office\ImportOfficeInteractor} constructor.
     *
     * @param \Domain\File\ReadonlyFileStorage $storage
     * @param \Domain\Office\OfficeRepository $officeRepository
     * @param \Domain\Office\OfficeGroupRepository $officeGroupRepository
     * @param \Domain\Office\HomeHelpServiceCalcSpecRepository $homeHelpServiceCalcSpecRepository
     * @param \Domain\Office\VisitingCareForPwsdCalcSpecRepository $visitingCareForPwsdCalcSpecRepository
     * @param \Domain\Office\HomeVisitLongTermCareCalcSpecRepository $homeVisitLongTermCareCalcSpecRepository
     * @param \Domain\TransactionManagerFactory $factory
     */
    public function __construct(
        ReadonlyFileStorage $storage,
        OfficeRepository $officeRepository,
        OfficeGroupRepository $officeGroupRepository,
        HomeHelpServiceCalcSpecRepository $homeHelpServiceCalcSpecRepository,
        VisitingCareForPwsdCalcSpecRepository $visitingCareForPwsdCalcSpecRepository,
        HomeVisitLongTermCareCalcSpecRepository $homeVisitLongTermCareCalcSpecRepository,
        TransactionManagerFactory $factory
    ) {
        $this->storage = $storage;
        $this->officeRepository = $officeRepository;
        $this->officeGroupRepository = $officeGroupRepository;
        $this->homeHelpServiceCalcSpecRepository = $homeHelpServiceCalcSpecRepository;
        $this->visitingCareForPwsdCalcSpecRepository = $visitingCareForPwsdCalcSpecRepository;
        $this->homeVisitLongTermCareCalcSpecRepository = $homeVisitLongTermCareCalcSpecRepository;
        $this->transaction = $factory->factory(
            $officeRepository,
            $officeGroupRepository,
            $homeHelpServiceCalcSpecRepository,
            $visitingCareForPwsdCalcSpecRepository,
            $homeVisitLongTermCareCalcSpecRepository
        );
    }

    /** {@inheritdoc} */
    public function handle(Context $context, string $filepath): void
    {
        $this->transaction->run(function () use ($context, $filepath): void {
            $now = Carbon::now();
            $group = $this->seedOfficeGroup($context, $now);
            $this->seedOffices($context, $filepath, $now, $group);
        });
    }

    /**
     * 事業所グループを登録する.
     *
     * @param \Domain\Context\Context $context
     * @param \Domain\Common\Carbon $now
     * @return \Domain\Office\OfficeGroup
     */
    private function seedOfficeGroup(Context $context, Carbon $now): OfficeGroup
    {
        $group = OfficeGroup::create([
            'organizationId' => $context->organization->id,
            'parentOfficeGroupId' => null,
            'name' => '未分類',
            'sortOrder' => 2147483647,
            'createdAt' => $now,
            'updatedAt' => $now,
        ]);
        return $this->officeGroupRepository->store($group);
    }

    /**
     * CSV からデータを読み込んで事業所を登録する.
     *
     * @param \Domain\Context\Context $context
     * @param string $filepath
     * @param \Domain\Common\Carbon $now
     * @param \Domain\Office\OfficeGroup $group
     * @return void
     */
    private function seedOffices(Context $context, string $filepath, Carbon $now, OfficeGroup $group): void
    {
        /** @var \SplFileInfo $file */
        $file = $this->storage->fetch($filepath)->getOrElse(function () use ($filepath): void {
            throw new NotFoundException("File({$filepath}) not found on storage");
        });
        $csv = Csv::read($file->getPathname())->drop(1); // ヘッダ行を除く
        foreach ($csv as $row) {
            $office = $this->storeOffice($context, $row, $now, $group);
            $this->storeHomeHelpServiceCalcSpec($office, $row, $now);
            $this->storeVisitingCareForPwsdCalcSpec($office, $row, $now);
            $this->storeHomeVisitLongTermCareCalcSpec($office, $row, $now);
        }
    }

    /**
     * 事業所を登録する.
     *
     * @param \Domain\Context\Context $context
     * @param array $row
     * @param \Domain\Common\Carbon $now
     * @param \Domain\Office\OfficeGroup $group
     * @return \Domain\Office\Office
     */
    private function storeOffice(Context $context, array $row, Carbon $now, OfficeGroup $group): Office
    {
        $purpose = Purpose::from(+$row[3]);
        $qualifications = Seq::from(
            ...($row[17] ? [OfficeQualification::dwsHomeHelpService()] : []),
            ...($row[18] ? [OfficeQualification::dwsVisitingCareForPwsd()] : []),
            ...($row[19] ? [OfficeQualification::dwsCommAccompany()] : []),
            ...($row[20] ? [OfficeQualification::dwsOthers()] : []),
            ...($row[21] ? [OfficeQualification::ltcsHomeVisitLongTermCare()] : []),
            ...($row[22] ? [OfficeQualification::ltcsCareManagement()] : []),
            ...($row[23] ? [OfficeQualification::ltcsCompHomeVisiting()] : []),
            ...($row[24] ? [OfficeQualification::ltcsOthers()] : []),
        );
        $isInternal = $purpose === Purpose::internal();
        $isExternal = $purpose === Purpose::external();
        $hasDwsGenericService = $qualifications->contains(OfficeQualification::dwsHomeHelpService())
            || $qualifications->contains(OfficeQualification::dwsVisitingCareForPwsd())
            || $qualifications->contains(OfficeQualification::dwsOthers());
        $hasDwsCommAccompanyService = $qualifications->contains(OfficeQualification::dwsCommAccompany());
        $hasLtcsHomeVisitLongTermCareService = $qualifications->contains(OfficeQualification::ltcsHomeVisitLongTermCare());
        $hasLtcsCareManagementService = $qualifications->contains(OfficeQualification::ltcsCareManagement());
        $hasLtcsCompHomeVisitingService = $qualifications->contains(OfficeQualification::ltcsCompHomeVisiting());
        $office = Office::create([
            'id' => +$row[0],
            'organizationId' => $context->organization->id,
            'name' => mb_convert_kana($row[1], 'asKV'),
            'phoneticName' => mb_convert_kana($row[2], 'asCKV'),
            'purpose' => $purpose,
            'abbr' => mb_convert_kana($row[4], 'asKV'),
            'corporationName' => mb_convert_kana($row[5], 'asKV'),
            'phoneticCorporationName' => mb_convert_kana($row[6], 'asCKV'),
            'addr' => new Addr(
                postcode: $row[7],
                prefecture: Prefecture::from(+$row[8]),
                city: $row[9],
                street: $row[10],
                apartment: $row[11],
            ),
            'location' => Location::create([
                'lat' => empty($row[12]) ? 0.0 : +$row[12],
                'lng' => empty($row[13]) ? 0.0 : +$row[13],
            ]),
            'tel' => $row[14],
            'fax' => $row[15],
            'email' => $row[16],
            'officeGroupId' => $isInternal ? $group->id : null,
            'qualifications' => $qualifications->toArray(),
            'dwsGenericService' => $hasDwsGenericService
                ? OfficeDwsGenericService::create([
                    'code' => $row[25],
                    'openedOn' => $isExternal ? null : Carbon::parse($row[26]),
                    'designationExpiredOn' => $isExternal || empty($row[27]) ? null : Carbon::parse($row[27]),
                    'dwsAreaGradeId' => $isExternal ? null : +$row[40],
                ])
                : null,
            'dwsCommAccompanyService' => $hasDwsCommAccompanyService
                ? OfficeDwsCommAccompanyService::create([
                    'code' => $row[28],
                    'openedOn' => $isExternal ? null : Carbon::parse($row[29]),
                    'designationExpiredOn' => $isExternal || empty($row[30]) ? null : Carbon::parse($row[30]),
                ])
                : null,
            'ltcsHomeVisitLongTermCareService' => $hasLtcsHomeVisitLongTermCareService
                ? OfficeLtcsHomeVisitLongTermCareService::create([
                    'code' => $row[31],
                    'openedOn' => $isExternal ? null : Carbon::parse($row[32]),
                    'designationExpiredOn' => $isExternal || empty($row[33]) ? null : Carbon::parse($row[33]),
                    'ltcsAreaGradeId' => $isExternal ? null : +$row[41],
                ])
                : null,
            'ltcsCareManagementService' => $hasLtcsCareManagementService
                ? OfficeLtcsCareManagementService::create([
                    'code' => $row[34],
                    'openedOn' => $isExternal ? null : Carbon::parse($row[35]),
                    'designationExpiredOn' => $isExternal || empty($row[36]) ? null : Carbon::parse($row[36]),
                ])
                : null,
            'ltcsCompHomeVisitingService' => $hasLtcsCompHomeVisitingService
                ? OfficeLtcsCompHomeVisitingService::create([
                    'code' => $row[37],
                    'openedOn' => $isExternal ? null : Carbon::parse($row[38]),
                    'designationExpiredOn' => $isExternal || empty($row[39]) ? null : Carbon::parse($row[39]),
                ])
                : null,
            'status' => OfficeStatus::inOperation(),
            'isEnabled' => true,
            'version' => 1,
            'createdAt' => $now,
            'updatedAt' => $now,
        ]);
        return $this->officeRepository->store($office);
    }

    /**
     * 障害福祉サービス：居宅介護：算定情報を登録する.
     *
     * @param \Domain\Office\Office $office
     * @param array $row
     * @param \Domain\Common\Carbon $now
     */
    private function storeHomeHelpServiceCalcSpec(Office $office, array $row, Carbon $now): void
    {
        if (!empty($row[42])) {
            $spec = HomeHelpServiceCalcSpec::create([
                'officeId' => $office->id,
                'period' => CarbonRange::create([
                    'start' => Carbon::parse($row[42]),
                    'end' => Carbon::create(2021, 12, 31),
                ]),
                'specifiedOfficeAddition' => HomeHelpServiceSpecifiedOfficeAddition::from(+$row[43]),
                'treatmentImprovementAddition' => DwsTreatmentImprovementAddition::from(+$row[44]),
                'specifiedTreatmentImprovementAddition' => DwsSpecifiedTreatmentImprovementAddition::from(+$row[45]),
                'isEnabled' => true,
                'version' => 1,
                'createdAt' => $now,
                'updatedAt' => $now,
            ]);
            $this->homeHelpServiceCalcSpecRepository->store($spec);
        }
    }

    /**
     * 障害福祉サービス：重度訪問介護：算定情報を登録する.
     *
     * @param \Domain\Office\Office $office
     * @param array $row
     * @param \Domain\Common\Carbon $now
     */
    private function storeVisitingCareForPwsdCalcSpec(Office $office, array $row, Carbon $now): void
    {
        if (!empty($row[46])) {
            $spec = VisitingCareForPwsdCalcSpec::create([
                'officeId' => $office->id,
                'period' => CarbonRange::create([
                    'start' => Carbon::parse($row[46]),
                    'end' => Carbon::create(2021, 12, 31),
                ]),
                'specifiedOfficeAddition' => VisitingCareForPwsdSpecifiedOfficeAddition::from(+$row[47]),
                'treatmentImprovementAddition' => DwsTreatmentImprovementAddition::from(+$row[48]),
                'specifiedTreatmentImprovementAddition' => DwsSpecifiedTreatmentImprovementAddition::from(+$row[49]),
                'isEnabled' => true,
                'version' => 1,
                'createdAt' => $now,
                'updatedAt' => $now,
            ]);
            $this->visitingCareForPwsdCalcSpecRepository->store($spec);
        }
    }

    /**
     * 介護保険サービス：訪問介護：算定情報を登録する.
     *
     * @param \Domain\Office\Office $office
     * @param array $row
     * @param \Domain\Common\Carbon $now
     */
    private function storeHomeVisitLongTermCareCalcSpec(Office $office, array $row, Carbon $now): void
    {
        if (!empty($row[50])) {
            $spec = HomeVisitLongTermCareCalcSpec::create([
                'officeId' => $office->id,
                'period' => CarbonRange::create([
                    'start' => Carbon::parse($row[50]),
                    'end' => Carbon::create(2021, 12, 31),
                ]),
                'specifiedOfficeAddition' => HomeVisitLongTermCareSpecifiedOfficeAddition::from(+$row[51]),
                'treatmentImprovementAddition' => LtcsTreatmentImprovementAddition::from(+$row[52]),
                'specifiedTreatmentImprovementAddition' => LtcsSpecifiedTreatmentImprovementAddition::from(+$row[53]),
                'locationAddition' => LtcsOfficeLocationAddition::from(+$row[54]),
                'isEnabled' => true,
                'version' => 1,
                'createdAt' => $now,
                'updatedAt' => $now,
            ]);
            $this->homeVisitLongTermCareCalcSpecRepository->store($spec);
        }
    }
}

<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\ProvisionReport;

use Domain\Billing\LtcsBillingServiceDetail;
use Domain\Common\Carbon;
use Domain\Context\Context;
use Domain\LtcsInsCard\LtcsInsCard;
use Domain\Office\Office;
use Domain\Permission\Permission;
use Domain\ProvisionReport\LtcsProvisionReport;
use Domain\ServiceCode\ServiceCode;
use Domain\User\User;
use Lib\Exceptions\NotFoundException;
use ScalikePHP\Map;
use ScalikePHP\Option;
use ScalikePHP\Seq;
use UseCase\Billing\BuildLtcsServiceDetailListUseCase;
use UseCase\File\StorePdfUseCase;
use UseCase\LtcsInsCard\IdentifyLtcsInsCardUseCase;
use UseCase\Office\LookupOfficeUseCase;
use UseCase\ServiceCodeDictionary\ResolveLtcsNameFromServiceCodesUseCase;
use UseCase\User\LookupUserUseCase;

/**
 * サービス提供票 PDF 生成ユースケース実装.
 */
final class GenerateLtcsProvisionReportSheetPdfInteractor implements GenerateLtcsProvisionReportSheetPdfUseCase
{
    private const STORE_TO = 'exported';
    private const TEMPLATE = 'pdfs.ltcs-provision-report-sheet.index';

    /**
     * {@link \UseCase\ProvisionReport\GenerateLtcsProvisionReportSheetPdfUseCase} constructor.
     *
     * @param \UseCase\ProvisionReport\BuildLtcsProvisionReportSheetPdfParamUseCase $buildPdfUseCase
     * @param \UseCase\ProvisionReport\BuildLtcsProvisionReportSheetAppendixPdfParamUseCase $buildAppendixPdfUseCase
     * @param \UseCase\File\StorePdfUseCase $storePdfUseCase
     * @param \UseCase\LtcsInsCard\IdentifyLtcsInsCardUseCase $identifyInsCardUseCase
     * @param \UseCase\Office\LookupOfficeUseCase $lookupOfficeUseCase
     * @param \UseCase\User\LookupUserUseCase $lookupUserUseCase
     * @param \UseCase\ProvisionReport\GetLtcsProvisionReportUseCase $getProvisionReportUseCase
     * @param \UseCase\Billing\BuildLtcsServiceDetailListUseCase $buildLtcsServiceDetailListUseCase
     * @param \UseCase\ServiceCodeDictionary\ResolveLtcsNameFromServiceCodesUseCase $resolveLtcsNameFromServiceCodesUseCase
     */
    public function __construct(
        private BuildLtcsProvisionReportSheetPdfParamUseCase $buildPdfUseCase,
        private BuildLtcsProvisionReportSheetAppendixPdfParamUseCase $buildAppendixPdfUseCase,
        private StorePdfUseCase $storePdfUseCase,
        private IdentifyLtcsInsCardUseCase $identifyInsCardUseCase,
        private LookupOfficeUseCase $lookupOfficeUseCase,
        private LookupUserUseCase $lookupUserUseCase,
        private GetLtcsProvisionReportUseCase $getProvisionReportUseCase,
        private BuildLtcsServiceDetailListUseCase $buildLtcsServiceDetailListUseCase,
        private ResolveLtcsNameFromServiceCodesUseCase $resolveLtcsNameFromServiceCodesUseCase
    ) {
    }

    /** {@inheritdoc} */
    public function handle(
        Context $context,
        int $officeId,
        int $userId,
        Carbon $providedIn,
        Carbon $issuedOn,
        bool $needsMaskingInsNumber,
        bool $needsMaskingInsName
    ): string {
        $office = $this->lookupOffice($context, $officeId);
        $user = $this->lookupUser($context, $userId);
        $insCardAtFirstOfMonth = $this->identifyInsCard($context, $user, $providedIn->firstOfMonth());
        $insCardAtLastOfMonth = $this->identifyInsCard($context, $user, $providedIn->lastOfMonth())
            ->getOrElse(function () use ($providedIn, $user): void {
                throw new NotFoundException(
                    "LtcsInsCard for User({$user->id}) in {$providedIn->lastOfMonth()->toDateString()} not found"
                );
            });
        $provisionReport = $this->getProvisionReport($context, $officeId, $userId, $providedIn);
        $serviceDetails = $this->buildLtcsServiceDetails($context, $providedIn, $provisionReport, Seq::from($user));
        $serviceDetailsForPlan = $this->buildLtcsServiceDetails($context, $providedIn, $provisionReport, Seq::from($user), true);
        $serviceCodeMap = $this->getServiceCodeMap($context, $serviceDetailsForPlan->append($serviceDetails), $providedIn);
        return $this->store(
            $context,
            $provisionReport,
            $insCardAtFirstOfMonth,
            $insCardAtLastOfMonth,
            $office,
            $user,
            $serviceDetails,
            $serviceDetailsForPlan,
            $issuedOn,
            $serviceCodeMap,
            $needsMaskingInsNumber,
            $needsMaskingInsName
        );
    }

    /**
     * PDF を生成して格納する.
     *
     * @param \Domain\Context\Context $context
     * @param \Domain\ProvisionReport\LtcsProvisionReport $provisionReport
     * @param \Domain\LtcsInsCard\LtcsInsCard[]&\ScalikePHP\Option $insCardAtFirstOfMonth
     * @param \Domain\LtcsInsCard\LtcsInsCard $insCardAtLastOfMonth
     * @param \Domain\Office\Office $office
     * @param \Domain\User\User $user
     * @param \Domain\Billing\LtcsBillingServiceDetail[]&\ScalikePHP\Seq $serviceDetails
     * @param \Domain\Billing\LtcsBillingServiceDetail[]&\ScalikePHP\Seq $serviceDetailsForPlan
     * @param Carbon $issuedOn
     * @param \ScalikePHP\Map&string[] $serviceCodeMap
     * @param bool $needsMaskingInsNumber
     * @param bool $needsMaskingInsName
     * @return string
     */
    private function store(
        Context $context,
        LtcsProvisionReport $provisionReport,
        Option $insCardAtFirstOfMonth,
        LtcsInsCard $insCardAtLastOfMonth,
        Office $office,
        User $user,
        Seq $serviceDetails,
        Seq $serviceDetailsForPlan,
        Carbon $issuedOn,
        Map $serviceCodeMap,
        bool $needsMaskingInsNumber,
        bool $needsMaskingInsName
    ): string {
        $mains = $this->buildPdfUseCase->handle(
            $context,
            $insCardAtFirstOfMonth,
            $insCardAtLastOfMonth,
            $serviceDetailsForPlan,
            $serviceDetails,
            $user,
            $issuedOn,
            $provisionReport,
            $office,
            $serviceCodeMap,
            $needsMaskingInsNumber,
            $needsMaskingInsName
        );
        $appendixPdf = $this->buildAppendixPdfUseCase->handle(
            $context,
            $provisionReport,
            $insCardAtFirstOfMonth,
            $insCardAtLastOfMonth,
            $office,
            $user,
            $serviceDetails,
            $serviceCodeMap,
            $needsMaskingInsNumber,
            $needsMaskingInsName
        );

        $params = [
            'mains' => $mains,
            'appendix' => $appendixPdf,
        ];
        return $this->storePdfUseCase->handle(
            $context,
            self::STORE_TO,
            self::TEMPLATE,
            ['sheet' => $params],
            'Landscape'
        );
    }

    /**
     * 利用者を取得する.
     *
     * @param \Domain\Context\Context $context
     * @param int $userId
     * @return \Domain\User\User
     */
    private function lookupUser(Context $context, int $userId): User
    {
        return $this->lookupUserUseCase
            ->handle($context, Permission::updateLtcsProvisionReports(), $userId)
            ->headOption()
            ->getOrElse(function () use ($userId): void {
                throw new NotFoundException("User({$userId}) not found");
            });
    }

    /**
     * 事業所を取得する.
     *
     * @param \Domain\Context\Context $context
     * @param int $officeId
     * @return \Domain\Office\Office
     */
    private function lookupOffice(Context $context, int $officeId): Office
    {
        return $this->lookupOfficeUseCase
            ->handle($context, [Permission::updateLtcsProvisionReports()], $officeId)
            ->headOption()
            ->getOrElse(function () use ($officeId): void {
                throw new NotFoundException("Office({$officeId}) not found");
            });
    }

    /**
     * 介護保険被保険者証を取得する.
     *
     * @param \Domain\Context\Context $context
     * @param \Domain\User\User $user
     * @param \Domain\Common\Carbon $targetDate
     * @return \Domain\LtcsInsCard\LtcsInsCard[]&\ScalikePHP\Option
     */
    private function identifyInsCard(Context $context, User $user, Carbon $targetDate): Option
    {
        return $this->identifyInsCardUseCase
            ->handle($context, $user, $targetDate);
    }

    /**
     * 介護保険サービス：予実を取得する.
     *
     * @param \Domain\Context\Context $context
     * @param int $officeId
     * @param int $userId
     * @param \Domain\Common\Carbon $providedIn
     * @return \Domain\ProvisionReport\LtcsProvisionReport
     */
    private function getProvisionReport(Context $context, int $officeId, int $userId, Carbon $providedIn): LtcsProvisionReport
    {
        return $this->getProvisionReportUseCase
            ->handle($context, Permission::updateLtcsProvisionReports(), $officeId, $userId, $providedIn)
            ->getOrElse(function () use ($officeId, $userId, $providedIn): void {
                throw new NotFoundException("LtcsProvisionReport for Office({$officeId}) and User({$userId}) in {$providedIn} not found");
            });
    }

    /**
     * 介護保険サービス：サービス詳細一覧を取得する.
     *
     * @param \Domain\Context\Context $context
     * @param \Domain\Common\Carbon $providedIn
     * @param \Domain\ProvisionReport\LtcsProvisionReport $provisionReport
     * @param \Domain\User\User[]&\ScalikePHP\Seq $users
     * @param bool $forPlan
     * @return \Domain\Billing\LtcsBillingServiceDetail[]|\ScalikePHP\Seq
     */
    private function buildLtcsServiceDetails(Context $context, Carbon $providedIn, LtcsProvisionReport $provisionReport, Seq $users, bool $forPlan = false): Seq
    {
        return Seq::fromArray($this->buildLtcsServiceDetailListUseCase->handle($context, $providedIn, Seq::from($provisionReport), $users, $forPlan));
    }

    /**
     * サービスコード => サービス名称 の Map を生成する.
     *
     * @param \Domain\Context\Context $context
     * @param \Domain\Billing\LtcsBillingServiceDetail[]&\ScalikePHP\Seq $serviceDetails
     * @param \Domain\Common\Carbon $providedIn
     * @return \ScalikePHP\Map&string[]
     */
    private function getServiceCodeMap(Context $context, Seq $serviceDetails, Carbon $providedIn): Map
    {
        $serviceCodes = $serviceDetails
            ->map(fn (LtcsBillingServiceDetail $x): ServiceCode => $x->serviceCode)
            ->distinctBy(fn (ServiceCode $x): string => $x->toString());

        return $this->resolveLtcsNameFromServiceCodesUseCase
            ->handle($context, $serviceCodes->computed(), $providedIn);
    }
}

<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\ProvisionReport;

use Domain\Billing\DwsBillingOffice;
use Domain\Billing\DwsBillingServiceReport;
use Domain\Billing\DwsBillingServiceReportPdf;
use Domain\Common\Carbon;
use Domain\Context\Context;
use Domain\DwsCertification\DwsCertification;
use Domain\Office\Office;
use Domain\Permission\Permission;
use Domain\ProvisionReport\DwsProvisionReport;
use Domain\User\User;
use Lib\Exceptions\NotFoundException;
use ScalikePHP\Option;
use ScalikePHP\Seq;
use UseCase\Billing\BuildDwsBillingServiceReportListByIdUseCase;
use UseCase\Billing\BuildDwsBillingStatementContractListUseCase;
use UseCase\DwsCertification\IdentifyDwsCertificationUseCase;
use UseCase\File\StorePdfUseCase;
use UseCase\Office\LookupOfficeUseCase;
use UseCase\User\LookupUserUseCase;

/**
 * サービス提供実績記録票（プレビュー版） PDF 生成ユースケース実装.
 */
final class GenerateDwsServiceReportPreviewPdfInteractor implements GenerateDwsServiceReportPreviewPdfUseCase
{
    // システムでは扱うことのないIDをダミーとして定義
    private const DUMMY_ID = -1;
    private const STORE_TO = 'exported';
    private const TEMPLATE = 'pdfs.billings.service-report.index';

    /**
     * {@link \UseCase\Billing\BuildDwsBillingInvoicePdfParamInteractor} Constructor.
     *
     * @param \UseCase\Billing\BuildDwsBillingStatementContractListUseCase $buildDwsBillingStatementContractListUseCase
     * @param \UseCase\Billing\BuildDwsBillingServiceReportListByIdUseCase $billingServiceReportListByIdUseCase
     * @param \UseCase\ProvisionReport\IdentifyDwsProvisionReportUseCase $identifyDwsProvisionReportUseCase
     * @param \UseCase\DwsCertification\IdentifyDwsCertificationUseCase $identifyDwsCertificationUseCase
     * @param \UseCase\Office\LookupOfficeUseCase $lookupOfficeUseCase
     * @param \UseCase\File\StorePdfUseCase $storePdfUseCase
     * @param \UseCase\User\LookupUserUseCase $lookupUserUseCase
     */
    public function __construct(
        private readonly BuildDwsBillingStatementContractListUseCase $buildDwsBillingStatementContractListUseCase,
        private readonly BuildDwsBillingServiceReportListByIdUseCase $billingServiceReportListByIdUseCase,
        private readonly IdentifyDwsProvisionReportUseCase $identifyDwsProvisionReportUseCase,
        private readonly IdentifyDwsCertificationUseCase $identifyDwsCertificationUseCase,
        private readonly LookupOfficeUseCase $lookupOfficeUseCase,
        private readonly StorePdfUseCase $storePdfUseCase,
        private readonly LookupUserUseCase $lookupUserUseCase
    ) {
    }

    /** {@inheritdoc} */
    public function handle(
        Context $context,
        int $officeId,
        int $userId,
        Carbon $providedIn
    ): string {
        $office = $this->lookupOffice($context, $officeId);
        /** @var DwsProvisionReport $provisionReport */
        $provisionReport = $this->getDwsProvisionReports($context, $officeId, $userId, $providedIn)
            ->getOrElse(function (): void {
                throw new NotFoundException('DwsProvisionReport not found');
            });
        $previousProvisionReport = $this->getDwsProvisionReports($context, $officeId, $userId, $providedIn->subMonth());
        $user = $this->lookupUser($context, $provisionReport->userId);
        $certification = $this->identifyDwsCertification($context, $provisionReport->userId, $providedIn);
        $contracts = $this->buildDwsBillingStatementContractListUseCase->handle($context, $office, $certification, $providedIn);

        $params = $this
            ->buildServiceReport($context, $provisionReport, $previousProvisionReport, $user)
            ->flatMap(fn (DwsBillingServiceReport $serviceReport): Seq => DwsBillingServiceReportPdf::from(
                $serviceReport,
                $providedIn,
                DwsBillingOffice::from($office),
                $contracts
            ));

        return $this->storePdfUseCase->handle(
            $context,
            self::STORE_TO,
            self::TEMPLATE,
            ['pdfs' => $params->toArray()]
        );
    }

    /**
     * サービス提供実積記録票を生成する.
     *
     * @param \Domain\Context\Context $context
     * @param \Domain\ProvisionReport\DwsProvisionReport $dwsProvisionReport
     * @param \Domain\ProvisionReport\DwsProvisionReport[]&\ScalikePHP\Option $previousProvisionReport
     * @param \Domain\User\User $user
     * @throws \Throwable
     * @return \Domain\Billing\DwsBillingServiceReport[]&\ScalikePHP\Seq
     */
    private function buildServiceReport(
        Context $context,
        DwsProvisionReport $dwsProvisionReport,
        Option $previousProvisionReport,
        User $user
    ): Seq {
        return $this->billingServiceReportListByIdUseCase->handle(
            $context,
            self::DUMMY_ID,
            self::DUMMY_ID,
            $dwsProvisionReport,
            $previousProvisionReport,
            $user,
            true
        );
    }

    /**
     * 事業所を取得する.
     *
     * @param \Domain\Context\Context $context
     * @param int $id
     * @return \Domain\Office\Office
     */
    private function lookupOffice(Context $context, int $id): Office
    {
        return $this->lookupOfficeUseCase->handle($context, [Permission::updateDwsProvisionReports()], $id)
            ->headOption()
            ->getOrElse(function () use ($id): void {
                throw new NotFoundException("office with id {$id} not found");
            });
    }

    /**
     * 障害福祉サービス受給者証を取得する.
     *
     * @param \Domain\Context\Context $context
     * @param int $userId
     * @param \Domain\Common\Carbon $providedIn
     * @return \Domain\DwsCertification\DwsCertification
     */
    private function identifyDwsCertification(Context $context, int $userId, Carbon $providedIn): DwsCertification
    {
        return $this->identifyDwsCertificationUseCase->handle($context, $userId, $providedIn)
            ->getOrElse(function () use ($userId, $providedIn): void {
                throw new NotFoundException("DwsCertification for User({$userId}) in {$providedIn->format('Y-m')} not found");
            });
    }

    /**
     * 障害福祉サービス：予実を取得する.
     *
     * @param \Domain\Context\Context $context
     * @param int $officeId
     * @param int $userId
     * @param \Domain\Common\Carbon $providedIn
     * @return \Domain\ProvisionReport\DwsProvisionReport[]&\ScalikePHP\Option
     */
    private function getDwsProvisionReports(Context $context, int $officeId, int $userId, Carbon $providedIn): Option
    {
        return $this->identifyDwsProvisionReportUseCase
            ->handle(
                $context,
                Permission::updateDwsProvisionReports(),
                $officeId,
                $userId,
                $providedIn,
            );
    }

    /**
     * 利用者を取得する.
     *
     * @param \Domain\Context\Context $context
     * @param int $userId
     * @throws \Lib\Exceptions\NotFoundException
     * @return \Domain\User\User
     */
    private function lookupUser(Context $context, int $userId): User
    {
        return $this->lookupUserUseCase
            ->handle($context, Permission::updateDwsProvisionReports(), $userId)
            ->headOption()
            ->getOrElse(function () use ($userId): void {
                throw new NotFoundException("User({$userId}) not found");
            });
    }
}

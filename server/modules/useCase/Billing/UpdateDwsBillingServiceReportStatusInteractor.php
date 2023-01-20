<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Billing;

use Domain\Context\Context;

/**
 * サービス影響実績記録表状態更新ユースケース実装.
 */
class UpdateDwsBillingServiceReportStatusInteractor implements UpdateDwsBillingServiceReportStatusUseCase
{
    private ConfirmDwsBillingStatusUseCase $confirmBillingStatusUseCase;
    private EditDwsBillingServiceReportUseCase $editUseCase;
    private GetDwsBillingServiceReportInfoUseCase $getInfoUseCase;

    /**
     * constructor.
     *
     * @param \UseCase\Billing\ConfirmDwsBillingStatusUseCase $confirmBillingStatusUseCase
     * @param \UseCase\Billing\EditDwsBillingServiceReportUseCase $editUseCase
     * @param \UseCase\Billing\GetDwsBillingServiceReportInfoUseCase $getInfoUseCase
     */
    public function __construct(
        ConfirmDwsBillingStatusUseCase $confirmBillingStatusUseCase,
        EditDwsBillingServiceReportUseCase $editUseCase,
        GetDwsBillingServiceReportInfoUseCase $getInfoUseCase
    ) {
        $this->confirmBillingStatusUseCase = $confirmBillingStatusUseCase;
        $this->editUseCase = $editUseCase;
        $this->getInfoUseCase = $getInfoUseCase;
    }

    /** {@inheritdoc} */
    public function handle(Context $context, int $dwsBillingId, int $dwsBillingBundleId, int $id, array $values): array
    {
        $this->editUseCase
            ->handle($context, $dwsBillingId, $dwsBillingBundleId, $id, $values);

        $info = $this->getInfoUseCase
            ->handle($context, $dwsBillingId, $dwsBillingBundleId, $id);

        $this->confirmBillingStatusUseCase
            ->handle($context, $info['billing']);

        return $info;
    }
}

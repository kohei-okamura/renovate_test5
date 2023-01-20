<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\CreateDwsBillingCopayCoordinationRequest;
use App\Http\Requests\StaffRequest;
use App\Http\Requests\UpdateDwsBillingCopayCoordinationRequest;
use App\Http\Requests\UpdateDwsBillingCopayCoordinationStatusRequest;
use App\Http\Response\JsonResponse;
use App\Http\Response\PdfResponse;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use UseCase\Billing\CreateDwsBillingCopayCoordinationUseCase;
use UseCase\Billing\DownloadDwsBillingCopayCoordinationUseCase;
use UseCase\Billing\EditDwsBillingCopayCoordinationUseCase;
use UseCase\Billing\GetDwsBillingCopayCoordinationInfoUseCase;
use UseCase\Billing\UpdateDwsBillingCopayCoordinationStatusUseCase;

/**
 * 障害福祉サービス利用者負担上限額管理結果票コントローラー.
 */
final class DwsBillingCopayCoordinationController extends Controller
{
    /**
     * 障害福祉サービス利用者負担上限額管理結果票を登録する.
     *
     * @param int $dwsBillingId
     * @param int $dwsBundleId
     * @param \App\Http\Requests\CreateDwsBillingCopayCoordinationRequest $request
     * @param \UseCase\Billing\CreateDwsBillingCopayCoordinationUseCase $useCase
     * @throws \Throwable
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function create(
        int $dwsBillingId,
        int $dwsBundleId,
        CreateDwsBillingCopayCoordinationRequest $request,
        CreateDwsBillingCopayCoordinationUseCase $useCase
    ): HttpResponse {
        $payload = $request->payload();
        $content = $useCase->handle(
            $request->context(),
            $dwsBillingId,
            $dwsBundleId,
            $payload['userId'],
            $payload['result'],
            $payload['exchangeAim'],
            $payload['items']
        );
        return JsonResponse::created($content);
    }

    /**
     * 障害福祉サービス利用者負担上限額管理結果票を取得する.
     *
     * @param int $dwsBillingId
     * @param int $dwsBillingBundleId
     * @param int $id
     * @param \App\Http\Requests\StaffRequest $request
     * @param \UseCase\Billing\GetDwsBillingCopayCoordinationInfoUseCase $useCase
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function get(
        int $dwsBillingId,
        int $dwsBillingBundleId,
        int $id,
        StaffRequest $request,
        GetDwsBillingCopayCoordinationInfoUseCase $useCase
    ): HttpResponse {
        $context = $request->context();
        $entity = $useCase->handle($context, $dwsBillingId, $dwsBillingBundleId, $id);
        return JsonResponse::ok($entity);
    }

    /**
     * 障害福祉サービス利用者負担上限額管理結果票を更新する.
     *
     * @param int $dwsBillingId
     * @param int $dwsBillingBundleId
     * @param int $id
     * @param \App\Http\Requests\UpdateDwsBillingCopayCoordinationRequest $request
     * @param \UseCase\Billing\EditDwsBillingCopayCoordinationUseCase $useCase
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function update(
        int $dwsBillingId,
        int $dwsBillingBundleId,
        int $id,
        UpdateDwsBillingCopayCoordinationRequest $request,
        EditDwsBillingCopayCoordinationUseCase $useCase
    ): HttpResponse {
        $context = $request->context();
        $payload = $request->payload();

        $response = $useCase->handle(
            $context,
            $dwsBillingId,
            $dwsBillingBundleId,
            $id,
            $payload['userId'],
            $payload['result'],
            $payload['exchangeAim'],
            $payload['items']
        );

        return JsonResponse::ok($response);
    }

    /**
     * 障害福祉サービス利用者負担上限額管理結果票 状態を更新する.
     *
     * @param int $dwsBillingId
     * @param int $dwsBillingBundleId
     * @param int $id
     * @param \App\Http\Requests\UpdateDwsBillingCopayCoordinationStatusRequest $request
     * @param \UseCase\Billing\UpdateDwsBillingCopayCoordinationStatusUseCase $useCase
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function status(
        int $dwsBillingId,
        int $dwsBillingBundleId,
        int $id,
        UpdateDwsBillingCopayCoordinationStatusRequest $request,
        UpdateDwsBillingCopayCoordinationStatusUseCase $useCase
    ): HttpResponse {
        $context = $request->context();
        $payload = $request->payload();

        $response = $useCase->handle(
            $context,
            $dwsBillingId,
            $dwsBillingBundleId,
            $id,
            $payload
        );

        return JsonResponse::ok($response);
    }

    /**
     * 障害福祉サービス利用者負担上限額管理結果票をダウンロードする.
     *
     * @param int $dwsBillingId
     * @param int $dwsBillingBundleId
     * @param int $id
     * @param \UseCase\Billing\DownloadDwsBillingCopayCoordinationUseCase $useCase
     * @param \App\Http\Requests\StaffRequest $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function download(
        int $dwsBillingId,
        int $dwsBillingBundleId,
        int $id,
        DownloadDwsBillingCopayCoordinationUseCase $useCase,
        StaffRequest $request
    ): HttpResponse {
        $view = 'pdfs.billings.dws-billing-copay-coordination.index';
        $values = $useCase->handle($request->context(), $dwsBillingId, $dwsBillingBundleId, $id);
        return PdfResponse::ok($view, $values['params'], $values['filename']);
    }
}

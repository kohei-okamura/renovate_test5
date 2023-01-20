<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\CreateLtcsProvisionReportSheetRequest;
use App\Http\Requests\DeleteLtcsProvisionReportRequest;
use App\Http\Requests\FindLtcsProvisionReportRequest;
use App\Http\Requests\GetLtcsProvisionReportScoreSummaryRequest;
use App\Http\Requests\StaffRequest;
use App\Http\Requests\UpdateLtcsProvisionReportRequest;
use App\Http\Requests\UpdateLtcsProvisionReportStatusRequest;
use App\Http\Response\JsonResponse;
use App\Http\Response\Response;
use App\Jobs\CreateLtcsProvisionReportSheetJob;
use Domain\Common\Carbon;
use Domain\Job\Job;
use Domain\Permission\Permission;
use ScalikePHP\Seq;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use UseCase\Job\CreateJobUseCase;
use UseCase\ProvisionReport\DeleteLtcsProvisionReportUseCase;
use UseCase\ProvisionReport\GetIndexLtcsProvisionReportDigestUseCase;
use UseCase\ProvisionReport\GetLtcsProvisionReportScoreSummaryUseCase;
use UseCase\ProvisionReport\GetLtcsProvisionReportUseCase;
use UseCase\ProvisionReport\UpdateLtcsProvisionReportStatusUseCase;
use UseCase\ProvisionReport\UpdateLtcsProvisionReportUseCase;

/**
 * 介護保険サービス：予実コントローラー.
 */
final class LtcsProvisionReportController extends Controller
{
    /**
     * 介護保険サービス：予実を削除する.
     *
     * @param int $officeId
     * @param int $userId
     * @param string $providedIn
     * @param \UseCase\ProvisionReport\DeleteLtcsProvisionReportUseCase $useCase
     * @param \App\Http\Requests\DeleteLtcsProvisionReportRequest $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function delete(
        int $officeId,
        int $userId,
        string $providedIn,
        DeleteLtcsProvisionReportUseCase $useCase,
        DeleteLtcsProvisionReportRequest $request
    ): HttpResponse {
        $useCase->handle($request->context(), $officeId, $userId, Carbon::parse($providedIn));
        return Response::noContent();
    }

    /**
     * 介護保険サービス：予実を取得する.
     *
     * @param int $officeId
     * @param int $userId
     * @param string $providedIn
     * @param \UseCase\ProvisionReport\GetLtcsProvisionReportUseCase $useCase
     * @param \App\Http\Requests\StaffRequest $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function get(
        int $officeId,
        int $userId,
        string $providedIn,
        GetLtcsProvisionReportUseCase $useCase,
        StaffRequest $request
    ): HttpResponse {
        $ltcsProvisionReportOption = $useCase->handle(
            $request->context(),
            Permission::updateLtcsProvisionReports(),
            $officeId,
            $userId,
            Carbon::parse($providedIn),
        );
        foreach ($ltcsProvisionReportOption as $ltcsProvisionReport) {
            return JsonResponse::ok(compact('ltcsProvisionReport'));
        }

        return JsonResponse::noContent();
    }

    /**
     * 介護保険サービス：予実状況の一覧取得をする.
     *
     * @param \UseCase\ProvisionReport\GetIndexLtcsProvisionReportDigestUseCase $useCase
     * @param \App\Http\Requests\FindLtcsProvisionReportRequest $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getIndex(
        GetIndexLtcsProvisionReportDigestUseCase $useCase,
        FindLtcsProvisionReportRequest $request
    ): HttpResponse {
        $finderResult = $useCase->handle($request->context(), $request->filterParams(), $request->paginationParams());
        return JsonResponse::ok($finderResult);
    }

    /**
     * 介護保険サービス：予実を更新（なければ登録）する.
     *
     * @param int $officeId
     * @param int $userId
     * @param string $providedIn
     * @param \UseCase\ProvisionReport\UpdateLtcsProvisionReportUseCase $useCase
     * @param \App\Http\Requests\UpdateLtcsProvisionReportRequest $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function update(
        int $officeId,
        int $userId,
        string $providedIn,
        UpdateLtcsProvisionReportUseCase $useCase,
        UpdateLtcsProvisionReportRequest $request
    ): HttpResponse {
        $ltcsProvisionReport = $useCase->handle(
            $request->context(),
            $officeId,
            $userId,
            $providedIn,
            $request->payload()
        );
        return JsonResponse::ok(compact('ltcsProvisionReport'));
    }

    /**
     * 介護保険サービス：予実 状態を更新する.
     *
     * @param int $officeId
     * @param int $userId
     * @param string $providedIn
     * @param \UseCase\ProvisionReport\UpdateLtcsProvisionReportStatusUseCase $useCase
     * @param \App\Http\Requests\UpdateLtcsProvisionReportStatusRequest $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function status(
        int $officeId,
        int $userId,
        string $providedIn,
        UpdateLtcsProvisionReportStatusUseCase $useCase,
        UpdateLtcsProvisionReportStatusRequest $request
    ): HttpResponse {
        $ltcsProvisionReport = $useCase->handle(
            $request->context(),
            $officeId,
            $userId,
            $providedIn,
            $request->payload()
        );
        return JsonResponse::ok(compact('ltcsProvisionReport'));
    }

    /**
     * 介護保険サービス：予実の合計単位数を取得する.
     *
     * @param \UseCase\ProvisionReport\GetLtcsProvisionReportScoreSummaryUseCase $useCase
     * @param \App\Http\Requests\GetLtcsProvisionReportScoreSummaryRequest $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getScoreSummary(
        GetLtcsProvisionReportScoreSummaryUseCase $useCase,
        GetLtcsProvisionReportScoreSummaryRequest $request
    ): HttpResponse {
        $payload = $request->payload();
        $result = $useCase->handle(
            $request->context(),
            $payload['officeId'],
            $payload['userId'],
            $payload['providedIn'],
            Seq::fromArray($payload['entries']),
            $payload['specifiedOfficeAddition'],
            $payload['treatmentImprovementAddition'],
            $payload['specifiedTreatmentImprovementAddition'],
            $payload['baseIncreaseSupportAddition'],
            $payload['locationAddition'],
            $payload['plan'],
            $payload['result'],
        );
        return JsonResponse::ok($result);
    }

    /**
     * サービス提供票を作成する.
     *
     * @param \UseCase\Job\CreateJobUseCase $useCase
     * @param \App\Http\Requests\CreateLtcsProvisionReportSheetRequest $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function createSheet(CreateJobUseCase $useCase, CreateLtcsProvisionReportSheetRequest $request): HttpResponse
    {
        $context = $request->context();
        $payload = $request->payload();
        $job = $useCase->handle($context, function (Job $domainJob) use ($context, $payload): void {
            $this->dispatch(
                new CreateLtcsProvisionReportSheetJob(
                    $context,
                    $domainJob,
                    $payload['officeId'],
                    $payload['userId'],
                    $payload['providedIn'],
                    $payload['issuedOn'],
                    $payload['needsMaskingInsNumber'],
                    $payload['needsMaskingInsName'],
                )
            );
        });
        return JsonResponse::accepted(compact('job'));
    }
}

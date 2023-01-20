<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\CreateDwsProjectRequest;
use App\Http\Requests\StaffRequest;
use App\Http\Requests\UpdateDwsProjectRequest;
use App\Http\Response\JsonResponse;
use App\Http\Response\PdfResponse;
use App\Http\Response\Response;
use Domain\Config\Config;
use Domain\Permission\Permission;
use Lib\Exceptions\NotFoundException;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use UseCase\Project\CreateDwsProjectUseCase;
use UseCase\Project\DownloadDwsProjectUseCase;
use UseCase\Project\EditDwsProjectUseCase;
use UseCase\Project\LookupDwsProjectUseCase;

/**
 * 障害福祉サービス：計画コントローラー.
 */
final class DwsProjectController extends Controller
{
    /**
     * 障害福祉サービス：計画を登録する.
     *
     * @param int $userId
     * @param \UseCase\Project\CreateDwsProjectUseCase $useCase
     * @param \App\Http\Requests\CreateDwsProjectRequest $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function create(
        int $userId,
        CreateDwsProjectUseCase $useCase,
        CreateDwsProjectRequest $request
    ): HttpResponse {
        $useCase->handle($request->context(), $userId, $request->payload());
        return Response::created();
    }

    /**
     * 障害福祉サービス：計画をダウンロードする.
     *
     * @param int $userId
     * @param int $id
     * @param \UseCase\Project\DownloadDwsProjectUseCase $useCase
     * @param \App\Http\Requests\StaffRequest $request
     * @param \Domain\Config\Config $config
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function download(
        int $userId,
        int $id,
        DownloadDwsProjectUseCase $useCase,
        StaffRequest $request,
        Config $config
    ): HttpResponse {
        $view = 'pdfs.projects.dws-project.index';
        $filename = $config->filename('zinger.filename.dws_project_pdf');
        $values = $useCase->handle($request->context(), $userId, $id);
        return PdfResponse::ok($view, $values, $filename);
    }

    /**
     * 障害福祉サービス：計画を取得する.
     *
     * @param int $userId
     * @param int $id
     * @param \UseCase\Project\LookupDwsProjectUseCase $useCase
     * @param \App\Http\Requests\StaffRequest $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function get(int $userId, int $id, LookupDwsProjectUseCase $useCase, StaffRequest $request): HttpResponse
    {
        $dwsProject = $useCase->handle($request->context(), Permission::viewDwsProjects(), $userId, $id)
            ->headOption()
            ->getOrElse(function () use ($id): void {
                throw new NotFoundException("DwsProject({$id}) not found");
            });
        return JsonResponse::ok(compact('dwsProject'));
    }

    /**
     * 障害福祉サービス：計画を更新する.
     *
     * @param int $userId
     * @param int $id
     * @param \UseCase\Project\EditDwsProjectUseCase $useCase
     * @param \App\Http\Requests\UpdateDwsProjectRequest $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function update(int $userId, int $id, EditDwsProjectUseCase $useCase, UpdateDwsProjectRequest $request): HttpResponse
    {
        $dwsProject = $useCase->handle($request->context(), $userId, $id, $request->payload());
        return JsonResponse::ok(compact('dwsProject'));
    }
}

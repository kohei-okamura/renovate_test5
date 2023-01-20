<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\CreateLtcsProjectRequest;
use App\Http\Requests\StaffRequest;
use App\Http\Requests\UpdateLtcsProjectRequest;
use App\Http\Response\JsonResponse;
use App\Http\Response\PdfResponse;
use App\Http\Response\Response;
use Domain\Config\Config;
use Domain\Permission\Permission;
use Lib\Exceptions\NotFoundException;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use UseCase\Project\CreateLtcsProjectUseCase;
use UseCase\Project\DownloadLtcsProjectUseCase;
use UseCase\Project\EditLtcsProjectUseCase;
use UseCase\Project\LookupLtcsProjectUseCase;

/**
 * 介護保険サービス：計画コントローラー.
 */
final class LtcsProjectController extends Controller
{
    /**
     * 介護保険サービス：計画を登録する.
     *
     * @param int $userId
     * @param \UseCase\Project\CreateLtcsProjectUseCase $useCase
     * @param \App\Http\Requests\CreateLtcsProjectRequest $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function create(int $userId, CreateLtcsProjectUseCase $useCase, CreateLtcsProjectRequest $request): HttpResponse
    {
        $useCase->handle($request->context(), $userId, $request->payload());
        return Response::created();
    }

    /**
     * 介護保険サービス：計画をダウンロードする.
     *
     * @param int $userId
     * @param int $id
     * @param \UseCase\Project\DownloadLtcsProjectUseCase $useCase
     * @param \App\Http\Requests\StaffRequest $request
     * @param \Domain\Config\Config $config
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function download(
        int $userId,
        int $id,
        DownloadLtcsProjectUseCase $useCase,
        StaffRequest $request,
        Config $config
    ): HttpResponse {
        $view = 'pdfs.projects.ltcs-project.index';
        $filename = $config->filename('zinger.filename.ltcs_project_pdf');
        $values = $useCase->handle($request->context(), $userId, $id);
        return PdfResponse::ok($view, $values, $filename);
    }

    /**
     * 介護保険サービス：計画を取得する.
     *
     * @param int $userId
     * @param int $id
     * @param \UseCase\Project\LookupLtcsProjectUseCase $useCase
     * @param \App\Http\Requests\StaffRequest $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function get(int $userId, int $id, LookupLtcsProjectUseCase $useCase, StaffRequest $request): HttpResponse
    {
        $ltcsProject = $useCase->handle($request->context(), Permission::viewLtcsProjects(), $userId, $id)
            ->headOption()
            ->getOrElse(function () use ($id) {
                throw new NotFoundException("LtcsProject({$id}) not found");
            });
        return JsonResponse::ok(compact('ltcsProject'));
    }

    /**
     * 介護保険サービス：計画を更新する.
     *
     * @param int $userId
     * @param int $id
     * @param \UseCase\Project\EditLtcsProjectUseCase $useCase
     * @param \App\Http\Requests\UpdateLtcsProjectRequest $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function update(int $userId, int $id, EditLtcsProjectUseCase $useCase, UpdateLtcsProjectRequest $request): HttpResponse
    {
        $ltcsProject = $useCase->handle($request->context(), $userId, $id, $request->payload());
        return JsonResponse::ok(compact('ltcsProject'));
    }
}

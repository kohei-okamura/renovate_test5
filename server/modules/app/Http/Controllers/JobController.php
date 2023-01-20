<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StaffRequest;
use App\Http\Response\JsonResponse;
use Lib\Exceptions\NotFoundException;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use UseCase\Job\LookupJobByTokenUseCase;

/**
 * ジョブコントローラー.
 */
final class JobController extends Controller
{
    /**
     * ジョブを取得する.
     *
     * @param string $token
     * @param \UseCase\Job\LookupJobByTokenUseCase $useCase
     * @param \App\Http\Requests\StaffRequest $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function get(string $token, LookupJobByTokenUseCase $useCase, StaffRequest $request): HttpResponse
    {
        $job = $useCase->handle($request->context(), $token)
            ->headOption()
            ->getOrElse(function () use ($token): void {
                throw new NotFoundException("Job({$token}) not found");
            });
        return JsonResponse::ok(compact('job'));
    }
}

<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StaffRequest;
use App\Http\Response\Response;
use Lib\Exceptions\NotFoundException;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use UseCase\File\DownloadFileUseCase;

/**
 * ダウンロードコントローラー.
 */
final class DownloadController extends Controller
{
    /**
     * ファイルをダウンロードする.
     *
     * @param string $dir
     * @param string $filename
     * @param \UseCase\File\DownloadFileUseCase $useCase
     * @param \App\Http\Requests\StaffRequest $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function download(
        string $dir,
        string $filename,
        DownloadFileUseCase $useCase,
        StaffRequest $request
    ): HttpResponse {
        $path = "{$dir}/{$filename}";
        $context = $request->context();
        $resource = $useCase->handle($context, $path)->getOrElse(function () use ($path): void {
            throw new NotFoundException("Target File({$path}) is not found.");
        });
        return Response::ok($resource);
    }
}

<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\CreateInvitationRequest;
use App\Http\Requests\OrganizationRequest;
use App\Http\Response\JsonResponse;
use App\Http\Response\Response;
use Domain\Staff\Invitation;
use Lib\Exceptions\ForbiddenException;
use Lib\Exceptions\NotFoundException;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use UseCase\Staff\CreateInvitationUseCase;
use UseCase\Staff\LookupInvitationByTokenUseCase;

/**
 * 招待コントローラー.
 */
final class InvitationController extends Controller
{
    /**
     * 招待を登録する.
     *
     * @param \UseCase\Staff\CreateInvitationUseCase $useCase
     * @param \App\Http\Requests\CreateInvitationRequest $request
     * @throws \Throwable
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function create(CreateInvitationUseCase $useCase, CreateInvitationRequest $request): HttpResponse
    {
        $useCase->handle($request->context(), $request->payload());
        return Response::created();
    }

    /**
     * 招待を取得する.
     *
     * @param string $token
     * @param \UseCase\Staff\LookupInvitationByTokenUseCase $useCase
     * @param \App\Http\Requests\OrganizationRequest $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function get(string $token, LookupInvitationByTokenUseCase $useCase, OrganizationRequest $request): HttpResponse
    {
        $invitation = $useCase->handle($request->context(), $token)
            ->map(function (Invitation $invitation): Invitation {
                if ($invitation->expiredAt->isPast()) {
                    throw new ForbiddenException('Token has been expired');
                }
                return $invitation;
            })
            ->getOrElse(function () use ($token): void {
                throw new NotFoundException("Invitation({$token}) not found");
            });
        return JsonResponse::ok(compact('invitation'));
    }
}

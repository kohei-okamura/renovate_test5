<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\AuthenticateStaffRequest;
use App\Http\Requests\StaffRequest;
use App\Http\Response\JsonResponse;
use App\Http\Response\Response;
use Domain\Config\Config;
use Lib\Json;
use Lib\Logging;
use ScalikePHP\Option;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use UseCase\Staff\AuthenticateStaffUseCase;
use UseCase\Staff\GetSessionInfoUseCase;
use UseCase\Staff\StaffLoggedOutUseCase;

/**
 * セッションコントローラー.
 */
final class SessionController extends Controller
{
    use Logging;

    public const SESSION_KEY_STAFF_ID = 'staffId';

    private Config $config;

    /**
     * Constructor.
     *
     * @param \Domain\Config\Config $config
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     * セッションを作成する.
     *
     * @param \UseCase\Staff\AuthenticateStaffUseCase $useCase
     * @param \App\Http\Requests\AuthenticateStaffRequest $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function create(AuthenticateStaffUseCase $useCase, AuthenticateStaffRequest $request): HttpResponse
    {
        $context = $request->context();
        return $useCase->handle($context, $request->email, $request->password, (bool)$request->rememberMe)
            ->map(function (array $data) use ($request) {
                $request->session()->put(self::SESSION_KEY_STAFF_ID, $data['auth']['staff']->id);
                return JsonResponse::created($data);
            })
            ->getOrElse(fn () => Response::unauthorized());
    }

    /**
     * セッションを破棄する.
     *
     * @param \UseCase\Staff\StaffLoggedOutUseCase $useCase
     * @param \App\Http\Requests\StaffRequest $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function delete(StaffLoggedOutUseCase $useCase, StaffRequest $request): HttpResponse
    {
        /** @var null|int $staffId */
        $staffId = $request->session()->remove(self::SESSION_KEY_STAFF_ID);
        if ($staffId !== null) {
            $this->logger()->info(
                'スタッフがログアウトしました',
                $request->context()->logContext()
            );
            $useCase->handle($request->context(), $this->getRememberTokenId($request));
        }
        return Response::noContent();
    }

    /**
     * セッションを取得する.
     *
     * @param \UseCase\Staff\GetSessionInfoUseCase $useCase
     * @param \App\Http\Requests\StaffRequest $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function get(GetSessionInfoUseCase $useCase, StaffRequest $request): HttpResponse
    {
        return $useCase->handle($request->context())
            ->map(fn (array $data) => JsonResponse::ok($data))
            ->getOrElse(fn () => Response::notFound());
    }

    /**
     * リメンバートークンのIDを取得する.
     *
     * @param \App\Http\Requests\StaffRequest $request
     * @return int[]|\ScalikePHP\Option
     */
    private function getRememberTokenId(StaffRequest $request): Option
    {
        $cookieName = $this->config->get('zinger.remember_token.cookie_name');
        return $request->hasCookie($cookieName)
            ? Json::decodeSafety($request->cookie($cookieName), true)->pick('id')
            : Option::none();
    }
}

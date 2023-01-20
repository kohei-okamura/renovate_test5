<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\GetIndexOfficeGroupOptionRequest;
use App\Http\Requests\GetIndexOfficeOptionRequest;
use App\Http\Requests\GetIndexRoleOptionRequest;
use App\Http\Requests\GetIndexStaffOptionRequest;
use App\Http\Requests\GetIndexUserOptionRequest;
use App\Http\Response\JsonResponse;
use Domain\Office\OfficeQualification;
use Domain\Office\Purpose;
use Domain\Permission\Permission;
use Illuminate\Support\Str;
use ScalikePHP\Option;
use ScalikePHP\Seq;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use UseCase\Office\GetIndexOfficeGroupOptionUseCase;
use UseCase\Office\GetIndexOfficeOptionUseCase;
use UseCase\Role\GetIndexRoleOptionUseCase;
use UseCase\Staff\GetIndexStaffOptionUseCase;
use UseCase\User\GetIndexUserOptionUseCase;

/**
 * 選択肢コントローラー.
 */
final class OptionController extends Controller
{
    /**
     * 事業所選択肢一覧を取得する.
     *
     * @param \UseCase\Office\GetIndexOfficeOptionUseCase $useCase
     * @param \App\Http\Requests\GetIndexOfficeOptionRequest $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function offices(GetIndexOfficeOptionUseCase $useCase, GetIndexOfficeOptionRequest $request): HttpResponse
    {
        $values = $useCase->handle(
            $request->context(),
            Option::from(empty($request->permission) ? null : Permission::from($request->permission)),
            Option::from(empty($request->userId) ? null : +$request->userId),
            Option::from(empty($request->purpose) ? null : Purpose::from(+$request->purpose)),
            Option::from(empty($request->isCommunityGeneralSupportCenter) ? null : $this->toBool($request->isCommunityGeneralSupportCenter)),
            Seq::fromArray($request->qualifications)->map(fn (string $x): OfficeQualification => OfficeQualification::from($x))
        );
        return JsonResponse::ok($values);
    }

    /**
     * 事業所グループ選択肢一覧を取得する.
     *
     * @param \UseCase\Office\GetIndexOfficeGroupOptionUseCase $useCase
     * @param \App\Http\Requests\GetIndexOfficeGroupOptionRequest $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function officeGroups(
        GetIndexOfficeGroupOptionUseCase $useCase,
        GetIndexOfficeGroupOptionRequest $request
    ): HttpResponse {
        $values = $useCase->handle($request->context(), Permission::from($request->permission));
        return JsonResponse::ok($values);
    }

    /**
     * ロール選択肢一覧を取得する.
     *
     * @param \UseCase\Role\GetIndexRoleOptionUseCase $useCase
     * @param \App\Http\Requests\GetIndexRoleOptionRequest $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function roles(GetIndexRoleOptionUseCase $useCase, GetIndexRoleOptionRequest $request): HttpResponse
    {
        $values = $useCase->handle($request->context(), Permission::from($request->permission));
        return JsonResponse::ok($values);
    }

    /**
     * スタッフ選択肢一覧を取得する.
     *
     * @param \UseCase\Staff\GetIndexStaffOptionUseCase $useCase
     * @param \App\Http\Requests\GetIndexStaffOptionRequest $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function staffs(GetIndexStaffOptionUseCase $useCase, GetIndexStaffOptionRequest $request): HttpResponse
    {
        $officeIds = $request->officeIds ?? [];
        $values = $useCase->handle($request->context(), Permission::from($request->permission), $officeIds);
        return JsonResponse::ok($values);
    }

    /**
     * 利用者選択肢一覧を取得する.
     *
     * @param \UseCase\User\GetIndexUserOptionUseCase $useCase
     * @param \App\Http\Requests\GetIndexUserOptionRequest $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function users(GetIndexUserOptionUseCase $useCase, GetIndexUserOptionRequest $request): HttpResponse
    {
        $officeIds = $request->officeIds ?? [];
        $values = $useCase->handle($request->context(), Permission::from($request->permission), $officeIds);
        return JsonResponse::ok($values);
    }

    /**
     * リクエストパラメータを bool 型に変換する.
     *
     * @param mixed $value
     * @return bool
     */
    private function toBool(mixed $value): bool
    {
        if (is_bool($value)) {
            return $value;
        } elseif (is_numeric($value)) {
            return (bool)$value;
        } elseif (is_string($value)) {
            return Str::lower($value) === 'true';
        } else {
            // bool, numeric, string 以外が来ることはないはずだが念の為
            return (bool)$value; // @codeCoverageIgnore
        }
    }
}

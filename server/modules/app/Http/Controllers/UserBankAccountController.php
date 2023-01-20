<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\UpdateUserBankAccountRequest;
use App\Http\Response\Response;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use UseCase\BankAccount\EditUserBankAccountUseCase;

/**
 * 利用者コントローラー.
 */
final class UserBankAccountController extends Controller
{
    /**
     * 利用者銀行口座を更新する.
     *
     * @param int $userId
     * @param \UseCase\BankAccount\EditUserBankAccountUseCase $useCase
     * @param \App\Http\Requests\UpdateUserBankAccountRequest $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function update(
        int $userId,
        EditUserBankAccountUseCase $useCase,
        UpdateUserBankAccountRequest $request
    ): HttpResponse {
        $bankAccount = $useCase->handle($request->context(), $userId, $request->payload());
        return Response::ok(compact('bankAccount'));
    }
}

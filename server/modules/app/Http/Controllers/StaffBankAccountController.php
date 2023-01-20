<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 *  UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\UpdateStaffBankAccountRequest;
use App\Http\Response\Response;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use UseCase\BankAccount\EditStaffBankAccountUseCase;

/**
 * スタッフ銀行口座コントローラー.
 */
final class StaffBankAccountController extends Controller
{
    /**
     * スタッフ銀行口座を更新する
     *
     * @param int $staffId
     * @param \UseCase\BankAccount\EditStaffBankAccountUseCase $useCase
     * @param \App\Http\Requests\UpdateStaffBankAccountRequest $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function update(
        int $staffId,
        EditStaffBankAccountUseCase $useCase,
        UpdateStaffBankAccountRequest $request
    ): HttpResponse {
        $bankAccount = $useCase->handle($request->context(), $staffId, $request->payload());
        return Response::ok(compact('bankAccount'));
    }
}

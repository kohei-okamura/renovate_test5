<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\FindLtcsHomeVisitLongTermCareDictionaryEntryRequest;
use App\Http\Requests\GetLtcsHomeVisitLongTermCareDictionaryEntryRequest;
use App\Http\Response\JsonResponse;
use Lib\Exceptions\NotFoundException;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use UseCase\ServiceCodeDictionary\GetIndexLtcsHomeVisitLongTermCareDictionaryEntryUseCase;
use UseCase\ServiceCodeDictionary\GetLtcsHomeVisitLongTermCareDictionaryEntryUseCase;

/**
 * 介護保険サービス：訪問介護：サービスコード辞書エントリコントローラー.
 */
final class LtcsHomeVisitLongTermCareDictionaryEntryController extends Controller
{
    /**
     * 介護保険サービス：訪問介護：サービスコード辞書エントリを検索する.
     *
     * @param \UseCase\ServiceCodeDictionary\GetIndexLtcsHomeVisitLongTermCareDictionaryEntryUseCase $useCase
     * @param \App\Http\Requests\FindLtcsHomeVisitLongTermCareDictionaryEntryRequest $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getIndex(
        GetIndexLtcsHomeVisitLongTermCareDictionaryEntryUseCase $useCase,
        FindLtcsHomeVisitLongTermCareDictionaryEntryRequest $request
    ): HttpResponse {
        $finderResult = $useCase->handle($request->context(), $request->filterParams());
        return JsonResponse::ok($finderResult);
    }

    /**
     * 介護保険サービス：訪問介護：サービスコード辞書エントリを取得する.
     *
     * @param string $serviceCode
     * @param \UseCase\ServiceCodeDictionary\GetLtcsHomeVisitLongTermCareDictionaryEntryUseCase $useCase
     * @param \App\Http\Requests\GetLtcsHomeVisitLongTermCareDictionaryEntryRequest $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function get(
        string $serviceCode,
        GetLtcsHomeVisitLongTermCareDictionaryEntryUseCase $useCase,
        GetLtcsHomeVisitLongTermCareDictionaryEntryRequest $request
    ): HttpResponse {
        $payload = $request->payload();
        $dictionaryEntry = $useCase->handle(
            $request->context(),
            $serviceCode,
            $payload['providedIn']
        )->getOrElse(function () use ($serviceCode): void {
            throw new NotFoundException("entry(serviceCode={$serviceCode}) is not found");
        });
        return JsonResponse::ok(compact('dictionaryEntry'));
    }
}

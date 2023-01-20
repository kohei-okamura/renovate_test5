<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Providers;

use App\Http\Requests\OrganizationRequest;
use App\Http\Requests\StaffRequest;
use App\Resolvers\OrganizationResolver;
use App\Resolvers\StaffResolver;
use Illuminate\Contracts\Validation\ValidatesWhenResolved;
use Illuminate\Support\ServiceProvider;
use Laravel\Lumen\Application;
use Lib\Exceptions\NotFoundException;
use Lib\Exceptions\UnauthorizedException;

/**
 * Validation Service Provider.
 */
final class RequestServiceProvider extends ServiceProvider
{
    /**
     * Laravel における FormRequest と同様にリクエストクラスの解決時にバリデーションを行う.
     *
     * @return void
     */
    public function boot(): void
    {
        $this->app->resolving(
            OrganizationRequest::class,
            function (OrganizationRequest $request, Application $app): void {
                // Lumen では Request を継承したクラスをメソッドに注入しただけでは
                // リクエストの内容がそのインスタンスに反映されないため手動でリクエスト内容のコピーを行う
                OrganizationRequest::createFrom($app['request'], $request);

                // Lumen はリクエストの内容に関わらずリクエストボディに対して json_decode() を実行しており
                // リクエストボディが空の場合などにエラーが発生する (json_last_error() が 0 以外を返すようになる）が
                // 下記例のような順番で json_encode(), json_decode() を実行した場合にエラーの発生状況がリセットされず
                // 結果としてレスポンスの json エンコードが失敗したと扱われる場合があるため
                // このタイミングで json_encode() を呼び出して、この時点ではエラーが発生していない……ということにする
                // 例）
                // 1. json_decode('') を呼び出す
                //    -> json_last_error() は JSON_ERROR_SYNTAX (4) を返すようになる
                // 2. json_encode('', JSON_THROW_ON_ERROR) を呼び出す
                //    -> エラー発生時に例外を投げるよう場合はエラーの状況がリセットされず
                //       json_last_error() は依然として JSON_ERROR_SYNTAX を返す
                // 3. json_encode('') を呼び出す
                //    -> エラー発生時に例外を投げない場合はエラーの状況がリセットされ
                //       json_last_error() は 0 を返すようになる
                json_encode('');

                // 組織情報を設定する.
                OrganizationRequest::prepareOrganizationRequest(
                    $request,
                    $app->make(OrganizationResolver::class)->resolve($request)->getOrElse(function (): void {
                        throw new NotFoundException('Organization not found');
                    })
                );
            }
        );
        $this->app->resolving(StaffRequest::class, function (StaffRequest $request, Application $app): void {
            $app->make(StaffResolver::class)->resolve($request)->getOrElse(function (): void {
                throw new UnauthorizedException();
            });
        });
        $this->app->afterResolving(ValidatesWhenResolved::class, function (ValidatesWhenResolved $resolved): void {
            $resolved->validateResolved();
        });
    }
}

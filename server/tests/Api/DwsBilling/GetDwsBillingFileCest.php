<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Api\DwsBilling;

use ApiTester;
use Codeception\Util\HttpCode;
use Illuminate\Support\Str;
use Tests\Api\Mixins\TransactionMixin;
use Tests\Api\Test;
use Tests\Unit\Examples\ExamplesConsumer;

/**
 * 障害福祉サービス 請求ファイル取得 のテスト.
 * GET /dws-billings/{id}/files/{token}
 */
final class GetDwsBillingFileCest extends Test
{
    use ExamplesConsumer;
    use TransactionMixin;

    /**
     * API正常呼び出しテスト.
     *
     * @param \ApiTester $I
     */
    public function succeedAPICall(ApiTester $I)
    {
        $I->wantTo('succeed API Call');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $id = $this->examples->dwsBillings[0]->id;
        $file = $this->examples->dwsBillings[0]->files[0];
        $token = $file->token;

        $I->sendGet("/dws-billings/{$id}/files/{$token}");

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeLogCount(0);
        $response = $I->grabResponseArray();
        $I->assertArrayHasKey('url', $response);
        $I->assertTrue(Str::endsWith($response['url'], $file->path));
    }
}

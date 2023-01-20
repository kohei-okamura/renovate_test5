<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Api\Download;

use ApiTester;
use Codeception\Util\HttpCode;
use Psr\Log\LogLevel;
use Tests\Api\Test;
use Tests\Unit\Examples\ExamplesConsumer;

/**
 * download のテスト.
 * GET /{baseRoute}/{dir}/{filename}
 */
class DownloadFileCest extends Test
{
    use ExamplesConsumer;

    private string $baseRoute = 'user-billings/download';

    /**
     * API正常呼び出し テスト
     *
     * @param ApiTester $I
     */
    public function suceedAPICall(ApiTester $I)
    {
        $I->wantTo('succeed API call');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $filename = 'DummyFileForDownloadTest.txt';
        $dir = 'artifacts';
        $dataPath = codecept_data_dir($filename);
        copy($dataPath, storage_path("app/{$dir}/{$filename}"));

        $I->sendGET("/{$this->baseRoute}/{$dir}/{$filename}");

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseEquals(file_get_contents($dataPath));
        $I->seeLogCount(0);
    }

    /**
     * ファイルが存在しない場合に404を返すテスト.
     *
     * @param ApiTester $I
     */
    public function failedWithNotFoundWhenPathNotFound(ApiTester $I)
    {
        $I->wantTo('failed with NotFound when Path not found');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $path = 'artifacts/NonExistPath';

        $I->sendGET("/{$this->baseRoute}/{$path}");

        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::WARNING, "Target File({$path}) is not found.");
    }

    /**
     * 権限のないスタッフによる操作で403が返るテスト.
     *
     * @param ApiTester $I
     */
    public function failWithForbiddenWhenNotHavingPermission(ApiTester $I)
    {
        $I->wantTo('fail with forbidden when not having permission');

        $staff = $this->examples->staffs[29];
        $I->actingAs($staff);
        $filename = 'DummyFileForDownloadTest.txt';
        $dir = 'artifacts';

        $I->sendGET("/{$this->baseRoute}/{$dir}/{$filename}");

        $I->seeResponseCodeIs(HttpCode::FORBIDDEN);
        $I->seeLogCount(0);
    }
}

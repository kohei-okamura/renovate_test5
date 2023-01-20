<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Api\Shift;

use ApiTester;
use App\Concretes\ConfigRepository;
use Codeception\Util\HttpCode;
use DateTime;
use Domain\Common\Carbon;
use Domain\Job\JobStatus;
use Psr\Log\LogLevel;
use Tests\Api\Mixins\TransactionMixin;
use Tests\Unit\Examples\ExamplesConsumer;

/**
 * Shift createTemplate のテスト.
 * POST /shift-templates
 */
class CreateShiftTemplateCest extends ShiftTest
{
    use ExamplesConsumer;
    use TransactionMixin;

    /**
     * API正常呼び出しテスト.
     *
     * @param ApiTester $I
     */
    public function succeedAPICall(ApiTester $I)
    {
        $I->wantTo('succeed API call');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $param = $this->defaultParam();
        $expected = [
            'status' => JobStatus::waiting()->value(),
        ];

        $I->sendPOST('shift-templates', $param);

        $I->seeResponseCodeIs(HttpCode::ACCEPTED);
        $I->seeResponseContainsJson($expected);
        $I->seeLogCount(3);
        $I->seeLogMessage(2, LogLevel::INFO, 'ジョブが登録されました', [
            'id' => '*',
            'organizationId' => $staff->organizationId,
            'staffId' => $staff->id,
        ]); // NOTE: QUEUEをsyncで実行しているため、JOBの処理が完了後に、投入後の処理が行われる
        $I->seeLogMessage(0, LogLevel::INFO, 'ジョブが更新されました', [
            'id' => '*',
            'organizationId' => $staff->organizationId,
            'staffId' => $staff->id,
            'status' => (string)JobStatus::inProgress()->value(),
        ]);
        $I->seeLogMessage(1, LogLevel::INFO, 'ジョブが更新されました', [
            'id' => '*',
            'organizationId' => $staff->organizationId,
            'staffId' => $staff->id,
            'status' => (string)JobStatus::success()->value(),
        ]);

        // Job完了の検証
        $job = $I->grabResponseArray()['job'];
        $I->setCookieFromResponse();

        $I->sendGET("jobs/{$job['token']}");

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseMatchesJsonType([
            'job' => [
                'status' => 'integer:=' . JobStatus::success()->value(),
                'data' => [
                    'uri' => 'string:regex(~https\:\/\/eustylelab1\.zinger\.test\/api\/shift-templates\/download\/exported\/zinger-~)',
                    'filename' => 'string:=' . (new ConfigRepository())->filename('zinger.filename.shift_template'),
                ],
                'createdAt' => 'string:regex(~' . preg_replace('/\+/', '\+', Carbon::now()->format(DateTime::ISO8601)) . '~)',
                'updatedAt' => 'string:regex(~' . preg_replace('/\+/', '\+', Carbon::now()->format(DateTime::ISO8601)) . '~)',
            ],
        ]);
    }

    /**
     * Shiftをコピーする指定でAPI正常呼び出しテスト.
     *
     * @param ApiTester $I
     */
    public function succeedAPICallWithCopingShifts(ApiTester $I)
    {
        $I->wantTo('succeed API call with coping shifts');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $param = [
            'isCopy' => true,
            'source' => [
                'start' => Carbon::now()->subWeek()->toDateString(),
                'end' => Carbon::now()->subWeek()->addDays(6)->toDateString(),
            ],
        ] + $this->defaultParam();
        $expected = [
            'status' => JobStatus::waiting()->value(),
        ];

        $I->sendPOST('shift-templates', $param);

        $I->seeResponseCodeIs(HttpCode::ACCEPTED);
        $I->seeResponseContainsJson($expected);
        $I->seeLogCount(3);
        $I->seeLogMessage(2, LogLevel::INFO, 'ジョブが登録されました', [
            'organizationId' => $staff->organizationId,
            'staffId' => $staff->id,
        ]); // NOTE: QUEUEをsyncで実行しているため、JOBの処理が完了後に、投入後の処理が行われる
        $I->seeLogMessage(0, LogLevel::INFO, 'ジョブが更新されました', [
            'organizationId' => $staff->organizationId,
            'staffId' => $staff->id,
            'status' => (string)JobStatus::inProgress()->value(),
        ]);
        $I->seeLogMessage(1, LogLevel::INFO, 'ジョブが更新されました', [
            'organizationId' => $staff->organizationId,
            'staffId' => $staff->id,
            'status' => (string)JobStatus::success()->value(),
        ]);

        // Job完了の検証
        $job = $I->grabResponseArray()['job'];
        $I->setCookieFromResponse();

        $I->sendGET("jobs/{$job['token']}");

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseMatchesJsonType([
            'job' => [
                'status' => 'integer:=' . JobStatus::success()->value(),
                'data' => [
                    'uri' => 'string:regex(~https\:\/\/eustylelab1\.zinger\.test\/api\/shift-templates\/download\/exported\/zinger-~)',
                    'filename' => 'string:=' . (new ConfigRepository())->filename('zinger.filename.shift_template'),
                ],
                'createdAt' => 'string:regex(~' . preg_replace('/\+/', '\+', Carbon::now()->format(DateTime::ISO8601)) . '~)',
                'updatedAt' => 'string:regex(~' . preg_replace('/\+/', '\+', Carbon::now()->format(DateTime::ISO8601)) . '~)',
            ],
        ]);
    }

    /**
     * 自分のorganizationじゃないOfficeIdを指定すると400が返るテスト.
     *
     * @param ApiTester $I
     */
    public function failWithBadRequestWhenOfficeIdNotInOrganization(ApiTester $I)
    {
        $I->wantTo('fail with BadRequest when officeId not in organization');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $param = ['officeId' => $this->examples->offices[1]->id] + $this->defaultParam();

        $I->sendPOST('shift-templates', $param);

        $I->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $I->seeResponseContainsJson(['errors' => ['officeId' => ['正しい値を入力してください。']]]);
        $I->seeLogCount(0);
    }

    /**
     * 事業所のアクセスが認可されていない場合に400を返すテスト.
     *
     * @param \ApiTester $I
     */
    public function failedWithBadRequestWhenOfficeIdIsNotPermitted(ApiTester $I)
    {
        $I->wantTo('failed with BadRequest when office_id is not permitted');

        $staff = $this->examples->staffs[28];
        $I->actingAs($staff);
        $param = ['officeId' => $this->examples->offices[2]->id] + $this->defaultParam();

        $I->sendPOST('shift-templates', $param);

        $I->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $I->seeResponseContainsJson(['errors' => ['officeId' => ['正しい値を入力してください。']]]);
        $I->seeLogCount(0);
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
        $param = ['officeId' => $this->examples->offices[2]->id] + $this->defaultParam();

        $I->sendPOST('shift-templates', $param);

        $I->seeResponseCodeIs(HttpCode::FORBIDDEN);
        $I->seeLogCount(0);
    }

    /**
     * リクエスト用パラメータ生成.
     *
     * @return array
     */
    private function defaultParam(): array
    {
        return [
            'officeId' => $this->examples->offices[0]->id,
            'isCopy' => false,
            'range' => [
                'start' => Carbon::now()->toDateString(),
                'end' => Carbon::now()->addDays(6)->toDateString(),
            ],
        ];
    }
}

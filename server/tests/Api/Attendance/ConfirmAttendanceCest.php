<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Api\Attendance;

use ApiTester;
use Codeception\Util\HttpCode;
use Domain\Job\JobStatus;
use Domain\Shift\Attendance;
use Lib\Json;
use function PHPUnit\Framework\assertEquals;
use Psr\Log\LogLevel;
use ScalikePHP\Seq;
use Tests\Api\Mixins\TransactionMixin;
use Tests\Unit\Examples\ExamplesConsumer;

/**
 * Attendance confirm のテスト.
 * POST /attendances/confirmation
 */
class ConfirmAttendanceCest extends AttendanceTest
{
    use ExamplesConsumer;
    use TransactionMixin;

    /**
     * API正常呼び出し テスト.
     *
     * @param \ApiTester $I
     */
    public function succeedAPICall(ApiTester $I)
    {
        $I->wantTo('succeed API call');

        // 未確定のIDリストを得る
        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $param = [
            'ids' => Seq::fromArray($this->examples->attendances)
                ->filter(fn (Attendance $x): bool => $x->organizationId === $staff->organizationId)
                ->filter(fn (Attendance $x): bool => !$x->isConfirmed)
                ->map(fn (Attendance $m): int => $m->id)
                ->toArray(),
        ];
        $expected = [
            'job' => ['status' => JobStatus::waiting()->value()],
        ];

        $I->sendPOST('attendances/confirmation', $param);

        $I->seeResponseCodeIs(HttpCode::ACCEPTED);
        $job = $I->grabResponseArray()['job'];
        $I->seeResponseContainsJson($expected);
        $I->seeLogCount(4);
        $I->seeLogMessage(3, LogLevel::INFO, 'ジョブが登録されました', [
            'organizationId' => $staff->organizationId,
            'staffId' => $staff->id,
        ]); // NOTE: QUEUEをsyncで実行しているため、JOBの処理が完了後に、投入後の処理が行われる
        $I->seeLogMessage(0, LogLevel::INFO, 'ジョブが更新されました', [
            'organizationId' => $staff->organizationId,
            'staffId' => $staff->id,
            'status' => (string)JobStatus::inProgress()->value(),
        ]);
        $I->seeLogMessage(1, LogLevel::INFO, '勤務実績が確定されました', [
            'organizationId' => $staff->organizationId,
            'staffId' => $staff->id,
        ]);
        $I->seeLogMessage(2, LogLevel::INFO, 'ジョブが更新されました', [
            'organizationId' => $staff->organizationId,
            'staffId' => $staff->id,
            'status' => (string)JobStatus::success()->value(),
        ]);

        $I->setCookieFromResponse();
        $I->sendGET("jobs/{$job['token']}");
        $I->seeResponseCodeIs(HttpCode::OK);
        $job = $I->grabResponseArray()['job'];
        assertEquals(JobStatus::success()->value(), $job['status'], Json::encode($job['data'] ?? []));
    }

    /**
     * IDの事業者が異なっていると400が返るテスト.
     *
     * @param \ApiTester $I
     */
    public function failedWithBadRequestWhenIdIsNotInOrganization(ApiTester $I)
    {
        $I->wantTo('failed with BadRequest when ID is not in Organization');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);

        $I->sendPOST('attendances/confirmation', ['ids' => [$this->examples->attendances[2]->id]]);

        $I->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $I->seeLogCount(0);
    }

    /**
     * IDが認可された事業所にいない場合400を返すテスト.
     *
     * @param \ApiTester $I
     */
    public function failedWithBadRequestWhenIdIsNotPermittedOffice(ApiTester $I)
    {
        $I->wantTo('failed with BadRequest when ID is not permitted Office');

        $staff = $this->examples->staffs[28];
        $I->actingAs($staff);

        $I->sendPOST('attendances/confirmation', ['ids' => [$this->examples->attendances[3]->id]]);

        $I->seeResponseCodeIs(HttpCode::BAD_REQUEST);
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

        $I->sendPOST('attendances/confirmation', ['ids' => [$this->examples->attendances[3]->id]]);

        $I->seeResponseCodeIs(HttpCode::FORBIDDEN);
        $I->seeLogCount(0);
    }
}

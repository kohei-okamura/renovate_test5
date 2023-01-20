<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Api\Shift;

use ApiTester;
use Codeception\Util\HttpCode;
use Domain\Common\Carbon;
use Domain\Job\JobStatus;
use Domain\Shift\Shift;
use function PHPUnit\Framework\assertNotEmpty;
use Psr\Log\LogLevel;
use ScalikePHP\Seq;
use Tests\Api\Mixins\TransactionMixin;
use Tests\Unit\Examples\ExamplesConsumer;

/**
 * Shift bulkCancelのテスト.
 * POST /shifts/cancel
 */
class BulkCancelShiftCest extends ShiftTest
{
    use ExamplesConsumer;
    use TransactionMixin;

    /**
     * API呼び出しテスト.
     *
     * @param ApiTester $I
     */
    public function succeedAPICall(ApiTester $I)
    {
        $I->wantTo('succeed API Call.');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $ids = Seq::fromArray($this->examples->shifts)
            ->filter(fn (Shift $x): bool => $x->organizationId === $staff->organizationId)
            ->filter(fn (Shift $x): bool => !$x->isCanceled)
            ->filter(fn (Shift $x): bool => !$x->schedule->start->lt(Carbon::now()))
            ->map(fn (Shift $x): int => $x->id)
            ->toArray();
        $param = ['ids' => $ids, 'reason' => 'キャンセル理由'];

        $I->sendPOST('/shifts/cancel', $param);

        $I->seeResponseCodeIs(HttpCode::ACCEPTED);
        // ログ確認
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
        $I->seeLogMessage(1, LogLevel::INFO, '勤務シフトがキャンセルされました', [
            'id' => '',  // TODO DEV-1577
            'organizationId' => $staff->organizationId,
            'staffId' => $staff->id,
        ]);
        $I->seeLogMessage(2, LogLevel::INFO, 'ジョブが更新されました', [
            'organizationId' => $staff->organizationId,
            'staffId' => $staff->id,
            'status' => (string)JobStatus::success()->value(),
        ]);
    }

    /**
     * IDが事業者に所属していない場合に400が返るテスト.
     *
     * @param \ApiTester $I
     */
    public function failedWithBadRequestWhenIdIsNotInOrganization(ApiTester $I)
    {
        $I->wantTo('failed with BadRequest when ID is not in Organization');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $ids = Seq::fromArray($this->examples->shifts)
            ->filter(fn (Shift $x): bool => $x->organizationId !== $staff->organizationId)
            ->filter(fn (Shift $x): bool => !$x->isCanceled)
            ->map(fn (Shift $x): int => $x->id)
            ->toArray();
        assertNotEmpty($ids, 'exampleの変更によってテストが実施できません');
        $param = ['ids' => $ids, 'reason' => 'キャンセル理由'];

        $I->sendPOST('/shifts/cancel', $param);

        $I->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $I->seeResponseContainsJson(['errors' => ['ids' => ['存在しないIDまたはキャンセル済みのIDが含まれています。']]]);
        $I->seeLogCount(0);
    }

    /**
     * IDがアクセス可能な事業所に所属していない場合に400が返るテスト.
     *
     * @param \ApiTester $I
     */
    public function failedWithBadRequestWhenIdIsNotInPermittedOffice(ApiTester $I)
    {
        $I->wantTo('failed with BadRequest when ID is not in permitted Office');

        $staff = $this->examples->staffs[28];
        $I->actingAs($staff);
        $ids = Seq::fromArray($this->examples->shifts)
            ->filter(fn (Shift $x): bool => $x->organizationId === $staff->organizationId)
            ->filter(fn (Shift $x): bool => !in_array($x->officeId, $staff->officeIds, true))
            ->map(fn (Shift $m): int => $m->id)
            ->toArray();
        assertNotEmpty($ids, 'exampleの変更によってテストが実施できません');
        $param = ['ids' => $ids, 'reason' => 'キャンセル理由'];

        $I->sendPOST('/shifts/cancel', $param);

        $I->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $I->seeResponseContainsJson(['errors' => ['ids' => ['存在しないIDまたはキャンセル済みのIDが含まれています。']]]);
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
        $ids = Seq::fromArray($this->examples->shifts)
            ->filter(fn (Shift $x): bool => $x->organizationId === $staff->organizationId)
            ->filter(fn (Shift $x): bool => !in_array($x->officeId, $staff->officeIds, true))
            ->map(fn (Shift $m): int => $m->id)
            ->toArray();
        $param = ['ids' => $ids, 'reason' => 'キャンセル理由'];

        $I->sendPOST('/shifts/cancel', $param);

        $I->seeResponseCodeIs(HttpCode::FORBIDDEN);
        $I->seeLogCount(0);
    }

    /**
     * 開始日が過去の勤務シフトは編集できないテスト.
     *
     * @param ApiTester $I
     */
    public function failWhenStartDateTimeIsPast(ApiTester $I)
    {
        $I->wantTo('fail when start date time is past');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $param = ['ids' => [$this->examples->shifts[11]->id], 'reason' => 'キャンセル理由'];

        $I->sendPOST('/shifts/cancel', $param);

        $I->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $I->seeResponseContainsJson(['errors' => ['ids' => ['過去の勤務シフト(12)はキャンセルできません。']]]);
        $I->seeLogCount(0);
    }
}

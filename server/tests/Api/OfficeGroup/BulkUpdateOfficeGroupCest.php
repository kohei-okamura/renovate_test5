<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Api\OfficeGroup;

use ApiTester;
use Codeception\Util\HttpCode;
use Domain\Office\OfficeGroup;
use Lib\Arrays;
use Psr\Log\LogLevel;
use ScalikePHP\Seq;
use Tests\Api\Mixins\TransactionMixin;
use Tests\Unit\Examples\ExamplesConsumer;

/**
 * OfficeGroup bulkUpdate のテスト.
 * PUT /office-groups
 */
class BulkUpdateOfficeGroupCest extends OfficeGroupTest
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

        $I->sendPUT(
            'office-groups',
            $this->buildRequestParameter(Seq::fromArray($this->examples->officeGroups)->take(3))
        );

        $I->seeResponseCodeIs(HttpCode::NO_CONTENT);
        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::INFO, '事業所グループが一括更新されました', [
            'id' => '*',
            'organizationId' => $staff->organizationId,
            'staffId' => $staff->id,
        ]);
    }

    /**
     * ParentグループIDを指定して正常呼び出しとなるテスト.
     *
     * @param ApiTester $I
     */
    public function succeedAPICallWithParentGroupId(ApiTester $I)
    {
        $I->wantTo('succeed API Call with parent group ID');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);

        $I->sendPUT(
            'office-groups',
            $this->buildRequestParameter(
                Seq::fromArray([
                    $this->examples->officeGroups[0]->copy(['parentOfficeGroupId' => $this->examples->officeGroups[1]->id]),
                ]),
            )
        );

        $I->seeResponseCodeIs(HttpCode::NO_CONTENT);
        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::INFO, '事業所グループが一括更新されました', [
            'id' => '*',
            'organizationId' => $staff->organizationId,
            'staffId' => $staff->id,
        ]);
    }

    /**
     * SortOrder を 入れ子にして正常に処理が行われるテスト.
     *
     * @param \ApiTester $I
     */
    public function succeedSwappingSortOrderInTheParentOfficeGroup(ApiTester $I)
    {
        $I->wantTo('succeed swapping sortOrder in the ParentOfficeGroup');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);

        /** @var array|\Domain\Office\OfficeGroup[] $originals */
        $originals = Seq::fromArray($this->examples->officeGroups)
            ->filter(fn (OfficeGroup $x): bool => $x->parentOfficeGroupId === $this->examples->officeGroups[0]->id)
            ->take(2)
            ->toArray();
        $I->sendPUT(
            'office-groups',
            $this->buildRequestParameter(
                Seq::from(
                    $originals[0]->copy(['sortOrder' => $originals[1]->sortOrder]),
                    $originals[1]->copy(['sortOrder' => $originals[0]->sortOrder]),
                )
            ),
        );

        $I->seeResponseCodeIs(HttpCode::NO_CONTENT);
        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::INFO, '事業所グループが一括更新されました', [
            'id' => '*',
            'organizationId' => $staff->organizationId,
            'staffId' => $staff->id,
        ]);
    }

    /**
     * 無効なIDが含まれている場合に400が返るテスト.
     *
     * @param ApiTester $I
     */
    public function failedWithBadRequestWhenIncludeInvalidId(ApiTester $I)
    {
        $I->wantTo('failed with BadRequest when include invalid ID');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);

        $I->sendPUT(
            'office-groups',
            $this->buildRequestParameter(
                Seq::fromArray([
                    $this->examples->officeGroups[0],
                    $this->examples->officeGroups[1]->copy(['id' => self::NOT_EXISTING_ID]),
                ])
            )
        );

        $I->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $I->seeLogCount(0);
    }

    /**
     * 指定した親グループが他の事業者だった場合に400が返るテスト.
     *
     * @param ApiTester $I
     */
    public function failedWithBadRequestWhenParentGroupIsOtherOrganization(ApiTester $I)
    {
        $I->wantTo('failed with BAD REQUEST when parent group is other organization');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);

        $I->sendPUT(
            'office-groups',
            $this->domainToArray(
                Seq::fromArray([
                    $this->examples->officeGroups[0],
                    $this->examples->officeGroups[0]->copy([
                        'parentOfficeGroupId' => 4,
                    ]),
                ])
            )
        );

        $I->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $I->seeLogCount(0);
        $I->seeResponseContainsJson(['errors' => ['list' => ['入力してください。']]]);
    }

    /**
     * 指定したグループが他の事業者だった場合に400が返るテスト.
     *
     * @param ApiTester $I
     */
    public function failedWithBadRequestWhenGroupIsOtherOrganization(ApiTester $I)
    {
        $I->wantTo('failed with BAD REQUEST when group is other organization');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);

        $I->sendPUT(
            'office-groups',
            $this->domainToArray(
                Seq::fromArray([
                    $this->examples->officeGroups[0],
                    $this->examples->officeGroups[3],
                ])
            )
        );

        $I->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $I->seeLogCount(0);
        $I->seeResponseContainsJson(['errors' => ['list' => ['入力してください。']]]);
    }

    /**
     * リクエストパラメータを組み立てる.
     * 'list' の中身のSeqを渡して、リクエストパラメータを返す
     *
     * @param \Domain\Office\OfficeGroup|\ScalikePHP\Seq $bulkUpdateList
     * @return array
     */
    private function buildRequestParameter(Seq $bulkUpdateList): array
    {
        $list = $bulkUpdateList->map(function (OfficeGroup $x): array {
            return Arrays::generate(function () use ($x): iterable {
                foreach ($x->toAssoc() as $key => $value) {
                    if (!empty($value)) {
                        yield $key => $value;
                    }
                }
            });
        });
        return $this->domainToArray(compact('list'));
    }
}

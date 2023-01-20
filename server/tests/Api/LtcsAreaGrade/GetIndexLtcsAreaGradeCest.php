<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Api\LtcsAreaGrade;

use ApiTester;
use Codeception\Util\HttpCode;
use Domain\LtcsAreaGrade\LtcsAreaGrade;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;

/**
 * LtcsAreaGrade getIndex のテスト.
 * GET /ltcs-area-grades
 */
class GetIndexLtcsAreaGradeCest extends LtcsAreaGradeTest
{
    use ExamplesConsumer;

    // tests

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
        $expected = Seq::fromArray($this->examples->ltcsAreaGrades)
            ->sortBy(fn (LtcsAreaGrade $x): int => $x->id)
            ->map(fn (LtcsAreaGrade $x): array => $this->domainToArray($x))
            ->toArray();

        $I->sendGET('ltcs-area-grades');

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
        $I->seeResponseContainsPaginationJson($expected, 0, 10, 'id');
        $I->seeLogCount(0);
    }

    /**
     * ソート指定テスト.
     *
     * @param ApiTester $I
     */
    public function succeedAPICallWithSortByCode(ApiTester $I)
    {
        $I->wantTo('succeed API call with sort by code');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $expected = Seq::fromArray($this->examples->ltcsAreaGrades)
            ->sortBy(fn (LtcsAreaGrade $x): string => $x->code)
            ->map(fn (LtcsAreaGrade $x): array => $this->domainToArray($x))
            ->toArray();

        $I->sendGET('ltcs-area-grades', ['sortBy' => 'code']);

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
        $I->seeResponseContainsPaginationJson($expected, 0, 10, 'code');
        $I->seeLogCount(0);
    }
}

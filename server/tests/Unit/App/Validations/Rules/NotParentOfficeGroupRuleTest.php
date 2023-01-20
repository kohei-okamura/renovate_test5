<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\App\Validations\Rules;

use App\Validations\CustomValidator;
use Domain\Common\Pagination;
use Domain\FinderResult;
use ScalikePHP\Seq;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\FindOfficeGroupUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;

/**
 * {@link \App\Validations\Rules\NotParentOfficeGroupRule} のテスト.
 */
final class NotParentOfficeGroupRuleTest extends Test
{
    use ExamplesConsumer;
    use FindOfficeGroupUseCaseMixin;
    use MatchesSnapshots;
    use MockeryMixin;
    use RuleTestSupport;
    use UnitSupport;

    /**
     * 初期化処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachTest(function (self $self): void {
        });
        self::beforeEachSpec(function (self $self): void {
            $self->findOfficeGroupUseCase
                ->allows('handle')
                ->andReturn(FinderResult::create([
                    'list' => Seq::emptySeq(),
                    'pagination' => Pagination::create(),
                ]))
                ->byDefault();
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_validateNotParentOfficeGroup(): void
    {
        $this->should('pass when the specified id is not parent of any office-groups', function (): void {
            $this->assertTrue(
                $this->notParentOfficeGroupValidator(
                    $this->examples->officeGroups[2]->id,
                )
                    ->passes()
            );
        });
        $this->should('fail when the specified id is parent of some office-groups', function (): void {
            $officeGroupIds = [$this->examples->officeGroups[0]->id];
            $this->findOfficeGroupUseCase
                ->expects('handle')
                ->with(
                    $this->context,
                    ['parentOfficeGroupIds' => $officeGroupIds],
                    ['all' => true]
                )
                ->andReturn(FinderResult::create([
                    'list' => Seq::fromArray([$this->examples->offices[0]]),
                    'pagination' => Pagination::create(),
                ]));
            $this->assertTrue(
                $this->notParentOfficeGroupValidator(
                    $this->examples->officeGroups[0]->id,
                )
                    ->fails()
            );
        });
        $this->should('use UseCase', function (): void {
            $officeGroupIds = [$this->examples->officeGroups[2]->id];
            $this->findOfficeGroupUseCase
                ->expects('handle')
                ->with(
                    $this->context,
                    ['parentOfficeGroupIds' => $officeGroupIds],
                    ['all' => true]
                )
                ->andReturn(FinderResult::create([
                    'list' => Seq::emptySeq(),
                    'pagination' => Pagination::create(),
                ]));
            $this->notParentOfficeGroupValidator(
                $this->examples->officeGroups[2]->id,
            )->validate();
        });
        $this->should('return false when the specified id is not numeric', function (): void {
            $this->assertTrue(
                $this->notParentOfficeGroupValidator(
                    'invalid',
                )->fails()
            );
        });
    }

    /**
     * 入力値のIDの事業所グループが、親事業所グループではないことを検証するバリデータを生成する.
     *
     * @param int|string $id
     * @return CustomValidator
     */
    private function notParentOfficeGroupValidator($id): CustomValidator
    {
        return $this->buildCustomValidator(
            ['id' => $id],
            ['id' => 'not_parent_office_group'],
        );
    }
}

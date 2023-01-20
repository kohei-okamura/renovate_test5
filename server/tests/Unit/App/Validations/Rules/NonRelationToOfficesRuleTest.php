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
use Domain\Permission\Permission;
use ScalikePHP\Seq;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\FindOfficeUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;

/**
 * {@link \App\Validations\Rules\NonRelationToOfficesRule} のテスト.
 */
final class NonRelationToOfficesRuleTest extends Test
{
    use ExamplesConsumer;
    use FindOfficeUseCaseMixin;
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
            $self->findOfficeUseCase
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
    public function describe_validateNonRelationToOffices(): void
    {
        $this->should('pass when the specified id is not related to any offices', function ($id): void {
            $this->assertTrue(
                $this->nonRelationToOfficesValidator(
                    $id,
                    Permission::deleteOfficeGroups()
                )
                    ->passes()
            );
        }, [
            'examples' => [
                'when a id' => [$this->examples->officeGroups[2]->id],
                'when multi ids' => [[$this->examples->officeGroups[2]->id, $this->examples->officeGroups[3]->id]],
            ],
        ]);
        $this->should('fail when the specified id is related to any offices', function (): void {
            $officeGroupIds = [$this->examples->officeGroups[0]->id];
            $this->findOfficeUseCase
                ->expects('handle')
                ->with(
                    $this->context,
                    [Permission::deleteOfficeGroups()],
                    ['officeGroupIds' => $officeGroupIds],
                    ['all' => true]
                )
                ->andReturn(FinderResult::create([
                    'list' => Seq::fromArray([$this->examples->offices[0]]),
                    'pagination' => Pagination::create(),
                ]));
            $this->assertTrue(
                $this->nonRelationToOfficesValidator(
                    $this->examples->officeGroups[0]->id,
                    Permission::deleteOfficeGroups()
                )
                    ->fails()
            );
        });
        $this->should('use UseCase', function (): void {
            $officeGroupIds = [$this->examples->officeGroups[2]->id];
            $this->findOfficeUseCase
                ->expects('handle')
                ->with(
                    $this->context,
                    [Permission::deleteOfficeGroups()],
                    ['officeGroupIds' => $officeGroupIds],
                    ['all' => true]
                )
                ->andReturn(FinderResult::create([
                    'list' => Seq::empty(),
                    'pagination' => Pagination::create(),
                ]));
            $this->nonRelationToOfficesValidator(
                $this->examples->officeGroups[2]->id,
                Permission::deleteOfficeGroups()
            )->validate();
        });
        $this->should('return false when the specified id is not numeric', function (): void {
            $this->assertTrue(
                $this->nonRelationToOfficesValidator(
                    'invalid',
                    Permission::deleteOfficeGroups()
                )->fails()
            );
        });
    }

    /**
     * 入力値のIDの事業所グループに、事業所が紐付いていないことを検証するバリデータを生成する.
     *
     * @param array|int|string $id
     * @param \Domain\Permission\Permission $permission
     * @return CustomValidator
     */
    private function nonRelationToOfficesValidator($id, Permission $permission): CustomValidator
    {
        return $this->buildCustomValidator(
            ['id' => $id],
            ['id' => 'non_relation_to_offices:' . $permission],
        );
    }
}

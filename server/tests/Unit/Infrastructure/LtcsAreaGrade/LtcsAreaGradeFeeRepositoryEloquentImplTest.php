<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Infrastructure\LtcsAreaGrade;

use Domain\Common\Carbon;
use Domain\Common\Decimal;
use Domain\LtcsAreaGrade\LtcsAreaGradeFee;
use Infrastructure\LtcsAreaGrade\LtcsAreaGradeFeeRepositoryEloquentImpl;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\DatabaseMixin;
use Tests\Unit\Test;

/**
 * {@link \Infrastructure\LtcsAreaGrade\LtcsAreaGradeFeeRepositoryEloquentImpl} のテスト.
 */
final class LtcsAreaGradeFeeRepositoryEloquentImplTest extends Test
{
    use CarbonMixin;
    use DatabaseMixin;
    use ExamplesConsumer;
    use UnitSupport;

    private LtcsAreaGradeFeeRepositoryEloquentImpl $repository;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachSpec(function (LtcsAreaGradeFeeRepositoryEloquentImplTest $self): void {
            $self->repository = app(LtcsAreaGradeFeeRepositoryEloquentImpl::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_lookup(): void
    {
        $this->should('return an entity when the id exists in db', function (): void {
            $expected = $this->examples->ltcsAreaGradeFees[0];
            $actual = $this->repository->lookup($this->examples->ltcsAreaGradeFees[0]->id);
            $this->AssertCount(1, $actual);
            $this->assertModelStrictEquals($expected, $actual->head());
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_store(): void
    {
        $this->should('add the entity via repository', function (): void {
            $attrs = [
                'ltcsAreaGradeId' => $this->examples->ltcsAreaGrades[0]->id,
                'effectivatedOn' => Carbon::now(),
                'fee' => Decimal::fromInt(11_0000),
            ];
            $entity = LtcsAreaGradeFee::create($attrs);
            $stored = $this->repository->store($entity);
            $expected = $this->repository->lookup($stored->id);
            $this->assertCount(1, $expected);
            $this->assertModelStrictEquals($expected->head(), $stored);
        });
        $this->should('update the entity', function (): void {
            $entity = $this->examples->ltcsAreaGradeFees[0]->copy([
                'fee' => Decimal::fromInt($this->examples->ltcsAreaGradeFees[0]->fee->toInt() * 2),
            ]);
            // 事前に存在していることの確認
            $before = $this->repository->lookup($entity->id);
            $this->assertCount(1, $before);

            $stored = $this->repository->store($entity);
            $this->assertModelStrictEquals($entity, $stored);

            // 更新されていることを確認
            $expected = $this->repository->lookup($stored->id);
            $this->assertCount(1, $expected);
            $this->assertModelStrictEquals($expected->head(), $stored);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_removeById(): void
    {
        $this->should('remove entity', function (): void {
            $id = $this->examples->ltcsAreaGradeFees[1]->id;
            $this->assertCount(1, $this->repository->lookup($id));

            $this->repository->removeById($id);

            $this->assertCount(0, $this->repository->lookup($id));
        });
        $this->should('run normally when non-exist id specified and not remove', function (): void {
            $id = self::NOT_EXISTING_ID;
            $this->assertCount(0, $this->repository->lookup($id));

            $this->repository->removeById($id);
        });
    }
}

<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\App\Validations\Rules;

use Domain\Permission\Permission;
use Lib\Exceptions\InvalidArgumentException;
use ScalikePHP\Seq;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\LookupShiftUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;
use UseCase\Shift\CreateShiftUseCase;
use UseCase\Shift\LookupShiftUseCase;

/**
 * {@link \App\Validations\Rules\ConfirmedIdRule} のテスト.
 */
final class ConfirmedIdRuleTest extends Test
{
    use ContextMixin;
    use ExamplesConsumer;
    use MatchesSnapshots;
    use LookupShiftUseCaseMixin;
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
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_validateConfirmedId(): void
    {
        $this->should('pass when LookupUseCase return entity with isConfirmed=true', function (): void {
            $this->lookupShiftUseCase
                ->expects('handle')
                ->with($this->context, Permission::updateShifts(), $this->examples->shifts[0]->id)
                ->andReturn(Seq::from($this->examples->shifts[0]->copy([
                    'isConfirmed' => true,
                ])));
            $validator = $this->buildCustomValidator(
                ['value' => $this->examples->shifts[0]->id],
                ['value' => 'confirmed_id:' . LookupShiftUseCase::class . ',' . Permission::updateShifts()],
            );

            $this->assertTrue($validator->passes());
        });
        $this->should('fail when LookupUseCase return entity with isConfirmed=false', function (): void {
            $this->lookupShiftUseCase
                ->expects('handle')
                ->with($this->context, Permission::updateShifts(), $this->examples->shifts[0]->id)
                ->andReturn(Seq::from($this->examples->shifts[0]->copy([
                    'isConfirmed' => false,
                ])));
            $validator = $this->buildCustomValidator(
                ['value' => $this->examples->shifts[0]->id],
                ['value' => 'confirmed_id:' . LookupShiftUseCase::class . ',' . Permission::updateShifts()],
            );

            $this->assertTrue($validator->fails());
        });
        $this->should('fail when value is non-numeric', function (): void {
            $validator = $this->buildCustomValidator(
                ['value' => 'INVALID'],
                ['value' => 'confirmed_id:' . LookupShiftUseCase::class . ',' . Permission::updateShifts()],
            );

            $this->assertTrue($validator->fails());
        });
        $this->should('throw Exception when argument number 1 is not valid UseCase', function (): void {
            $validator = $this->buildCustomValidator(
                ['value' => $this->examples->shifts[0]->id],
                ['value' => 'confirmed_id:' . CreateShiftUseCase::class . ',' . Permission::updateShifts()],
            );

            $this->assertThrows(
                InvalidArgumentException::class,
                function () use ($validator): void {
                    $validator->passes();
                }
            );
        });
    }
}

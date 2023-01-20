<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\App\Validations\Rules;

use Domain\Common\Carbon;
use Domain\Permission\Permission;
use ScalikePHP\Option;
use ScalikePHP\Seq;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\FindOfficeUseCaseMixin;
use Tests\Unit\Mixins\IdentifyLtcsInsCardUseCaseMixin;
use Tests\Unit\Mixins\LookupUserUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;

/**
 * {@link \App\Validations\Rules\NoLtcsInsCardThreeMoreThanValidRule} のテスト.
 */
final class NoLtcsInsCardThreeMoreThanValidRuleTest extends Test
{
    use IdentifyLtcsInsCardUseCaseMixin;
    use LookupUserUseCaseMixin;
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
            $self->lookupUserUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->examples->users[0]))
                ->byDefault();
            $self->identifyLtcsInsCardUseCase
                ->allows()
                ->andReturn(Option::from($self->examples->ltcsInsCards[0]))
                ->byDefault();
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_validateNoLtcsInsCardThreeMoreThanValidRule(): void
    {
        $this->should('return true when user is empty', function (): void {
            $this->lookupUserUseCase
                ->allows('handle')
                ->andReturn(Seq::empty())
                ->byDefault();
            $this->assertTrue(
                $this->buildCustomValidator(
                    [
                        'id' => $this->examples->ltcsInsCards[0]->id,
                        'userId' => $this->examples->users[0]->id,
                        'values' => '2020-01-01',
                    ],
                    ['values' => 'no_ltcs_ins_card_three_more_than_valid:' . Permission::createLtcsInsCards()]
                )->passes()
            );
        });
        $this->should('return true when update insCard and one insCard exists', function (): void {
            $this->identifyLtcsInsCardUseCase
                ->expects('handle')
                ->with($this->context, $this->examples->users[0], equalTo(Carbon::parse('2020-01-01')->startOfMonth()))
                ->andReturn(Option::from($this->examples->ltcsInsCards[0]));
            $this->identifyLtcsInsCardUseCase
                ->expects('handle')
                ->with($this->context, $this->examples->users[0], equalTo(Carbon::parse('2020-01-01')->endOfMonth()))
                ->andReturn(Option::none());
            $this->assertTrue(
                $this->buildCustomValidator(
                    [
                        'id' => $this->examples->ltcsInsCards[0]->id,
                        'userId' => $this->examples->users[0]->id,
                        'values' => '2020-01-01',
                    ],
                    ['values' => 'no_ltcs_ins_card_three_more_than_valid:' . Permission::createLtcsInsCards()]
                )->passes()
            );
        });
        $this->should('return true when update insCard and two insCard exists', function (): void {
            $this->identifyLtcsInsCardUseCase
                ->expects('handle')
                ->with($this->context, $this->examples->users[0], equalTo(Carbon::parse('2020-01-01')->startOfMonth()))
                ->andReturn(Option::from($this->examples->ltcsInsCards[0]));
            $this->identifyLtcsInsCardUseCase
                ->expects('handle')
                ->with($this->context, $this->examples->users[0], equalTo(Carbon::parse('2020-01-01')->endOfMonth()))
                ->andReturn(Option::from($this->examples->ltcsInsCards[1]));
            $this->assertTrue(
                $this->buildCustomValidator(
                    [
                        'id' => $this->examples->ltcsInsCards[0]->id,
                        'userId' => $this->examples->users[0]->id,
                        'values' => '2020-01-01',
                    ],
                    ['values' => 'no_ltcs_ins_card_three_more_than_valid:' . Permission::createLtcsInsCards()]
                )->passes()
            );
        });
        $this->should('return true when create insCard and one insCard exists', function (): void {
            $this->identifyLtcsInsCardUseCase
                ->expects('handle')
                ->with($this->context, $this->examples->users[0], equalTo(Carbon::parse('2020-01-01')->startOfMonth()))
                ->andReturn(Option::from($this->examples->ltcsInsCards[0]));
            $this->identifyLtcsInsCardUseCase
                ->expects('handle')
                ->with($this->context, $this->examples->users[0], equalTo(Carbon::parse('2020-01-01')->endOfMonth()))
                ->andReturn(Option::from($this->examples->ltcsInsCards[0]));
            $this->assertTrue(
                $this->buildCustomValidator(
                    [
                        'userId' => $this->examples->users[0]->id,
                        'values' => '2020-01-01',
                    ],
                    ['values' => 'no_ltcs_ins_card_three_more_than_valid:' . Permission::createLtcsInsCards()]
                )->passes()
            );
        });
        $this->should('return false when create insCard and two insCard exists', function (): void {
            $this->identifyLtcsInsCardUseCase
                ->expects('handle')
                ->with($this->context, $this->examples->users[0], equalTo(Carbon::parse('2020-01-01')->startOfMonth()))
                ->andReturn(Option::from($this->examples->ltcsInsCards[0]));
            $this->identifyLtcsInsCardUseCase
                ->expects('handle')
                ->with($this->context, $this->examples->users[0], equalTo(Carbon::parse('2020-01-01')->endOfMonth()))
                ->andReturn(Option::from($this->examples->ltcsInsCards[1]));
            $this->assertTrue(
                $this->buildCustomValidator(
                    [
                        'userId' => $this->examples->users[0]->id,
                        'values' => '2020-01-01',
                    ],
                    ['values' => 'no_ltcs_ins_card_three_more_than_valid:' . Permission::createLtcsInsCards()]
                )->fails()
            );
        });
        $this->should('return false when update insCard and two other insCard exists', function (): void {
            $this->identifyLtcsInsCardUseCase
                ->expects('handle')
                ->with($this->context, $this->examples->users[0], equalTo(Carbon::parse('2020-01-01')->startOfMonth()))
                ->andReturn(Option::from($this->examples->ltcsInsCards[0]));
            $this->identifyLtcsInsCardUseCase
                ->expects('handle')
                ->with($this->context, $this->examples->users[0], equalTo(Carbon::parse('2020-01-01')->endOfMonth()))
                ->andReturn(Option::from($this->examples->ltcsInsCards[1]));
            $this->assertTrue(
                $this->buildCustomValidator(
                    [
                        'id' => $this->examples->ltcsInsCards[3]->id,
                        'userId' => $this->examples->users[0]->id,
                        'values' => '2020-01-01',
                    ],
                    ['values' => 'no_ltcs_ins_card_three_more_than_valid:' . Permission::createLtcsInsCards()]
                )->fails()
            );
        });
    }
}

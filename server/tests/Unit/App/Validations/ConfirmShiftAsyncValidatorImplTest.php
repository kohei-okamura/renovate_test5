<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\App\Validations;

use App\Validations\ConfirmShiftAsyncValidatorImpl;
use Domain\Common\Pagination;
use Domain\Context\Context;
use Domain\FinderResult;
use Domain\Permission\Permission;
use Lib\Exceptions\ValidationException;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\FindShiftUseCaseMixin;
use Tests\Unit\Mixins\LookupShiftUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\SessionMixin;
use Tests\Unit\Test;

/**
 * {@link \App\Validations\ConfirmShiftAsyncValidatorImpl} のテスト.
 */
final class ConfirmShiftAsyncValidatorImplTest extends Test
{
    use CarbonMixin;
    use ContextMixin;
    use ExamplesConsumer;
    use FindShiftUseCaseMixin;
    use MockeryMixin;
    use SessionMixin;
    use UnitSupport;
    use LookupShiftUseCaseMixin;

    private ConfirmShiftAsyncValidatorImpl $validator;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (ConfirmShiftAsyncValidatorImplTest $self): void {
            $self->findShiftUseCase
                ->allows('handle')
                ->andReturn(
                    FinderResult::from(
                        [
                            $self->examples->shifts[0],
                            $self->examples->shifts[2],
                            $self->examples->shifts[3],
                            $self->examples->shifts[8],
                        ],
                        Pagination::create(['all' => true])
                    )
                );
            $self->lookupShiftUseCase
                ->allows('handle')
                ->with(anInstanceOf(Context::class), Permission::updateShifts(), $self->examples->shifts[0]->id)
                ->andReturn(Seq::from($self->examples->shifts[0]))
                ->byDefault();
            $self->lookupShiftUseCase
                ->allows('handle')
                ->with(anInstanceOf(Context::class), Permission::updateShifts(), $self->examples->shifts[2]->id)
                ->andReturn(Seq::from($self->examples->shifts[2]))
                ->byDefault();
            $self->lookupShiftUseCase
                ->allows('handle')
                ->with(anInstanceOf(Context::class), Permission::updateShifts(), $self->examples->shifts[8]->id)
                ->andReturn(Seq::from($self->examples->shifts[8]))
                ->byDefault();
            $self->lookupShiftUseCase
                ->allows('handle')
                ->with(anInstanceOf(Context::class), Permission::updateShifts(), self::NOT_EXISTING_ID)
                ->andReturn(Seq::emptySeq())
                ->byDefault();
            $self->lookupShiftUseCase
                ->allows('handle')
                ->with(
                    anInstanceOf(Context::class),
                    Permission::updateShifts(),
                    $self->examples->shifts[0]->id,
                    $self->examples->shifts[2]->id
                )
                ->andReturn(Seq::fromArray([$self->examples->shifts[0], $self->examples->shifts[2]]))
                ->byDefault();
            $self->lookupShiftUseCase
                ->allows('handle')
                ->with(
                    anInstanceOf(Context::class),
                    Permission::updateShifts(),
                    $self->examples->shifts[0]->id,
                    $self->examples->shifts[2]->id,
                    $self::NOT_EXISTING_ID
                )
                ->andReturn(Seq::emptySeq())
                ->byDefault();

            $self->validator = app(ConfirmShiftAsyncValidatorImpl::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_validate(): void
    {
        $this->should('not throw ValidationException when the data passes the validation rules', function (): void {
            $this->validator->validate($this->context, $this->defaultInput());
        });

        $examples = [
            'when ids.* is already double booking' => [
                ['ids.0は勤務シフトが重複しています。'],
                ['ids' => [$this->examples->shifts[8]->id]],
                $this->defaultInput(),
            ],
        ];
        $this->should(
            'throw ValidationException when the data does not pass the validation rules',
            function ($expected, $invalid, $valid): void {
                try {
                    $this->validator->validate($this->context, $invalid + $this->defaultInput());
                    assert(false, 'unreachable when throw ValidationException');
                } catch (ValidationException $e) {
                    $this->assertSame($expected, Seq::fromArray($e->getErrors())->toArray());
                }
                if ($valid !== null) {
                    $this->validator->validate($this->context, $valid + $this->defaultInput());
                }
            },
            compact('examples')
        );
    }

    /**
     * 入力のデフォルト値.
     *
     * @return array
     */
    private function defaultInput(): array
    {
        return [
            'ids' => [
                $this->examples->shifts[0]->id,
                $this->examples->shifts[2]->id,
            ],
        ];
    }
}

<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\Calling;

use Domain\Common\Carbon;
use Domain\Common\Pagination;
use Domain\FinderResult;
use Domain\Permission\Permission;
use Domain\Shift\Shift;
use Lib\Exceptions\NotFoundException;
use ScalikePHP\Option;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\LoggerMixin;
use Tests\Unit\Mixins\LookupCallingByTokenUseCaseMixin;
use Tests\Unit\Mixins\LookupShiftUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\TransactionManagerMixin;
use Tests\Unit\Test;
use UseCase\Calling\GetShiftsByTokenInteractor;

/**
 * {@link \UseCase\Calling\GetShiftsByTokenInteractor} のテスト.
 */
final class GetShiftsByTokenInteractorTest extends Test
{
    use CarbonMixin;
    use ContextMixin;
    use ExamplesConsumer;
    use LoggerMixin;
    use LookupCallingByTokenUseCaseMixin;
    use LookupShiftUseCaseMixin;
    use MockeryMixin;
    use TransactionManagerMixin;
    use UnitSupport;

    public const TOKEN = '1234567890abcdefghij1234567890ABCDEFGHIJ12345678901234567890';

    private Seq $callingShifts;
    private GetShiftsByTokenInteractor $interactor;
    private FinderResult $finderResult;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (GetShiftsByTokenInteractorTest $self): void {
            $self->callingShifts = Seq::fromArray([
                $self->examples->shifts[0],
                $self->examples->shifts[1],
            ]);
            $paginationPlams = [
                'count' => $self->callingShifts->count(),
                'desc' => false,
                'itemsPerPage' => $self->callingShifts->count(),
                'page' => 1,
                'pages' => 1,
                'sortBy' => 'date',
            ];
            $pagination = Pagination::create($paginationPlams);
            $self->finderResult = FinderResult::from($self->callingShifts, $pagination);

            $self->lookupCallingByTokenUseCase
                ->allows('handle')
                ->andReturn(Option::from($self->examples->callings[0]))
                ->byDefault();
            $self->lookupShiftUseCase
                ->allows('handle')
                ->andReturn($self->callingShifts)
                ->byDefault();

            $self->interactor = app(GetShiftsByTokenInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('return Json of FinderResult', function (): void {
            $this->assertModelStrictEquals(
                $this->finderResult,
                $this->interactor->handle($this->context, self::TOKEN)
            );
        });
        $this->should('return Json of Shifts sorted by `date`', function (): void {
            $callingShifts = Seq::fromArray([
                $this->examples->shifts[0],
                $this->examples->shifts[1],
                $this->examples->shifts[2],
            ])
                ->sortBy(fn (Shift $x): Carbon => $x->schedule->start);
            $this->lookupShiftUseCase
                ->expects('handle')
                ->andReturn($callingShifts);
            $paginationPlams = [
                'count' => $callingShifts->count(),
                'desc' => false,
                'itemsPerPage' => $callingShifts->count(),
                'page' => 1,
                'pages' => 1,
                'sortBy' => 'date',
            ];
            $pagination = Pagination::create($paginationPlams);
            $finderResult = FinderResult::from($callingShifts, $pagination);

            $this->assertModelStrictEquals(
                $finderResult,
                $this->interactor->handle($this->context, self::TOKEN)
            );
        });
        $this->should('use LookupCallingByTokenUseCase in the process', function (): void {
            $this->lookupCallingByTokenUseCase
                ->expects('handle')
                ->with($this->context, self::TOKEN)
                ->andReturn(Option::from($this->examples->callings[0]));

            $this->interactor->handle($this->context, self::TOKEN);
        });
        $this->should('use LookupShiftUseCase in the process', function (): void {
            $this->lookupShiftUseCase
                ->expects('handle')
                ->with($this->context, Permission::listShifts(), ...$this->examples->callings[0]->shiftIds)
                ->andReturn(Seq::fromArray($this->callingShifts));

            $this->interactor->handle($this->context, self::TOKEN);
        });
        $this->should('throw a NotFoundException when lookupCallingByTokenUseCase return empty', function (): void {
            $this->lookupCallingByTokenUseCase
                ->expects('handle')
                ->with($this->context, self::TOKEN)
                ->andReturn(Option::none());

            $this->assertThrows(
                NotFoundException::class,
                function (): void {
                    $this->interactor->handle($this->context, self::TOKEN);
                }
            );
        });
        $this->should('throw a NotFoundException when LookupShiftUseCase return empty', function (): void {
            $this->lookupShiftUseCase
                ->expects('handle')
                ->with($this->context, Permission::listShifts(), ...$this->examples->callings[0]->shiftIds)
                ->andReturn(Seq::emptySeq());

            $this->assertThrows(
                NotFoundException::class,
                function (): void {
                    $this->interactor->handle($this->context, self::TOKEN);
                }
            );
        });
    }
}

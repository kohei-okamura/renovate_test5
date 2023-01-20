<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\LtcsInsCard;

use Domain\Common\Carbon;
use Domain\Common\Pagination;
use Domain\FinderResult;
use Domain\LtcsInsCard\LtcsInsCard;
use Domain\Permission\Permission;
use Domain\User\User;
use Mockery;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\LtcsInsCardFinderMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;
use UseCase\LtcsInsCard\IdentifyLtcsInsCardInteractor;

/**
 * {@link \UseCase\LtcsInsCard\IdentifyLtcsInsCardInteractor} のテスト.
 */
final class IdentifyLtcsInsCardInteractorTest extends Test
{
    use CarbonMixin;
    use ContextMixin;
    use LtcsInsCardFinderMixin;
    use ExamplesConsumer;
    use MockeryMixin;
    use UnitSupport;

    private Carbon $targetDate;
    private Permission $permission;
    private LtcsInsCard $ltcsInsCard;
    private User $user;

    private IdentifyLtcsInsCardInteractor $interactor;

    /**
     * 初期化処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachTest(function (self $self): void {
            $self->targetDate = Carbon::create(2021, 1, 25);
            $self->permission = Permission::createBillings();
            $self->ltcsInsCard = $self->examples->ltcsInsCards[0];
            $self->user = Seq::from(...$self->examples->users)
                ->find(fn (User $x): bool => $x->id === $self->ltcsInsCard->userId)
                ->get();
        });
        self::beforeEachSpec(function (self $self): void {
            $self->ltcsInsCardFinder
                ->allows('find')
                ->andReturn(FinderResult::from(Seq::from($self->ltcsInsCard), Pagination::create()))
                ->byDefault();

            $self->interactor = app(IdentifyLtcsInsCardInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('return Option of LtcsInsCard', function (): void {
            $actual = $this->interactor->handle($this->context, $this->user, $this->targetDate);

            $this->assertNotEmpty($actual);
            $this->assertInstanceOf(LtcsInsCard::class, $actual->get());
        });
        $this->should('call finder with specified parameters', function (): void {
            $expectedFilterParams = [
                'organizationId' => $this->context->organization->id,
                'userId' => $this->user->id,
                'effectivatedBefore' => $this->targetDate,
            ];
            $expectedPaginationParams = [
                'itemsPerPage' => 1,
                'sortBy' => 'effectivatedOn',
                'desc' => true,
            ];
            $this->ltcsInsCardFinder
                ->expects('find')
                ->with(Mockery::capture($actualFilterParams), Mockery::capture($actualPaginationParams))
                ->andReturn(FinderResult::from(
                    Seq::from($this->examples->ltcsHomeVisitLongTermCareDictionaries[0]),
                    Pagination::create()
                ));

            $this->interactor->handle($this->context, $this->user, $this->targetDate);

            $this->assertSame($expectedFilterParams, $actualFilterParams);
            $this->assertSame($expectedPaginationParams, $actualPaginationParams);
        });
    }
}

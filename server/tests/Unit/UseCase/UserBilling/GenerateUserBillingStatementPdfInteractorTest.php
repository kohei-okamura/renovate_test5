<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\UserBilling;

use Domain\Common\Carbon;
use Domain\Permission\Permission;
use Domain\UserBilling\UserBilling;
use Lib\Exceptions\NotFoundException;
use ScalikePHP\Seq;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\BuildUserBillingStatementPdfParamUseCaseMixin;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\LookupUserBillingUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\StorePdfUseCaseMixin;
use Tests\Unit\Test;
use UseCase\UserBilling\GenerateUserBillingStatementPdfInteractor;

/**
 * {@link \UseCase\UserBilling\GenerateUserBillingStatementPdfInteractor} のテスト.
 */
final class GenerateUserBillingStatementPdfInteractorTest extends Test
{
    use BuildUserBillingStatementPdfParamUseCaseMixin;
    use ContextMixin;
    use ExamplesConsumer;
    use LookupUserBillingUseCaseMixin;
    use MatchesSnapshots;
    use MockeryMixin;
    use StorePdfUseCaseMixin;
    use UnitSupport;

    private Seq $userBillings;
    private array $ids;
    private Carbon $issuedOn;
    private GenerateUserBillingStatementPdfInteractor $interactor;

    /**
     * 初期化処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachSpec(function (GenerateUserBillingStatementPdfInteractorTest $self): void {
            $self->userBillings = Seq::from(
                $self->examples->userBillings[0],
                $self->examples->userBillings[1]
            );
            $self->ids = $self->userBillings->map(fn (UserBilling $x) => $x->id)->toArray();
            $self->issuedOn = Carbon::parse('2020-01-01');

            $self->lookupUserBillingUseCase
                ->allows('handle')
                ->andReturn($self->userBillings)
                ->byDefault();
            $self->buildUserBillingStatementPdfParamUseCase
                ->allows('handle')
                ->andReturn([])
                ->byDefault();
            $self->storePdfUseCase
                ->allows('handle')
                ->andReturn('path/to/stored-file.pdf')
                ->byDefault();

            $self->interactor = app(GenerateUserBillingStatementPdfInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('lookup UserBilling using LookupUserBillingUseCase', function (): void {
            $this->lookupUserBillingUseCase
                ->expects('handle')
                ->with($this->context, Permission::viewUserBillings(), ...$this->ids)
                ->andReturn($this->userBillings);

            $this->interactor->handle($this->context, $this->ids, $this->issuedOn);
        });
        $this->should('throw NotFoundException when LookupUserBillingUseCase returns empty Seq', function (): void {
            $this->lookupUserBillingUseCase
                ->expects('handle')
                ->andReturn(Seq::empty());
            $this->assertThrows(NotFoundException::class, function (): void {
                $this->interactor->handle($this->context, $this->ids, $this->issuedOn);
            });
        });
        $this->should('build params using BuildUserBillingStatementPdfUseCase', function (): void {
            $params = [];
            $this->buildUserBillingStatementPdfParamUseCase
                ->expects('handle')
                ->with($this->context, $this->userBillings, $this->issuedOn)
                ->andReturn($params);

            $this->interactor->handle($this->context, $this->ids, $this->issuedOn);
        });
        $this->should('store the pdf using StorePdfUseCase', function (): void {
            $template = 'pdfs.user-billings.statement';
            $this->storePdfUseCase
                ->expects('handle')
                ->with($this->context, 'exported', $template, [])
                ->andReturn('path/to/stored-file.pdf');

            $this->interactor->handle($this->context, $this->ids, $this->issuedOn);
        });
        $this->should('return filepath', function (): void {
            $this->assertSame(
                'path/to/stored-file.pdf',
                $this->interactor->handle($this->context, $this->ids, $this->issuedOn)
            );
        });
    }
}

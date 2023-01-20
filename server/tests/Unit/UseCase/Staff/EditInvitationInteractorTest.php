<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\Staff;

use App\Concretes\PermanentDatabaseTransactionManager;
use Closure;
use Domain\Staff\Invitation;
use Lib\Exceptions\NotFoundException;
use Lib\Json;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\InvitationRepositoryMixin;
use Tests\Unit\Mixins\LoggerMixin;
use Tests\Unit\Mixins\LookupInvitationUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\OrganizationRepositoryMixin;
use Tests\Unit\Mixins\TransactionManagerMixin;
use Tests\Unit\Test;
use UseCase\Staff\EditInvitationInteractor;

/**
 * {@link \UseCase\Staff\EditInvitationInteractor} のテスト
 */
class EditInvitationInteractorTest extends Test
{
    use CarbonMixin;
    use ContextMixin;
    use ExamplesConsumer;
    use InvitationRepositoryMixin;
    use LoggerMixin;
    use LookupInvitationUseCaseMixin;
    use MockeryMixin;
    use OrganizationRepositoryMixin;
    use TransactionManagerMixin;
    use UnitSupport;

    private Invitation $invitation;
    private EditInvitationInteractor $interactor;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (EditInvitationInteractorTest $self): void {
            $self->lookupInvitationUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->examples->invitations[0]))
                ->byDefault();
            $self->invitationRepository
                ->allows('store')
                ->andReturn($self->examples->invitations[0])
                ->byDefault();
            $self->invitationRepository
                ->allows('transactionManager')
                ->andReturn(PermanentDatabaseTransactionManager::class)
                ->passthru();
            $self->logger
                ->allows('info')
                ->andReturnNull()
                ->byDefault();

            $self->invitation = $self->examples->invitations[0];
            $self->interactor = app(EditInvitationInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('edit the Invitation after transaction begun', function (): void {
            $this->transactionManager
                ->expects('run')
                ->andReturnUsing(function (Closure $callback) {
                    $this->lookupInvitationUseCase
                        ->expects('handle')
                        ->with($this->context, $this->invitation->id)
                        ->andReturn(Seq::from($this->invitation));
                    $this->invitationRepository
                        ->expects('store')
                        ->andReturn($this->invitation);
                    return $callback();
                });

            $this->interactor->handle($this->context, $this->invitation->id, $this->payload());
        });
        $this->should('return the Invitation', function (): void {
            $this->assertModelStrictEquals(
                $this->invitation,
                $this->interactor->handle($this->context, $this->invitation->id, $this->payload())
            );
        });
        $this->should('throw a NotFoundException when LookupInvitationUseCase return empty seq', function (): void {
            $this->lookupInvitationUseCase
                ->expects('handle')
                ->with($this->context, $this->invitation->id)
                ->andReturn(Seq::emptySeq());

            $this->assertThrows(NotFoundException::class, function (): void {
                $this->interactor->handle(
                    $this->context,
                    $this->invitation->id,
                    $this->payload()
                );
            });
        });
        $this->should('log using info', function (): void {
            $context = ['organizationId' => $this->examples->organizations[0]->id];
            $this->context
                ->expects('logContext')
                ->andReturn($context);
            $this->logger
                ->expects('info')
                ->with('招待が更新されました', ['id' => $this->invitation->id] + $context);

            $this->interactor->handle(
                $this->context,
                $this->invitation->id,
                $this->payload()
            );
        });
    }

    /**
     * payload が返す配列.
     *
     * @return array
     */
    private function payload(): array
    {
        return Json::decode(Json::encode($this->invitation), true);
    }
}

<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\File;

use function app;
use Domain\Common\Carbon;
use Domain\Permission\Permission;
use Domain\User\User;
use Lib\Exceptions\NotFoundException;
use ScalikePHP\Seq;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\GenerateFileNameUseCaseMixin;
use Tests\Unit\Mixins\LookupUserUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;
use UseCase\File\GenerateFileNameContainsUserNameInteractor;

/**
 * {@link \UseCase\File\GenerateFileNameContainsUserNameInteractor} のテスト.
 */
final class GenerateFileNameContainsUserNameInteractorTest extends Test
{
    use ContextMixin;
    use ExamplesConsumer;
    use GenerateFileNameUseCaseMixin;
    use LookupUserUseCaseMixin;
    use MatchesSnapshots;
    use MockeryMixin;
    use UnitSupport;

    private const FILENAME = 'dummy.pdf';

    private User $user;
    private int $userId;
    private Carbon $providedIn;
    private GenerateFileNameContainsUserNameInteractor $interactor;

    /**
     * 初期化処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachSpec(function (self $self): void {
            $self->user = $self->examples->users[0];
            $self->userId = $self->examples->ltcsProvisionReports[0]->userId;
            $self->providedIn = $self->examples->ltcsProvisionReports[0]->providedIn;
            $self->lookupUserUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->user))
                ->byDefault();
            $self->generateFileNameUseCase
                ->allows('handle')
                ->andReturn(self::FILENAME)
                ->byDefault();
            $self->interactor = app(GenerateFileNameContainsUserNameInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('use LookupUserUseCase', function (): void {
            $this->lookupUserUseCase
                ->expects('handle')
                ->with($this->context, Permission::updateLtcsProvisionReports(), $this->userId)
                ->andReturn(Seq::from($this->user));

            $this->interactor->handle(
                $this->context,
                $this->userId,
                ''
            );
        });

        $this->should('throw NotFoundException when LookupUserUseCase return empty', function (): void {
            $this->lookupUserUseCase
                ->expects('handle')
                ->andReturn(Seq::empty());

            $this->assertThrows(NotFoundException::class, function (): void {
                $this->interactor->handle(
                    $this->context,
                    $this->userId,
                    ''
                );
            });
        });

        $this->should('use GenerateFileNameUseCase', function (): void {
            $fileName = 'example_file_name';
            $this->generateFileNameUseCase
                ->expects('handle')
                ->with($fileName, ['user' => $this->user->name->displayName, 'providedIn' => $this->providedIn])
                ->andReturn(self::FILENAME);

            $this->interactor->handle(
                $this->context,
                $this->userId,
                $fileName,
                ['providedIn' => $this->providedIn]
            );
        });

        $this->should('return file name', function (): void {
            $this->assertSame(
                self::FILENAME,
                $this->interactor->handle(
                    $this->context,
                    $this->userId,
                    ''
                ),
            );
        });
    }
}

<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\Project;

use Domain\Common\Pagination;
use Domain\FinderResult;
use Domain\Office\Office;
use Domain\Permission\Permission;
use Domain\Project\LtcsProject;
use Domain\Project\LtcsProjectServiceMenu;
use Domain\Staff\Staff;
use Domain\User\User;
use Lib\Exceptions\NotFoundException;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\LookupLtcsProjectUseCaseMixin;
use Tests\Unit\Mixins\LtcsProjectServiceMenuFinderMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\OfficeRepositoryMixin;
use Tests\Unit\Mixins\StaffRepositoryMixin;
use Tests\Unit\Mixins\UserRepositoryMixin;
use Tests\Unit\Test;
use UseCase\Project\DownloadLtcsProjectInteractor;

/**
 * {@link \UseCase\Project\DownloadLtcsProjectInteractor} のテスト.
 */
class DownloadLtcsProjectInteractorTest extends Test
{
    use ContextMixin;
    use ExamplesConsumer;
    use LookupLtcsProjectUseCaseMixin;
    use LtcsProjectServiceMenuFinderMixin;
    use MockeryMixin;
    use OfficeRepositoryMixin;
    use StaffRepositoryMixin;
    use UnitSupport;
    use UserRepositoryMixin;

    private DownloadLtcsProjectInteractor $interactor;
    private LtcsProject $ltcsProject;
    private User $user;
    private Staff $staff;
    private Office $office;
    private LtcsProjectServiceMenu $serviceMenu1;
    private LtcsProjectServiceMenu $serviceMenu2;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (DownloadLtcsProjectInteractorTest $self): void {
            $self->ltcsProject = $self->examples->ltcsProjects[0];
            $self->user = $self->examples->users[0];
            $self->staff = $self->examples->staffs[0];
            $self->office = $self->examples->offices[0];
            $self->serviceMenu1 = $self->examples->ltcsProjectServiceMenus[0];
            $self->serviceMenu2 = $self->examples->ltcsProjectServiceMenus[1];
            $self->lookupLtcsProjectUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->ltcsProject))
                ->byDefault();
            $self->userRepository
                ->allows('lookup')
                ->andReturn(Seq::from($self->user))
                ->byDefault();
            $self->staffRepository
                ->allows('lookup')
                ->andReturn(Seq::from($self->staff))
                ->byDefault();
            $self->officeRepository
                ->allows('lookup')
                ->andReturn(Seq::from($self->office))
                ->byDefault();
            $self->officeRepository
                ->allows('lookup')
                ->andReturn(Seq::from($self->office))
                ->byDefault();
            $self->ltcsProjectServiceMenuFinder
                ->allows('find')
                ->andReturn(FinderResult::from(Seq::fromArray($self->examples->ltcsProjectServiceMenus), Pagination::create()))
                ->byDefault();

            $self->interactor = app(DownloadLtcsProjectInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('use LookupLtcsProjectUseCase', function (): void {
            $this->lookupLtcsProjectUseCase
                ->expects('handle')
                ->with($this->context, Permission::viewLtcsProjects(), $this->ltcsProject->userId, $this->ltcsProject->id)
                ->andReturn(Seq::from($this->ltcsProject));

            $this->interactor->handle($this->context, $this->ltcsProject->userId, $this->ltcsProject->id);
        });
        $this->should('use userRepository', function (): void {
            $this->userRepository
                ->expects('lookup')
                ->with($this->ltcsProject->userId)
                ->andReturn(Seq::from($this->user));

            $this->interactor->handle($this->context, $this->ltcsProject->userId, $this->ltcsProject->id);
        });
        $this->should('use staffRepository', function (): void {
            $this->staffRepository
                ->expects('lookup')
                ->with($this->ltcsProject->staffId)
                ->andReturn(Seq::from($this->staff));

            $this->interactor->handle($this->context, $this->ltcsProject->userId, $this->ltcsProject->id);
        });
        $this->should('use officeRepository', function (): void {
            $this->officeRepository
                ->expects('lookup')
                ->with($this->ltcsProject->officeId)
                ->andReturn(Seq::from($this->office));

            $this->interactor->handle($this->context, $this->ltcsProject->userId, $this->ltcsProject->id);
        });
        $this->should('use LtcsProjectServiceMenuFinder', function (): void {
            $this->ltcsProjectServiceMenuFinder
                ->expects('find')
                ->with([], ['sortBy' => 'id'])
                ->andReturn(FinderResult::from(Seq::fromArray($this->examples->ltcsProjectServiceMenus), Pagination::create()));

            $this->interactor->handle($this->context, $this->ltcsProject->userId, $this->ltcsProject->id);
        });
        $this->should('throw NotFoundException when Project is not found', function (): void {
            $this->lookupLtcsProjectUseCase
                ->expects('handle')
                ->with($this->context, Permission::viewLtcsProjects(), $this->ltcsProject->userId, self::NOT_EXISTING_ID)
                ->andReturn(Seq::emptySeq());

            $this->assertThrows(
                NotFoundException::class,
                function (): void {
                    $this->interactor->handle($this->context, $this->ltcsProject->userId, self::NOT_EXISTING_ID);
                }
            );
        });
        $this->should('throw NotFoundException when User is not found', function (): void {
            $this->userRepository
                ->expects('lookup')
                ->andReturn(Seq::emptySeq());

            $this->assertThrows(
                NotFoundException::class,
                function (): void {
                    $this->interactor->handle($this->context, $this->ltcsProject->userId, $this->ltcsProject->id);
                }
            );
        });
        $this->should('throw NotFoundException when Staff is not found', function (): void {
            $this->staffRepository
                ->expects('lookup')
                ->andReturn(Seq::emptySeq());

            $this->assertThrows(
                NotFoundException::class,
                function (): void {
                    $this->interactor->handle($this->context, $this->ltcsProject->userId, $this->ltcsProject->id);
                }
            );
        });
        $this->should('throw NotFoundException when Office is not found', function (): void {
            $this->officeRepository
                ->expects('lookup')
                ->andReturn(Seq::emptySeq());

            $this->assertThrows(
                NotFoundException::class,
                function (): void {
                    $this->interactor->handle($this->context, $this->ltcsProject->userId, $this->ltcsProject->id);
                }
            );
        });
    }
}

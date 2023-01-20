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
use Domain\Project\DwsProject;
use Domain\Project\DwsProjectServiceMenu;
use Domain\Staff\Staff;
use Domain\User\User;
use Lib\Exceptions\NotFoundException;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\DwsProjectServiceMenuFinderMixin;
use Tests\Unit\Mixins\LookupDwsProjectUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\OfficeRepositoryMixin;
use Tests\Unit\Mixins\StaffRepositoryMixin;
use Tests\Unit\Mixins\UserRepositoryMixin;
use Tests\Unit\Test;
use UseCase\Project\DownloadDwsProjectInteractor;

/**
 * DownloadDwsProjectInteractor のテスト.
 */
class DownloadDwsProjectInteractorTest extends Test
{
    use ContextMixin;
    use DwsProjectServiceMenuFinderMixin;
    use ExamplesConsumer;
    use LookupDwsProjectUseCaseMixin;
    use MockeryMixin;
    use OfficeRepositoryMixin;
    use StaffRepositoryMixin;
    use UnitSupport;
    use UserRepositoryMixin;

    private DownloadDwsProjectInteractor $interactor;
    private DwsProject $dwsProject;
    private User $user;
    private Staff $staff;
    private Office $office;
    private DwsProjectServiceMenu $serviceMenu1;
    private DwsProjectServiceMenu $serviceMenu2;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (DownloadDwsProjectInteractorTest $self): void {
            $self->dwsProject = $self->examples->dwsProjects[0];
            $self->user = $self->examples->users[0];
            $self->staff = $self->examples->staffs[0];
            $self->office = $self->examples->offices[0];
            $self->serviceMenu1 = $self->examples->dwsProjectServiceMenus[0];
            $self->serviceMenu2 = $self->examples->dwsProjectServiceMenus[1];
            $self->lookupDwsProjectUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->dwsProject))
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
            $self->dwsProjectServiceMenuFinder
                ->allows('find')
                ->andReturn(FinderResult::from(Seq::fromArray($self->examples->dwsProjectServiceMenus), Pagination::create()))
                ->byDefault();

            $self->interactor = app(DownloadDwsProjectInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('use LookupDwsProjectUseCase', function (): void {
            $this->lookupDwsProjectUseCase
                ->expects('handle')
                ->with($this->context, Permission::viewDwsProjects(), $this->dwsProject->userId, $this->dwsProject->id)
                ->andReturn(Seq::from($this->dwsProject));

            $this->interactor->handle($this->context, $this->dwsProject->userId, $this->dwsProject->id);
        });
        $this->should('use userRepository', function (): void {
            $this->userRepository
                ->expects('lookup')
                ->with($this->dwsProject->userId)
                ->andReturn(Seq::from($this->user));

            $this->interactor->handle($this->context, $this->dwsProject->userId, $this->dwsProject->id);
        });
        $this->should('use staffRepository', function (): void {
            $this->staffRepository
                ->expects('lookup')
                ->with($this->dwsProject->staffId)
                ->andReturn(Seq::from($this->staff));

            $this->interactor->handle($this->context, $this->dwsProject->userId, $this->dwsProject->id);
        });
        $this->should('use officeRepository', function (): void {
            $this->officeRepository
                ->expects('lookup')
                ->with($this->dwsProject->officeId)
                ->andReturn(Seq::from($this->office));

            $this->interactor->handle($this->context, $this->dwsProject->userId, $this->dwsProject->id);
        });
        $this->should('use DwsProjectServiceMenuFinder', function (): void {
            $this->dwsProjectServiceMenuFinder
                ->expects('find')
                ->with([], ['sortBy' => 'id'])
                ->andReturn(FinderResult::from(Seq::fromArray($this->examples->dwsProjectServiceMenus), Pagination::create()));

            $this->interactor->handle($this->context, $this->dwsProject->userId, $this->dwsProject->id);
        });
        $this->should('throw NotFoundException when Project is not found', function (): void {
            $this->lookupDwsProjectUseCase
                ->expects('handle')
                ->with($this->context, Permission::viewDwsProjects(), $this->dwsProject->userId, self::NOT_EXISTING_ID)
                ->andReturn(Seq::emptySeq());

            $this->assertThrows(
                NotFoundException::class,
                function (): void {
                    $this->interactor->handle($this->context, $this->dwsProject->userId, self::NOT_EXISTING_ID);
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
                    $this->interactor->handle($this->context, $this->dwsProject->userId, $this->dwsProject->id);
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
                    $this->interactor->handle($this->context, $this->dwsProject->userId, $this->dwsProject->id);
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
                    $this->interactor->handle($this->context, $this->dwsProject->userId, $this->dwsProject->id);
                }
            );
        });
    }
}

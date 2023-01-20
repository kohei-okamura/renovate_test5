<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\User;

use Domain\Common\Pagination;
use Domain\Common\ServiceSegment;
use Domain\Contract\Contract;
use Domain\FinderResult;
use Domain\Permission\Permission;
use Lib\Exceptions\NotFoundException;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\FindContractUseCaseMixin;
use Tests\Unit\Mixins\FindDwsCertificationUseCaseMixin;
use Tests\Unit\Mixins\FindDwsProjectUseCaseMixin;
use Tests\Unit\Mixins\FindLtcsInsCardUseCaseMixin;
use Tests\Unit\Mixins\FindLtcsProjectUseCaseMixin;
use Tests\Unit\Mixins\FindUserDwsCalcSpecUseCaseMixin;
use Tests\Unit\Mixins\FindUserDwsSubsidyUseCaseMixin;
use Tests\Unit\Mixins\FindUserLtcsCalcSpecUseCaseMixin;
use Tests\Unit\Mixins\FindUserLtcsSubsidyUseCaseMixin;
use Tests\Unit\Mixins\LookupBankAccountUseCaseMixin;
use Tests\Unit\Mixins\LookupUserUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;
use UseCase\User\GetUserInfoInteractor;

/**
 * GetUserInfoInteractor のテスト.
 */
class GetUserInfoInteractorTest extends Test
{
    use ContextMixin;
    use ExamplesConsumer;
    use FindContractUseCaseMixin;
    use FindDwsCertificationUseCaseMixin;
    use FindDwsProjectUseCaseMixin;
    use FindUserLtcsCalcSpecUseCaseMixin;
    use FindLtcsInsCardUseCaseMixin;
    use FindLtcsProjectUseCaseMixin;
    use FindUserDwsCalcSpecUseCaseMixin;
    use FindUserDwsSubsidyUseCaseMixin;
    use FindUserLtcsSubsidyUseCaseMixin;
    use LookupBankAccountUseCaseMixin;
    use LookupUserUseCaseMixin;
    use MockeryMixin;
    use UnitSupport;

    private Seq $contracts;
    private GetUserInfoInteractor $interactor;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (GetUserInfoInteractorTest $self): void {
            $self->contracts = Seq::fromArray($self->examples->contracts)
                ->filter(fn (Contract $x): bool => $x->organizationId === $self->examples->organizations[0]->id);
            $self->lookupUserUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->examples->users[0]))
                ->byDefault();
            $self->lookupBankAccountUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->examples->bankAccounts[0]))
                ->byDefault();
            $self->findContractUseCase
                ->allows('handle')
                ->andReturn(FinderResult::from($self->contracts, Pagination::create()))
                ->byDefault();
            $self->findDwsCertificationUseCase
                ->allows('handle')
                ->andReturn(FinderResult::from($self->examples->dwsCertifications, Pagination::create()))
                ->byDefault();
            $self->findDwsProjectUseCase
                ->allows('handle')
                ->andReturn(FinderResult::from($self->examples->dwsProjects, Pagination::create()))
                ->byDefault();
            $self->findLtcsInsCardUseCase
                ->allows('handle')
                ->andReturn(FinderResult::from($self->examples->ltcsInsCards, Pagination::create()))
                ->byDefault();
            $self->findLtcsProjectUseCase
                ->allows('handle')
                ->andReturn(FinderResult::from($self->examples->ltcsProjects, Pagination::create()))
                ->byDefault();
            $self->findUserDwsCalcSpecUseCase
                ->allows('handle')
                ->andReturn(FinderResult::from($self->examples->userDwsCalcSpecs, Pagination::create()))
                ->byDefault();
            $self->findUserDwsSubsidyUseCase
                ->allows('handle')
                ->andReturn(FinderResult::from($self->examples->userDwsSubsidies, Pagination::create()))
                ->byDefault();
            $self->findUserLtcsSubsidyUseCase
                ->allows('handle')
                ->andReturn(FinderResult::from($self->examples->userLtcsSubsidies, Pagination::create()))
                ->byDefault();
            $self->findUserLtcsCalcSpecUseCase
                ->allows('handle')
                ->andReturn(FinderResult::from($self->examples->userLtcsCalcSpecs, Pagination::create()))
                ->byDefault();
            $self->context
                ->allows('isAuthorizedTo')
                ->andReturnTrue()
                ->byDefault();

            $self->interactor = app(GetUserInfoInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('call handle of lookupUserUseCase', function (): void {
            $this->lookupUserUseCase
                ->expects('handle')
                ->with($this->context, Permission::viewUsers(), $this->examples->users[0]->id)
                ->andReturn(Seq::from($this->examples->users[0]));

            $this->interactor->handle($this->context, $this->examples->users[0]->id);
        });
        $this->should('return an Array contains bankAccount', function (): void {
            $actual = $this->interactor->handle($this->context, $this->examples->users[0]->id);

            $this->assertModelStrictEquals($this->examples->bankAccounts[0], $actual['bankAccount']);
        });
        $this->should('return an Array contains contracts', function (): void {
            $actual = $this->interactor->handle($this->context, $this->examples->users[0]->id);

            foreach ($this->contracts->toArray() as $index => $contracts) {
                $this->assertModelStrictEquals($contracts, $actual['contracts'][$index]);
            }
        });
        $this->should('return an Array contains dwsCertifications', function (): void {
            $actual = $this->interactor->handle($this->context, $this->examples->users[0]->id);

            foreach ($this->examples->dwsCertifications as $index => $dwsCertification) {
                $this->assertModelStrictEquals($dwsCertification, $actual['dwsCertifications'][$index]);
            }
        });
        $this->should('return an Array contains dwsProjects', function (): void {
            $actual = $this->interactor->handle($this->context, $this->examples->users[0]->id);

            foreach ($this->examples->dwsProjects as $index => $dwsProject) {
                $this->assertModelStrictEquals($dwsProject, $actual['dwsProjects'][$index]);
            }
        });
        $this->should('return an Array contains ltcsProjects', function (): void {
            $actual = $this->interactor->handle($this->context, $this->examples->users[0]->id);

            foreach ($this->examples->ltcsProjects as $index => $ltcsProject) {
                $this->assertModelStrictEquals($ltcsProject, $actual['ltcsProjects'][$index]);
            }
        });
        $this->should('return an Array contains ltcsInsCards', function (): void {
            $actual = $this->interactor->handle($this->context, $this->examples->users[0]->id);

            foreach ($this->examples->ltcsInsCards as $index => $ltcsInsCard) {
                $this->assertModelStrictEquals($ltcsInsCard, $actual['ltcsInsCards'][$index]);
            }
        });

        $this->should('lookup bankAccount', function (): void {
            $this->lookupBankAccountUseCase
                ->expects('handle')
                ->with($this->context, $this->examples->users[0]->bankAccountId)
                ->andReturn(Seq::from($this->examples->bankAccounts[0]));
            $this->interactor->handle($this->context, $this->examples->users[0]->id);
        });
        $this->should('find contracts', function (): void {
            $this->findContractUseCase
                ->expects('handle')
                ->with(
                    $this->context,
                    Permission::viewUsers(),
                    ['userId' => $this->examples->users[0]->id],
                    ['all' => true]
                )
                ->andReturn(FinderResult::from($this->examples->contracts, Pagination::create(['all' => true])));
            $this->interactor->handle($this->context, $this->examples->users[0]->id);
        });
        $this->should('find dwsCertifications', function (): void {
            $this->findDwsCertificationUseCase
                ->expects('handle')
                ->with(
                    $this->context,
                    Permission::viewUsers(),
                    ['userId' => $this->examples->users[0]->id],
                    ['all' => true]
                )
                ->andReturn(FinderResult::from(
                    $this->examples->dwsCertifications,
                    Pagination::create(['all' => true])
                ));
            $this->interactor->handle($this->context, $this->examples->users[0]->id);
        });
        $this->should('find dwsProjects', function (): void {
            $this->findDwsProjectUseCase
                ->expects('handle')
                ->with(
                    $this->context,
                    Permission::viewUsers(),
                    ['userId' => $this->examples->users[0]->id],
                    ['all' => true]
                )
                ->andReturn(FinderResult::from($this->examples->dwsProjects, Pagination::create(['all' => true])));
            $this->interactor->handle($this->context, $this->examples->users[0]->id);
        });
        $this->should('find ltcsInsCards', function (): void {
            $this->findLtcsInsCardUseCase
                ->expects('handle')
                ->with(
                    $this->context,
                    Permission::viewUsers(),
                    ['userId' => $this->examples->users[0]->id],
                    ['all' => true]
                )
                ->andReturn(FinderResult::from($this->examples->ltcsInsCards, Pagination::create(['all' => true])));
            $this->interactor->handle($this->context, $this->examples->users[0]->id);
        });
        $this->should('find ltcsProjects', function (): void {
            $this->findLtcsProjectUseCase
                ->expects('handle')
                ->with(
                    $this->context,
                    Permission::viewUsers(),
                    ['userId' => $this->examples->users[0]->id],
                    ['all' => true]
                )
                ->andReturn(FinderResult::from($this->examples->ltcsProjects, Pagination::create(['all' => true])));
            $this->interactor->handle($this->context, $this->examples->users[0]->id);
        });
        $this->should('find userDwsCalcSpecs', function (): void {
            $this->findUserDwsCalcSpecUseCase
                ->expects('handle')
                ->with(
                    $this->context,
                    Permission::viewUsers(),
                    ['userId' => $this->examples->users[0]->id],
                    ['sortBy' => 'effectivatedOn', 'desc' => true, 'all' => true]
                )
                ->andReturn(FinderResult::from($this->examples->userDwsCalcSpecs, Pagination::create(['all' => true])));
            $this->interactor->handle($this->context, $this->examples->users[0]->id);
        });
        $this->should('find userLtcsCalcSpecs', function (): void {
            $this->findUserLtcsCalcSpecUseCase
                ->expects('handle')
                ->with(
                    $this->context,
                    Permission::viewUsers(),
                    ['userId' => $this->examples->users[0]->id],
                    ['sortBy' => 'effectivatedOn', 'desc' => true, 'all' => true]
                )
                ->andReturn(FinderResult::from($this->examples->userLtcsCalcSpecs, Pagination::create(['all' => true])));
            $this->interactor->handle($this->context, $this->examples->users[0]->id);
        });
        $this->should('find userDwsSubsidies', function (): void {
            $this->findUserDwsSubsidyUseCase
                ->expects('handle')
                ->with(
                    $this->context,
                    Permission::viewUsers(),
                    ['userId' => $this->examples->users[0]->id],
                    ['all' => true]
                )
                ->andReturn(FinderResult::from($this->examples->userDwsSubsidies, Pagination::create(['all' => true])));
            $this->interactor->handle($this->context, $this->examples->users[0]->id);
        });

        $this->should('throw a NotFoundException when the id not exists in db', function (): void {
            $this->lookupUserUseCase
                ->expects('handle')
                ->with($this->context, Permission::viewUsers(), self::NOT_EXISTING_ID)
                ->andReturn(Seq::emptySeq());

            $this->assertThrows(
                NotFoundException::class,
                function (): void {
                    $this->interactor->handle($this->context, self::NOT_EXISTING_ID);
                }
            );
        });
        $this->should('throw a NotFoundException when the bankAccountId not exists in db', function (): void {
            $this->lookupBankAccountUseCase
                ->expects('handle')
                ->andReturn(Seq::emptySeq());

            $this->assertThrows(
                NotFoundException::class,
                function (): void {
                    $this->interactor->handle($this->context, $this->examples->users[0]->id);
                }
            );
        });
        $this->should(
            'return empty array when is not given specified permission',
            function ($permissions, $key, $expect): void {
                foreach ($permissions as $permission) {
                    $this->context
                        ->expects('isAuthorizedTo')
                        ->with($permission)
                        ->andReturnFalse();
                }
                $actual = $this->interactor->handle($this->context, $this->examples->users[0]->id);
                $this->assertSame($expect, $actual[$key]);
            },
            [
                'examples' => [
                    'not given `viewUsersBankAccount`' => [
                        'permissions' => [Permission::viewUsersBankAccount()],
                        'key' => 'bankAccount',
                        'expect' => null,
                    ],
                    'not given `listUsersDwsCertifications`' => [
                        'permissions' => [Permission::listDwsCertifications()],
                        'key' => 'dwsCertifications',
                        'expect' => [],
                    ],
                    'not given `listUsersLtcsInsCards`' => [
                        'permissions' => [Permission::listLtcsInsCards()],
                        'key' => 'ltcsInsCards',
                        'expect' => [],
                    ],
                    'not given `listUsersDwsProjects`' => [
                        'permissions' => [Permission::listDwsProjects()],
                        'key' => 'dwsProjects',
                        'expect' => [],
                    ],
                    'not given `listUsersLtcsProjects`' => [
                        'permissions' => [Permission::listLtcsProjects()],
                        'key' => 'ltcsProjects',
                        'expect' => [],
                    ],
                    'not given `listUsersDwsContracts`' => [
                        'permissions' => [Permission::listDwsContracts()],
                        'key' => 'contracts',
                        'expect' => Seq::fromArray($this->examples->contracts)
                            ->filter(fn (Contract $x): bool => $x->organizationId === $this->context->organization->id)
                            ->filter(fn (
                                Contract $x
                            ): bool => $x->serviceSegment !== ServiceSegment::disabilitiesWelfare())
                            ->toArray(),
                    ],
                    'not given `listUsersLtcsContracts`' => [
                        'permissions' => [Permission::listLtcsContracts()],
                        'key' => 'contracts',
                        'expect' => Seq::fromArray($this->examples->contracts)
                            ->filter(fn (Contract $x): bool => $x->organizationId === $this->context->organization->id)
                            ->filter(fn (Contract $x): bool => $x->serviceSegment !== ServiceSegment::longTermCare())
                            ->toArray(),
                    ],
                    'not given `listUsersDwsContracts` and `listUsersLtcsContracts`' => [
                        'permissions' => [Permission::listDwsContracts(), Permission::listLtcsContracts()],
                        'key' => 'contracts',
                        'expect' => Seq::fromArray($this->examples->contracts)
                            ->filter(fn (Contract $x): bool => $x->organizationId === $this->context->organization->id)
                            ->filter(
                                fn (Contract $x): bool => !in_array(
                                    $x->serviceSegment,
                                    [ServiceSegment::disabilitiesWelfare(), ServiceSegment::longTermCare()],
                                    true
                                )
                            )
                            ->toArray(),
                    ],
                ],
            ]
        );
        $this->should('use FindLtcsSubsidyUseCase', function (): void {
            $this->findUserLtcsSubsidyUseCase
                ->expects('handle')
                ->with(
                    $this->context,
                    Permission::viewUsers(),
                    ['userId' => $this->examples->users[0]->id],
                    ['all' => true],
                )
                ->andReturn(FinderResult::from($this->examples->userLtcsSubsidies, Pagination::create()));

            $this->interactor->handle($this->context, $this->examples->users[0]->id);
        });
    }
}

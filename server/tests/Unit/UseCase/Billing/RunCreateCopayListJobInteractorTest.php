<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\Billing;

use Closure;
use Domain\Billing\DwsBilling;
use Domain\Billing\DwsBillingStatement;
use Domain\Context\Context;
use Domain\Job\Job;
use Domain\Job\Job as DomainJob;
use Domain\Permission\Permission;
use Lib\Exceptions\FileIOException;
use Lib\Exceptions\NotFoundException;
use Lib\Exceptions\RuntimeException;
use Mockery;
use ScalikePHP\Option;
use ScalikePHP\Seq;
use SplFileInfo;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\ConfigMixin;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\FileStorageMixin;
use Tests\Unit\Mixins\GenerateCopayListPdfUseCaseMixin;
use Tests\Unit\Mixins\GenerateFileNameUseCaseMixin;
use Tests\Unit\Mixins\IdentifyDwsCertificationUseCaseMixin;
use Tests\Unit\Mixins\LoggerMixin;
use Tests\Unit\Mixins\LookupDwsBillingBundleUseCaseMixin;
use Tests\Unit\Mixins\LookupDwsBillingUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\OfficeRepositoryMixin;
use Tests\Unit\Mixins\RunJobUseCaseMixin;
use Tests\Unit\Mixins\SimpleLookupDwsBillingStatementUseCaseMixin;
use Tests\Unit\Mixins\TemporaryFilesMixin;
use Tests\Unit\Mixins\ZipArchiveMixin;
use Tests\Unit\Test;
use UseCase\Billing\RunCreateCopayListJobInteractor;
use ZipArchive;

/**
 * {@link \UseCase\Billing\RunCreateCopayListJobInteractor} のテスト.
 */
final class RunCreateCopayListJobInteractorTest extends Test
{
    use ConfigMixin;
    use ContextMixin;
    use ExamplesConsumer;
    use SimpleLookupDwsBillingStatementUseCaseMixin;
    use LoggerMixin;
    use LookupDwsBillingBundleUseCaseMixin;
    use LookupDwsBillingUseCaseMixin;
    use GenerateCopayListPdfUseCaseMixin;
    use GenerateFileNameUseCaseMixin;
    use FileStorageMixin;
    use IdentifyDwsCertificationUseCaseMixin;
    use TemporaryFilesMixin;
    use MockeryMixin;
    use RunJobUseCaseMixin;
    use ZipArchiveMixin;
    use OfficeRepositoryMixin;
    use UnitSupport;

    private const PATH = 'dummies/download/dummy';
    private const FILENAME = 'dummy.pdf';
    private const ZIP_FILEPATH = 'dummy';

    private Job $domainJob;
    private int $billingId;
    private array $ids;
    private bool $isDivided;
    private Seq $statements;
    private Seq $bundles;
    private DwsBilling $billing;
    private SplFileInfo $file;
    private RunCreateCopayListJobInteractor $interactor;

    /**
     * 初期化処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachSpec(function (self $self): void {
            $self->domainJob = $self->examples->jobs[0];
            $self->billingId = $self->examples->dwsBillingStatements[0]->dwsBillingId;
            $self->ids = [$self->examples->dwsBillingStatements[0]->id];
            $self->isDivided = false;
            $self->statements = Seq::from($self->examples->dwsBillingStatements[0]);
            $self->bundles = Seq::from($self->examples->dwsBillingBundles[0]);
            $self->billing = Seq::fromArray($self->examples->dwsBillings)
                ->find(fn (DwsBilling $x) => $x->id === $self->billingId)
                ->get();
            $self->file = new SplFileInfo('filename');
            $self->runJobUseCase
                ->allows('handle')
                ->andReturnNull()
                ->byDefault();
            $self->simpleLookupDwsBillingStatementUseCase
                ->allows('handle')
                ->andReturn($self->statements)
                ->byDefault();
            $self->logger
                ->allows('info')
                ->andReturnNull()
                ->byDefault();
            $self->lookupDwsBillingBundleUseCase
                ->allows('handle')
                ->andReturn($self->bundles)
                ->byDefault();
            $self->lookupDwsBillingUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->billing))
                ->byDefault();
            $self->generateCopayListPdfUseCase
                ->allows('handle')
                ->andReturn(self::PATH)
                ->byDefault();
            $self->generateFileNameUseCase
                ->allows('handle')
                ->andReturn(self::FILENAME)
                ->byDefault();
            $self->fileStorage
                ->allows('store')
                ->andReturn(Option::some('exported/hoge.zip'))
                ->byDefault();
            $self->fileStorage
                ->allows('fetch')
                ->andReturn(Option::some($self->file))
                ->byDefault();
            $self->identifyDwsCertificationUseCase
                ->allows('handle')
                ->andReturn(Option::from($self->examples->dwsCertifications[0]))
                ->byDefault();
            $self->zipArchive
                ->allows('open')
                ->andReturn(true)
                ->byDefault();
            $self->zipArchive
                ->allows('close')
                ->andReturn(true)
                ->byDefault();
            $self->zipArchive
                ->allows('addFile')
                ->andReturn(true)
                ->byDefault();
            $self->temporaryFiles
                ->allows('create')
                ->andReturnUsing(fn (): SplFileInfo => $self->createTemporaryFileInfoStub())
                ->byDefault();
            $self->officeRepository
                ->allows('lookup')
                ->andReturn(Seq::from($self->examples()->offices[0]))
                ->byDefault();

            $self->interactor = app(RunCreateCopayListJobInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('call RunJobUseCase', function (): void {
            $this->runJobUseCase
                ->expects('handle')
                ->with($this->context, $this->domainJob, Mockery::any())
                ->andReturnNull();

            $this->interactor->handle(
                $this->context,
                $this->domainJob,
                $this->billingId,
                $this->ids,
                $this->isDivided
            );
        });
        $this->should('use SimpleLookupDwsBillingStatementUseCase', function (): void {
            $this->runJobUseCase
                ->expects('handle')
                ->andReturnUsing(
                    function (Context $context, DomainJob $job, Closure $f): void {
                        $this->simpleLookupDwsBillingStatementUseCase
                            ->expects('handle')
                            ->with($this->context, Permission::viewBillings(), ...$this->ids)
                            ->andReturn($this->statements);
                        $f();
                    }
                );

            $this->interactor->handle(
                $this->context,
                $this->domainJob,
                $this->billingId,
                $this->ids,
                $this->isDivided
            );
        });
        $this->should('use LookupDwsBillingBundleUseCase', function (): void {
            $bundleIds = $this->statements
                ->map(fn (DwsBillingStatement $x): int => $x->dwsBillingBundleId)
                ->toArray();

            $this->runJobUseCase
                ->expects('handle')
                ->andReturnUsing(
                    function (Context $context, DomainJob $job, Closure $f) use ($bundleIds): void {
                        $this->lookupDwsBillingBundleUseCase
                            ->expects('handle')
                            ->with($this->context, Permission::viewBillings(), $this->billingId, ...$bundleIds)
                            ->andReturn(Seq::from($this->examples->dwsBillingBundles[0]));
                        $f();
                    }
                );

            $this->interactor->handle(
                $this->context,
                $this->domainJob,
                $this->billingId,
                $this->ids,
                $this->isDivided
            );
        });
        $this->should('use LookupDwsBillingUseCase', function (): void {
            $this->runJobUseCase
                ->expects('handle')
                ->andReturnUsing(
                    function (Context $context, DomainJob $job, Closure $f): void {
                        $this->lookupDwsBillingUseCase
                            ->expects('handle')
                            ->with($this->context, Permission::viewBillings(), $this->billingId)
                            ->andReturn(Seq::from($this->billing));
                        $f();
                    }
                );

            $this->interactor->handle(
                $this->context,
                $this->domainJob,
                $this->billingId,
                $this->ids,
                $this->isDivided
            );
        });
        $this->should('call GenerateCopayListPdfUseCase', function (): void {
            $uri = $this->context->uri('copay-lists/download/' . self::PATH);
            $this->runJobUseCase
                ->expects('handle')
                ->andReturnUsing(
                    function (Context $context, DomainJob $job, Closure $f) use ($uri): void {
                        $this->generateCopayListPdfUseCase
                            ->expects('handle')
                            ->with($this->context, $this->billing, $this->bundles, $this->statements)
                            ->andReturn(self::PATH);

                        // 正しい値を返すことも検証
                        $res = $f();
                        $this->assertSame(
                            ['uri' => $uri, 'filename' => self::FILENAME],
                            $res
                        );
                    }
                );

            $this->interactor->handle(
                $this->context,
                $this->domainJob,
                $this->billingId,
                $this->ids,
                $this->isDivided
            );
        });
        $this->should('use GenerateFileNameUseCase once when isDivided is false', function (): void {
            $this->runJobUseCase
                ->expects('handle')
                ->andReturnUsing(
                    function (Context $context, DomainJob $job, Closure $f): void {
                        $this->generateFileNameUseCase
                            ->expects('handle')
                            ->with('copay_list_pdf', [
                                'office' => $this->billing->office->abbr,
                                'providedIn' => $this->bundles->head()->providedIn,
                            ])
                            ->andReturn(self::FILENAME);
                        $f();
                    }
                );

            $this->interactor->handle(
                $this->context,
                $this->domainJob,
                $this->billingId,
                $this->ids,
                false
            );
        });
        $this->should('use GenerateFileNameUseCase twice when isDivided is true', function (): void {
            $this->runJobUseCase
                ->expects('handle')
                ->andReturnUsing(
                    function (Context $context, DomainJob $job, Closure $f): void {
                        $this->generateFileNameUseCase
                            ->expects('handle')
                            ->with('copay_list_zip', [
                                'office' => $this->billing->office->abbr,
                                'providedIn' => $this->bundles->head()->providedIn,
                            ])
                            ->andReturn(self::FILENAME);
                        $this->generateFileNameUseCase
                            ->expects('handle')
                            ->with('copay_list_divided_pdf', [
                                'office' => $this->billing->office->abbr,
                                'toOffice' => $this->examples()->offices[0]->abbr,
                                'providedIn' => $this->bundles->head()->providedIn,
                            ])
                            ->andReturn(self::FILENAME);
                        $f();
                    }
                );

            $this->interactor->handle(
                $this->context,
                $this->domainJob,
                $this->billingId,
                $this->ids,
                true
            );
        });
        $this->should('throw NotFoundException if billing is empty', function (): void {
            $this->lookupDwsBillingUseCase
                ->expects('handle')
                ->andReturn(Seq::empty());

            $this->assertThrows(
                NotFoundException::class,
                function (): void {
                    $this->runJobUseCase
                        ->expects('handle')
                        ->andReturnUsing(
                            function (Context $context, DomainJob $job, Closure $f): void {
                                $f();
                            }
                        );
                    $this->interactor->handle(
                        $this->context,
                        $this->domainJob,
                        $this->billingId,
                        $this->ids,
                        $this->isDivided
                    );
                }
            );
        });
        $this->should('throw NotFoundException if statement is empty', function (): void {
            $this->simpleLookupDwsBillingStatementUseCase
                ->expects('handle')
                ->andReturn(Seq::empty());
            $this->assertThrows(
                NotFoundException::class,
                function (): void {
                    $this->runJobUseCase
                        ->expects('handle')
                        ->andReturnUsing(
                            function (Context $context, DomainJob $job, Closure $f): void {
                                $f();
                            }
                        );
                    $this->interactor->handle(
                        $this->context,
                        $this->domainJob,
                        $this->billingId,
                        $this->ids,
                        $this->isDivided
                    );
                }
            );
        });
        $this->should('call fetch on FileStorage when isDivided is true', function (): void {
            $this->runJobUseCase
                ->expects('handle')
                ->andReturnUsing(
                    function (Context $context, DomainJob $job, Closure $f): void {
                        $this->fileStorage
                            ->expects('fetch')
                            ->with(self::PATH)
                            ->andReturn(Option::some($this->file));
                        $f();
                    }
                );
            $this->interactor->handle(
                $this->context,
                $this->domainJob,
                $this->billingId,
                $this->ids,
                true,
            );
        });
        $this->should('call store on FileStorage when isDivided is true', function (): void {
            $this->runJobUseCase
                ->expects('handle')
                ->andReturnUsing(
                    function (Context $context, DomainJob $job, Closure $f): void {
                        $this->fileStorage
                            ->expects('store')
                            ->andReturn(Option::some('exported/hoge.zip'));
                        $f();
                    }
                );
            $this->interactor->handle(
                $this->context,
                $this->domainJob,
                $this->billingId,
                $this->ids,
                true,
            );
        });
        $this->should('use IdentifyDwsCertificationUseCase', function (): void {
            $this->runJobUseCase
                ->expects('handle')
                ->andReturnUsing(
                    function (Context $context, DomainJob $job, Closure $f): void {
                        $this->identifyDwsCertificationUseCase
                            ->expects('handle')
                            ->with($this->context, $this->statements->head()->user->userId, $this->bundles->head()->providedIn)
                            ->andReturn(Option::from($this->examples->dwsCertifications[0]));
                        $f();
                    }
                );
            $this->interactor->handle(
                $this->context,
                $this->domainJob,
                $this->billingId,
                $this->ids,
                true,
            );
        });
        $this->should('use zipArchive Open', function (): void {
            $this->runJobUseCase
                ->expects('handle')
                ->andReturnUsing(
                    function (Context $context, DomainJob $job, Closure $f): void {
                        $this->zipArchive
                            ->expects('open')
                            ->andReturn(true);
                        $f();
                    }
                );
            $this->interactor->handle(
                $this->context,
                $this->domainJob,
                $this->billingId,
                $this->ids,
                true,
            );
        });
        $this->should('use zipArchive Close', function (): void {
            $this->runJobUseCase
                ->expects('handle')
                ->andReturnUsing(
                    function (Context $context, DomainJob $job, Closure $f): void {
                        $this->zipArchive
                            ->expects('close')
                            ->andReturn(true);
                        $f();
                    }
                );
            $this->interactor->handle(
                $this->context,
                $this->domainJob,
                $this->billingId,
                $this->ids,
                true,
            );
        });
        $this->should('call addFile on zipArchive', function (): void {
            $this->runJobUseCase
                ->expects('handle')
                ->andReturnUsing(
                    function (Context $context, DomainJob $job, Closure $f): void {
                        $this->zipArchive
                            ->expects('addFile')
                            ->with($this->file->getPathname(), self::FILENAME)
                            ->andReturn();
                        $f();
                    }
                );
            $this->interactor->handle(
                $this->context,
                $this->domainJob,
                $this->billingId,
                $this->ids,
                true,
            );
        });
        $this->should('use temporaryFile', function (): void {
            $this->runJobUseCase
                ->expects('handle')
                ->andReturnUsing(
                    function (Context $context, DomainJob $job, Closure $f): void {
                        $this->temporaryFiles
                            ->expects('create')
                            ->with('zip-', '-zip')
                            ->andReturnUsing(fn (): SplFileInfo => $this->createTemporaryFileInfoStub());
                        $f();
                    }
                );
            $this->interactor->handle(
                $this->context,
                $this->domainJob,
                $this->billingId,
                $this->ids,
                true,
            );
        });
        $this->should('use officeRepository', function (): void {
            $this->runJobUseCase
                ->expects('handle')
                ->andReturnUsing(
                    function (Context $context, DomainJob $job, Closure $f): void {
                        $this->officeRepository
                            ->expects('lookup')
                            ->with($this->examples()->offices[0]->id)
                            ->andReturn(Seq::from($this->examples()->offices[0]));
                        $f();
                    }
                );
            $this->interactor->handle(
                $this->context,
                $this->domainJob,
                $this->billingId,
                $this->ids,
                true,
            );
        });
        $this->should('throw FileIOException if zipArchive not open', function (): void {
            $this->zipArchive
                ->expects('open')
                ->andReturn(ZipArchive::EM_NONE);
            $this->assertThrows(
                FileIOException::class,
                function (): void {
                    $this->runJobUseCase
                        ->expects('handle')
                        ->andReturnUsing(
                            function (Context $context, DomainJob $job, Closure $f): void {
                                $f();
                            }
                        );
                    $this->interactor->handle(
                        $this->context,
                        $this->domainJob,
                        $this->billingId,
                        $this->ids,
                        true
                    );
                }
            );
        });
        $this->should('throw FileIOException if fetch on FileStorage return None', function (): void {
            $this->fileStorage
                ->expects('fetch')
                ->andReturn(Option::none());
            $this->runJobUseCase
                ->expects('handle')
                ->andReturnUsing(
                    function (Context $context, DomainJob $job, Closure $f): void {
                        $f();
                    }
                );

            $this->assertThrows(RuntimeException::class, function (): void {
                $this->interactor->handle(
                    $this->context,
                    $this->domainJob,
                    $this->billingId,
                    $this->ids,
                    true
                );
            });
        });
        $this->should('throw FileIOException if store on FileStorage return None', function (): void {
            $this->fileStorage
                ->expects('store')
                ->andReturn(Option::none());
            $this->assertThrows(FileIOException::class, function (): void {
                $this->runJobUseCase
                    ->expects('handle')
                    ->andReturnUsing(
                        function (Context $context, DomainJob $job, Closure $f): void {
                            $f();
                        }
                    );
                $this->interactor->handle(
                    $this->context,
                    $this->domainJob,
                    $this->billingId,
                    $this->ids,
                    true
                );
            });
        });
        $this->should('throw NotFoundException if dwsCertification return None', function (): void {
            $this->identifyDwsCertificationUseCase
                ->expects('handle')
                ->andReturn(Option::none());
            $this->assertThrows(
                NotFoundException::class,
                function (): void {
                    $this->runJobUseCase
                        ->expects('handle')
                        ->andReturnUsing(
                            function (Context $context, DomainJob $job, Closure $f): void {
                                $f();
                            }
                        );
                    $this->interactor->handle(
                        $this->context,
                        $this->domainJob,
                        $this->billingId,
                        $this->ids,
                        true
                    );
                }
            );
        });
        $this->should('throw RuntimeException if office is empty', function (): void {
            $this->officeRepository
                ->expects('lookup')
                ->andReturn(Seq::empty());
            $this->assertThrows(
                RuntimeException::class,
                function (): void {
                    $this->runJobUseCase
                        ->expects('handle')
                        ->andReturnUsing(
                            function (Context $context, DomainJob $job, Closure $f): void {
                                $f();
                            }
                        );
                    $this->interactor->handle(
                        $this->context,
                        $this->domainJob,
                        $this->billingId,
                        $this->ids,
                        true
                    );
                }
            );
        });
    }

    /**
     * テスト用の {@link \SplFileInfo} を生成する.
     *
     * @return \SplFileInfo
     */
    private function createTemporaryFileInfoStub(): SplFileInfo
    {
        $file = tempnam(sys_get_temp_dir(), 'test-');
        return new SplFileInfo($file);
    }
}

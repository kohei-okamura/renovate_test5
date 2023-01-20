<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\ServiceCodeDictionary;

use Domain\Common\Carbon;
use Domain\File\ReadonlyFileStorage;
use Domain\ServiceCodeDictionary\DwsHomeHelpServiceDictionary;
use Domain\ServiceCodeDictionary\DwsHomeHelpServiceDictionaryCsv;
use Domain\ServiceCodeDictionary\DwsHomeHelpServiceDictionaryEntryRepository;
use Domain\ServiceCodeDictionary\DwsHomeHelpServiceDictionaryRepository;
use Domain\TransactionManager;
use Domain\TransactionManagerFactory;
use Lib\Csv;
use Lib\Exceptions\NotFoundException;
use Lib\Logging;

/**
 * 障害福祉サービス：居宅介護：サービスコード辞書登録実装.
 */
final class ImportDwsHomeHelpServiceDictionaryInteractor implements ImportDwsHomeHelpServiceDictionaryUseCase
{
    use Logging;

    private DwsHomeHelpServiceDictionaryRepository $repository;
    private DwsHomeHelpServiceDictionaryEntryRepository $entryRepository;
    private ReadonlyFileStorage $storage;
    private TransactionManager $transaction;

    /**
     * Constructor.
     *
     * @param \Domain\ServiceCodeDictionary\DwsHomeHelpServiceDictionaryRepository $repository
     * @param \Domain\ServiceCodeDictionary\DwsHomeHelpServiceDictionaryEntryRepository $entryRepository
     * @param \Domain\File\ReadonlyFileStorage $storage
     * @param \Domain\TransactionManagerFactory $factory
     */
    public function __construct(
        DwsHomeHelpServiceDictionaryRepository $repository,
        DwsHomeHelpServiceDictionaryEntryRepository $entryRepository,
        ReadonlyFileStorage $storage,
        TransactionManagerFactory $factory
    ) {
        $this->repository = $repository;
        $this->entryRepository = $entryRepository;
        $this->storage = $storage;
        $this->transaction = $factory->factory($repository, $entryRepository);
    }

    /** {@inheritdoc} */
    public function handle(int $id, string $filepath, string $effectivatedOn, string $name): int
    {
        /** @var \SplFileInfo $file */
        $file = $this->storage->fetch($filepath)->getOrElse(function () use ($filepath): void {
            throw new NotFoundException("File({$filepath}) not found");
        });
        $csv = DwsHomeHelpServiceDictionaryCsv::create(Csv::read($file->getPathname()));

        return $this->transaction->run(function () use ($id, $effectivatedOn, $name, $csv) {
            $version = $this->repository->lookup($id)
                ->headOption()
                ->map(fn ($x) => $x->version + 1)
                ->getOrElseValue(1);
            $this->repository->store(
                DwsHomeHelpServiceDictionary::create([
                    'id' => $id,
                    'effectivatedOn' => Carbon::parse($effectivatedOn),
                    'name' => $name,
                    'version' => $version,
                    'createdAt' => Carbon::now(),
                    'updatedAt' => Carbon::now(),
                ])
            );

            $rows = $csv->rows();
            $attrs = ['dwsHomeHelpServiceDictionaryId' => $id];
            foreach ($rows as $row) {
                $x = $row->toDictionaryEntry($attrs);
                $this->entryRepository->store($x);
            }
            return $rows->size();
        });
    }
}

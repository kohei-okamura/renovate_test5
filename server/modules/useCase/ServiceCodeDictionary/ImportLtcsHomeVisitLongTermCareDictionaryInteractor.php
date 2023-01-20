<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\ServiceCodeDictionary;

use Domain\Common\Carbon;
use Domain\File\ReadonlyFileStorage;
use Domain\ServiceCodeDictionary\LtcsHomeVisitLongTermCareDictionary;
use Domain\ServiceCodeDictionary\LtcsHomeVisitLongTermCareDictionaryCsv;
use Domain\ServiceCodeDictionary\LtcsHomeVisitLongTermCareDictionaryEntryRepository;
use Domain\ServiceCodeDictionary\LtcsHomeVisitLongTermCareDictionaryRepository;
use Domain\TransactionManager;
use Domain\TransactionManagerFactory;
use Lib\Csv;
use Lib\Exceptions\NotFoundException;

/**
 * 介護保険サービス：訪問介護：サービスコード辞書一括インポートユースケース実装.
 */
final class ImportLtcsHomeVisitLongTermCareDictionaryInteractor implements ImportLtcsHomeVisitLongTermCareDictionaryUseCase
{
    private LtcsHomeVisitLongTermCareDictionaryRepository $repository;
    private LtcsHomeVisitLongTermCareDictionaryEntryRepository $entryRepository;
    private ReadonlyFileStorage $storage;
    private TransactionManager $transaction;

    /**
     * {@link \UseCase\ServiceCodeDictionary\ImportLtcsHomeVisitLongTermCareDictionaryInteractor} constructor.
     *
     * @param \Domain\ServiceCodeDictionary\LtcsHomeVisitLongTermCareDictionaryRepository $repository
     * @param \Domain\ServiceCodeDictionary\LtcsHomeVisitLongTermCareDictionaryEntryRepository $entryRepository
     * @param \Domain\File\ReadonlyFileStorage $storage
     * @param \Domain\TransactionManagerFactory $factory
     */
    public function __construct(
        LtcsHomeVisitLongTermCareDictionaryRepository $repository,
        LtcsHomeVisitLongTermCareDictionaryEntryRepository $entryRepository,
        ReadonlyFileStorage $storage,
        TransactionManagerFactory $factory
    ) {
        $this->repository = $repository;
        $this->entryRepository = $entryRepository;
        $this->storage = $storage;
        $this->transaction = $factory->factory($repository, $entryRepository);
    }

    /** {@inheritdoc} */
    public function handle(string $filepath, int $id, Carbon $effectivatedOn, string $name): int
    {
        return $this->transaction->run(function () use ($id, $filepath, $effectivatedOn, $name): int {
            $csv = $this->fetchCsv($filepath);
            $dictionary = $this->storeDictionary($id, $effectivatedOn, $name);
            return $this->storeEntries($dictionary, $csv);
        });
    }

    /**
     * ファイルストレージから CSV ファイルを取得してモデルオブジェクトに変換する.
     *
     * @param string $filepath
     * @return \Domain\ServiceCodeDictionary\LtcsHomeVisitLongTermCareDictionaryCsv
     */
    private function fetchCsv(string $filepath): LtcsHomeVisitLongTermCareDictionaryCsv
    {
        /** @var \SplFileInfo $file */
        $file = $this->storage->fetch($filepath)->getOrElse(function () use ($filepath): void {
            throw new NotFoundException("File({$filepath}) not found on storage");
        });
        $csv = Csv::read($file->getPathname());
        return LtcsHomeVisitLongTermCareDictionaryCsv::create($csv);
    }

    /**
     * 介護保険サービス：訪問介護：サービスコード辞書を生成してリポジトリに格納する.
     *
     * @param int $id
     * @param \Domain\Common\Carbon $effectivatedOn
     * @param string $name
     * @return \Domain\ServiceCodeDictionary\LtcsHomeVisitLongTermCareDictionary
     */
    private function storeDictionary(int $id, Carbon $effectivatedOn, string $name): LtcsHomeVisitLongTermCareDictionary
    {
        $attrs = [
            'effectivatedOn' => $effectivatedOn,
            'name' => $name,
            'updatedAt' => Carbon::now(),
        ];
        $dictionary = $this->repository
            ->lookup($id)
            ->headOption()
            ->map(function (LtcsHomeVisitLongTermCareDictionary $x) use ($attrs): LtcsHomeVisitLongTermCareDictionary {
                $updated = $x->copy(['version' => $x->version + 1] + $attrs);
                // 古い辞書を削除する(headOption の map なので、ここは一度しか呼ばれない)
                $this->repository->remove($x);
                return $updated;
            })
            ->getOrElse(function () use ($id, $attrs): LtcsHomeVisitLongTermCareDictionary {
                $values = [
                    'id' => $id,
                    'version' => 1,
                    'createdAt' => Carbon::now(),
                ];
                return LtcsHomeVisitLongTermCareDictionary::create($values + $attrs);
            });
        return $this->repository->store($dictionary);
    }

    /**
     * CSV から介護保険サービス：訪問介護：サービスコード辞書エントリを生成してリポジトリに格納する.
     *
     * @param \Domain\ServiceCodeDictionary\LtcsHomeVisitLongTermCareDictionary $dictionary
     * @param \Domain\ServiceCodeDictionary\LtcsHomeVisitLongTermCareDictionaryCsv $csv
     * @return int リポジトリに格納したエントリの数
     */
    private function storeEntries(
        LtcsHomeVisitLongTermCareDictionary $dictionary,
        LtcsHomeVisitLongTermCareDictionaryCsv $csv
    ): int {
        $attrs = ['dictionaryId' => $dictionary->id];
        $rows = $csv->rows();
        foreach ($rows as $row) {
            $x = $row->toDictionaryEntry($attrs);
            $this->entryRepository->store($x);
        }
        return $rows->size();
    }
}

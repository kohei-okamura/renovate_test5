<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Api\ServiceCodeDictionary;

use ApiTester;
use Domain\Common\Carbon;
use Domain\ServiceCodeDictionary\DwsVisitingCareForPwsdDictionary;
use Domain\ServiceCodeDictionary\DwsVisitingCareForPwsdDictionaryEntryFinder;
use Domain\ServiceCodeDictionary\DwsVisitingCareForPwsdDictionaryRepository;
use function PHPUnit\Framework\assertCount;
use function PHPUnit\Framework\assertEquals;
use function PHPUnit\Framework\assertGreaterThanOrEqual;
use function PHPUnit\Framework\assertSame;
use Tests\Api\Test;

/**
 * ImportDwsVisitingCareForPwsdDictionaryCommand テスト.
 */
class ImportDwsVisitingCareForPwsdDictionaryCommandCest extends Test
{
    /**
     * Artisan Command テスト.
     *
     * @param ApiTester $I
     */
    public function succeedArtisanCommand(ApiTester $I)
    {
        $pathname = storage_path('app/readonly/');
        $filename = 'dws-visiting-care-for-pwsd-dictionary.csv';
        $I->wantTo('succeed artisan command');

        // ファイルの準備
        if (!file_exists($pathname)) {
            mkdir($pathname, 0777, true);
        } else {
            if (file_exists($pathname . $filename)) {
                unlink($pathname . $filename);
            }
        }
        copy(
            codecept_data_dir('ServiceCodeDictionary/dws-visiting-care-for-pwsd-dictionary.csv'),
            $pathname . $filename
        );

        $id = Carbon::now()->format('Ymd');
        $effectivatedOn = Carbon::now()->addDay()->toDateString();
        $name = Carbon::now()->format('Y年m月版');
        $expect = DwsVisitingCareForPwsdDictionary::create([
            'id' => $id,
            'effectivatedOn' => Carbon::parse($effectivatedOn),
            'name' => $name,
            'version' => 1,
            'createdAt' => Carbon::now(),
            'updatedAt' => Carbon::now(),
        ]);

        $result = $I->callArtisanCommand('dws-visiting-care-for-pwsd-dictionary:import', compact(
            'id',
            'filename',
            'effectivatedOn',
            'name',
        ));

        assertSame(self::COMMAND_SUCCESS, $result);
        $I->seeLogCount(2);

        /** @var \Domain\ServiceCodeDictionary\DwsVisitingCareForPwsdDictionaryRepository $repository */
        $repository = app(DwsVisitingCareForPwsdDictionaryRepository::class);
        $actualSeq = $repository->lookup((int)$id);
        assertCount(1, $actualSeq);
        assertEquals($expect->toAssoc(), $actualSeq->head()->toAssoc());

        /** @var \Domain\ServiceCodeDictionary\DwsVisitingCareForPwsdDictionaryEntryFinder $dictionaryFinder */
        $dictionaryFinder = app(DwsVisitingCareForPwsdDictionaryEntryFinder::class);
        $finderResult = $dictionaryFinder->find([
            'providedIn' => Carbon::now(),
        ], [
            'itemsPerPage' => 1,
            'sortBy' => 'id',
        ]);
        assertCount(1, $finderResult->list);
        assertGreaterThanOrEqual(1, $finderResult->pagination->count);
    }
}

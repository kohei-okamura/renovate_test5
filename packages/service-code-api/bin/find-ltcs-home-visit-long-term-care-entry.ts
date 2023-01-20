#!/usr/bin/env node
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { Command } from 'commander'
import {
  findLtcsHomeVisitLongTermCareEntry,
  FindLtcsHomeVisitLongTermCareEntryParams
} from '../lib/app/find-ltcs-home-visit-long-term-care-entry'

type Options = FindLtcsHomeVisitLongTermCareEntryParams

const main = async (options: Options) => {
  const items = await findLtcsHomeVisitLongTermCareEntry(options)
  const content = JSON.stringify(items, null, 2)
  console.info(content)
}

const options = (new Command())
  .requiredOption('-p, --providedIn <date>', 'サービス提供年月, e.g. 2022-02')
  .option('-c, --category <value>', '介護保険サービス：請求：サービスコード区分')
  .option('-C, --headcount <value>', '提供人数')
  .option('-H, --houseworkMinutes <value>', '生活時間数（分単位）')
  .option('-P, --physicalMinutes <value>', '身体時間数（分単位）')
  .option('-q <value>', 'サービスコード（前方一致）')
  .option('-s, --serviceCodes <values...>', 'サービスコード（完全一致・複数指定可）')
  .option('-o, --specifiedOfficeAddition <value>', '特定事業所加算区分')
  .option('-t, --timeframe <value>', '時間帯')
  .option('-T, --totalMinutes <value>', '合計時間数（分単位）')
  .parse(process.argv)
  .opts<Options>()

// noinspection JSIgnoredPromiseFromCall
main(options).then(() => process.exit())

#!/usr/bin/env node
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { Command } from 'commander'
import { outputDwsHomeHelpServiceCsv } from '../lib/app/output-dws-home-help-service-csv'
import { outputDwsPwsdServiceCsv } from '../lib/app/output-dws-pwsd-service-csv'
import { DWS_HOME_HELP_SERVICE_XLSX, DwsVersion } from '../lib/constants'

type Options = {
  division: string
  output: string
  target: string
}

function ensureVersion (version: number): asserts version is DwsVersion {
  if (!Object.keys(DWS_HOME_HELP_SERVICE_XLSX).some(x => +x === version)) {
    throw new Error(`Unsupported target given: ${version}`)
  }
}

const main = async ({ division, target, output }: Options) => {
  const version = +target
  ensureVersion(version)
  switch (division) {
    case '11': // 居宅介護
      await outputDwsHomeHelpServiceCsv(version, output)
      break
    case '12': // 重度訪問介護
      await outputDwsPwsdServiceCsv(version, output)
      break
    default:
      throw new Error(`Unsupported division given: ${division}`)
  }
}

const options = (new Command())
  .version('0.0.1')
  .requiredOption('-d, --division [value]', 'service division code')
  .requiredOption('-o, --output [path]', 'output to')
  .requiredOption('-t, --target [target]', 'target version, e.g. 202210')
  .parse(process.argv)
  .opts<Options>()

// noinspection JSIgnoredPromiseFromCall
main(options)

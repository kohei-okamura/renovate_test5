#!/usr/bin/env node
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { Command } from 'commander'
import { outputLtcsHomeVisitLongTermCareCsv } from '../lib/app/output-ltcs-home-visit-long-term-care-csv'
import { LTCS_HOME_VISIT_LONG_TERM_CARE_CSV, LtcsVersion } from '../lib/constants'

type Options = {
  output: string
  target: string
}

function ensureVersion (version: number): asserts version is LtcsVersion {
  if (!Object.keys(LTCS_HOME_VISIT_LONG_TERM_CARE_CSV).some(x => +x === version)) {
    throw new Error(`Unsupported target given: ${version}`)
  }
}

const main = async ({ target, output }: Options) => {
  const version = +target
  ensureVersion(version)
  await outputLtcsHomeVisitLongTermCareCsv(version, output)
}

const options = (new Command())
  .version('0.0.1')
  .requiredOption('-o, --output [path]', 'output to')
  .requiredOption('-t, --target [target]', 'target version, e.g. 202210')
  .parse(process.argv)
  .opts<Options>()

// noinspection JSIgnoredPromiseFromCall
main(options)

/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { Profile } from '~aws/scripts/params'
import { outputAsJson } from '~aws/scripts/utils/output-as-json'
import { setupAwsSdk } from '~aws/scripts/utils/setup-aws-sdk'

export type RunCommandOptions = {
  github?: boolean
  profile: Profile
}

export const runCommand = <T> ({ github, profile }: RunCommandOptions, f: () => Promise<T>): Promise<true | void> => {
  github || setupAwsSdk({ profile })
  return f()
    .then(data => typeof data === 'undefined' || outputAsJson(data))
    .catch(error => {
      console.error(error, error.stack)
      process.exit(-1)
    })
}

/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { getEcrHost } from '~aws/scripts/ecs-task-definitions/functions'
import { createCommand } from '~aws/scripts/utils/create-command'
import { getAccountId } from '~aws/scripts/utils/get-account-id'
import { runCommand, RunCommandOptions } from '~aws/scripts/utils/run-command'

const main = (options: RunCommandOptions) => runCommand(options, async () => {
  const accountId = await getAccountId()
  return {
    host: getEcrHost({ accountId })
  }
})

const options = createCommand().parse(process.argv).opts<RunCommandOptions>()

main(options)

/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { createCommand } from '~aws/scripts/utils/create-command'
import { createEventBridgeService } from '~aws/scripts/utils/create-event-bridge-service'
import { describeTargetIds } from '~aws/scripts/utils/describe-target-ids'
import { runAwsCommand } from '~aws/scripts/utils/run-aws-command'
import { runCommand, RunCommandOptions } from '~aws/scripts/utils/run-command'
import { withConfirm } from '~aws/scripts/utils/with-confirm'
import { ZINGER_CREATE_USER_BILLING_EVENT_RULE } from '~aws/variables'

const main = (options: RunCommandOptions) => runCommand(options, withConfirm(
  'DELETE EVENT BRIDGE?',
  async () => {
    const events = createEventBridgeService()
    const rule = ZINGER_CREATE_USER_BILLING_EVENT_RULE
    const ids = await describeTargetIds(rule)
    return [
      await runAwsCommand(() => events.removeTargets({ Ids: ids, Rule: rule })),
      await runAwsCommand(() => events.deleteRule({ Name: rule }))
    ]
  }
))

const options = createCommand().parse(process.argv).opts<RunCommandOptions>()

// noinspection JSIgnoredPromiseFromCall
main(options)

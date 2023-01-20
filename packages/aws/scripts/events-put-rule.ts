/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { createCommand } from '~aws/scripts/utils/create-command'
import { createEventBridgeService } from '~aws/scripts/utils/create-event-bridge-service'
import { runAwsCommand } from '~aws/scripts/utils/run-aws-command'
import { runCommand, RunCommandOptions } from '~aws/scripts/utils/run-command'
import { ZINGER_CREATE_USER_BILLING_EVENT_RULE } from '~aws/variables'

const main = (options: RunCommandOptions) => runCommand(options, async () => {
  const events = createEventBridgeService()
  return await runAwsCommand(() => events.putRule({
    Name: ZINGER_CREATE_USER_BILLING_EVENT_RULE,
    // JST(+09:00) で毎月11日の 00:05 に実行したいが UTC(+00:00) で設定する必要があるため
    // UTC(+00:00) で毎月10日の 15:05 に実行するよう設定する
    ScheduleExpression: 'cron(5 15 10 * ? *)'
  }))
})

const options = createCommand().parse(process.argv).opts<RunCommandOptions>()

// noinspection JSIgnoredPromiseFromCall
main(options)

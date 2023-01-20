/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { DateTime } from 'luxon'
import { Hostname } from '~aws/scripts/params'
import { createCommand } from '~aws/scripts/utils/create-command'
import { createRoute53Service } from '~aws/scripts/utils/create-route53-service'
import { runAwsCommand } from '~aws/scripts/utils/run-aws-command'
import { runCommand, RunCommandOptions } from '~aws/scripts/utils/run-command'

type Options = RunCommandOptions & {
  hostname: Hostname
}

const main = (options: Options) => runCommand(options, async () => {
  const route53 = createRoute53Service()
  return await runAwsCommand(() => route53.createHostedZone({
    Name: options.hostname,
    CallerReference: DateTime.local().toFormat('yyyy-MM-dd_HH-mm-ss')
  }))
})

const options = createCommand()
  .requiredOption('-H, --hostname [hostname]', 'hostname')
  .parse(process.argv)
  .opts<Options>()

main(options)

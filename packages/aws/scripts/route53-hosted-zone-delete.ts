/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import * as AWS from 'aws-sdk'
import { Hostname } from '~aws/scripts/params'
import { createCommand } from '~aws/scripts/utils/create-command'
import { createRoute53Service } from '~aws/scripts/utils/create-route53-service'
import { describeHostedZoneId } from '~aws/scripts/utils/describe-hosted-zone-id'
import { runAwsCommand } from '~aws/scripts/utils/run-aws-command'
import { runCommand, RunCommandOptions } from '~aws/scripts/utils/run-command'
import { withConfirm } from '~aws/scripts/utils/with-confirm'

type Options = RunCommandOptions & {
  hostname: Hostname
}

const deleteHostedZoneRequest = async (hostname: string): Promise<AWS.Route53.Types.DeleteHostedZoneRequest> => ({
  Id: await describeHostedZoneId(hostname)
})

const main = (options: Options) => runCommand(options, withConfirm(
  'DELETE ROUTE53 HOSTED ZONE?',
  async () => {
    const route53 = createRoute53Service()
    const params = await deleteHostedZoneRequest(options.hostname)
    return await runAwsCommand(() => route53.deleteHostedZone(params))
  }
))

const options = createCommand()
  .requiredOption('-H, --hostname [hostname]', 'hostname')
  .parse(process.argv)
  .opts<Options>()

main(options)

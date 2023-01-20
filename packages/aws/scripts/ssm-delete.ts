/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { createCommand } from '~aws/scripts/utils/create-command'
import { createSsmService } from '~aws/scripts/utils/create-ssm-service'
import { getSsmSecureParameters } from '~aws/scripts/utils/get-ssm-secure-parameters'
import { runAwsCommand } from '~aws/scripts/utils/run-aws-command'
import { runCommand, RunCommandOptions } from '~aws/scripts/utils/run-command'
import { withConfirm } from '~aws/scripts/utils/with-confirm'

const main = (options: RunCommandOptions) => runCommand(options, withConfirm(
  'DELETE SSM PARAMETER?',
  async () => {
    const ssm = createSsmService()
    return await Promise.all(getSsmSecureParameters(options.profile).map(item => {
      return runAwsCommand(() => ssm.deleteParameter({ Name: item.Name }))
    }))
  }
))

const options = createCommand().parse(process.argv).opts<RunCommandOptions>()

main(options)

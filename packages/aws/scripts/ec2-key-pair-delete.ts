/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { assert } from '@zinger/helpers'
import { createCommand } from '~aws/scripts/utils/create-command'
import { createEc2Service } from '~aws/scripts/utils/create-ec2-service'
import { describeEc2KeyPairIds } from '~aws/scripts/utils/describe-ec2-key-pair-ids'
import { runAwsCommand } from '~aws/scripts/utils/run-aws-command'
import { runCommand, RunCommandOptions } from '~aws/scripts/utils/run-command'
import { withConfirm } from '~aws/scripts/utils/with-confirm'

const main = (options: RunCommandOptions) => runCommand(options, withConfirm(
  'DELETE EC2 KEY PAIR?',
  async () => {
    const ec2 = createEc2Service()
    const ids = await describeEc2KeyPairIds()

    assert(ids.length > 0, 'No key pairs')

    return await Promise.all(ids.map(id => {
      return runAwsCommand(() => ec2.deleteKeyPair({ KeyPairId: id }))
    }))
  }
))

const options = createCommand().parse(process.argv).opts<RunCommandOptions>()

main(options)

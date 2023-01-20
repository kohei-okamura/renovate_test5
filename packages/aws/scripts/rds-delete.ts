/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { createCommand } from '~aws/scripts/utils/create-command'
import { createRdsService } from '~aws/scripts/utils/create-rds-service'
import { runAwsCommand } from '~aws/scripts/utils/run-aws-command'
import { runCommand, RunCommandOptions } from '~aws/scripts/utils/run-command'
import { withConfirm } from '~aws/scripts/utils/with-confirm'
import { ZINGER_MYSQL_INSTANCE_IDENTIFIER } from '~aws/variables'

const main = (options: RunCommandOptions) => runCommand(options, withConfirm(
  'DELETE RDS INSTANCE?',
  async () => {
    const rds = createRdsService()
    return [
      await runAwsCommand(() => rds.modifyDBInstance({
        ApplyImmediately: true,
        DBInstanceIdentifier: ZINGER_MYSQL_INSTANCE_IDENTIFIER,
        DeletionProtection: false
      })),
      await runAwsCommand(() => rds.deleteDBInstance({
        DBInstanceIdentifier: ZINGER_MYSQL_INSTANCE_IDENTIFIER,
        SkipFinalSnapshot: true
      }))
    ]
  }
))

const options = createCommand().parse(process.argv).opts<RunCommandOptions>()

main(options)

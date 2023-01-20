/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { createCommand } from '~aws/scripts/utils/create-command'
import { createEcrService } from '~aws/scripts/utils/create-ecr-service'
import { runAwsCommand } from '~aws/scripts/utils/run-aws-command'
import { runCommand, RunCommandOptions } from '~aws/scripts/utils/run-command'
import { withConfirm } from '~aws/scripts/utils/with-confirm'
import {
  ZINGER_APP_CLI_PROD_REPOSITORY_NAME,
  ZINGER_APP_SERVER_PROD_REPOSITORY_NAME,
  ZINGER_WEB_PROD_REPOSITORY_NAME
} from '~aws/variables'

const repositoryNames: string[] = [
  ZINGER_APP_CLI_PROD_REPOSITORY_NAME,
  ZINGER_APP_SERVER_PROD_REPOSITORY_NAME,
  ZINGER_WEB_PROD_REPOSITORY_NAME
]

const main = (options: RunCommandOptions) => runCommand(options, withConfirm(
  'DELETE ECR REPOSITORIES?',
  async () => {
    const ecr = createEcrService()
    return await Promise.all(repositoryNames.map(repositoryName => runAwsCommand(() => ecr.deleteRepository({
      repositoryName,
      force: true
    }))))
  }
))

const options = createCommand().parse(process.argv).opts<RunCommandOptions>()

main(options)

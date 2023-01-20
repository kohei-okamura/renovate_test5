/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { assert, nonEmpty } from '@zinger/helpers'
import { Profile } from '~aws/scripts/params'
import { createCommand } from '~aws/scripts/utils/create-command'
import { createS3Service } from '~aws/scripts/utils/create-s3-service'
import { describeS3Buckets } from '~aws/scripts/utils/describe-s3-buckets'
import { describeS3Objects } from '~aws/scripts/utils/describe-s3-objects'
import { runAwsCommand } from '~aws/scripts/utils/run-aws-command'
import { runCommand, RunCommandOptions } from '~aws/scripts/utils/run-command'
import { withConfirm } from '~aws/scripts/utils/with-confirm'
import { ZINGER_APP_STORAGE, ZINGER_LOG_STORAGE, ZINGER_OPS_STORAGE, ZINGER_PUBLIC_STORAGE } from '~aws/variables'

const duplicatedArr = (array1: string[], array2: string[]) => {
  return [...new Set(array1)].filter(value => array2.includes(value))
}

const getBucketNames = (profile: Profile): string[] => {
  const xs = [
    ZINGER_APP_STORAGE,
    ZINGER_LOG_STORAGE,
    ZINGER_OPS_STORAGE,
    ZINGER_PUBLIC_STORAGE
  ]
  switch (profile) {
    case 'zinger':
      return xs
    case 'zinger-staging':
      return xs.map(x => `${x}-staging`)
    case 'zinger-sandbox':
      return xs.map(x => `${x}-sandbox`)
    default:
      throw new Error(`Undefined profile given: ${profile}`)
  }
}

const getTargetBuckets = async (profile: Profile): Promise<string[]> => {
  const xs = duplicatedArr(getBucketNames(profile), await describeS3Buckets())
  assert(xs.length > 0, 'No target buckets')
  return xs.filter(nonEmpty)
}

const main = (options: RunCommandOptions) => runCommand(options, withConfirm(
  'DELETE S3 BUCKET?',
  async () => {
    const s3 = createS3Service()
    const targetBuckets = await getTargetBuckets(options.profile)
    return await Promise.all(targetBuckets.map(async bucket => {
      const objects = await describeS3Objects(bucket)
      return objects.length > 0
        ? await runAwsCommand(() => s3.deleteObjects({
          Bucket: bucket,
          Delete: {
            Objects: objects
          }
        }))
        : await runAwsCommand(() => s3.deleteBucket({
          Bucket: bucket
        }))
    }))
  }
))

const options = createCommand().parse(process.argv).opts<RunCommandOptions>()

main(options)

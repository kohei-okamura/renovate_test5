/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { createS3Service } from '~aws/scripts/utils/create-s3-service'
import { runAwsCommand } from '~aws/scripts/utils/run-aws-command'

export const describeS3Objects = async (bucket: string) => {
  const s3 = createS3Service()

  const data = await runAwsCommand(() => s3.listObjects({ Bucket: bucket }))

  const contents = data.Contents ?? []
  return contents.map(content => {
    return { Key: content.Key ?? '' }
  })
}

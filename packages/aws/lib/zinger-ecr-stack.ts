/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import {
  Stack,
  aws_ecr as ecr
} from 'aws-cdk-lib'
import { Construct } from 'constructs'
import { ZingerEcrStackProps } from '~aws/lib/props'

export class ZingerEcrStack extends Stack {
  constructor (scope: Construct, id: string, props: ZingerEcrStackProps) {
    super(scope, id, props)
    props.repositories.forEach(({ id, repositoryName }) => {
      new ecr.Repository(this, id, {
        lifecycleRules: [{
          description: 'holded 10 images',
          maxImageCount: 10
        }],
        repositoryName
      })
    })
  }
}

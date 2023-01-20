/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import {
  Stack,
  aws_ecs as ecs
} from 'aws-cdk-lib'
import { Construct } from 'constructs'
import { ZingerEcsStackProps } from '~aws/lib/props'

export class ZingerEcsStack extends Stack {
  constructor (scope: Construct, id: string, props: ZingerEcsStackProps) {
    super(scope, id, props)
    const { vpc } = props

    //
    // ECS クラスター
    //
    new ecs.Cluster(this, 'ZingerEcs', {
      clusterName: props.name,
      vpc
    })
  }
}

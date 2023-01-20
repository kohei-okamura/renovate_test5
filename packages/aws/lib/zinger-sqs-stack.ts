/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import {
  Stack,
  StackProps,
  aws_sqs as sqs
} from 'aws-cdk-lib'
import { Construct } from 'constructs'
import { ZINGER_QUEUE, ZINGER_QUEUE_NAME } from '~aws/variables'

export class ZingerSqsStack extends Stack {
  constructor (scope: Construct, id: string, props?: StackProps) {
    super(scope, id, props)

    new sqs.Queue(this, ZINGER_QUEUE, {
      contentBasedDeduplication: true,
      fifo: true,
      queueName: ZINGER_QUEUE_NAME
    })
  }
}

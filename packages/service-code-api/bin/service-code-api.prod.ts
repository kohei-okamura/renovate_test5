#!/usr/bin/env node
import * as cdk from 'aws-cdk-lib'
import 'source-map-support/register'
import { ZINGER_ACCOUNT_ID } from '../env'
import { ServiceCodeApiStack } from '../lib/stack/service-code-api-stack'

const app = new cdk.App()

// eslint-disable-next-line no-new
new ServiceCodeApiStack(app, 'ServiceCodeApiStack', {
  env: {
    account: ZINGER_ACCOUNT_ID,
    region: 'ap-northeast-1'
  },
  hostedZoneName: 'careid.jp'
})

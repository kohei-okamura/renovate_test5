/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import {
  Duration,
  Stack,
  aws_s3 as s3
} from 'aws-cdk-lib'
import { pascalCase } from 'change-case'
import { Construct } from 'constructs'
import { ZingerS3StackProps } from '~aws/lib/props'
import { ZINGER_APP_STORAGE, ZINGER_LOG_STORAGE, ZINGER_OPS_STORAGE, ZINGER_PUBLIC_STORAGE } from '~aws/variables'

export class ZingerS3Stack extends Stack {
  public logStorage: s3.Bucket

  constructor (scope: Construct, id: string, props: ZingerS3StackProps) {
    super(scope, id, props)

    const getBucketName = (bucketName: string | undefined) => {
      if (bucketName === undefined) {
        throw new Error('bucketName is undefined')
      } else {
        return typeof props.suffix === 'string'
          ? `${bucketName}-${props.suffix}`
          : bucketName
      }
    }

    const bucket = (name: string, bucketProps: s3.BucketProps): s3.Bucket => {
      const bucketName = getBucketName(name)
      return new s3.Bucket(this, pascalCase(bucketName), {
        ...bucketProps,
        bucketName
      })
    }

    //
    // アプリケーション用ストレージ
    //
    bucket(ZINGER_APP_STORAGE, {
      lifecycleRules: [
        {
          id: 'artifacts',
          expiration: Duration.days(3650),
          prefix: 'artifacts',
          transitions: [
            {
              transitionAfter: Duration.days(365),
              storageClass: s3.StorageClass.INFREQUENT_ACCESS
            }
          ]
        },
        {
          id: 'temp',
          expiration: Duration.days(30),
          prefix: 'temp'
        },
        {
          id: 'exported',
          expiration: Duration.days(365),
          prefix: 'exported',
          transitions: [
            {
              transitionAfter: Duration.days(30),
              storageClass: s3.StorageClass.INFREQUENT_ACCESS
            },
            {
              transitionAfter: Duration.days(180),
              storageClass: s3.StorageClass.GLACIER
            }
          ]
        },
        {
          id: 'imported',
          expiration: Duration.days(365),
          prefix: 'imported',
          transitions: [
            {
              transitionAfter: Duration.days(30),
              storageClass: s3.StorageClass.INFREQUENT_ACCESS
            },
            {
              transitionAfter: Duration.days(180),
              storageClass: s3.StorageClass.GLACIER
            }
          ]
        }
      ]
    })

    //
    // ログ用ストレージ
    //
    this.logStorage = bucket(ZINGER_LOG_STORAGE, {
      lifecycleRules: [{
        id: 'zinger-log-storage',
        expiration: Duration.days(3650)
      }]
    })

    //
    // 運用系ファイル格納用ストレージ
    //
    bucket(ZINGER_OPS_STORAGE, {
      lifecycleRules: [{
        id: 'zinger-ops-storage',
        expiration: Duration.days(30)
      }]
    })

    //
    // WEB 公開用ストレージ
    //
    bucket(ZINGER_PUBLIC_STORAGE, {
      lifecycleRules: [{
        id: 'zinger-public-storage',
        noncurrentVersionExpiration: Duration.days(3650),
        transitions: [{
          transitionAfter: Duration.days(365),
          storageClass: s3.StorageClass.GLACIER
        }]
      }],
      publicReadAccess: true,
      versioned: true,
      websiteIndexDocument: 'index.html'
    })
  }
}

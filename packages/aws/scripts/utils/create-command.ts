/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { Command } from 'commander'

export const createCommand = () => (new Command())
  .version('0.0.1')
  .option('-g, --github', 'enable to github actions mode')
  .requiredOption('-p, --profile [profile]', 'profile')

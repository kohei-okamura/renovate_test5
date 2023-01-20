/*
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import * as RandomSeed from 'random-seed'

const words = [
  'ラーメン',
  'つけめん',
  '油そば',
  '冷やし中華',
  'チャーシューメン',
  'ワンタンメン',
  'タンメン',
  'サンマーメン',
  '焼きラーメン',
  '担々麺',
  '醤油',
  '味噌',
  '塩',
  'とんこつ',
  '魚介系',
  '鶏白湯',
  '背脂',
  '焼豚',
  'チャーシュー',
  '雲呑',
  'ワンタン',
  'ほうれん草',
  '味玉',
  'メンマ',
  'わかめ',
  '海苔',
  'ネギ',
  'タマネギ',
  'もやし',
  'ニンニク',
  '野菜',
  '魚粉',
  '高菜',
  '紅ショウガ',
  '替え玉',
  'あっさり',
  'こってり',
  '細麺',
  '中太麺',
  '太麺',
  'ちぢれ麺'
]

const particles = [
  'が',
  'と',
  'に',
  'の',
  'は'
]

const verbs = [
  'マシマシで。',
  'を注文する。',
  'が好きだ。',
  'も好きだ。',
  'が食べたい。',
  'も好きだ。',
  'にしよう。',
  'だった。'
]

function generate (maxLength: number, random: RandomSeed.RandomSeed): string {
  let xs = ''
  while (xs.length + 20 < maxLength) {
    const x = random(10) // 0-9
    xs += words[random(words.length)]
    if (x <= 0) {
      xs += particles[random(particles.length)] + '、'
    } else if (x <= 6) {
      xs += particles[random(particles.length)]
    } else {
      xs += verbs[random(verbs.length)]
    }
  }
  xs += words[random(words.length)]
  xs += verbs[random(verbs.length)]
  return xs
}

export interface RamenIpsum {
  ipsum (maxLength: number): string
}

export interface RamenIpsumFactory {
  factory (seed: string): RamenIpsum
}

const ramenIpsum: RamenIpsumFactory = {
  factory (seed: string): RamenIpsum {
    const random = RandomSeed.create(seed)
    return {
      ipsum: maxLength => generate(maxLength, random)
    }
  }
}

export default ramenIpsum

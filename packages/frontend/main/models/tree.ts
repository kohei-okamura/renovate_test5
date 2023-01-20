/*
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
export type TreeNode<T> = Readonly<{
  item: T
  children: TreeNode<T>[]
}>

export type Tree<T> = TreeNode<T>[]

function createTreeNode<T> (item: T, children: T[] = []): TreeNode<T> {
  return {
    item,
    children: children.map(x => createTreeNode(x))
  }
}

export function createTree<T extends { id: number }> (items: T[], keyToParent: keyof T, key: keyof T = 'id'): Tree<T> {
  const parents = items.filter(x => !x[keyToParent])
  return parents.map(item => createTreeNode(item, items.filter(x => x[keyToParent] === item[key])))
}

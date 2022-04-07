## Conventional Commit BLT Plugin

This is Conventional Commit BLT Plugin to enforce Conventional Commits while committing.

## Installation and usage

In your project, require the plugin with Composer:

`composer require koustubhdudhe/conventional-commit-blt-plugin`

Run `blt blt:init:git-hooks`.

Add Below Configurations in the blt.yml file

Search for and replace the following placeholders within this file:
```
git:
  default_branch: master
  commit-msg:
    pattern: '/((SOMETHING)-[0-9]+(: )[^ ].{15,})|(Merge branch (.)+)/'
  ticket-id:
    pattern: '(SOMETHING)-[0-9]*'
  commit-type:
    pattern: 'feat|fix|refactor|style|test|docs|build'
    description:
      feat: 'Feat: Commits, that adds a new feature'
      fix: 'Fix: Commits, that fixes a bug'
      refactor: 'Refactor: Commits, that rewrite/restructure your code, however does not change any behaviour'
      style: 'Style: Commits, that do not affect the meaning (white-space, formatting, missing semi-colons, etc)'
      test: 'Test: Commits, that add missing tests or correcting existing tests'
      docs: 'Docs: Commits, that affect documentation only'
      build: 'Build: Commits, that affect build components like build tool, ci pipeline, dependencies, project version'
```
## Commands to use
`blt commit`
`git commit`

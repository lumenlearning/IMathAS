# OHM Branching

Due to:
 
- Changes both coming and going to/from MOM
- The intentionally delayed schedule of pulling changes in from MOM
- Rebuilding of MOM change-set branches
- Ongoing development of OHM-specific changes
- Multiple paths of development happening concurrently
- etc.

The following branch structure has worked well for keeping things (mostly) sane across much context switching and long periods of time between deploys.

## Quick Reference

| Branch name | Description |
| ----------- | ----------- |
| master | Represents the current state of production at all times. Only merge into master prior to a deploy to prod. |
| **dev** | This is a development integration branch, where MOM changes, security fixes, and feature branches planned for prod come together to play. Create new branches from `dev` and merge back into `dev`. :) |
| staging | Currently deployed to staging. This branch may be deleted and recreated at will, as necessary. |
| mom\_YYYY-MM-DD | Latest MOM commits up to the specified date. |
| mom\_YYYY-MM-DD_bugfixes | Most recent MOM bugfixes for the branch matching this date. |
| rc-for-YYYY-MM-DD | Release candidate branches for PROD deployment. |
| security\_YYYY-MM-DD | Latest security fixes committed up to the specified date. |
| NNNN_feature_desc_here | Personal feature branch with story ID, if any. |

### OHM-specific features or changes

If you are working on an OHM feature or change, just branch off `dev` and PR
back into `dev` unless you are working from a different integration branch.

### Branch tracking

[https://docs.google.com/spreadsheets/d/1-DodYMSwghGrveh4RLIpQgHkaymyEtkZqMjWz4jei4o/edit#gid=0](https://docs.google.com/spreadsheets/d/1-DodYMSwghGrveh4RLIpQgHkaymyEtkZqMjWz4jei4o/edit#gid=0)

## Code paths

Typically, code flows from:

- OHM feature branch merged into ➤ `dev`
- MOM changes cherry picked into ➤ `mom_YYYY-MM-DD` (see [MOM to OHM merges](ohm_mom_merges.md))
- MOM changes + bug fixes cherry picked into ➤ `mom_YYYY-MM-DD_bugfixes`

New RC branches (`rc-for-YYYY-MM-DD`) are created as needed,
when the above branches change,
and are deployed to staging either directly from the RC branch or by git pushing a `staging` branch.

After testing in staging: RC branch ➤ `dev` ➤ `master` ➤ release tag ➤ deploy to AWS

### Why?

Sometimes the `mom_*` branches will need to be rebuilt.
Keeping these branches separate and NOT merged into `dev` keeps this easy.
There is some risk of OHM-specific development depending on things that have changed in MOM.
This workflow has still proved to be the least painful way of managing things.
(totally open to better ways of doing this!)

Rebuilding the `mom_*` branches usually happens for one of the following reasons:

- Commits were incorrectly cherry picked from MOM.
- The desired commit set to cherry pick from MOM has changed.

A separate branch is used for MOM bug fixes so we can easily get the last commit pulled in from MOM by looking at the `mom_YYYY-MM-DD` commit history.

## Next steps

[MOM to OHM merges](ohm_mom_merges.md).


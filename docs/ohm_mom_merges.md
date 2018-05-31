# Summary

OHM is a fork of MyOpenMath. Both are actively and independently developed, with
some bidirectional code sharing.

We periodically pull MOM changes into OHM, but due to OHM-specific changes
there can be many (or few) merge conflicts to resolve.

This describes how to pull a set of changes from MOM into separate OHM branches.

# Merging MOM changes into OHM

For the impatient, instructions begin after these very important notes.
Skipping over these important notes is highly discouraged, of course. :)

## Important note - How to cherry pick

When cherry picking commits from MOM, always use the `-x` option. This
will allow for easy commit matching between MOM and OHM.

Example:

    $ git cherry-pick -x mom-commit-id-here

## Important note - Excluded specific commits

When cherry picking MOM commits into OHM, we may occasionally exclude one, a
few, and/or a range of commits. This will be decided by the Lumen OHM product
manager.

Generally, this should be avoided if at all possible. Keeping track of what
has been excluded will be (and has been) painful over time and/or likely
forgotten.

## Important note - Merge conflicts

OHM is a fork of MyOpenMath. We have made many OHM-specific changes which do
not exist in MOM, and this is the reason for many merge conflicts.

When resolving merge conflicts, special care must be paid to not lose those
OHM-specific changes. Some common things to look for are listed near the end
of this file.

Most OHM-specific changes look something like:

		// #### Begin OHM-specific code #####################################################
		// #### Begin OHM-specific code #####################################################
		// #### Begin OHM-specific code #####################################################
		// #### Begin OHM-specific code #####################################################
		// #### Begin OHM-specific code #####################################################

		if (!$assessmentclosed) {
			require(__DIR__ . '/../ohm/assessments/paywall_start.php');
		}

		// #### End OHM-specific code #######################################################
		// #### End OHM-specific code #######################################################
		// #### End OHM-specific code #######################################################
		// #### End OHM-specific code #######################################################
		// #### End OHM-specific code #######################################################

A few OHM-specific changes may not be as visible. If you are unsure if a change
is OHM-specific and it's not listed near the end of this file, you should ask
someone more familiar with OHM-specific code or features.

## Quick overview

You will be creating two `mom_*` branches.

Creating a separate `mom_*` branch without bug fixes allows for easily checking
the last merged MOM commit by simply looking at commit history. This is done
not just for convenience, but also to ease finding that information after
context has long been lost. (this WILL happen often)

### Step 1: Create a mom_YYYY-MM-DD branch

This branch will contain all MOM changes from the last `mom_YYYY-MM-DD` branch
commit up to the agreed upon cut-off date. The cut-off date is typically 1-2
weeks before today's current date and is chosen by the Lumen OHM product manager.

1. From the the most recent `mom_YYYY-MM-DD` branch (not `mom_*_bugfixes`),
   checkout a new branch named `mom_YYYY-MM-DD`.
1. Cherry pick all commits from MOM (with the `-x` option) within the range of
   the last `mom_YYYY-MM-DD` branch commit up to the desired cut-off date.
1. You may encounter merge conflicts to resolve. Here, we prefer MOM's version
   of things. (while preserving OHM-specific changes)

### Step 2: Create a mom_YYYY-MM-DD\_bugfixes branch

This branch will contain all the desired MOM commits up to the desired
cut-off date AND ONLY all bug fixes up to today.

You may be skipping over many feature commits as you cherry pick bug fixes.
Some bug fixes may depend on or address things in those skipped feature
commits. You'll want to skip over those bug fixes as well.

1. While still on the new `mom_YYYY-MM-DD`, checkout a new branch named
   `mom_YYYY-MM-DD_bugfixes`.
1. Cherry pick all BUG FIX commits ONLY from MOM (with the `-x` option) up to
   today's date.

### Next steps

[Prepare an RC branch](ohm_prepare_rc_branch.md).

# Known OHM-specific changes to look out for

## SQL column: "created_at"

Some DB tables contain a `created_at` column. This does not exist in MOM.
If MOM changes include updates to SQL statements referencing those tables,
they may attempt to remove OHM-specific references to `created_at`.

Details: [DB migration 140](../migrations/140_created_at.php)

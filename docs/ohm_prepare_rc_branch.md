# Summary

RC branches are deployed to staging for testing by development and the Lumen
OHM product manager.

They are eventually merged into `dev`.

## Step 1: Create an rc-for-YYYY-MM-DD branch

This will contain, in order:

- OHM as it exists in PROD.
- All OHM-specific features and changes sitting in `dev`.
- The desired `mom_YYYY-MM-DD` branch.
- The desired `mom_YYYY-MM-DD_bugfixes` branch.
- Any OHM-specific features that must be merged after MOM changes. (sometimes
  the RC branch lives too long, or we code against new MOM features)

NOTE: Always use the `--no-ff` option when merging branches into the RC branch.
This will assist troubleshooting after context is long lost.

1. From the `dev` branch, checkout a new branch named `rc-for-YYYY-MM-DD`.
1. Merge the desired `mom_YYYY-MM-DD` branch. (with `--no-ff`)
1. Resolve merge conflicts. (preserve OHM-specific code!)
1. Merge the desired `mom_YYYY-MM-DD_bugfixes` branch. (with `--no-ff`)
1. Resolve merge conflicts. (preserve OHM-specific code!)
1. Merge any OHM-specific feature branches that must be merged after MOM changes. (usually there are none)
1. Done!

## Next steps

[AWS deployment](ohm_deployment.md).


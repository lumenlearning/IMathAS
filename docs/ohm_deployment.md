# AWS deployment

We use AWS Beanstalk for OHM, and the AWS Beanstalk CLI for deploying to AWS.

# Requirements

1. AWS CLI tools setup on your computer. See details
   [here](ohm_deploy_requirements.md).
1. A git commit prepared and ready to deploy. (deploys are done by commits)

# Basic overview

NOTE: This assumes you have a branch prepared and ready to deploy. For prod,
this branch is normally `master`. For staging / test, this could be any branch.

1. Checkout the branch you want to deploy.
1. `eb status` (**verify the environment you are about to deploy to**)
1. `eb deploy`

The environment you will deploy to is determined by your AWS API credentials
and how you answer questions when running `eb init`.

# Deploying to Staging

1. Checkout the branch you want to deploy.
1. `eb status` (**verify the environment you are about to deploy to**)
1. `eb deploy`

# Deployments to PROD

1. Merge the desired RC branch into `dev`. (with the `--no-ff` option)
1. Merge `dev` into `master`. (with the `--no-ff` option) There should be ZERO
   merge conflicts.

Once `master` is ready for deployment:

1. `eb status` (**verify the environment you are about to deploy to**)
1. `eb deploy`

# Emergency hot-fixes

Ideally:

1. Checkout a new branch from master
1. Make your changes
1. Merge back into master with `--no-ff`
1. Follow the basic deployment instructions above

Once the dust settles, merge those same hot-fixes or changes into the `dev` branch.
Any existing branches based off `dev` will need to be rebased.


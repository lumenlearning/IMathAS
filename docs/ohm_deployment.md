# Deployment Instructions

These instructions will walk you through deploying OHM to staging and production.
We use AWS Beanstalk and the AWS Beanstalk CLi for deploying to AWS.

## Getting Started

If you haven't already,
make sure to setup your local environment to deploy.
For details on how to do that,
follow
[these instructions](ohm_deploy_requirements.md).

### Requirements

1. AWS CLI tools setup on your computer.
1. A git commit prepared and ready to deploy. 
    (deploys are done by commits)

### Basic Overview

**NOTE:** This assumes you have a branch prepared and ready to deploy. For prod,
this branch is normally `master`. For staging / test, this could be any branch.

1. Checkout the branch you want to deploy.
1. `eb status` (**verify the environment you are about to deploy to**)
1. `eb deploy`

The environment you will deploy to is determined by your AWS API credentials
and how you answer questions when running `eb init`.

### Deploying to Staging

1. Checkout the branch you want to deploy.
1. `eb status` (**verify the environment you are about to deploy to**)
1. `eb deploy`

### Deployments to PROD

1. Merge the desired RC branch into `dev`. (with the `--no-ff` option)
1. Merge `dev` into `master`. (with the `--no-ff` option) There should be ZERO
   merge conflicts.

Once `master` is ready for deployment:

1. `eb status` (**verify the environment you are about to deploy to**)
1. `eb deploy`

### Emergency hot-fixes

Ideally:

1. Checkout a new branch from master
1. Make your changes
1. Merge back into master with `--no-ff`
1. Follow the basic deployment instructions above

Example:

    $ git checkout master
    $ git checkout -b hotfix_omg_itbroke
    
    ... fix the thing and commit ...
    
    $ git checkout master
    $ git merge --no-ff hotfix_omg_itbroke
    $ eb status
    $ eb deploy

Once the dust settles, merge those same hot-fixes or changes into the `dev` branch.
Any existing branches based off `dev` will need to be rebased.

    $ git checkout dev
    $ git merge master (no need for --no-ff here)


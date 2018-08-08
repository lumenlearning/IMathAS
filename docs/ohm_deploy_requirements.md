# Deployment Setup Instructions

These instructions will help you setup local deployment to staging and production environments in AWS.

## Install the AWS Beanstalk CLI tools

Using Homebrew packages:

    $ brew search aws

Using pip:

    $ pip install awscli
    $ pip install awsebcli

Using pip in a virtualenv:

    $ virtualenv venv
    $ . venv/bin/activate
    $ pip install awscli
    $ pip install awsebcli

For more details,
see
[docs-dev docs](https://github.com/lumenlearning/docs-dev/blob/master/aws_beanstalk/install_and_setup.md).

## Configure your AWS credentials

Before you can use the AWS Beanstalk CLI tools,
you must provide your AWS API credentials,
which can be obtained from a Lumen AWS admin.

    $ aws configure

When prompted for a region,
select `us-west-2`.
And if asked if you would like to continue with CodeCommit, enter `n` for "No."

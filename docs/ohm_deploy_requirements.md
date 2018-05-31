# Requirements to deploy OHM to AWS Beanstalk

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

For more details, see
[docs-dev docs](https://github.com/lumenlearning/docs-dev/blob/master/aws_beanstalk/install_and_setup.md).

## Configure your AWS credentials

Before you can use the AWS Beanstalk CLI tools, you must provide your AWS API
credentials.

Your AWS API credentials are obtained from a Lumen AWS admin.

    $ aws configure

We are in `us-west-2` and we do NOT use CodeDeploy.


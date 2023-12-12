#!/bin/bash
#
# This script:
#   - deploys a docker image to a Beanstalk environment
#
# Required environment variables:
#   - GITHUB_WORKFLOW           - provided by GHA
#   - GITHUB_RUN_NUMBER         - provided by GHA
#   - GITHUB_RUN_ATTEMPT        - provided by GHA
#   - GITHUB_SHA                - provided by GHA

set -euox pipefail

export VERSION_LABEL=gha-build-$GITHUB_WORKFLOW-$GITHUB_RUN_NUMBER-$GITHUB_RUN_ATTEMPT

while [[ $# -gt 0 ]]; do
  case $1 in
    -b|--bucket-name)
      S3_BUCKET_NAME="$2"
      shift # past argument
      shift # past value
      ;;
    -e|--environment-name)
      BEANSTALK_ENV="$2"
      shift # past argument
      shift # past value
      ;;
    -a|--application-name)
      BEANSTALK_APPLICATION="$2"
      shift # past argument
      shift # past value
      ;;
    -i|--image-tag)
      AWS_ECR_IMAGE_TAG="$2"
      shift # past argument
      shift # past value
      ;;
    -*|--*)
      echo "Unknown option $1"
      exit 1
      ;;
  esac
done

#
# We now need to:
#   1. Upload the build artifact to an S3 bucket
#   2. Create a Beanstalk application version.
#   3. Update a Beanstalk environment with that app version.
#

ZIP_FILE_NAME="${BEANSTALK_ENV}-beanstalk-${AWS_ECR_IMAGE_TAG}.zip"

echo -e "\n============================================================"
echo "  Uploading to S3: beanstalk.zip -> s3://${S3_BUCKET_NAME}/$ZIP_FILE_NAME"
echo -e "============================================================\n"

aws s3 --region us-west-2 cp beanstalk.zip "s3://${S3_BUCKET_NAME}/$ZIP_FILE_NAME"

echo -e "\n============================================================"
echo "  Creating application version"
echo -e "============================================================\n"

aws elasticbeanstalk create-application-version --region us-west-2 --application-name "$BEANSTALK_APPLICATION" --version-label "$VERSION_LABEL" --description "Git commit $GITHUB_SHA" --source-bundle S3Bucket="$S3_BUCKET_NAME",S3Key="$ZIP_FILE_NAME"

echo -e "\n============================================================"
echo "  Beginning deployment to AWS Beanstalk environment: $BEANSTALK_ENV"
echo -e "============================================================\n"

aws elasticbeanstalk update-environment --region us-west-2 --environment-name "$BEANSTALK_ENV" --version-label "$VERSION_LABEL"

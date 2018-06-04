# Online Homework Manager (OHM)

A [MyOpenMath](https://github.com/lumenlearning/myopenmath) (MOM) fork.

**NOTE:** Some development and changes flow in both directions between OHM and MyOpenMath.

## Code of Conduct

Lumen Learning's
[Code of Conduct](https://github.com/lumenlearning/code_of_conduct)
is one of the ways we put our values into practice.
Our commitment to the standards outlined in the Code of Conduct help us build great teams,
craft great code,
and maintain a safe,
pleasant work environment.

## Getting Started

This application is developed in PHP,
with some features being developed using the 
[Slim](https://www.slimframework.com/)
Microframework.
These instructions will get you up and running on your local machine for development and testing purposes.

***TODO:** Add instructions for setting up a local instance of OHM.*

### Order of development and operations

1. [OHM branching](docs/ohm_branching.md)
1. [Merging MOM changes into OHM](docs/ohm_mom_merges.md)
1. [Prepare an RC branch](docs/ohm_prepare_rc_branch.md)
1. [Setup your ability to deploy to AWS](docs/ohm_deploy_requirements.md)
1. [AWS deployment and hot-fixes](docs/ohm_deployment.md)

### OHM branching

OHM has a fairly wild branching structure and workflow.

Before deploying anything to PROD or merging things into `dev`,
please read and understand how branching is done in OHM!
(and if you have suggestions for improvements,
please share!)

See details and why [here](docs/ohm_branching.md).

### Merging MOM changes into OHM

This is typically done when building an RC branch with recent MOM changes.

See details [here](docs/ohm_mom_merges.md).

### Prepare an RC branch

Release candidate branches contain: (merged in this order)

1. OHM-specific changes
1. MOM changes
1. MOM bug fixes

RC branches live in staging for some time during testing, and are then merged
into `dev`, merged into `master`, are release tagged, then deployed to PROD.

See details [here](docs/ohm_prepare_rc_branch.md).

### Deployment

Assuming you have everything configured correctly locally for AWS,
deployment is as simple as running `eb deploy` from the project root directory.

For details on setting up your local environment to deploy with AWS,
follow [these instructions](docs/ohm_deploy_requirements.md).

For details on general deployment with AWS,
follow [these instructions](docs/ohm_deployment.md).

## Reference

### User Permissions

In the `imas_users` table, each user record has one of these values in the
`rights` column to define their role and access level within OHM.

```
switch ($rights) {
    case 5: return _("Guest"); break;
    case 10: return _("Student"); break;
    case 12: return _("Pending"); break;
    case 15: return _("Tutor/TA/Proctor"); break;
    case 20: return _("Teacher"); break;
    case 40: return _("LimCourseCreator"); break;
    case 75: return _("GroupAdmin"); break;
    case 100: return _("Admin"); break;
}
```


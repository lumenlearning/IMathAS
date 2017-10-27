# About

Online Homework Manager, an IMathAS fork.

# Branching

| Branch name | Description |
| ----------- | ----------- |
| master | Currently deployed to production. Only merge into master prior to a deploy to prod. |
| staging | Currently deployed to staging. This branch may be deleted and recreated as necessary to represent staging. |
| **dev** | Where feature branches planned for prod come together to play. When creating a new feature branch, do it from here. :) |
| mom\_(dateHere) | Latest MOM commits up to the specified date. |
| security\_(dateHere) | Latest security fixes committed up to the specified date. |
| Everything else | Personal feature branch. |

## Branch tracking

[https://docs.google.com/spreadsheets/d/1-DodYMSwghGrveh4RLIpQgHkaymyEtkZqMjWz4jei4o/edit#gid=0](https://docs.google.com/spreadsheets/d/1-DodYMSwghGrveh4RLIpQgHkaymyEtkZqMjWz4jei4o/edit#gid=0)

# Rights


	if ($role_num == 5) {return "Guest User";}
	if ($role_num == 10) {return "Student";}
	if ($role_num == 20) {return "Teacher";}
	if ($role_num == 40) {return "Limited Course Creator";}
	if ($role_num == 75) {return "Group Admin";}
	if ($role_num == 100) {return "Full Admin";}


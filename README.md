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


<?php

namespace app\components;

class AppConstant
{
    const VERSION_NUMBER = 13;
    const REMEMBER_ME_TIME = 2592000; //Time in second
    const ZERO_VALUE = '0';
    const ONE_VALUE = '1';
    const NINE_VALUE = '9';
    const AlWAYS_TIME_VALUE = '2000000000';
    const INVALID_USERNAME_PASSWORD = 'Invalid username or password.';
    const MAX_SESSION_TIME = 86400;
    const GIVE_OLD_SESSION_TIME = 90000;
    const LOGIN_FIRST = 'Please login into the system.';
    const FORGOT_PASS_MAIL_SUBJECT = 'Password Reset Request';
    const FORGOT_USER_MAIL_SUBJECT = 'User Name Request';
    const INVALID_EMAIL = 'User does not exist with this email.';
    const INVALID_USER_NAME = 'User does not exist.';
    const ADMIN_RIGHT = 100;
    const STUDENT_RIGHT = 10;
    const TEACHER_RIGHT = 20;
    const TUTOR_RIGHT = 20;
    const GUEST_RIGHT = 5;
    const LIMITED_COURSE_CREATOR_RIGHT = 40;
    const DIAGNOSTIC_CREATOR_RIGHT = 60;
    const GROUP_ADMIN_RIGHT = 75;
    const INSTALL_NAME = 'OpenMath';
    const UNAUTHORIZED = 'You are not authorized to view this page';
    const NORESULT = 'No results found';
    const INSTRUCTOR_REQUEST_SUCCESS = 'Your new account request has been sent.';
    const ADD_NEW_USER = 'Added new user.';
    const INSTRUCTOR_REQUEST_MAIL_SUBJECT = 'New Instructor Account Request';
    const STUDENT_REQUEST_MAIL_SUBJECT = 'New Student Account Request';
    const STUDENT_REQUEST_SUCCESS = 'Your new account request has been sent.';
    const DEFAULT_TIME_ZONE = 'Asia/Kolkata';
    const UPLOAD_DIRECTORY = 'Uploads/';
    const DESCENDING = SORT_DESC;
    const ASCENDING = SORT_ASC;
    const HIDE_ICONS_VALUE = 0;
    const CPLOC_VALUE = 7;
    const CHATSET_VALUE = 0;
    const SHOWLATEPASS = 1;
    const UNENROLL_VALUE = 0;
    const PIC_ICONS_VALUE = 1;
    const TOPBAR_VALUE = '0,1,2,3,9|0,2,3,4,6,9|1';
    const AVAILABLE_NOT_CHECKED_VALUE = 3;
    const NAVIGATION_NOT_CHECKED_VALUE = 7;
    const ITEM_ORDER = 'a:0:{}';
    const NUMERIC_ZERO = 0;
    const NUMERIC_ONE = 1;
    const NUMERIC_TWO = 2;
    const NUMERIC_THREE = 3;
    const NUMERIC_FOUR = 4;
    const NUMERIC_FIVE = 5;
    const NUMERIC_SIX = 6;
    const NUMERIC_SEVEN = 7;
    const NUMERIC_EIGHT = 8;
    const NUMERIC_NINE = 9;
    const NUMERIC_TEN = 10;
    const NUMERIC_ELEVEN = 11;
    const NUMERIC_TWELVE = 12;
    const NUMERIC_THIRTEEN = 13;
    const NUMERIC_FOURTEEN = 14;
    const NUMERIC_FIFTEEN = 15;
    const NUMERIC_THIRTY = 30;
    const NUMERIC_ONE_HUNDRED_TWENTY_SIX = 126;
    const NUMERIC_ONE_HUNDRED_THIRTY_THREE = 133;
    const NUMERIC_ONE_HUNDRED_AND_SIXTY = 160;
    const NUMERIC_THREE_HUNDRED = 300;
    const NUMERIC_ONE_THOUSAND_NINE_HUNDRED = 1900;
    const NUMERIC_THREE_THOUSAND_FIVE_HUNDRED_NINETY_NINE = 3599;
    const NUMERIC_TEN_THOUSAND = 10000;
    const NUMERIC_FIFTY_FIVE_THOUSAND_TWO_HUNDRED_NINETY_FIVE = 55295;
    const NUMERIC_NEGATIVE_ONE = -1;
    const NUMERIC_NEGATIVE_THREE = -3;
    const NUMERIC_SIXTY_ONE = 61;
    const NUMERIC_THIRTY_TWO = 32;
    const TRIPLE_SEVEN = 0777;
    const POINT_ZERO_TWO = .02;
    const YEAR_TWENTY_ELEVEN = 2011;
    const SECONDS_CONVERSION = 86400;
    const NINETY_SEVEN = 97;

    const GB_USE_WEIGHT = 0;
    const GB_ORDERED_BY = 0;
    const GB_DEF_GB_MODE = 21;
    const GB_USER_SORT = 0;

    const SOMETHING_WENT_WRONG = 'Something went wrong, please try latter.';

    const SET_PASSWORD_ERROR = 'Password incorrect. Try again.';
    const RETURN_SUCCESS = 0;
    const RETURN_ERROR = -1;
    const UNAUTHORIZED_ACCESS = 'Unauthorized access, please login with correct credentials.';
    const WRONG_OPTION = 'You need to access this page from the link on the course page';

    const ASSET_TYPE_CSS = "css";
    const ASSET_TYPE_JS = "js";

    const NO_USER_FOUND = "User not found.";
    const NO_MESSAGE_FOUND = "No message found.";
    const NO_COURSE_FOUND = "Course not found.";
    const DELETED_SUCCESSFULLY = "Deleted successfully.";
    /*
     * Assessment Constant
     */
    const WEEK_TIME = 604800;
    const SECONDS = 60;
    const MINUTE = 60;
    const HOURS = 24;
    const MINUTES = 3600;
    const DEFAULT_FEEDBACK = "This assessment contains items that are not automatically graded.  Your grade may be inaccurate until your instructor grades these items.";
    const SAVE_BUTTON = "Save Changes";
    const CREATE_BUTTON = "Create Assessment";
    const DEFAULT_OUTCOMES = 'No default outcome selected';
    const DEFAULT_OUTCOMES_FOR_FORUM='Select an outcome...';
    const GROUP_SET ='Create new set of groups';
    const NONE = "None";
    const UNLIMITED = "Unlimited";
    const TUTOR_NO_ACCESS = "No access";
    const TUTOR_READ_SCORES = "View Scores";
    const TUTOR_READ_WRITE_SCORES = "View and Edit Scores";
    const New_Item = "Create Item";
    const ALWAYS_TIME = 2000000000;
    const STUDENT_ERROR_MESSAGE = "Student not found please enter correct username.";
    const TEACHER_CANNOT_CHANGE_AS_SRUDENT = "Teachers can\'t be enrolled as students - use Student View, or create a separate student account.";
    const UPDATE_STUDENT_SUCCESSFULLY="Student information updated successfully.";
    const USER_EXISTS ="Username already exists";
    const ADD_AT_LEAST_ONE_RECORD = "Add atleast one records in file.";
    const RECORD_EXISTS = "Entered record(s) already exist in file.";
    const GREATER_THEN_END_DATE="Available date(Available After) cannot be greater than end date(Available Until).";
    const IMPORTED_SUCCESSFULLY="Imported student successfully.";
    const STUDENT_EXISTS="All the student from file already exits.";
    const USERNAME_ENROLLED="This username is already enrolled in the class";
    const MIN_LATEPASS = "These students all have %u latepasses.";
    const MIN_MAX_LATEPASS="These students have %u-%u latepasses.";
    const ASSESSMENT_ALREADY_STARTED = "Sorry, cannot switch to use pre-defined groups after students have already started the assessment";
    const DEFAULT_ASSESSMENT_INTRO = "<p>Enter intro/instructions</p>";
    const DEFAULT_ASSESSMENT_NAME = "Enter assessment name";
    const DEFAULT_ASSESSMENT_SUMMARY = "<p>Enter summary here (shows on course page)</p>";
    const MODIFY_ASSESSMENT ="Modify Assessment";
    const MODIFY_BlOCK ="Modify Block";
    const ADD_ASSESSMENT ="Add Assessment";
    const ADD_BLOCK ="Add Block";
    const TEST_TYPE = "AsGo";
    const SHOW_ANSWER = "A";
    const CALTAG = '?';
    const CALRTAG = 'R';
    const AM = 'am';
    const PM = 'pm';
    const FORUM = 'Forum';
    const WIKI = 'Wiki';
    const LINK ='LinkedText';
    const ASSESSMENT ='Assessment';
    const INLINE_TEXT = 'InlineText';
    const CALENDAR = 'Calendar';
    const BLOCK = 'Block';
    const MESSAGE_SUCCESS = 'Message sent successfully.';
    const FIRST_NAME = 'FirstName';
    const ENROLL_SUCCESS = 'You have been enrolled in course ';
    const ALREADY_ENROLLED = 'You are already enrolled in the course.';
    const TEACHER_CANNOT_ENROLL_AS_STUDENT = 'You are a teacher for this course, and can not enroll as a student.Use Student View to see the class from a student perspective, or create a dummy student account.';
    const TUTOR_CANNOT_ENROLL_AS_STUDENT = 'You are a tutor for this course, and can not enroll as a student.';
    const INVALID_COMBINATION = 'Invalid combination of enrollment key and course id.';
    const INLINE_TEXT_MODIFY_TITLE = 'Modify Inline Text';
    const INLINE_TEXT_ADD_TITLE = 'Add Inline Text';
    const CHOOSE_STUDENT = 'Select course from list to choose students';
    const ADD_FORUM = 'Add Forum';
    const CREATE_FORUM = 'Create Forum';
    const CREATE_BLOCK = 'Create Block';
    const SIXTEEN = 16;
    const COPY = " (Copy)";
    const NO_ACCESS_RIGHTS = "You don't have the authority for this action";
    const ADD_INLINE_TEXT = "Add Inline Text";
    const CREATE_ITEM = "Create Item";
    const NO_TEACHER_RIGHTS = "You need to log in as a teacher to access this page";
    const REQUIRED_ADMIN_ACCESS = "You need to log in as an admin to access this page";
    Const ACCESS_THROUGH_MENU = "Please access this page from the menu links only.";
    const PAST_DUE_DATE = 'Past Due Date of %s. Showing as Review';
    const AVAILABLE_UNTIL = 'Available %1$s until %2$s';
    const NUMERIC_HUNDREAD = 100;
    const NUMERIC_THOUSAND = 1000;
    const INSTRUCTORNOTE = '<p>Instructor note: Message sent to these students from course';
    const NUMERIC_TWO_THOUSAND = 2000;
    const NUMERIC_TRIPLE_NINE =999;
    const QUARTER_NINE = 9999;
    const Question_OUTPUT_MSG1 = " Local copy of Question Created ";
    const Question_OUTPUT_MSG2 = " Question Added to QuestionSet. ";
    const IMAGE_FILE_ERROR1 = "<p>Need to specify variable for image to be referenced by</p>";
    const IMAGE_FILE_ERROR2 = "is not an allowed variable name";
    const QUESTION_DESCRIPTION = "Enter description here";
    const GROUP_NAME = 'Unnamed group set';
    const NEW_GROUP_NAME = 'Unnamed Group';
    const GROUP_MESSAGE = 'You need to log in as a teacher to access this page';
    const NUMERIC_SIXTY = 60;
    const NO_SETTING = 'No settings have been selected to be changed. Use the checkboxes along the left to indicate that you want to change that setting.';
    const NO_ASSESSMENT = 'No assessments are selected to be changed.';
    const NO_ASSESSMENT_TO_CHANGE = 'No Assessments to change.';
    const NON_STUDENT = "Group - Non Students";
    const PENDING_USERS = "Pending Users";
    const QUESTION_TITLE = "Add/Remove Questions";
    const NO_PAGE_ACCESS = "You need to access this page from the course page menu";
    const ADD_OFFLINE_GRADE = 'Add Offline Grades';
    const MODIFY_OFFLINE_GRADE = 'Modify Offline Grades';
    const NO_AUTHORITY = "You don't have authority for this action";
    const CONFIRMATION_MESSAGE= 'Are you SURE you want to delete this item and all associated grades from the gradebook?';
    const CONFIRM_DELETE = 'Confirm Delete';
    const DEFAULT_THEME = 'default.css';
    const CHANGE_ASSESSMENT_SUCCESSFULLY = 'Assessment data changes successfully.';
    const NO_QUESTION_SELECTED = 'No questions selected';
    const POINT_FIVE = 0.5;
    const POINT_SIX = 0.6;
    const POINT_SEVEN = 0.5;
    const EIGHT_POINT_FIVE = 8.5;
    const USE_GROUP_SET = 'Use group set';
    const NO_FORUM_SELECTED = 'No forums are selected to be changed';
    const FORUM_UPDATED = 'Forums data changes successfully';
    const UNASSIGNED = 'Unassigned';
    const TABLE_HEADER_LIB = "<th>Library</th>";
    const PARAGRAPH_TAG = '<p></p>';
    const OUTPUT_MSG_ONE = "Question Updated. ";
    const OUTPUT_MSG_TWO = "Library Assignments Updated. ";
    const IMAGE_FILE_ERROR3 = "<p>File is not image file</p>";
    const ERROR_IN_IMAGE_UPLOAD = "<p>Error uploading image file!</p>\n";
    const SAVE_AND_TEST = "Save and Test Question";
    const OUTPUT_MSG_THREE = "previewpop = window.open(addr,'Testing','width='+(.4*screen.width)+',height='+(.8*screen.height)+',scrollbars=1,resizable=1,status=1,top=20,left='+(.6*screen.width-20));\n";
    const OUTPUT_MSG_FOUR = "previewpop.focus();";
    const SCRIPT_TAG = "</script>";
    const ADD = "Add";
    const MODIFY = "Modify";
    const PAGE_MSG1 = "<h3>Warning</h3>\n";
    const PAGE_MSG2 = "<p>This assessment has already been taken.  Altering the points or penalty will not change the scores of students who already completed this question. ";
    const PAGE_MSG3 = "If you want to make these changes, or add additional copies of this question, you should clear all existing assessment attempts</p> ";
    const VALIDATION_MSG = "error: validation";
    const OK = "OK";
    const NOT_SAVE_ERROR = "error: not saved";
    const ERROR = "Error";
    const MANAGE_QUE_SET_TITLE1 = "Manage Question Sets";
    const MANAGE_QUE_SET_TITLE2 = "Transfer Ownership";
    const MANAGE_QUE_SET_TITLE3 = "Modify Library Assignments";
    const MANAGE_QUE_SET_TITLE4 = "Template Questions";
    const MANAGE_QUE_SET_TITLE5 = "Change Question License/Attribution";
    const MANAGE_QUE_SET_TITLE6 = "Change Question Rights";
    const MANAGE_QUE_SET_TITLE7 = "Template Questions";
    const PAGE_ADMIN_MSG1 = "You are in Admin mode, which means actions will apply to all questions, regardless of owner";
    const PAGE_ADMIN_MSG2 = "You are in Group Admin mode, which means actions will apply to all group's questions, regardless of owner";
    const ERROR_UPLOADING_FILE = "Error uploading file!";
    const EMPTY_FILE = 'File appears to contain nothing';
    const SEVENTY_SIX = 76;
    const SELECT_TOOL = 'Select a tool...';
    const CREATE_LINK = 'Create Link';
    const ENTER_TITLE = 'Enter title here';
    const ENTER_SUMMARY = '<p>Enter summary here (displays on course page)</p>';
    const FORUM_DESCRIPTION = '<p>Enter forum description here</p>';
    const FOUR_ZERO_NINE_SIX = 4096;
    const SIXTY = 60;
    const MIN_START_DATE = 9999999999;
    const NO_ASSESSEMENT_SELECTED = 'No assessments selected';
    const ADD_LINK = 'Add Link';
    const NO_FORUM_ACCESS = 'No access to scores for this forum';
    const INVALID_PARAMETERS = 'invalid parameters';
}
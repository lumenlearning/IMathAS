<?php

namespace OHM\tests\integration;

use Faker\Factory as FakerFactory;
use Faker\Generator as FakerGenerator;
use Illuminate\Database\Capsule\Manager as DB;
use OHM\Api\Services\GroupService;
use OHM\Models\Course;
use OHM\Models\Group;
use OHM\Models\User;
use Ramsey\Uuid\Uuid;


/**
 * @covers GroupService
 */
final class GroupServiceTest extends SlimPhp3TestCase
{
    private FakerGenerator $faker;
    private GroupService $groupService;

    private Group $group;
    private User $user;
    /** @var Course[] */
    private array $courses;

    function setUp(): void
    {
        parent::setUp();

        $this->faker = FakerFactory::create();
        $this->groupService = new GroupService($this->container);

        DB::beginTransaction();

        try {
            // Create a group.
            $groupName = $this->faker->company . ' University with courses';
            $createdAt = $this->faker->numberBetween(time() - 86400 * 365 * 5, time());
            $this->group = new Group;
            $this->group->grouptype = 0;
            $this->group->name = $groupName;
            $this->group->parent = 0;
            $this->group->student_pay_enabled = 0;
            $this->group->lumen_guid = Uuid::uuid4();
            $this->group->created_at = $createdAt;
            $this->group->save();

            // Create a User in the Group.
            $username = $this->faker->userName;
            $createdAt = $this->faker->numberBetween(time() - 86400 * 365 * 5, time());
            $this->user = new User;
            $this->user->SID = $username;
            $this->user->password = password_hash($username, PASSWORD_BCRYPT);
            $this->user->rights = 20;
            $this->user->FirstName = $this->faker->firstName;
            $this->user->LastName = $this->faker->lastName;
            $this->user->email = $this->faker->email;
            $this->user->groupid = $this->group->id;
            $this->user->jsondata = '';
            $this->user->hideonpostswidget = '';
            $this->user->mfa = '';
            $this->user->created_at = $createdAt;
            $this->user->save();

            // Create a Course owned by the User.
            $course1 = new Course;
            $course1->ownerid = $this->user->id;
            $course1->name = 'Test course 1';
            $course1->enrollkey = '';
            $course1->itemorder = 'a:0:{}';
            $course1->allowunenroll = '0';
            $course1->copyrights = '0';
            $course1->blockcnt = '1';
            $course1->msgset = '0';
            $course1->toolset = '4';
            $course1->showlatepass = '1';
            $course1->available = '0';
            $course1->lockaid = '0';
            $course1->theme = 'lumen.css_fw1920';
            $course1->latepasshrs = '24';
            $course1->newflag = '0';
            $course1->istemplate = '0';
            $course1->deflatepass = '0';
            $course1->deftime = '106000600';
            $course1->termsurl = '';
            $course1->outcomes = '';
            $course1->ancestors = '';
            $course1->ltisecret = '';
            $course1->student_pay_required = null;
            $course1->jsondata = '';
            $course1->created_at = '1701107802';
            $course1->dates_by_lti = '0';
            $course1->startdate = '0';
            $course1->enddate = '2000000000';
            $course1->cleanupdate = '0';
            $course1->UIver = '2';
            $course1->level = 'othermeow';
            $course1->ltisendzeros = '0';
            $course1->save();

            // Create a Course owned by the User.
            $course2 = new Course;
            $course2->ownerid = $this->user->id;
            $course2->name = 'Test course 2';
            $course2->enrollkey = '';
            $course2->itemorder = 'a:0:{}';
            $course2->allowunenroll = '0';
            $course2->copyrights = '0';
            $course2->blockcnt = '1';
            $course2->msgset = '0';
            $course2->toolset = '4';
            $course2->showlatepass = '1';
            $course2->available = '0';
            $course2->lockaid = '0';
            $course2->theme = 'lumen.css_fw1920';
            $course2->latepasshrs = '24';
            $course2->newflag = '0';
            $course2->istemplate = '0';
            $course2->deflatepass = '0';
            $course2->deftime = '106000600';
            $course2->termsurl = '';
            $course2->outcomes = '';
            $course2->ancestors = '';
            $course2->ltisecret = '';
            $course2->student_pay_required = null;
            $course2->jsondata = '';
            $course2->created_at = '1701107803';
            $course2->dates_by_lti = '0';
            $course2->startdate = '0';
            $course2->enddate = '2000000000';
            $course2->cleanupdate = '0';
            $course2->UIver = '2';
            $course2->level = 'othermeow';
            $course2->ltisendzeros = '0';
            $course2->save();

            // Create a Course owned by the User.
            $course3 = new Course;
            $course3->ownerid = $this->user->id;
            $course3->name = 'Test course 3';
            $course3->enrollkey = '';
            $course3->itemorder = 'a:0:{}';
            $course3->allowunenroll = '0';
            $course3->copyrights = '0';
            $course3->blockcnt = '1';
            $course3->msgset = '0';
            $course3->toolset = '4';
            $course3->showlatepass = '1';
            $course3->available = '0';
            $course3->lockaid = '0';
            $course3->theme = 'lumen.css_fw1920';
            $course3->latepasshrs = '24';
            $course3->newflag = '0';
            $course3->istemplate = '0';
            $course3->deflatepass = '0';
            $course3->deftime = '106000600';
            $course3->termsurl = '';
            $course3->outcomes = '';
            $course3->ancestors = '';
            $course3->ltisecret = '';
            $course3->student_pay_required = null;
            $course3->jsondata = '';
            $course3->created_at = '1701107804';
            $course3->dates_by_lti = '0';
            $course3->startdate = '0';
            $course3->enddate = '2000000000';
            $course3->cleanupdate = '0';
            $course3->UIver = '2';
            $course3->level = 'othermeow';
            $course3->ltisendzeros = '0';
            $course3->save();

            // Make all courses available to tests.
            $this->courses[] = $course1;
            $this->courses[] = $course2;
            $this->courses[] = $course3;

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollback();
            throw $e;
        }
    }

    function tearDown(): void
    {
        parent::tearDown();

        foreach ($this->courses as $course) {
            $course->delete();
        }
        $this->user->delete();
        $this->group->delete();
    }

    /*
     * findByIdOrUuid
     */

    public function testFindByIdOrUuid_by_id(): void
    {
        $foundGroup = $this->groupService->findByIdOrUuid($this->group->id);
        $this->assertEquals($this->group->lumen_guid, $foundGroup->lumen_guid);
    }

    public function testFindByIdOrUuid_by_uuid(): void
    {
        $foundGroup = $this->groupService->findByIdOrUuid($this->group->lumen_guid);
        $this->assertEquals($this->group->id, $foundGroup->id);
    }

    /*
     * updateByIdOrUuid
     */

    public function testUpdateByIdOrUuid_by_id(): void
    {
        /*
         * Enable payments.
         */

        $returnedGroup = $this->groupService->updateByIdOrUuid(
            $this->group->id, ['student_pay_enabled' => 1]);
        $this->assertInstanceOf(Group::class, $returnedGroup);
        $this->assertEquals(1, $returnedGroup->student_pay_enabled);

        // Pull fresh data from the DB.
        $group = Group::find($this->group->id);
        $this->assertEquals(1, $group->student_pay_enabled);

        // Ensure all courses within the group were also updated.
        foreach ($this->courses as $course) {
            $reloadedCourse = Course::find($course->id);
            $this->assertEquals(1, $reloadedCourse->student_pay_required);
        }

        /*
         * Disable payments.
         */

        $returnedGroup = $this->groupService->updateByIdOrUuid(
            $this->group->id, ['student_pay_enabled' => 0]);
        $this->assertInstanceOf(Group::class, $returnedGroup);
        $this->assertEquals(0, $returnedGroup->student_pay_enabled);

        // Pull fresh data from the DB.
        $group = Group::find($this->group->id);
        $this->assertEquals(0, $group->student_pay_enabled);

        // Ensure all courses within the group were also updated.
        foreach ($this->courses as $course) {
            $reloadedCourse = Course::find($course->id);
            $this->assertEquals(0, $reloadedCourse->student_pay_required);
        }
    }

    public function testUpdateByIdOrUuid_by_uuid(): void
    {
        /*
         * Enable payments.
         */

        $returnedGroup = $this->groupService->updateByIdOrUuid(
            $this->group->lumen_guid, ['student_pay_enabled' => 1]);
        $this->assertInstanceOf(Group::class, $returnedGroup);
        $this->assertEquals(1, $returnedGroup->student_pay_enabled);

        // Pull fresh data from the DB.
        $group = Group::find($this->group->id);
        $this->assertEquals(1, $group->student_pay_enabled);

        // Ensure all courses within the group were also updated.
        foreach ($this->courses as $course) {
            $reloadedCourse = Course::find($course->id);
            $this->assertEquals(1, $reloadedCourse->student_pay_required);
        }

        /*
         * Disable payments.
         */

        $returnedGroup = $this->groupService->updateByIdOrUuid(
            $this->group->lumen_guid, ['student_pay_enabled' => 0]);
        $this->assertInstanceOf(Group::class, $returnedGroup);
        $this->assertEquals(0, $returnedGroup->student_pay_enabled);

        // Pull fresh data from the DB.
        $group = Group::find($this->group->id);
        $this->assertEquals(0, $group->student_pay_enabled);

        // Ensure all courses within the group were also updated.
        foreach ($this->courses as $course) {
            $reloadedCourse = Course::find($course->id);
            $this->assertEquals(0, $reloadedCourse->student_pay_required);
        }
    }
}

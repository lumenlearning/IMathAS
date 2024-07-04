<?php


use Phinx\Seed\AbstractSeed;
use Faker\Factory as FakerFactory;
use Ramsey\Uuid\Uuid;

class CourseSeeder extends AbstractSeed
{
    /**
     * Run Method.
     *
     * Write your database seeder using this method.
     *
     * More information on writing seeders is available here:
     * http://docs.phinx.org/en/latest/seeding.html
     */
    public function run(): void
    {
        // https://github.com/fzaninotto/Faker
        $faker = FakerFactory::create();

        $groupName = $faker->company . ' University';
        $this->table('imas_groups')->insert([
            'grouptype' => 0,
            'name' => $groupName,
            'parent' => 0,
            'student_pay_enabled' => 0,
            'lumen_guid' => Uuid::uuid4(),
            'created_at' => $faker->numberBetween(time() - 86400 * 365 * 5, time()),
        ])->save();
        $row = $this->fetchRow(sprintf('SELECT * FROM imas_groups WHERE name = "%s"', $groupName));
        $groupId = $row['id'];

        $username = $faker->unique()->userName;
        $this->table('imas_users')->insert([
            'SID' => $username,
            'password' => password_hash($username, PASSWORD_BCRYPT),
            'rights' => 20,
            'FirstName' => $faker->firstName,
            'LastName' => $faker->lastName,
            'email' => $faker->email,
            'groupid' => $groupId,
            'jsondata' => '',
            'hideonpostswidget' => '',
            'mfa' => '',
            'created_at' => $faker->numberBetween(time() - 86400 * 365 * 5, time()),
        ])->save();
        $row = $this->fetchRow(sprintf('SELECT * FROM imas_users WHERE SID = "%s"', $username));
        $userId = $row['id'];


        $data = [];

        $data[] = [
            'ownerid' => $userId,
            'name' => 'Test course 1',
            'enrollkey' => '',
            'itemorder' => 'a:0:{}',
            'allowunenroll' => '0',
            'copyrights' => '0',
            'blockcnt' => '1',
            'msgset' => '0',
            'toolset' => '4',
            'showlatepass' => '1',
            'available' => '0',
            'lockaid' => '0',
            'theme' => 'lumen.css_fw1920',
            'latepasshrs' => '24',
            'newflag' => '0',
            'istemplate' => '0',
            'deflatepass' => '0',
            'deftime' => '106000600',
            'termsurl' => '',
            'outcomes' => '',
            'ancestors' => '',
            'ltisecret' => '',
            'student_pay_required' => null,
            'jsondata' => '',
            'created_at' => '1701107802',
            'dates_by_lti' => '0',
            'startdate' => '0',
            'enddate' => '2000000000',
            'cleanupdate' => '0',
            'UIver' => '2',
            'level' => 'othermeow',
            'ltisendzeros' => '0',
        ];
        $data[] = [
            'ownerid' => $userId,
            'name' => 'Test course 2',
            'enrollkey' => '',
            'itemorder' => 'a:0:{}',
            'allowunenroll' => '0',
            'copyrights' => '0',
            'blockcnt' => '1',
            'msgset' => '0',
            'toolset' => '4',
            'showlatepass' => '1',
            'available' => '0',
            'lockaid' => '0',
            'theme' => 'lumen.css_fw1920',
            'latepasshrs' => '24',
            'newflag' => '0',
            'istemplate' => '0',
            'deflatepass' => '0',
            'deftime' => '106000600',
            'termsurl' => '',
            'outcomes' => '',
            'ancestors' => '',
            'ltisecret' => '9x2EY4o6',
            'student_pay_required' => null,
            'jsondata' => '',
            'created_at' => '1701107802',
            'dates_by_lti' => '0',
            'startdate' => '0',
            'enddate' => '2000000000',
            'cleanupdate' => '0',
            'UIver' => '2',
            'level' => 'othermeow',
            'ltisendzeros' => '0',
        ];
        $data[] = [
            'ownerid' => $userId,
            'name' => 'Test course 3',
            'enrollkey' => '',
            'itemorder' => 'a:0:{}',
            'allowunenroll' => '0',
            'copyrights' => '0',
            'blockcnt' => '1',
            'msgset' => '0',
            'toolset' => '4',
            'showlatepass' => '1',
            'available' => '0',
            'lockaid' => '0',
            'theme' => 'lumen.css_fw1920',
            'latepasshrs' => '24',
            'newflag' => '0',
            'istemplate' => '0',
            'deflatepass' => '0',
            'deftime' => '106000600',
            'termsurl' => '',
            'outcomes' => '',
            'ancestors' => '',
            'ltisecret' => '',
            'student_pay_required' => null,
            'jsondata' => '',
            'created_at' => '1701107802',
            'dates_by_lti' => '0',
            'startdate' => '0',
            'enddate' => '2000000000',
            'cleanupdate' => '0',
            'UIver' => '2',
            'level' => 'othermeow',
            'ltisendzeros' => '0',
        ];

        $this->table('imas_courses')->insert($data)->save();
    }
}

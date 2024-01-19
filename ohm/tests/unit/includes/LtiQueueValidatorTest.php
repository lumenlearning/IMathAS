<?php


namespace OHM\Tests\Unit\includes;

use OHM\Includes\LtiQueueValidator;
use PHPUnit\Framework\TestCase;


/**
 * @covers LtiQueueValidator
 */
final class LtiQueueValidatorTest extends TestCase
{
    private $ltiQueueValidator;

    public function setUp(): void
    {
        $this->ltiQueueValidator = new LtiQueueValidator();
    }

    /*
     * is_valid_sourcedid - Checks not specific to LTI version.
     */

    public function test_is_valid_sourcedid_hash_is_empty(): void
    {
        // Only check for empty strings.
        // A DB column constraint ensures there are no null hashes.
        $sourcedid = 'LTI1.3:|:8fbdc725-0775-4123-840b-8cb9f1684d68:|:https://mylearning.suny.edu/api/v1/grades:|:42';
        $isValid = $this->ltiQueueValidator->is_valid_sourcedid('', $sourcedid);

        $this->assertFalse($isValid);
    }

    public function test_is_valid_sourcedid_sourcedid_is_empty(): void
    {
        // Only check for empty strings.
        // A DB column constraint ensures there are no null sourcedids.
        $isValid = $this->ltiQueueValidator->is_valid_sourcedid('meow', '');

        $this->assertFalse($isValid);
    }

    /*
     * is_valid_sourcedid - 1.1 tests
     */

    public function test_is_valid_sourcedid_11_all_good(): void
    {
        // This is what a valid sourcedid should look like. (key type 'u')
        // Using implode for visibility.
        $sourcedid = implode(':|:', [
            '110363-1246813-12812888-3844898-103f57b65885349a84a258b5fffe16b4026f8a5f',
            'https://lumen.instructure.com/api/lti/v1/tools/123456/grade_passback',
            'ltikeyhere',
            'u'
        ]);
        $isValid = $this->ltiQueueValidator->is_valid_sourcedid('meow', $sourcedid);
        $this->assertTrue($isValid);

        // This is what a valid sourcedid should look like. (key type 'c')
        // Using implode for visibility.
        $sourcedid = implode(':|:', [
            '110363-1246813-12812888-3844898-103f57b65885349a84a258b5fffe16b4026f8a5f',
            'https://lumen.instructure.com/api/lti/v1/tools/123456/grade_passback',
            'ltikeyhere_42',
            'c'
        ]);
        $isValid = $this->ltiQueueValidator->is_valid_sourcedid('meow', $sourcedid);
        $this->assertTrue($isValid);
    }

    public function test_is_valid_sourcedid_11_three_parts(): void
    {
        // This sourcedid has too few parts. Using implode for visibility.
        $sourcedid = implode(':|:', [
            '110363-1246813-12812888-3844898-103f57b65885349a84a258b5fffe16b4026f8a5f',
            'https://lumen.instructure.com/api/lti/v1/tools/123456/grade_passback',
            'ltikeyhere',
        ]);
        $isValid = $this->ltiQueueValidator->is_valid_sourcedid('meow', $sourcedid);
        $this->assertFalse($isValid);
    }

    public function test_is_valid_sourcedid_11_five_parts(): void
    {
        // This sourcedid has too many parts. Using implode for visibility.
        $sourcedid = implode(':|:', [
            '110363-1246813-12812888-3844898-103f57b65885349a84a258b5fffe16b4026f8a5f',
            'https://lumen.instructure.com/api/lti/v1/tools/123456/grade_passback',
            'ltikeyhere',
            'u',
            'meow'
        ]);
        $isValid = $this->ltiQueueValidator->is_valid_sourcedid('meow', $sourcedid);
        $this->assertFalse($isValid);
    }

    public function test_is_valid_sourcedid_11_lti_sourcedid_empty(): void
    {
        $sourcedid = implode(':|:', [
            '110363-1246813-12812888-3844898-103f57b65885349a84a258b5fffe16b4026f8a5f',
            '',
            'ltikeyhere',
            'u',
        ]);
        $isValid = $this->ltiQueueValidator->is_valid_sourcedid('meow', $sourcedid);
        $this->assertFalse($isValid);
    }

    public function test_is_valid_sourcedid_11_lti_url_empty(): void
    {
        $sourcedid = implode(':|:', [
            '110363-1246813-12812888-3844898-103f57b65885349a84a258b5fffe16b4026f8a5f',
            'meow',
            'ltikeyhere',
            'u',
        ]);
        $isValid = $this->ltiQueueValidator->is_valid_sourcedid('meow', $sourcedid);
        $this->assertFalse($isValid);
    }

    public function test_is_valid_sourcedid_11_lti_url_invalid(): void
    {
        $sourcedid = implode(':|:', [
            '',
            'https://lumen.instructure.com/api/lti/v1/tools/123456/grade_passback',
            'ltikeyhere',
            'u',
        ]);
        $isValid = $this->ltiQueueValidator->is_valid_sourcedid('meow', $sourcedid);
        $this->assertFalse($isValid);
    }

    public function test_is_valid_sourcedid_ltikey_empty(): void
    {
        // This is what a valid sourcedid should look like. (key type 'u')
        // Using implode for visibility.
        $sourcedid = implode(':|:', [
            '110363-1246813-12812888-3844898-103f57b65885349a84a258b5fffe16b4026f8a5f',
            'https://lumen.instructure.com/api/lti/v1/tools/123456/grade_passback',
            '',
            'u'
        ]);
        $isValid = $this->ltiQueueValidator->is_valid_sourcedid('meow', $sourcedid);
        $this->assertFalse($isValid);
    }

    // TODO: Add tests for valid keytypes after we've determined what
    //       validation should look like.

    /*
     * is_valid_sourcedid - 1.3 tests
     */

    public function test_is_valid_sourcedid_13_all_good(): void
    {
        // This is what a valid sourcedid should look like.
        $sourcedid = implode(':|:', [
            'LTI1.3',
            '8fbdc725-0775-4123-840b-8cb9f1684d68',
            'https://mylearning.suny.edu/api/v1/grades',
            '42'
        ]);
        $isValid = $this->ltiQueueValidator->is_valid_sourcedid('meow', $sourcedid);

        $this->assertTrue($isValid);
    }

    public function test_is_valid_sourcedid_13_three_parts(): void
    {
        // This sourcedid has too few parts. Using implode for visibility.
        $sourcedid = implode(':|:', [
            'LTI1.3',
            '8fbdc725-0775-4123-840b-8cb9f1684d68',
            'https://mylearning.suny.edu/api/v1/grades'
        ]);
        $isValid = $this->ltiQueueValidator->is_valid_sourcedid('meow', $sourcedid);
        $this->assertFalse($isValid);
    }

    public function test_is_valid_sourcedid_13_five_parts(): void
    {
        // This sourcedid has too many parts. Using implode for visibility.
        $sourcedid = implode(':|:', [
            'LTI1.3',
            '8fbdc725-0775-4123-840b-8cb9f1684d68',
            'https://mylearning.suny.edu/api/v1/grades',
            '42',
            'meow'
        ]);
        $isValid = $this->ltiQueueValidator->is_valid_sourcedid('meow', $sourcedid);
        $this->assertFalse($isValid);
    }

    public function test_is_valid_sourcedid_13_ltiver_missing(): void
    {
        // Due to how the LTI version is detected, this will be parsed
        // by the LTI 1.1 validator. It should still fail as it's not
        // a valid 1.1 sourcedid either.
        $sourcedid = ':|:8fbdc725-0775-4123-840b-8cb9f1684d68:|:https://mylearning.suny.edu/api/v1/grades:|:42';
        $isValid = $this->ltiQueueValidator->is_valid_sourcedid('meow', $sourcedid);

        $this->assertFalse($isValid);
    }

    public function test_is_valid_sourcedid_13_ltiver_invalid(): void
    {
        // IMathAS uses substr to check for "LTI1.3". This check ensures
        // there are no trailing spaces.
        $sourcedid = 'LTI1.3 :|:8fbdc725-0775-4123-840b-8cb9f1684d68:|:https://mylearning.suny.edu/api/v1/grades:|:42';
        $isValid = $this->ltiQueueValidator->is_valid_sourcedid('meow', $sourcedid);

        $this->assertFalse($isValid);
    }

    public function test_is_valid_sourcedid_13_ltiuserid_is_empty(): void
    {
        $sourcedid = 'LTI1.3:|::|:https://mylearning.suny.edu/api/v1/grades:|:42';
        $isValid = $this->ltiQueueValidator->is_valid_sourcedid('meow', $sourcedid);

        $this->assertFalse($isValid);
    }

    public function test_is_valid_sourcedid_13_score_url_is_empty(): void
    {
        $sourcedid = 'LTI1.3:|:8fbdc725-0775-4123-840b-8cb9f1684d68:|::|:42';
        $isValid = $this->ltiQueueValidator->is_valid_sourcedid('meow', $sourcedid);

        $this->assertFalse($isValid);
    }

    public function test_is_valid_sourcedid_13_score_url_is_invalid(): void
    {
        $sourcedid = 'LTI1.3:|:8fbdc725-0775-4123-840b-8cb9f1684d68:|:meow:|:42';
        $isValid = $this->ltiQueueValidator->is_valid_sourcedid('meow', $sourcedid);

        $this->assertFalse($isValid);
    }

    public function test_is_valid_sourcedid_13_platformid_missing(): void
    {
        $sourcedid = 'LTI1.3:|:8fbdc725-0775-4123-840b-8cb9f1684d68:|:https://mylearning.suny.edu/api/v1/grades:|:';
        $isValid = $this->ltiQueueValidator->is_valid_sourcedid('meow', $sourcedid);

        $this->assertFalse($isValid);
    }

    public function test_is_valid_sourcedid_13_platformid_is_float(): void
    {
        $sourcedid = 'LTI1.3:|:8fbdc725-0775-4123-840b-8cb9f1684d68:|:https://mylearning.suny.edu/api/v1/grades:|:42.5';
        $isValid = $this->ltiQueueValidator->is_valid_sourcedid('meow', $sourcedid);

        $this->assertFalse($isValid);
    }

    public function test_is_valid_sourcedid_13_platformid_is_string(): void
    {
        $sourcedid = 'LTI1.3:|:8fbdc725-0775-4123-840b-8cb9f1684d68:|:https://mylearning.suny.edu/api/v1/grades:|:meow';
        $isValid = $this->ltiQueueValidator->is_valid_sourcedid('meow', $sourcedid);

        $this->assertFalse($isValid);
    }
}

<?php

namespace OHM\Tests\Unit\includes;

use OHM\Includes\LibImportMappingCsv;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use RuntimeException;


/**
 * @covers \OHM\Includes\LibImportMappingCsv
 */
final class LibImportMappingCsvTest extends TestCase
{
    private LibImportMappingCsv $libImportMappingCsv;

    public function setUp(): void
    {
        $this->libImportMappingCsv = new LibImportMappingCsv();
    }

    /*
     * __construct
     */

    public function testConstruct(): void
    {
        $this->assertStringContainsString('/qid_mapping_',
            $this->libImportMappingCsv->getFilesystemPath());
    }

    /*
     * getDownloadUrl
     */

    public function testGetDownloadUrl(): void
    {
        $this->assertStringContainsString('/filestore/qid_mapping_',
            $this->libImportMappingCsv->getDownloadUrl());
    }

    /*
     * getFilesystemPath
     */

    public function testGetFilesystemPath(): void
    {
        $this->assertStringContainsString('/filestore/qid_mapping_',
            $this->libImportMappingCsv->getFilesystemPath());
    }

    /*
     * open
     */

    public function open_invalidPath(): void
    {
        $reflection = new ReflectionClass(get_class($this->libImportMappingCsv));
        $property = $reflection->getProperty('CSV_DOCUMENT_ROOT_PREFIX');
        $property->setAccessible(true);

        $property->setValue('/meowwwww');

        $this->expectException(RuntimeException::class);
    }

    /*
     * getFeedbackMacroCsvColumnValues
     */

    public function testGetFeedbackMacroCsvColumnValues(): void
    {
        $questionControl = 'meow this
            meow that

            $feedback = getfeedbackbasic(asdf, $lol, 2)
            $moreFeedback = getfeedbacktxtessay($meow, asdf, $lol);
            $moarFeedback = getfeedbacktxtmultans(1, 2)';

        $reflection = new ReflectionClass(get_class($this->libImportMappingCsv));
        $method = $reflection->getMethod('getFeedbackMacroCsvColumnValues');
        $method->setAccessible(true);

        $csvColumns = $method->invokeArgs($this->libImportMappingCsv, [$questionControl]);

        $expected = ['x', '', 'x', '', '', '', 'x'];

        $this->assertEquals($expected, $csvColumns);
    }
}

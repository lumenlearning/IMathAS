<?php

namespace Tests\unit\Services;

use App\Services\ohm\QuestionCodeParserService;
use Tests\TestCase;

class QuestionCodeParserServiceTest extends TestCase
{
    public function setUp(): void
    {
        if (!$this->app) {
            // Without this, the following error is generated during tests:
            //   RuntimeException: A facade root has not been set.
            $this->refreshApplication();
        }
    }

    public function testDetectFunctionCalls_detectsMultipleFunctions(): void {
        $code = '
            loadlibrary("stats")
            //Give the table in frequencies and then make one wrong answer the frequencies NOT as percentage
            $datatable=array(2005,2006,2007,2008,2009,2010,2011,2012,2013,2014,2015,2016,2017,2018,2019,5,5,10,14,32,72,57,48,49,36,50,40,22,23,49,13,19,35,42,84,226,188,215,197,230,143,132,98,89,225,1,2,4,2,13,37,33,69,49,44,33,40,33,26,86)
            $index=rand(0,14)
            $year=$datatable[$index]
            $large=$datatable[$index+15]
            $medium=$datatable[$index+30]
            $small=$datatable[$index+45]
            $sum=$large+$medium+$small

            $percent=array($small/$sum,$medium/$sum,$large/$sum)
            $wrongorder=array($medium/$sum,$small/$sum,$large/$sum)
            $wrongorder2=array($small,$large,$medium)
            $allequal=array(33.3,33.3,33.3)

            $cats = array("Small","Medium","Large")

            $pie1 = piechart($percent,$cats)
            $pie2 = piechart($wrongorder,$cats)
            $pie3 = piechart($allequal,$cats)
            $pie4 = piechart($wrongorder2,$cats)

            $questions=array($pie1,$pie2,$pie3,$pie4)
            $answer = 0;
            loadlibrary("ohm_macros")
            $feedback = ohm_getfeedbackbasic($stuanswers[$thisq],"Perfect!","It might be best to convert each category into percentage first before selecting the correct pie chart. Note: Percent = part/whole.",$answer)
        ';

        $questionCodeParserService = new QuestionCodeParserService($code);
        $functionCalls = $questionCodeParserService->detectFunctionCalls();

        $this->assertNotEmpty($functionCalls);
        $this->assertCount(15, $functionCalls);

        $this->assertEquals('loadlibrary', $functionCalls[0]['name']);
        $this->assertEquals('"stats"', $functionCalls[0]['arguments']);
        $this->assertEquals('ohm_getfeedbackbasic', $functionCalls[count($functionCalls) - 1]['name']);
        $this->assertEquals('$stuanswers[$thisq],"Perfect!","It might be best to convert each category into percentage first before selecting the correct pie chart. Note: Percent = part/whole.",$answer', $functionCalls[count($functionCalls) - 1]['arguments']);
    }

    public function testDetectFunctionCalls_detectsNestedFunctionCalls(): void {
        $code = 'includecodefrom(rand(num(1,3),5), 2)';

        $questionCodeParserService = new QuestionCodeParserService($code);
        $functionCalls = $questionCodeParserService->detectFunctionCalls();

        $this->assertNotEmpty($functionCalls);
        $this->assertCount(3, $functionCalls);

        $this->assertEquals('includecodefrom', $functionCalls[0]['name']);
        $this->assertEquals('rand(num(1,3),5), 2', $functionCalls[0]['arguments']);

        $this->assertEquals('rand', $functionCalls[1]['name']);
        $this->assertEquals('num(1,3),5', $functionCalls[1]['arguments']);

        $this->assertEquals('num', $functionCalls[2]['name']);
        $this->assertEquals('1,3', $functionCalls[2]['arguments']);
    }

    public function testDetectFunctionCalls_detectsIncludecodefrom(): void {
        $code = 'includecodefrom(1021)';

        $questionCodeParserService = new QuestionCodeParserService($code);
        $functionCalls = $questionCodeParserService->detectFunctionCalls();

        $this->assertNotEmpty($functionCalls);
        $this->assertCount(1, $functionCalls);

        $this->assertEquals('includecodefrom', $functionCalls[0]['name']);
        $this->assertEquals('1021', $functionCalls[0]['arguments']);
    }

    public function testIsAlgorithmic_withRandomNumberFunction_returnsTrue(): void {
        $code = '
            $a = rand(1, 10)
            $b = $a + 5
            $answer = $a + $b
        ';

        $questionCodeParserService = new QuestionCodeParserService($code);
        $isAlgorithmic = $questionCodeParserService->isAlgorithmic();

        $this->assertTrue($isAlgorithmic);
    }

    public function testIsAlgorithmic_withRandomSelectFunction_returnsTrue(): void {
        $code = '
            $options = array(1, 2, 3, 4, 5)
            $a = randfrom($options)
            $answer = $a * 2
        ';

        $questionCodeParserService = new QuestionCodeParserService($code);
        $isAlgorithmic = $questionCodeParserService->isAlgorithmic();

        $this->assertTrue($isAlgorithmic);
    }

    public function testIsAlgorithmic_withShuffleFunction_returnsTrue(): void {
        $code = '
            $options = array(1, 2, 3, 4, 5)
            $shuffled = singleshuffle($options)
            $answer = $shuffled[0]
        ';

        $questionCodeParserService = new QuestionCodeParserService($code);
        $isAlgorithmic = $questionCodeParserService->isAlgorithmic();

        $this->assertTrue($isAlgorithmic);
    }

    public function testIsAlgorithmic_withRandomStringFunction_returnsTrue(): void {
        $code = '
            $name = randname()
            $answer = "Hello, " . $name
        ';

        $questionCodeParserService = new QuestionCodeParserService($code);
        $isAlgorithmic = $questionCodeParserService->isAlgorithmic();

        $this->assertTrue($isAlgorithmic);
    }

    public function testIsAlgorithmic_withNoRandomFunctions_returnsFalse(): void {
        $code = '
            $a = 5
            $b = 10
            $answer = $a + $b
        ';

        $questionCodeParserService = new QuestionCodeParserService($code);
        $isAlgorithmic = $questionCodeParserService->isAlgorithmic();

        $this->assertFalse($isAlgorithmic);
    }

    public function testIsAlgorithmic_withNestedRandomFunction_returnsTrue(): void {
        $code = 'array(rand(1, 5), 2)';

        $questionCodeParserService = new QuestionCodeParserService($code);
        $isAlgorithmic = $questionCodeParserService->isAlgorithmic();

        $this->assertTrue($isAlgorithmic);
    }
}

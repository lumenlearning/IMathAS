<?php
/**
 * @OA\Info(title="API", version="1.0")
 */

namespace App\Http\Controllers;

use AssessRecord;
use AssessStandalone;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

use App\Repositories\Interfaces\AssessmentRepositoryInterface;
use App\Repositories\Interfaces\QuestionSetRepositoryInterface;

use PDO;
use Rand;

class QuestionController extends ApiBaseController
{
    /**
     * @var AssessmentRepositoryInterface
     */
    private $assessmentRepository;
    /**
     * @var QuestionSetRepositoryInterface
     */
    private $questionSetRepository;

    /**
     * @var PDO DBH
     */
    private $DBH;

    /**
     * Controller constructor.
     * @param AssessmentRepositoryInterface $assessmentRepository
     * @param QuestionSetRepositoryInterface $questionSetRepository
     */
    public function __construct(AssessmentRepositoryInterface $assessmentRepository,
                                QuestionSetRepositoryInterface $questionSetRepository)
    {
        parent::__construct();
        $this->assessmentRepository = $assessmentRepository;
        $this->questionSetRepository = $questionSetRepository;

        $this->loadGlobals();

        $dsn = 'mysql:host=' . env('DB_HOST') . ':' . env('DB_PORT') . ';dbname=' . env('DB_DATABASE');
        $this->DBH = new PDO($dsn, env('DB_USERNAME'), env('DB_PASSWORD'));
    }

    /**
     * @OA\Post(
     *     path="/question/{questionId}",
     *     @OA\Parameter(
     *         name="questionId",
     *         in="path",
     *         description="The questionId parameter in path",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Returns some sample category things",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Error: Bad request. When required parameters were not supplied.",
     *     ),
     * )
     */
    public function GetQuestion($questionId, Request $request): JsonResponse
    {
        try {
            $this->validate($request,[]);
            $inputState = $request->all();

            $questionSet = $this->questionSetRepository->getById($inputState['qsid'][$questionId]);
            if (!$questionSet) return $this->BadRequest(['Unable to locate question set']);

            $assessStandalone = new AssessStandalone($this->DBH);
            $assessStandalone->setQuestionData($questionSet['id'], $questionSet);
            $assessStandalone->setState($inputState);

            $overrides = [];

            $question = $assessStandalone->displayQuestion($questionId, $overrides);

            // force submitall
            if ($inputState['submitall']) {
                $question['jsparams']['submitall'] = 1;
            }

            return response()->json($question);
        } catch (exception $e) {
            Log::error($e);
            return $this->BadRequest([$e->getMessage()]);
        }
    }

    public function ScoreQuestion($questionId, Request $request): JsonResponse
    {
        try {
            $this->validate($request,[]);
            $inputState = $request->all();

            // Change input so that this is not an array. i.e. questionSetId: 12345
            $questionSetId = $inputState['qsid'][0];
            $questionSet = $this->questionSetRepository->getById($questionSetId);
            if (!$questionSet) return $this->BadRequest(['Unable to locate question set']);

            $assessStandalone = new AssessStandalone($this->DBH);
            $assessStandalone->setQuestionData($questionSetId, $questionSet);
            $assessStandalone->setState($inputState);

            $overrides = [];

            // Set the answer as provided by the student.
//            $_POST['qn0'] = ["0","2|1|6|7|0|4|5|3"];
//            $_POST['qn0-0'] = 2;// ["0","0|2|2|3|7|5|5|7"];
//            $_POST['qn0-1'] = 1;// ["0|2|2|3|7|5|5|7"];
//            $_POST['qn0-2'] = 6;// ["0|2|2|3|7|5|5|7"];
//            $_POST['qn0-3'] = 7;// ["0|2|2|3|7|5|5|7"];
//            $_POST['qn0-4'] = 0;// ["0|2|2|3|7|5|5|7"];
//            $_POST['qn0-5'] = 4;// ["0|2|2|3|7|5|5|7"];
//            $_POST['qn0-6'] = 5;// ["0|2|2|3|7|5|5|7"];
//            $_POST['qn0-7'] = 3;// ["0|2|2|3|7|5|5|7"];

            $_POST['qn0'] = ["0","0|2|2|3|7|5|5|7"];
            $_POST['qn0-0'] = 0;// ["0","0|2|2|3|7|5|5|7"];
            $_POST['qn0-1'] = 2;// ["0|2|2|3|7|5|5|7"];
            $_POST['qn0-2'] = 2;// ["0|2|2|3|7|5|5|7"];
            $_POST['qn0-3'] = 3;// ["0|2|2|3|7|5|5|7"];
            $_POST['qn0-4'] = 7;// ["0|2|2|3|7|5|5|7"];
            $_POST['qn0-5'] = 5;// ["0|2|2|3|7|5|5|7"];
            $_POST['qn0-6'] = 5;// ["0|2|2|3|7|5|5|7"];
            $_POST['qn0-7'] = 7;// ["0|2|2|3|7|5|5|7"];

            // qn = question number as displayed to the user
            $question = $assessStandalone->scoreQuestion($questionId, ['1' => true]);

            //showscoredonsubmit
            //$a2->getState()

            return response()->json($question);
        } catch (exception $e) {
            Log::error($e);
            return $this->BadRequest([$e->getMessage()]);
        }
    }

    private function loadGlobals() {

        // Lists compiled from existing assess flow
        $allowedmacros = ["sin","cos","tan","sinh","cosh","tanh","arcsin","arccos","arctan","arcsinh","arccosh",
            "arctanh","sqrt","ceil","floor","round","log","ln","abs","max","min","count", "getprime","getprimes",
            "loadlibrary","importcodefrom","includecodefrom","array","off","true","false","e","pi","null","setseed",
            "if","for","where", "exp","sec","csc","cot","sech","csch","coth","nthlog",
            "sinn","cosn","tann","secn","cscn","cotn","rand","rrand","rands","rrands",
            "randfrom","randsfrom","jointrandfrom","diffrandsfrom","nonzerorand",
            "nonzerorrand","nonzerorands","nonzerorrands","diffrands","diffrrands",
            "nonzerodiffrands","nonzerodiffrrands","singleshuffle","jointshuffle",
            "makepretty","makeprettydisp","showplot","addlabel","showarrays","horizshowarrays",
            "showasciisvg","listtoarray","arraytolist","calclisttoarray","sortarray","consecutive",
            "gcd","lcm","calconarray","mergearrays","sumarray","dispreducedfraction","diffarrays",
            "intersectarrays","joinarray","unionarrays","count","polymakepretty",
            "polymakeprettydisp","makexpretty","makexprettydisp","calconarrayif","in_array",
            "prettyint","prettyreal","prettysigfig","roundsigfig","arraystodots","subarray",
            "showdataarray","arraystodoteqns","array_flip","arrayfindindex","fillarray",
            "array_reverse","root","getsnapwidthheight","is_numeric","sign","sgn","prettynegs",
            "dechex","hexdec","print_r","replacealttext","randpythag","changeimagesize","mod",
            "numtowords","randname","randnamewpronouns","randmalename","randfemalename",
            "randnames","randmalenames","randfemalenames","randcity","randcities","prettytime",
            "definefunc","evalfunc","evalnumstr","safepow","arrayfindindices","stringtoarray","strtoupper",
            "strtolower","ucfirst","makereducedfraction","makereducedmixednumber","stringappend",
            "stringprepend","textonimage","addplotborder","addlabelabs","makescinot","today",
            "numtoroman","sprintf","arrayhasduplicates","addfractionaxislabels","decimaltofraction",
            "ifthen","multicalconarray","htmlentities","formhoverover","formpopup","connectthedots",
            "jointsort","stringpos","stringlen","stringclean","substr","substr_count","str_replace",
            "makexxpretty","makexxprettydisp","forminlinebutton","makenumberrequiretimes",
            "comparenumbers","comparefunctions","getnumbervalue","showrecttable","htmldisp",
            "getstuans","checkreqtimes","stringtopolyterms","getfeedbackbasic","getfeedbacktxt",
            "getfeedbacktxtessay","getfeedbacktxtnumber","getfeedbacktxtnumfunc",
            "getfeedbacktxtcalculated","explode","gettwopointlinedata","getdotsdata",
            "getopendotsdata","gettwopointdata","getlinesdata","getineqdata","adddrawcommand",
            "mergeplots","array_unique","ABarray","scoremultiorder","scorestring","randstate",
            "randstates","prettysmallnumber","makeprettynegative","rawurlencode","fractowords",
            "randcountry","randcountries","sorttwopointdata"];
        $GLOBALS['allowedmacros'] = $allowedmacros;

        $disallowedvar = ['$link','$qidx','$qnidx','$seed','$qdata','$toevalqtxt','$la',
            '$laarr','$shanspt','$GLOBALS','$laparts','$anstype','$kidx','$iidx','$tips',
            '$optionsPack','$partla','$partnum','$score','$disallowedvar','$allowedmacros',
            '$wherecount','$forloopcnt','$countcnt','$myrights','$myspecialrights',
            '$this', '$quesData', '$toevalsoln', '$doShowAnswer', '$doShowAnswerParts'];
        $GLOBALS['disallowedvar'] = $disallowedvar;

        $RND = new Rand();
        $GLOBALS['RND'] = $RND;

        $GLOBALS['myrights'] = 100;
    }
}

<?php

namespace IMathAS\assess2\questions\scorepart;

require_once __DIR__ . '/ScorePart.php';
require_once __DIR__ . '/../models/ScorePartResult.php';

use IMathAS\assess2\questions\models\ScorePartResult;
use IMathAS\assess2\questions\models\ScoreQuestionParams;

class NumberScorePart implements ScorePart
{
    private $scoreQuestionParams;

    public function __construct(ScoreQuestionParams $scoreQuestionParams)
    {
        $this->scoreQuestionParams = $scoreQuestionParams;
    }

    public function getResult(): ScorePartResult
    {
        global $mathfuncs;

        $scorePartResult = new ScorePartResult();

        $RND = $this->scoreQuestionParams->getRandWrapper();
        $options = $this->scoreQuestionParams->getVarsForScorePart();
        $qn = $this->scoreQuestionParams->getQuestionNumber();
        $givenans = $this->scoreQuestionParams->getGivenAnswer();
        $multi = $this->scoreQuestionParams->getIsMultiPartQuestion();
        $partnum = $this->scoreQuestionParams->getQuestionPartNumber();

        $defaultreltol = .0015;

        $optionkeys = ['answer', 'reltolerance', 'abstolerance', 'reqdecimals',
            'reqsigfigs', 'answerformat', 'requiretimeslistpart', 
            'ansprompt'];
        foreach ($optionkeys as $optionkey) {
            ${$optionkey} = getOptionVal($options, $optionkey, $multi, $partnum);
        }
        $optionkeys = ['partialcredit'];
        foreach ($optionkeys as $optionkey) {
            ${$optionkey} = getOptionVal($options, $optionkey, $multi, $partnum, 2);
        }
        $optionkeys = ['requiretimes'];
        foreach ($optionkeys as $optionkey) {
            ${$optionkey} = getOptionVal($options, $optionkey, $multi, $partnum, 1);
        }

        if ($reltolerance === '' && $abstolerance === '') { $reltolerance = $defaultreltol;}

        $ansformats = array_map('trim',explode(',',$answerformat));
        if ($multi) { $qn = ($qn+1)*1000+$partnum; }
        
        $hasUnits = in_array('units',$ansformats);
        if ($hasUnits) {
            require_once __DIR__.'/../../../assessment/libs/units.php';
        }
        
        $givenans = normalizemathunicode($givenans);

        if (in_array('nosoln',$ansformats) || in_array('nosolninf',$ansformats)) {
            list($givenans, $answer) = scorenosolninf($qn, $givenans, $answer, $ansprompt ?? '');
        }

        $givenans = trim($givenans," ,");
        $scorePartResult->setLastAnswerAsGiven($givenans);

        if ($answer==='' && $givenans==='') {
            $scorePartResult->setRawScore(1);
            return $scorePartResult;
        }

        $isListAnswer = (in_array('exactlist',$ansformats) || in_array('orderedlist',$ansformats) || in_array('list',$ansformats));
        if ($isListAnswer && !empty($requiretimes) && checkreqtimes($givenans,$requiretimes)==0) {
            $scorePartResult->setRawScore(0);
            return $scorePartResult;
        }

        if (in_array('integer',$ansformats) && preg_match('/\..*[1-9]/',$givenans)) {
            $scorePartResult->setRawScore(0);
            return $scorePartResult;
        }

        $exactreqdec = false;
        if ($reqdecimals !== '') {
            list($reqdecimals, $exactreqdec, $reqdecoffset, $reqdecscoretype) = parsereqsigfigs($reqdecimals);
            if ($exactreqdec || count($reqdecscoretype)==2) { // exact or not default
                if ($reqdecscoretype[0] == 'rel') {
                    $reltolerance = $reqdecscoretype[1];
                } else if ($reqdecscoretype[0] == 'abs') {
                    $abstolerance = $reqdecscoretype[1];
                }
            }
        }

        if ($reqsigfigs !== '') {
            list($reqsigfigs, $exactsigfig, $reqsigfigoffset, $sigfigscoretype) = parsereqsigfigs($reqsigfigs);
        }

        if ($answer==='') {
            if (trim($givenans)==='') {
                $scorePartResult->setRawScore(1);
                return $scorePartResult;
            } else {
                $scorePartResult->setRawScore(0);
                return $scorePartResult;
            }
        }
        if ($answer==='0 or ') {
            if (trim($givenans)==='' || trim($givenans)==='0') {
                $scorePartResult->setRawScore(1);
                return $scorePartResult;
            } else {
                $scorePartResult->setRawScore(0);
                return $scorePartResult;
            }
        }
        if ($givenans == null) {
            $scorePartResult->setRawScore(0);
            return $scorePartResult;
        }
        if (in_array('set',$ansformats) || in_array('exactset',$ansformats)) {
            $givenans = trim($givenans);
            if ($givenans[0]!='{' || substr($givenans,-1)!='}') {
                $scorePartResult->setRawScore(0);
                return $scorePartResult;
            }
            $answer = str_replace(array('{','}'),'', $answer);
            $givenans = str_replace(array('{','}'),'', $givenans);
            $answerformat = str_replace('set','list',$answerformat);
            $ansformats = array_map('trim',explode(',',$answerformat));
        }
        if (in_array('exactlist',$ansformats)) {
            $gaarr = array_map('trim', explode(',',$givenans));
            $gaarrcnt = count($gaarr);
            $anarr = explode(',',$answer);
        } else if (in_array('orderedlist',$ansformats)) {
            $gaarr = array_map('trim', explode(',',$givenans));
            $anarr = explode(',',$answer);
        } else if (in_array('list',$ansformats)) {
            $tmp = array();
            $gaarr = array();
            foreach (array_map('trim', explode(',',$givenans)) as $v) {
                if (is_numeric($v)) {
                    $tmp[] = $v;
                } else {
                    $gaarr[] = $v;
                }
            }
            //$tmp = array_map('trim', explode(',',$givenans));
            if (count($tmp)>0) {
                sort($tmp);
                $gaarr[] = $tmp[0];
                for ($i=1;$i<count($tmp);$i++) {
                    if ($tmp[$i]-$tmp[$i-1]>1E-12) {
                        $gaarr[] = $tmp[$i];
                    }
                }
            }
            $gaarrcnt = count($gaarr);
            $tmp = array_map('trim', explode(',',$answer));
            sort($tmp);
            $anarr = array($tmp[0]);
            for ($i=1;$i<count($tmp);$i++) {
                if (!is_numeric($tmp[$i]) || !is_numeric($tmp[$i-1]) || 
                    $tmp[$i]-$tmp[$i-1]>1E-12
                ) {
                    $anarr[] = $tmp[$i];
                }
            }
        } else {
            $origgivenans = array($givenans);
            if (empty($GLOBALS['CFG']['nocommathousandsseparator'])) {
                $givenans = preg_replace('/(\d)\s*,\s*(?=\d{3}\b)/','$1',$givenans);
            }
            $givenans = str_replace(',','99999999',$givenans); //force wrong ans on lingering commas
            $gaarr = array($givenans);

            if (strpos($answer,'[')===false && strpos($answer,'(')===false) {
                $anarr = array(str_replace(',','',$answer));
            } else {
                $anarr = array($answer);
            }
            $isListAnswer = false;
            $gaarrcnt = 1;
        }

        if (in_array('orderedlist',$ansformats)) {
            if (count($gaarr)!=count($anarr)) {
                $scorePartResult->setRawScore(0);
                return $scorePartResult;
            }
        }

        if (in_array('parenneg',$ansformats)) {
            foreach ($gaarr as $k=>$v) {
                if ($v[0]=='(') {
                    $gaarr[$k] = -1*substr($v,1,-1);
                }
            }
        }

        $gaunitsarr = [];
        foreach ($gaarr as $k=>$v) {
            if ($hasUnits) {
                if (strtoupper($v)=='DNE') {
                    $v = 'DNE';
                } else {
                    $givenansUnits = parseunits($v);
                    if (is_array($givenansUnits)) {
                        $v = $givenansUnits[0];
                        $gaunitsarr[$k] = $givenansUnits;
                    } else { // handle invalid
                        $gaunitsarr[$k] = [0,'invalid',0,0];
                    }
                }
            }

            $gaarr[$k] = trim(str_replace(array('$',',',' '),'',$v));
            if (strtoupper($gaarr[$k])=='DNE') {
                $gaarr[$k] = 'DNE';
            } else if ($gaarr[$k]=='oo' || $gaarr[$k]=='-oo' || $gaarr[$k]=='-oo') {
                //leave alone
            } else if (preg_match('/\d\s*(x|y|z|r|t|i|X|Y|Z|I|pi)([^a-zA-Z]|$)/', $gaarr[$k])) {
                //has a variable - don't strip
            } else {
                //strip out units. Must start with a letter
                $gaarr[$k] = preg_replace('/^((-|\+)?(\d+\.?\d*|\.\d+)([Ee]([+\-]\d+|\d+)))?\s*[a-zA-Z][^+\-]*$/','$1',$gaarr[$k]);
            }
        }
        if (in_array('orderedlist',$ansformats)) {
            //define $gamasterarr with processed $gaarr
            $gamasterarr = $gaarr;
            if ($hasUnits) {
                $gamasterunitsarr = $gaunitsarr;
            }
        }

        $extrapennum = count($gaarr)+count($anarr);

        $correct = 0;
        foreach($anarr as $i=>$answer) {
            $foundloc = -1;
            if (in_array('orderedlist',$ansformats)) {
                $gaarr = array($gamasterarr[$i]);  
                if ($hasUnits) {
                    $gaunitsarr = array($gamasterunitsarr[$i]);
                }
            }
            $anss = explode(' or ',$answer);
            $anssunits = [];
            $partialpts = array_fill(0, count($anss), 1);
            $origanscnt = count($anss);
            if (!empty($partialcredit) && !$isListAnswer) { // partial credit only works for non-list answers
                if (!is_array($partialcredit)) {$partialcredit = explode(',',$partialcredit);}
                $removeReqTimesOnDup = (is_array($requiretimes) && 2*count($requiretimes) > count($partialcredit));
                for ($i=0;$i<count($partialcredit);$i+=2) {
                    if (!in_array($partialcredit[$i], $anss) || $partialcredit[$i+1]<1) {
                        $anss[] = $partialcredit[$i];
                        $partialpts[] = $partialcredit[$i+1];
                    } else {
                        // ignoring element; need to remove corresponding from requiretimes
                        if ($removeReqTimesOnDup) {
                            unset($requiretimes[1+$i/2]);
                        }
                    }
                }
                if (is_array($requiretimes)) {
                    $requiretimes = array_values($requiretimes);
                }
            }
            foreach ($anss as $k=>$anans) {
                if ($anans === 'DNE') { continue; }
                if ($hasUnits) {
                    if (strtoupper($anans)=='DNE') {
                        $anans = 'DNE';
                    } else {
                        $anssUnits = parseunits($anans);
                        if (is_array($anssUnits)) {
                            $anss[$k] = $anssUnits[0];
                            $anssunits[$k] = $anssUnits;
                        } else {
                            //echo "Invalid answer $anans";
                            $anssunits[$j] = [0,'invalidans',0,0];
                        }
                    }
                }
            }
            foreach($gaarr as $j=>$givenans) {
                if (!empty($requiretimeslistpart) && checkreqtimes($givenans,$requiretimeslistpart)==0) {
                    continue;
                }
                foreach ($anss as $k=>$anans) {
                    if (is_array($requiretimes)) {
                        if ($k<$origanscnt) {
                            $thisreqtimes = $requiretimes[0] ?? '';
                        } else {
                            $thisreqtimes = $requiretimes[$k-$origanscnt+1] ?? '';
                        }
                    } else {
                        $thisreqtimes = $requiretimes;
                    }
                    if ($k < $origanscnt) {
                        $thisreqdecimals = $reqdecimals;
                        $thisreqsigfigs = $reqsigfigs;
                    } else {
                        // backwards compatability: don't apply reqdecimals/reqsigfigs on partial credit answers
                        // for now at least; eventually add support for array reqdecimals/reqsigfigs
                        $thisreqdecimals = '';
                        $thisreqsigfigs = '';
                    }
                    if (!$isListAnswer && !empty($thisreqtimes) && checkreqtimes($origgivenans[$j], $thisreqtimes)==0) {
                        // doesn't meet requiretimes
                        continue;
                    }
                    if (!is_numeric($anans)) {
                        if (preg_match('/(\(|\[)\s*(-?[\d\.]+|-?[\d\.]+[Ee]?[+\-]?\d+|-oo)\s*\,\s*(-?[\d\.]+|-?[\d\.]+[Ee]?[+\-]?\d+|oo)\s*(\)|\])/',$anans,$matches) && is_numeric($givenans)) {
                            //check reqdecimals/sigfigs
                            if ($thisreqdecimals !== '') {
                                $decimalsingivenans = ($p = strpos($givenans,'.'))===false ? 0 : (strlen($givenans)-$p-1);
                                if ($exactreqdec == 1) {
                                    if ($thisreqdecimals != $decimalsingivenans ) {
                                        continue;
                                    }
                                } else {
                                    /*  Don't bother to check in this case, since 
                                        0.1 could be considered as 0.100, so if not exact,
                                        no reason to bother checking
                                    if ($thisreqdecimals > $decimalsingivenans ) {
                                        continue;
                                    }
                                    */
                                }
                            }
                            if ($thisreqsigfigs !== '') {
                                // only check sigfigs, not value
                                if (!checksigfigs($givenans, 1, $thisreqsigfigs, $exactsigfig, $reqsigfigoffset, false)) {
                                    continue;
                                } 
                            }
                            if ($matches[2]=='-oo') {$matches[2] = -1e99;}
                            if ($matches[3]=='oo') {$matches[3] = 1e99;}
                            if (($matches[1]=="(" && $givenans>$matches[2]) || ($matches[1]=="[" && $givenans>=$matches[2])) {
                                if (($matches[4]==")" && $givenans<$matches[3]) || ($matches[4]=="]" && $givenans<=$matches[3])) {
                                    $correct += $partialpts[$k];
                                    $foundloc = $j;
                                    break 2;
                                }
                            }
                        } else	if ($anans=="DNE" && $givenans=="DNE") {
                            $correct += $partialpts[$k]; $foundloc = $j; break 2;
                        } else if (($anans=="+oo" || $anans=="oo") && ($givenans=="+oo" || $givenans=="oo")) {
                            $correct += $partialpts[$k]; $foundloc = $j; break 2;
                        } else if ($anans=="-oo" && $givenans=="-oo") {
                            $correct += $partialpts[$k]; $foundloc = $j; break 2;
                        } else if (strtoupper($anans)==strtoupper($givenans)) {
                            $correct += $partialpts[$k]; $foundloc = $j; break 2;
                        }
                    } else {//{if (is_numeric($givenans)) {
                        //$givenans = preg_replace('/[^\-\d\.eE]/','',$givenans); //strip out units, dollar signs, whatever
                        if (is_numeric($givenans)) {
                            if ($hasUnits) {
                                // check units type
                                if ($gaunitsarr[$j][1] != $anssunits[$k][1]) {
                                    continue;
                                }
                                if ($thisreqdecimals !== '' && $exactreqdec == 1) {
                                    //check number of decimal places in base givenans
                                    if ($thisreqdecimals != (($p = strpos($gaunitsarr[$j][2],'.'))===false?0:(strlen($gaunitsarr[$j][2])-$p-1))) {
                                        continue;
                                    }
                                } 
                                if ($thisreqsigfigs !== '') {
                                    if (checkunitssigfigs($gaunitsarr[$j], $anssunits[$k], $thisreqsigfigs, $exactsigfig, $reqsigfigoffset, $sigfigscoretype)) {
                                        $correct += $partialpts[$k]; $foundloc = $j; break 2;
                                    } else {
                                        continue;
                                    }
                                } else if ($abstolerance !== '') {
                                    $adjabstolerance = $abstolerance*$anssunits[$k][3];
                                    if (abs($anans-$givenans) < $adjabstolerance + (($anans==0||abs($anans)>1)?1E-12:(abs($anans)*1E-12))) {$correct += $partialpts[$k]; $foundloc = $j; break 2;}
                                } else {
                                    if ($anans==0) {
                                        if (abs($anans - $givenans) < $reltolerance/1000 + 1E-12) {$correct += $partialpts[$k]; $foundloc = $j; break 2;}
                                    } else {
                                        if (abs($anans - $givenans)/(abs($anans)+(abs($anans)>1?1E-12:(abs($anans)*1E-12))) < $reltolerance+ 1E-12) {$correct += $partialpts[$k]; $foundloc = $j; break 2;}
                                    }
                                }
                            } else { 
                                if ($thisreqdecimals !== '' && $exactreqdec) {
                                    //check number of decimal places in givenans
                                    if ($exactreqdec == 1 && $thisreqdecimals != (($p = strpos($givenans,'.'))===false?0:(strlen($givenans)-$p-1))) {
                                        continue;
                                    }
                                    $anans = round($anans, $thisreqdecimals);
                                }
                                if ($thisreqsigfigs !== '') {
                                    if (checksigfigs($givenans, $anans, $thisreqsigfigs, $exactsigfig, $reqsigfigoffset, $sigfigscoretype)) {
                                        $correct += $partialpts[$k]; $foundloc = $j; break 2;
                                    } else {
                                        continue;
                                    }
                                } else if ($abstolerance !== '') {
                                    if (abs($anans-$givenans) < $abstolerance + (($anans==0||abs($anans)>1)?1E-12:(abs($anans)*1E-12))) {$correct += $partialpts[$k]; $foundloc = $j; break 2;}
                                } else {
                                    if ($anans==0) {
                                        if (abs($anans - $givenans) < $reltolerance/1000 + 1E-12) {$correct += $partialpts[$k]; $foundloc = $j; break 2;}
                                    } else {
                                        if (abs($anans - $givenans)/(abs($anans)+(abs($anans)>1?1E-12:(abs($anans)*1E-12))) < $reltolerance+ 1E-12) {$correct += $partialpts[$k]; $foundloc = $j; break 2;}
                                    }
                                }
                            }
                        }
                    }
                }
            }
            if ($foundloc>-1) {
                array_splice($gaarr,$foundloc,1); //remove from list
                if (count($gaarr)==0 && !in_array('orderedlist',$ansformats)) {
                    break; //stop if no student answers left
                }
            }
        }
        if (!in_array('orderedlist',$ansformats)) {
            if ($gaarrcnt<=count($anarr)) {
                $score = $correct/count($anarr);
            } else {
                $score = $correct/count($anarr) - ($gaarrcnt-count($anarr))/$extrapennum;  //take off points for extranous stu answers
            }
        } else {
            $score = $correct/count($anarr);
        }

        if ($score<0) { $score = 0; }
        
        $scorePartResult->setRawScore($score);
        return $scorePartResult;
    }
}

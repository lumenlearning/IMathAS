<?php
    class Ohm2MacroBackport {
        // regex pattern for matching and determining whether a given code string contains an Ohm2 macro
        const OHM2_MACRO_REGEX = '/ohm_getfeedback(\w+)\(([^)]*)\)/';

        // maximum number of arguments allowed for each macro
        const MACRO_ARG_COUNTS = [
            'basic' => 5,
            'txt' => 3,
            'txtessay' => 2,
            'txtnumber' => 5,
            'txtcalculated' => 8,
            'txtnumfunc' => 8,
        ];

        /* 
        * This function converts Ohm2 macros into their Ohm1 counterparts
        * https://lumenlearning.atlassian.net/wiki/spaces/OHM/pages/3392733192/OHM+macros+-+Mapping+OHM2+to+OHM1+macro+functions
        */
        public static function backportFeedbackControl(string $control): string {
            // this regex puts the macro name and its arguments into match groups
            $result = preg_replace_callback(Ohm2MacroBackport::OHM2_MACRO_REGEX, function ($matches) {
                // pull the matched groups out of the regex into a macro name and argument list
                $macroName = $matches[1];

                $macroArgs = Ohm2MacroBackport::splitArguments($matches[2]);
        
                // return the original string with no changes if this is not a macro we know about
                if (!isset(Ohm2MacroBackport::MACRO_ARG_COUNTS[$macroName])) {
                    return $matches[0];
                }
        
                // strip any additonal arguments beyondn the maximum allowed
                $argCount = Ohm2MacroBackport::MACRO_ARG_COUNTS[$macroName];
                $macroArgs = array_slice($macroArgs, 0, $argCount);

                // getfeedbackbasic does some extra manipulation to arguments
                if ($macroName == 'basic') {
                    // the first argument is deleted entirely
                    array_splice($macroArgs, 0, 1);

                    // clean up whitespace after shifting arguments
                    $macroArgs[0] = preg_replace('/^ /', '', $macroArgs[0], 1);
                    
                    // the now 3rd argument is replaced with "$thisq", preserving whitespace in the argument
                    $macroArgs[2] = preg_replace('/\S+/', "\$thisq", $macroArgs[2]);
                }

                // create the output string by prepending getfeedback with the macro name and reassembling the remaining macro arguments
                $output = 'getfeedback' . $macroName . '(' . implode(',', $macroArgs) . ')';
        
                return $output;
            }, $control);
        
            return $result;
        }

        /* 
         * This adds $feedback to the question text after every $answerbox
         */
        public static function backportFeedbackQuestionText(string $questionText): string {
            // Split the input string into an array of lines
            $lines = explode("\n", $questionText);
            // Create a new array for the output to avoid modifying the input array while iterating
            $outputLines = [];
        
            // Process each line one at a time to avoid issues with multi-line regexes
            foreach ($lines as $index => $line) {
                // Add the current line to the output
                $outputLines[] = $line;

                // if the line contains $answerbox add the corresponding $feedback on the following line (either plain or with an index)
                if (preg_match('/\$(answerbox)(?:\[(.*?)\])?/', $line, $matches)) {
                    $outputLines[] = (isset($matches[2]) ? "<p>\$feedback[" . $matches[2] . "]</p>" : "<p>\$feedback</p>");
                }
            }
        
            // Reconstruct the string with the updated lines
            return implode("\n", $outputLines);
        }
        
        /* 
         * This function splits a comma-separate string into an array, unless the comma is enclosed in double or single quotes
         */
        private static function splitArguments(string $argsString): array {
            // https://stackoverflow.com/questions/41449352/php-split-comma-separated-values-but-retain-quotes
            $matches = preg_split('/((?:"[^"]*"|)|(?:\'[^\']*\'|))\K(,|$)/', $argsString);
            $result = array_filter($matches, function ($value) {
                return ($value !== '');
            });
            return $result; // Extract matched arguments
        }
    
        /* 
         * This checks to see if the input string matches the regex for Ohm2 macros
         */
        public static function containsOhm2Macro(string $control): bool {
            return preg_match_all(Ohm2MacroBackport::OHM2_MACRO_REGEX, $control);
        }
    }
?>
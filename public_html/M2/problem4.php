<?php

require_once "base.php";

$ucid = "rt524"; // <-- set your ucid

// Don't edit the arrays below, they are used to test your code
$array1 = ["hello world!", "php programming", "special@#$%^&characters", "numbers 123 456", "mIxEd CaSe InPut!"];
$array2 = ["hello world", "php programming", "this is a title case test", "capitalize every word", "mixEd CASE input"];
$array3 = ["  hello   world  ", "php    programming  ", "  extra    spaces  between   words   ",
    "      leading and trailing spaces      ", "multiple      spaces"];
$array4 = ["hello world", "php programming", "short", "a", "even"];


function transformText($arr, $arrayNumber) {
    // Only make edits between the designated "Start" and "End" comments
    printArrayInfoBasic($arr, $arrayNumber);

    // Challenge 1: Remove non-alphanumeric characters except spaces
    // Challenge 2: Convert text to Title Case
    // Challenge 3: Trim leading/trailing spaces and remove duplicate spaces
    // Result 1-3: Assign final phrase to `$placeholderForModifiedPhrase`
    // Challenge 4 (extra credit): Extract up to the middle 3 characters (middle index and +/- 1 if it's not the first/last character),
    // Do not include the first or last character of the phrase/word. (e.g., oven should show as ve)
    // assign the result to `$placeholderForMiddleCharacters`
    // If the phrase is shorter than 3 characters, return "Not enough characters"

    // Step 1: sketch out plan using comments (include ucid and date)
    // Step 2: Add/commit your outline of comments (required for full credit)
    // Step 3: Add code to solve the problem (add/commit as needed)
    $placeholderForModifiedPhrase = "";
    $placeholderForMiddleCharacters = "";

    $alphaNumOnly = [];
    foreach ($arr as $index => $text) {
        // Start Solution Edits
        // rt524 09-29-2025
        // Challenge 1: if char is not alphanumeric or a space, do not add to string. Otherwise append string.
        // Challenge 2: Capitalize first letter of first word and all first letters after encountering a space
        // Challenge 3: use a trim function for leading and trailing, then delete spaces if the next char is not a letter
        $placeholderForModifiedPhrase = "";
        // Challenge 1:
        for ($i = 0; $i < strlen($text); $i++) {
            if (!ctype_alnum($text[$i]) && $text[$i] != ' ') {
                continue;
            }
            else {
                $placeholderForModifiedPhrase = $placeholderForModifiedPhrase . $text[$i];
            }
        }
        // Challenge 3:
        $placeholderForModifiedPhrase = trim($placeholderForModifiedPhrase);
        $placeholderForModifiedPhrase = preg_replace('/ +/', ' ', $placeholderForModifiedPhrase);

        // Challenge 2:
        $myString = '';
        for ($i = 0; $i < strlen($placeholderForModifiedPhrase); $i++) {
            if ($i == 0) {
                $placeholderForModifiedPhrase[$i] = strtoupper($placeholderForModifiedPhrase[$i]);
                continue;
            }
            if ($placeholderForModifiedPhrase[$i] == ' ') {
                $placeholderForModifiedPhrase[$i+1] = strtoupper($placeholderForModifiedPhrase[$i+1]);
                $i += 1;
            } 
            else {
                $placeholderForModifiedPhrase[$i] = strtolower($placeholderForModifiedPhrase[$i]);
            }
        }

        // End Solution Edits
        echo "<div>";
        printStringTransformations($index, $placeholderForModifiedPhrase, $placeholderForMiddleCharacters);
        echo "</div>";
    }

    echo "<br>______________________________________<br>";
}

// Run the problem
printHeader($ucid, 4);
transformText($array1, 1);
transformText($array2, 2);
transformText($array3, 3);
transformText($array4, 4);
printFooter($ucid, 4);

?>
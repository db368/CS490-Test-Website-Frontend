<html>
<style>
.inline {
    display: inline;
}

.link-button {
    background: none;
    border: none;
    color: black;
    text-decoration: underline;
    cursor: pointer;
    font-size: 1em;
    font-family: serif;
}

.link-button:focus {
    outline: none;
}

.link-button:active {
    color: red;
}
good{
    text-ccolor:rgb(0,255,0);
}
bad{
    color:rgb(255,0,0);
    text-decoration:line-through;
}

</style>
<head>
    <title> Results </title>
    <link rel="stylesheet" href="../styles.css">

</head>
<body>

    <header> <h1> Results for Student <?php echo isset($_POST['sid']) ? $_POST['sid'] : "Test User?";?> </h1></header>
    <div>

    <?php

    //For the student file, most of this will be the same, but we will remove the comment section  for a non-interactive verison.
    $debug = 1; // Enables the debug boxes
    $testdata = 0; //Enables the use of live data

    $eid = ((isset($_POST['eid']))) ? $_POST['eid'] : 39;
    $sid = ((isset($_POST['sid']))) ? $_POST['sid'] : 39;

    $_POST['identifier'] = 's_results';

    if (!$testdata) {
        $target = "https://web.njit.edu/~jll25/CS490/switch.php";
        $ch= curl_init();
        curl_setopt($ch, CURLOPT_URL, "$target");
        curl_setopt($ch, CURLOPT_POST, 1); // Set it to post
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(array('identifier'=>'s_results', 'eid'=> $eid, 'sid' => $sid)));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $return_val=curl_exec($ch);
        $results = json_decode($return_val, true);
        curl_close($ch);

        //We need to get the comments as well
        $unique_qids = array();
        $ULTIMATE = array();
        //Should be a similar affair for this page
        foreach($results as $result){
            // Save incoming data
            $inc_qid = $result['Qid'];

            $inc_testcase = $result['TestCase'];        //Testcase
            $inc_solution = $result['Answer'];           //Solution to Testcase
            $inc_output = $result['TCSTUDENT_ANSWER']; //Output
            $inc_ag = $result['Auto_Grader'];          //Autograder Comment

            //It's not in the database, we must make it
            if (!in_array($inc_qid, $unique_qids)) {
                $inc_result = $result; //Clone this
                array_push($unique_qids, $inc_qid);
                //Save these as new arrays

                $inc_result['TestCase'] = array($inc_testcase);
                $inc_result['solution'] = array($inc_solution);
                $inc_result['output'] = array($inc_output);
                $inc_result['autograder'] = array($inc_ag);
                array_push($ULTIMATE, $inc_result); //Put it in ultimate
                continue;
            }
            //It's already in the database just push it
            else{
                for ($i=0; $i<sizeof($ULTIMATE); $i++){
                    if ($ULTIMATE[$i]['Qid'] == $inc_qid) {
                        //It's a match! Add it!
                        array_push($ULTIMATE[$i]['TestCase'], $inc_testcase);
                        array_push($ULTIMATE[$i]['solution'], $inc_solution);
                        array_push($ULTIMATE[$i]['output'], $inc_output);
                        array_push($ULTIMATE[$i]['autograder'], $inc_ag);
                    }
                }
            }
        }

        //Now we pretend nothing happened
        $results=$ULTIMATE;

        $commentarray = array(); // This will store all returned jsons from the comment seraches
        for($i=0; $i < sizeof($results); $i++){
            $res_qid = $results[$i]['Qid'];
            $target = "https://web.njit.edu/~jll25/CS490/switch.php";
            $postarray = array('identifier'=>'g_comment','qid'=> $res_qid,'sid' => $sid,'exid' => $eid);
            $ch= curl_init();
            curl_setopt($ch, CURLOPT_URL, "$target");//
            curl_setopt($ch, CURLOPT_POST, 1); // Set it to post
            curl_setopt(
                $ch, CURLOPT_POSTFIELDS,
                $postarray
            );
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            $return_val2= curl_exec($ch);
            curl_close($ch);

            $comment = json_decode($return_val2, true)[0];
            $results[$i]['comment'] = $comment['Comments'];
            $results[$i]['newgrade'] = $comment['Score'];
        }
        ?>
        <?php if ($debug) : ?>
            <h2> POST INPUT </h2>
            <div class='debug'>
                <?php echo ($_POST != null) ? print_r($_POST) : "No Post!"; ?>
            </div>
            <h2> JSON OUTPUT </h2>
            <h3> Getting Questions </h3>
            <div class='debug'>
                <?php echo ($return_val == null) ? "No Return Value!" : $return_val  ?>
            </div><br>
            <h3> GETTING COMMENTS </h3>
            <div class='debug'>
                <?php print_r($commentarray); ?>
            </div>
        <?php endif ?>

        <?php if ($return_val == null) : ?>
             <h2> ERROR: ANSWERS COULD NOT BE RETRIEVED </h2>
            <?php // $testdata = rand(10, 20); Sadly, we probably won't want test data on the live release.
        endif;
    }

    if ($testdata) { //Generate our own joke data
        $results = array();
        //Student simulator values
        $int = rand(0, 15); // a value for every type of letter grade A B C D E F
        $strictness = rand(0, 15); // a value for every type of letter grade A B C D E F
        for ($i=0; $i<$testdata; $i++){
            //Generate some nonsense
            $max = rand(5, 39);
            $score = rand(0, $max);
            $text=rand(100000, 99999999);
            $answer = rand(100000, 99999999);

            $tests = array();
            $outputs = array();
            $sols = array();
            $tcr = rand(1, 5);
            for ($k=0; $k<$tcr; $k++){
                array_push($tests, "( ". rand(100000, 99999999) ."," . rand(100000, 99999999) . "," . rand(100000, 99999999) .")");
                array_push($sols, rand(1, 10));
                array_push($outputs, rand(1, 10));

            }
            $comment = null;
            $newscore = null;
            //Student Simulator Code
            if (rand(0, 20) <= $int + rand(0, 20)) { //Roll to get the right answer
                for($j=0; $j<$tcr; $j++) {
                    $sol[$j] = $outputs[$j];
                }
                $score = $max;
            }
            // Modification check
            if (rand(0, 20) <= $strictness + rand(0, 20)) {
                //Check succeeded roll for positive or negative modification
                if (rand(0, 7) <= $int) { //Positive
                    if ($score != $max) {
                        $comment = "Auto grader must be bugged, have a few points";
                        $newgrade = $score + rand(1, $max - $score);
                    }
                    elseif ($score > 0) { //Negative
                        $comment = "This is incorrect, autograder missed it.";
                        $newgrade = $score - rand(3, $score);
                    }
                }
            }

            $result = array(
                'maxscore' => $max,
                'score' => $score,
                'Question' => $text,
                'Answer' => $answer,
                'testcase' => $tests,
                'solution' => $sols,
                'output' => $outputs,
                'qid' => rand(1, 100),
                'comment' => $comment,
                'newgrade' => $newgrade

            );
            array_push($results, $result);
        }
    }
    ?>

    <table>
        <tr> <th> Question </th> <th> Answer </th> <th> Testcase Results </th> <th> Score </th> </tr>
        <?php
        foreach($results as $question){
            $maxscore = ((isset($question['Total_points']))) ? $question['Total_points'] : "39";
            $qid = ((isset($question['Qid']))) ? $question['Qid'] : "??";
            $score = ((isset($question['score']))) ? $question['score'] : "39";
            $qtext = ((isset($question['Question']))) ? $question['Question'] : "How could this happen?!?!?";
            $answer = ((isset($question['Student_Answer']))) ? nl2br($question['Student_Answer']) : "print('there's a bug?')";

            $comment = ((isset($question['comment']))) ? $question['comment'] : "";
            $testcases = ((isset($question['TestCase']))) ? $question['TestCase'] : array("I didn't", "read this", "correctly");
            $solutions = ((isset($question['solution']))) ? $question['solution'] : array("This didn't", "happen like", "I expected");
            $output = ((isset($question['output']))) ? $question['output'] : array("Fix", "This", "Bug");
            $autograder = ((isset($question['autograder']))) ? $question['autograder'] : array("program", "dont", "work");

            $tcnum = sizeof($testcases);
            ?>
            <tr>
                <td>
                    <?php echo $qtext; ?> </td>
                <td>
                    <?php echo $answer; ?> </td>

                <td>
                    <table>
                        <tr>
                            <th class="small"> TESTCASE </th>
                            <th class="small"> RESULT </th>
                            <th class="small"> SOLUTION </th>
                            <th class="small"> AUTOGRADER </th>
                        </tr>
                        <?php for ($i=0; $i<$tcnum; $i++): ?>
                        <tr>
                            <td>
                                Testcase
                                <?php echo $i. " "; ?> :
                                <?php echo $testcases[$i]; ?>
                            </td>
                            <td>
                                <?php echo $output[$i]; ?>
                            </td>
                            <td>
                                <?php echo $solutions[$i]; ?>
                            </td>
                            <td>
                                <?php echo $autograder[$i]; ?>
                            </td>
                        </tr>
                        <?php endfor ?>
                    </table>
                    <td>
                        <h3> SCORE: <?php echo $newgrade; ?> / <?php echo $maxscore; ?> </h3><br>
                        <?php if ($comment != "None") : ?>
                            Instructor Comment: <p> <?php echo $comment; ?> </p> <br>
                        <?php endif ?>
                    </td>
            </tr><?php
        }
        ?>
    </table>
</body>
</html>

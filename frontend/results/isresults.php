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
</style>
<head>
    <title> Results </title>
    <link rel="stylesheet" href="../styles.css">

</head>
<body>
    <?php
    $debug = 1;
    echo "<div>";
    echo "<h1> RESULTS FOR ".$_POST['exname']."</h1>";
        //First, we get a list of exams
        $target = "https://web.njit.edu/~jll25/CS490/switch.php";
        $ch= curl_init();
        curl_setopt($ch, CURLOPT_URL, "$target");
        curl_setopt($ch, CURLOPT_POST, 1); // Set it to post
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(array('identifier'=>'results', 'eid'=> $_POST['eid'])));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $return_val=curl_exec($ch);
    $results = json_decode($return_val, true);

    if ($debug) {
        echo "<h2> POST INPUT </h2>";
        echo "<div class='debug'>";
        if ($_POST != null) {
            print_r($_POST);
        }
        else{ echo "No Post!";
        }
        echo "</div>";
        echo '<br>';
        echo "<h2> JSON OUTPUT </h2>";
        echo "<div class='debug'>";
        if ($return_val != null) {
            echo $return_val;
        }
        else{
            echo "No return value!";
        }
        echo "</div>";
        echo '<br>';
    }
    if ($return_val == null) {
        echo "<h2> ERROR: EXAM LIST COULD NOT BE RETRIEVED </h2>";
        exit;
    }

    echo "<table>";
    echo "<tr> <th> STUDENT </th> <th> AVERAGE </th> </tr>"; //Only need to do a single form I think
    foreach($results as $student){
        echo "<tr>";
        $exid = "error"; $exname = "error";
        if (isset($student['sid'])) { $sid = $exam['sid'];
        }
        if (isset($student['average'])) { $average = $exam['average'];
        }
        //echo '<form method="post" action="../debug.php">';
        echo '<form method="post" action="isresults.php">';
        echo '<input type="hidden" name="sid" value="'.$sid.'">';
        echo '<td> <button type="submit" class="link-button" name="eid" value="'.$exid.'"> '.$sid.' </button> </td>';
        echo "</form></tr>";
    }
    echo "</table>";

    ?>

</body>
</html>
<?php

if(!isset($_POST['identifier'])) { die('Error: No identifier');
}
$identifier = $_POST['identifier'];



switch ($_POST['identifier'])
{
//view test questions
case "v_testbank":
    $difficulty = $_POST['difficulty'];
    $exam = $_POST['examid'];
    $conn = mysqli_connect("sql1.njit.edu", "jll25", "EzzrnW0B0", "jll25");

    if ($conn->connect_error) {
        die("Connection failure" . $conn->connect_error);
    }

    //getting the results from Questions if the post word is difficulty
    $sql = "SELECT * from Questions where Difficulty ='$difficulty'";

    $Difficulty_result = $conn->query($sql);
    $json_array = array();
    if ($Difficulty_result->num_rows > 0) {
        // output data of each row
        while($row = $Difficulty_result->fetch_assoc()) {
            $difficulty_array[]=$row;
        }
        $difficulty_encoded = json_encode($difficulty_array);

        echo $difficulty_encoded;

    }
    else {
        //getting results using examid

        $sql= "SELECT * FROM Questions WHERE NOT EXISTS (select Questions.Qid, Questions.Question, Questions.Difficulty from ExQuestions left join Questions on Questions.Qid = ExQuestions.Question_id where ExQuestions.Exam_id = '$exam')";
        //$sql = " select Questions.Qid, Questions.Question, Questions.Difficulty from ExQuestions left join Questions on Questions.Qid = ExQuestions.Question_id where ExQuestions.Exam_id = '$exam'";
        $Examid_result = $conn->query($sql);
        $json_array = array();
        if ($Examid_result->num_rows > 0) {
            // output data of each row
            while($row = $Examid_result->fetch_assoc()) {
                $Examid_array[]=$row;
            }
            $Exam_encoded = json_encode($Examid_array);

            echo $Exam_encoded;

        }
        else {
            //if there is neither examid, or difficulty
            $all = "select * from Questions;";
            $allqu = $conn->query($all);
            $all_array = array();
            if ($allqu->num_rows > 0) {

                while($row = $allqu->fetch_assoc()) {
                    $all_array[]=$row;
                }
                $allq = json_encode($all_array);
                echo $allq;
            }
        }
    }


    break;



//this views exams.
case "v_exams":
    $conn = mysqli_connect("sql1.njit.edu", "jll25", "EzzrnW0B0", "jll25");
    if (mysqli_connect_errno()) {
        echo "Failed to connect to MySQL: " . mysqli_connect_error();
    }

    $viewe = "Select * from Exams;";
    $Exameview = $conn->query($viewe);
    $json_array = array();
    if ($Exameview->num_rows > 0) {
        // output data of each row
        while($row = $Exameview->fetch_assoc()) {
            $Examview_array[]=$row;
        }
        $Examv_encode = json_encode($Examview_array);

        echo $Examv_encode;

    }

    break;

//creates exams
case "a_exam":


    $conn =  new mysqli("sql1.njit.edu", "jll25", "EzzrnW0B0", "jll25");
    $ae = $_POST['examname'];

    $add = "Insert into Exams(Name) values ('$ae');";
    $addresult = $conn->query($add);
    if($addresult) {return 1;
    }
    else {return 0;
    }
    break;


//inserting to answer from students
case "answers":
    $aqe= $_POST['answers'];
    $conn = new mysqli("sql1.njit.edu", "jll25", "EzzrnW0B0", "jll25");

$sid = $_POST['sid'];
$eid = $_POST['eid'];
$qid = $_POST['qid'];
$score = $_POST['score'];

$adde = "insert into StudentResult(Student_id,Eid, Qid,Answer) values ('$sid','$eid', '$qid', '$score');";


if ($conn->query($adde) === TRUE) {
    echo "Answers added successfully";
} else {
    echo "Error: " . $adde . "<br>" . $conn->error;
}


    break;

//Used to grab all questions from exam id for editing an exam


case "e_get_questions":
    $eid = $_POST['id'];

    $conn =  new mysqli("sql1.njit.edu", "jll25", "EzzrnW0B0", "jll25");
    if (mysqli_connect_errno()) {
        echo "Failed to connect to MySQL: " . mysqli_connect_error();
    }

    $vieweq = "select Questions.Qid, Questions.Question, Questions.Difficulty, ExQuestions.Total_points from ExQuestions left join Questions on ExQuestions.Question_id = Questions.Qid where ExQuestions.Exam_id ='$eid'";

    $Exameview = $conn->query($vieweq);
    $json_array = array();
    if ($Exameview->num_rows > 0) {
        // output data of each row
        while($row = $Exameview->fetch_assoc()) {
            $Examview_array[]=$row;
        }
        $Examv_encode = json_encode($Examview_array);

        echo $Examv_encode;
    }
    else {return 0;
    }

    break;
//Get Question from id (to edit)
case "qb_get_question":

    $eq = $_POST['qb_get_question'];
    $qid = $_POST['questionid'];

    $conn =  new mysqli("sql1.njit.edu", "jll25", "EzzrnW0B0", "jll25");
    if (mysqli_connect_errno()) {
        echo "Failed to connect to MySQL: " . mysqli_connect_error();
    }

    $viewe = "select Questions.Question, TC.TestCase, Questions.Difficulty from Questions left join TC on Questions.Qid= TC.Qid where Questions.Qid ='$qid'";
    $Exameview = $conn->query($viewe);
    $json_array = array();
    if ($Exameview->num_rows > 0) {
        // output data of each row
        while($row = $Exameview->fetch_assoc()) {
            $Examview_array[]=$row;
        }
        $Examv_encode = json_encode($Examview_array);

        echo $Examv_encode;
    }
    break;
//Used to grab all questions from exam id for editing an exam
case "e_get_question":

    $qid = $_POST['question_id'];

    $eid = $_POST['eid'];


    $conn =  new mysqli("sql1.njit.edu", "jll25", "EzzrnW0B0", "jll25");
    if (mysqli_connect_errno()) {
        echo "Failed to connect to MySQL: " . mysqli_connect_error();
    }
    $viewe = "select Questions.Question, Questions.Difficulty, ExQuestions.Total_points from ExQuestions left join Questions on ExQuestions.Question_id = Questions.Qid where ExQuestions.Exam_id = '$eid'and ExQuestions.Question_id ='$qid'";
    $Exameview = $conn->query($viewe);
    $json_array = array();
    if ($Exameview->num_rows > 0) {
        // output data of each row
        while($row = $Exameview->fetch_assoc()) {
            $Examview_array[]=$row;
        }
        $Examv_encode = json_encode($Examview_array);

        echo $Examv_encode;
    }
    else{return 0;
    }

    break;

//edit question

case "e_question":
    $qid = $_POST['qid'];
    $question = $_POST['question'];
    $difficulty = $_POST['difficulty'];
    $testcase = $_POST['testcase'];
    $answer = $_POST['solution'];
    $score = $_POST['score'];


    $conn =  new mysqli("sql1.njit.edu", "jll25", "EzzrnW0B0", "jll25");
    if (mysqli_connect_errno()) {
        echo "Failed to connect to MySQL: " . mysqli_connect_error();
    }
    /*
    if (isset($_POST['score'])){
    $update = "UPDATE ExQuestions SET Total_Points= '$score' WHERE Question_id = '$qid'";
    }

    */

    $updatq = "UPDATE Questions SET Question = '$question' where Qid ='$qid'";
    $updateq = $conn->query($updatq);

    $updatescore = "UPDATE ExQuestions SET Total_points = '$score' where Question_id ='$qid'";
    $updatescore = $conn->query($updatescore);


    foreach( $testcase as $index => $col ){
      $query = "UPDATE TC SET TestCase='".mysql_real_escape_string($testcase).
               "', Answer='".mysql_real_escape_string($answer)."' WHERE id=".$qid;
                 }
    $query = rtrim( $query, ',');
    mysqli_query($conn,$query);
     if ($conn->query($query) === TRUE) {
        	echo "TestCase added successfully";
    	}
    	else {
       		 echo "Error: " . $query . "<br>" . $conn->error;}

        $add= "Insert into Questions(Question, Difficulty) values ('$question', '$difficulty');";



/*
    $updatetc =  "UPDATE TC SET TestCase = '$testcases' where Question_id ='$qid'";
    $updateq = $conn->query($updatq);

    $updatetc =  "UPDATE TC SET TestCase = '$answer' where Question_id ='$qid'";
    $updateq = $conn->query($updatq);

    /*
    $update = "UPDATE Questions SET Question='$question','Difficulty'='$difficulty' WHERE Qid = '$qid'";
    $addresult = $conn->query($update);
    if($addresult)
    {
    if(!isset($_POST['testcases'])){ return 1;}
    else {
    if(!isset($_POST['solutions'])){return 1;}
    else{
    $update2 = "UPDATE 'TC' SET TestCase= '$testcases', Answer ='$answer' WHERE Qid = '$qid'";
    $updateresult =  $conn->query($update2);
    if($updateresult)
    {return 1;}
    else{ $update2 = "UPDATE 'TC' SET 'Case'='$testcases'WHERE Qid = '$qid'";
    $updateresult =  $conn->query($update2);
    if($updateresult)
    {return 1;}
    else{return 0;}
    }
    }
    }
    }*/
    break;

//delete
case "req_exam":

    $eid = $_POST['eid'];

    $qid = $_POST['qid'];

    if(isset($_POST['qid'])) {
        $conn =  new mysqli("sql1.njit.edu", "jll25", "EzzrnW0B0", "jll25");
        $deleteq ="Delete from ExQuestions where Exam_id = '$eid' and Question_id ='$qid'";
        $deleteqresult = $conn->query($deleteq);
        if($deleteqresult) {
            return 1;
        }
        else {return 0;
        }
    }

    break;

//add question to test bank
case "a_testbank":
    $ate = $_POST['a_testbank'];


    $question = $_POST['question'];
    $difficulty = $_POST['difficulty'];
    $case = $_POST['testcase'];
    $solution = $_POST['solution'];


    $conn =  new mysqli("sql1.njit.edu", "jll25", "EzzrnW0B0", "jll25");

$getai = "SELECT AUTO_INCREMENT FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = 'jll25' AND TABLE_NAME = 'Questions';";
$getairesult = $conn->query($getai);



$query = 'insert into TC (Qid,TC,Answer) values';

foreach( $_POST['testcase'] as $index => $col ){
    $query .= "('".$getai."' '".$case[$index]."', '".$solution[$index]."'),";
}
$query = rtrim( $query, ',');
mysqli_query($conn,$query);
 if ($conn->query($query) === TRUE) {
    	echo "TestCase added successfully";
	}
	else {
   		 echo "Error: " . $query . "<br>" . $conn->error;}

    $add= "Insert into Questions(Question, Difficulty) values ('$question', '$difficulty');";

    /*$addresult = $conn->query($add);

    echo "<pre>";
    print_r($case);
    echo "</pre>";


    $sql = array();
		/*
    foreach( $testcases as $row ) {
        $sql[] = '("'.mysql_real_escape_string($row['testcases']).'", '.mysql_real_escape_string($row['solution']).')';
    }
    mysql_query('INSERT INTO TC (TestCase, Answer) VALUES '.implode(',', $sql));*/

		 for($i=0; $i<sizeof($case);$i++){
			 $testcaseresult = "Insert into TC(Eid,TestCase,Answer) values '$getairesult','$case[$i]','$solution[$i]';";
			 $testcaseresultq= $conn->query($testcaseresult);
			 if(!$testcaseresultq){echo "error";}

		 }



    break;

//add question to exam

case "aq_exam":
    $aqe= $_POST['aq_exam'];

    $eid = $_POST['eid'];
    $qid = $_POST['qid'];
    $score = $_POST['score'];


    $conn =  new mysqli("sql1.njit.edu", "jll25", "EzzrnW0B0", "jll25");

    //add if exists to put number in
    /*$add ="INSERT INTO ExQuestions(Exam_id, Question_id, Total_points) VALUES ('$eid','$qid','$score');";
    $addresult = $conn->query($add);*/


    $ieq ="INSERT INTO ExQuestions (Exam_id, Question_id, Total_points)
    VALUES ('$eid','$qid','$score')
    ON DUPLICATE KEY
    UPDATE Total_points = '$score';";

     if ($conn->query($ieq) === TRUE) {
        	echo "Added Exam question  successfully";
    	}
    	else {
       		 echo "Error: " . $ieq . "<br>" . $conn->error;
    	}

    break;

//remove an exam
case 'r_exam':
    $eid = $_POST['eid'];
    $conn =  new mysqli("sql1.njit.edu", "jll25", "EzzrnW0B0", "jll25");

    $deleteq ="Delete from ExQuestions where Exam_id = '$eid'";
    if ($conn->query($deleteq) === TRUE) {
        echo "Exam questions deleted successfully";
    } else {
        echo "Error: " . $deleteq . "<br>" . $conn->error;
    }

    $remove = "Delete from Exams where eid ='$eid';";
    if ($conn->query($remove) === TRUE) {
        echo "Exam deleted successfully";
    } else {
        echo "Error: " . $remove . "<br>" . $conn->error;
    }
    break;

case 'release':
	$eid = $_POST['eid'];
	$conn =  new mysqli("sql1.njit.edu", "jll25", "EzzrnW0B0", "jll25");
	$release = "update Exams set Release_Ready ='Yes' where eid ='$eid'";

	if ($conn->query($release) === TRUE) {
	    	echo "Exam is ready to be released";
	}
	else {
   		 echo "Error: " . $release . "<br>" . $conn->error;}


break;

case "results":
//

break;
default:
    header('Location: https://web.njit.edu/~jll25/CS490/student.html');
}




?>

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
case "answer":

$conn = mysqli_connect("sql1.njit.edu", "jll25", "EzzrnW0B0", "jll25");

$sid = $_POST['sid'];
$eid = $_POST['exid'];
$qid = $_POST['questions'];
$answers = $_POST['answer'];

var_dump($answers);
var_dump($qid);

if(!is_array($qid)){echo "no array";}
else{var_dump($qid);}


for ($i=0; $i <sizeof($_POST['questions']) ; $i++) {
  $question_id = $qid[$i];
  $answer = $answers[$i];
  $answer = mysqli_real_escape_string($conn, $answer);

  $insertquery = "Insert into StudentResults(Student_id, Eid, Qid, Answer) values ('$sid', '$eid', '$qid[$i]', '$answer')
  ON DUPLICATE KEY
  UPDATE Answer = '$answer';";
  //echo '<br>';
  //echo $insertquery;
  if ($conn->query($insertquery) === TRUE) {
       echo "Student's answer added successfully";
   }
   else {
        echo "Error: " . $insertquery . "<br>" . $conn->error;
      }


if(empty($answer)){
  $zero = "Update StudentResults set Score = 0, Auto_Grader = 'There is no answer. No points' where Student_id = '$sid' and Eid = '$eid' and Qid = '$qid[$i]'";
  if ($conn->query($zero) === TRUE) {
       echo "Score added successfully";
   }
   else {
        echo "Error: " . $zero. "<br>" . $conn->error;
      }
}


  $answer = stripslashes($answer);
  $son = file_put_contents("jream.py",$answer);

  $ret_val = exec('python jream.py 2>&1', $son);
  //echo $ret_val;
  $ret2 = strstr($ret_val, ':', true);

  if($ret2 == "NameError"){
    $ret_val = mysqli_real_escape_string($conn, $ret_val);
    $zero = "Update StudentResults set Score = 0, Auto_Grader = '$ret_val' where Student_id = '$sid' and Eid = '$eid' and Qid = '$qid[$i]'";
    if ($conn->query($zero) === TRUE) {
         echo "Score added successfully";
     }
     else {
          echo "Error: " . $zero. "<br>" . $conn->error;
        }
  }


  else if($ret2 == "SyntaxError"){
      $ret_val = mysqli_real_escape_string($conn, $ret_val);
      $zero = "Update StudentResults set Score = 0, Auto_Grader = '$ret_val' where Student_id = '$sid' and Eid = '$eid' and Qid = '$qid[$i]'";
      if ($conn->query($zero) === TRUE) {
           echo "Score added successfully";
       }
       else {
            echo "Error: " . $zero. "<br>" . $conn->error;
          }
    }

    else{

          $select = "select TestCase, Answer from TC where Qid = '$qid[$i]'";
          //echo $select;
          $selects = $conn->query($select);

          if ($selects->num_rows > 0) {
              while($row = $selects->fetch_assoc()) {
                 $rows[]=$row;

              }
            }

              foreach($rows as $row){
                $testingcases = $row['TestCase'];
                $tcanswers = $row['Answer'];


                $maxpoint = "select Total_points from ExQuestions where Question_id = '$qid[$i]' and Exam_id='$eid';";
                //echo $maxpoint;
                $selects = $conn->query($maxpoint);

                if ($selects->num_rows > 0) {
                    while($row = $selects->fetch_assoc()) {
                       $rows[]=$row;

                    }
                  }
                  else{echo "no records";}

                    foreach($rows as $row){
                      $total = $row['Total_points'];
                          }
                  $tcpoint = "Select COUNT(TestCase) as TCount from TC where Qid = 'qid[$i];'";
                  $result = mysqli_query($conn,$tcpoint);
                  $values = mysqli_fetch_assoc($result);
                  $numtc = $values['TCount'];

                  //echo $numtc;


                $maxtestcase = $total/$numtc;
                //echo $maxtestcase;

                $count = "SELECT COUNT(TestCase)
                      FROM TC
                      WHERE Qid = '$qid[$i]'; ";


                $inserttestcases = "insert into TTC(Qid, Sid, Eid, TC, TC_Answer, Max_Points) values ('$qid[$i]', '$sid', '$eid', '$testingcases', '$tcanswers', '$maxtestcase');";
                //echo $inserttestcases;
                if ($conn->query($inserttestcases) === TRUE) {
                     echo "TestCase added successfully";
//                     $updatemaxscore = "insert into TTC(Max_Points)values('select Totalpoints div ')"
                 }
                 else {
                      echo "Error: " . $inserttestcases. "<br>" . $conn->error;
                    }


                $my_file = 'jream.py';
                $anstestcase = $answer . " " .$testingcases;
                $handle = fopen($my_file, 'w') or die('Cannot open file:  '.$my_file);
                fwrite($handle, $anstestcase);

                  $ret_val2 = exec('python jream.py 2>&1', $handle);
            //      echo $ret_val2;

                  $ret3 = strstr($ret_val2, ':', true);

                  if($ret3 == "NameError"){
                    $ret_val = mysqli_real_escape_string($conn, $ret_val);
                    $zero = "Update TTC set Student_Points = 0, Student_Answer = '$ret_val2' where Sid = '$sid' and Eid = '$eid' and Qid = '$qid[$i]'and TC = '$tesstingcases[$i]";
                    if ($conn->query($zero) === TRUE) {
                         echo "Score added successfully";
                     }
                     else {
                          echo "Error: " . $zero. "<br>" . $conn->error;
                        }
                  }


                  else if($ret3 == "SyntaxError"){
                      $ret_val = mysqli_real_escape_string($conn, $ret_val);
                      $zero = "Update TTC set Student_Points = 1, Student_Answer ='$ret_val2' where Sid = '$sid' and Eid = '$eid' and Qid = '$qid[$i]' and TC = '$tesstingcases'";
                      //Auto_Grader = '$ret_val'
                      if ($conn->query($zero) === TRUE) {
                           echo "Score added successfully";
                            }



                       else {
                            echo "Error: " . $zero. "<br>" . $conn->error;
                          }
                    }


                    else{
                          $ret_val2 = mysqli_real_escape_string($conn, $ret_val2);
                          $score = "Update StudentResults set Score = (select Total_points from ExQuestions where Exam_id ='$eid' and Question_id = '$qid[$i]'), Results = 'Passed Preliminary and was able to run. Need Test Cases to test it more.' where Student_id = '$sid' and Eid = '$eid' and Qid = '$qid[$i]'";
                          if ($conn->query($score) === TRUE) {
                               //echo "Score added successfully";
                           }
                           else {
                                echo "Error: " . $zero. "<br>" . $conn->error;
                              }

                    }



                //echo $testingcases . '<br/>';
          }
    }


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

    $viewe = "select Questions.Question, TC.TestCase, TC.Answer, Questions.Difficulty from Questions left join TC on Questions.Qid= TC.Qid where Questions.Qid ='$qid'";
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
    $order = $_POST['order'];


    $conn =  new mysqli("sql1.njit.edu", "jll25", "EzzrnW0B0", "jll25");
    if (mysqli_connect_errno()) {
        echo "Failed to connect to MySQL: " . mysqli_connect_error();
    }


    $updatq = "UPDATE Questions SET Question = '$question' where Qid ='$qid'";
    $updateq = $conn->query($updatq);

    $updatescore = "UPDATE ExQuestions SET Total_points = '$score' where Question_id ='$qid'";
    $updatescore = $conn->query($updatescore);

    for ($i=0; $i <sizeof($testcase) ; $i++) {
      // code...
      $answer = mysql_real_escape_string($answer);

    $query = "Update TC set TestCase ='$testcase[$i]', Answer ='$answer[$i]', Order = '$order[$i]' where Qid ='$qid'";
    if ($conn->query($query) === TRUE) {
         echo "TestCase and Solution added successfully";
     }
     else {
          echo "Error: " . $query . $conn->error;}
        }
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
    $conn =  new mysqli("sql1.njit.edu", "jll25", "EzzrnW0B0", "jll25");


    $question = $_POST['question'];
    $difficulty = $_POST['difficulty'];
    $case = $_POST['testcase'];
    $solution = $_POST['solution'];
    var_dump($case, $solution);
    print $case[0];

    //$getai = "SELECT AUTO_INCREMENT FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = 'jll25' AND TABLE_NAME = 'Questions';";
    //$getairesult = $conn->query($getai);
    //echo $getairesult;
    $tc = "TC";
    $TCquery =mysqli_query("show tables status where name = '$TC' ");
    $row = mysql_fetch_array($TCquery);
    $next_inc_value =$row["AUTO_INCREMENT"];




    $add= "Insert into Questions(Question, Difficulty) values ('$question', '$difficulty');";
      if ($conn->query($add)=== TRUE) {
          $last_id = $conn->insert_id;
              echo "question added successfully";
            }
              else {
                echo "Error: " . $add . "<br>" . $conn->error;}
      for ($i=0; $i <sizeof($case) ; $i++) {
        // code...

      $query = "insert into TC(Qid, TestCase, Answer) values('$last_id','$case[$i]','$solution[$i]')";
      if ($conn->query($query) === TRUE) {
           echo "TestCase and Solution added successfully";
       }
       else {
            echo "Error: " . $query . $conn->error;}
          }

                /*foreach($case as $index=>$col){
                $query = "insert into TC( TestCase, Answer) values('$case[$index]','$solution[$index]'),";


                $query = rtrim( $query, ',');

                 if ($conn->query($query) === TRUE) {
                    	echo "TestCase added successfully";
                	}
                	else {
                   		 echo "Error: " . $query . $conn->error;}
                     }

      /*for ($i=0; $i < sizeof($case) ; $i++) {
        $testcaseresult = "Insert into TC(Eid,TestCase,Answer) values '$getairesult','$case[$i]','$solution[$i]';";
 			  $testcaseresultq= $conn->query($testcaseresult);
 			  if(!$testcaseresultq){echo "error";}

      }



    /*
    foreach($case as $index=>$col){
    $query = "insert into TC(TestCase, Answer) values('".$case[$index]."','".$solution[$index]."');";
    }

    $query = rtrim( $query, ',');
    mysqli_query($conn,$query);
     if ($conn->query($query) === TRUE) {
        	echo "TestCase added successfully";
    	}
    	else {
       		 echo "Error: " . $query . "<br>" . $conn->error;}
*/
    break;

//add question to exam

case "aq_exam":
    $aqe= $_POST['aq_exam'];

    $eid = $_POST['eid'];
    $qid = $_POST['qid'];
    $score = $_POST['score'];


    $conn =  new mysqli("sql1.njit.edu", "jll25", "EzzrnW0B0", "jll25");

    //add if exists to put number in
  //  $add ="INSERT INTO ExQuestions(Exam_id, Question_id, Total_points) VALUES ('$eid','$qid','$score');";
    //ddresult = $conn->query($add);


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
	$release = "update Exams set Release_Ready ='1' where eid ='$eid'";

	if ($conn->query($release) === TRUE) {
	    	echo "Exam is ready to be released";
	}
	else {
   		 echo "Error: " . $release . "<br>" . $conn->error;}


break;

case "r_testbank":

$qid = $_POST['id'];

$conn =  new mysqli("sql1.njit.edu", "jll25", "EzzrnW0B0", "jll25");
$deleteeq = "delete from ExQuestions where Question_id= '$qid'";
if ($conn->query($deleteeq) === TRUE) {
      echo "Exam questions has been deleted";
}
else {
     echo "Error: " . $deleteeq . "<br>" . $conn->error;}

$deleteq = "delete from Questions where Qid= '$qid'";
     if ($conn->query($deleteq) === TRUE) {
           echo "Questions has been deleted from the Testbank";
     }
     else {
          echo "Error: " . $deleteq . "<br>" . $conn->error;}

break;

case "results":


$eid = $_POST['eid'];






$conn = mysqli_connect("sql1.njit.edu", "jll25", "EzzrnW0B0", "jll25");

if ($conn->connect_error) {
    die("Connection failure" . $conn->connect_error);
}

$Students = "select Student_id, sum(score) from StudentResults where Student_id in (select Stid from Student) and StudentResults.Eid ='$eid' group by Student_id;";

$Studentsr = $conn->query($Students);
$json_array = array();
if ($Studentsr->num_rows > 0) {
    // output data of each row
    while($row = $Studentsr->fetch_assoc()) {
        $studentid[]=$row;
    }
    $student_encoded = json_encode($studentid);

    echo $student_encoded;
}


break;

case "s_results":

$sid = $_POST['sid'];
$eid = $_POST['eid'];

$conn = mysqli_connect("sql1.njit.edu", "jll25", "EzzrnW0B0", "jll25");
if ($conn->connect_error) {
    die("Connection failure" . $conn->connect_error);
}



$sel = "select Questions.Question, Questions.Qid, StudentResults.score, StudentResults.Answer as Student_Answer, TC.TestCase, TC.Answer, TTC.Student_Points, TTC.Student_Answer, ExQuestions.Total_points from StudentResults inner join Questions on Questions.Qid = StudentResults.Qid inner join ExQuestions on StudentResults.Eid = ExQuestions.Exam_id inner join TC on TC.Qid = Questions.Qid inner join TTC on TTC.Qid = Questions.Qid where StudentResults.Student_id ='$sid' and StudentResults.Eid = '$eid' group by TC.TestCase";

  $Sel = $conn->query($sel);
  $json_array = array();
  if ($Sel->num_rows > 0) {
      // output data of each row
      while($row = $Sel->fetch_assoc()) {
          $studentid[]=$row;
      }
      $student_encoded = json_encode($studentid);

      echo $student_encoded;


}




break;

case "c_comment":

$comment = $_POST['comment'];
$newgrade = $_POST['newgrade'];
$eid = $_POST['exid'];
$qid = $_POST['qid'];
$sid = $_POST['sid'];

$conn = mysqli_connect("sql1.njit.edu", "jll25", "EzzrnW0B0", "jll25");

for ($i=0; $i <sizeof($comment) ; $i++) {
  $comment = $comment[$i];
  $comment = $mysqli->real_escape_string($comment);



  $sql = "update StudentResults set Score = '$newgrade[$i]', Result = '$comment' where Eid = '$eid' and Qid = '$qid' and Student_id = '$sid'";
  if ($conn->query($comment) === TRUE) {
        echo "Student score has been updated";
  }
  else {
       echo "Error: " . $comment . "<br>" . $conn->error;
     }


  $grade = "insert into Updated_Grades(Stid, Eid, Qid, Grade) values ('$sid','$eid','$qid','$newgrade[$i]');";
  if ($conn->query($grade) === TRUE) {
        echo "Score change has been documented";
  }
  else {
       echo "Error: " . $grade . "<br>" . $conn->error;}


}

break;

case "g_comment":
$eid = $_POST['exid'];
$qid = $_POST['questionid'];
$sid = $_POST['sid'];


$conn = mysqli_connect("sql1.njit.edu", "jll25", "EzzrnW0B0", "jll25");


$sql = "select Comments, Score from StudentResults where Eid = '$eid' and Student_id = '$sid' and Qid = '$qid';";


$comment  = $conn->query($sql);
$json_array = array();
if ($comment->num_rows > 0) {
    // output data of each row
    while($row = $comment->fetch_assoc()) {
        $comment_row[]=$row;
    }
    $comment_encoded = json_encode($comment_row);

    echo $comment_encoded;

}


  break;


default:
    header('Location: https://web.njit.edu/~jll25/CS490/student.html');
}




?>

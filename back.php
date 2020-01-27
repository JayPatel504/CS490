<?php
$servername = "sql1.njit.edu";
$username = "jhp54";
$password = "";
$dbname = "jhp54";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

function uAuth($u, $p) {
	global $conn;
	$sql = "SELECT * FROM Users";
	$result = $conn->query($sql);
	while($row = $result->fetch_assoc()) {
		if ($u === $row["U"] and $p === $row["P"]){
			if ($row["access"] === "i"){
				$data = array();
				$data['User'] = array("access"=>"instructor", "user"=>$u);
				echo json_encode($data);
				return;
			}
			elseif ($row["access"] === "s"){
				$data = array();
				$data['User']=array("access"=>"student", "user"=>$u);
				echo json_encode($data);
				return;
			}
		}
	}
	$data = array();
	$data['User']=array("access"=>"bad");
	echo json_encode($data);
	return;
}

function inQ($name, $par, $Qt, $Ot, $tcase, $diff, $des){
	global $conn;
	$par = json_encode($par);
	$tcase = json_encode($tcase);
	$par = str_replace("'", "%27", $par);
	$des = str_replace("'", "%27", $des);
	$tcase = str_replace("'", "%27", $tcase);
	$sql = "INSERT INTO qBank (Fname, Para, Qtype, Otype, Tcase, Diff, Descript) VALUES ('".$name."', '".$par."', '".$Qt."', '".$Ot."', '".$tcase."', '".$diff."', '".$des."')";
	if ($conn->query($sql) === true) { echo json_encode(1); } 
	else { echo json_encode(0); }
	return;
}

function getQ(){
	global $conn;
	$data = array();
	$sql = "SELECT * FROM qBank";
	$result = $conn->query($sql);
	while($row = $result->fetch_assoc()){
		$temp = array("QID"=>$row['QID'], "Fname"=>$row['Fname'], "Params"=>json_decode(str_replace("%27", "'", $row['Para'])), "Qtype"=>$row['Qtype'], "Otype"=>$row['Otype'], "Tcases"=>json_decode(str_replace("%27", "'", $row['Tcase']), true), "Diff"=>$row['Diff'], "Desc"=>str_replace("%27", "'", $row['Descript']));
		array_push($data, array("Question"=>$temp));
	}
	$ret = array("Questions"=>$data);
	echo json_encode($ret);
	return;
}

function getQID($q){
	global $conn;
	$data = array();
	$sql = "SELECT * FROM qBank WHERE QID=".$q;
	$result = $conn->query($sql);
	while($row = $result->fetch_assoc()){
		$temp = array("QID"=>$row['QID'], "Fname"=>$row['Fname'], "Params"=>json_decode(str_replace("%27", "'", $row['Para'])), "Qtype"=>$row['Qtype'], "Otype"=>$row['Otype'], "Tcases"=>json_decode(str_replace("%27", "'", $row['Tcase']), true), "Diff"=>$row['Diff'], "Desc"=>str_replace("%27", "'", $row['Descript']));
		$data=$temp;
	}
	echo json_encode($data);
	return;
}

function createT($temp){
	global $conn;
	$sql = "INSERT INTO Tests (Qs) VALUES ('".$temp."')";
	if ($conn->query($sql) === true) { echo json_encode(1); } 
	else { echo json_encode(0); }
	return;	
}

function getT(){
	global $conn;
	$data = array();
	$sql = "SELECT * FROM Tests";
	$result = $conn->query($sql);
	while($row = $result->fetch_assoc()){
		$temp = array("TID"=>$row['TestID'], "Qs"=>json_decode($row['Qs'], true));
		array_push($data, array("Test"=>$temp));
	}
	$ret = array("Tests"=>$data);
	echo json_encode($ret);
	return;
}

function getTID($t){
	global $conn;
	$data = array();
	$sql = "SELECT * FROM Tests WHERE TestID=".$t;
	$result = $conn->query($sql);
	while($row = $result->fetch_assoc()){
		$temp = array("TID"=>$row['TestID'], "Qs"=>json_decode($row['Qs'], true));
		$data=$temp;
	}
	echo json_encode($data);
	return;
}

function actT($T){
	global $conn;
	$sql = "TRUNCATE TABLE Exam";
	$conn->query($sql);
	$sql = "INSERT INTO Exam (TestID) VALUES ('".$T."')";
	if ($conn->query($sql) === true) { echo json_encode(1); } 
	else { echo json_encode(0); }
	return;
}

function getactT(){
	global $conn;
	$data = array();
	$sql = "SELECT * FROM Exam";
	$result = $conn->query($sql);
	while($row = $result->fetch_assoc()){
		return getTID($row["TestID"]);
	}
	echo "{}";
	return;
}

function deAct(){
	global $conn;
	$sql = "TRUNCATE TABLE Exam";
	$conn->query($sql);
	echo "1";
	return;
}

function allRes($t){
	global $conn;
	$submissions = array("Submissions"=>array());
	$sql = "SELECT * FROM Results WHERE TESTID=".$t;
	$result = $conn->query($sql);
	while($row = $result->fetch_assoc()){
		$temp = array("user"=>$row['U'],"TID"=>$row['TestID'],"Submission"=>json_decode(str_replace("%28", "\\",(str_replace("%27", "'", $row['Subm']))), true), "TotalScore"=>$row['Fscore']);
		array_push($submissions["Submissions"], $temp);
	}
	echo json_encode($submissions);
	return;	
}

function oneRes($u, $TID){
	global $conn;
	$submissions = array();
	$sql = "SELECT * FROM Results WHERE U='".$u."' AND TestID=".$TID;
	$result = $conn->query($sql);
	while($row = $result->fetch_assoc()){
		$temp = array("user"=>$row['U'],"TID"=>$row['TestID'],"Submission"=>json_decode(str_replace("%28", "\\",(str_replace("%27", "'", $row['Subm']))), true), "TotalScore"=>$row['Fscore']);
		$submissions = $temp;
	}
	echo json_encode($submissions);
	return;
}

function oneStuRes($u){
	global $conn;
	$submissions = array("Submissions"=>array());
	$sql = "SELECT * FROM Results WHERE U='".$u."'";
	$result = $conn->query($sql);
	while($row = $result->fetch_assoc()){
		$temp = array("user"=>$row['U'],"TID"=>$row['TestID'],"Submission"=>json_decode(str_replace("%28", "\\",(str_replace("%27", "'", $row['Subm']))), true), "TotalScore"=>$row['Fscore']);
		array_push($submissions["Submissions"], $temp);
	}
	echo json_encode($submissions);
	return;
}

function storeRes($user, $TID, $sub, $totS){
	global $conn;
	$sub = json_encode($sub);
	$sub = str_replace("\\", "%28",(str_replace("'", "%27", $sub)));
	$sql = "INSERT INTO Results (U, TestID, Subm, Fscore) VALUES ('".$user."', '".$TID."', '".$sub."', '".$totS."')";
	if ($conn->query($sql) === true) { echo json_encode(1); } 
	else { echo json_encode(0); }
	return;
}
	
function updateResult($user, $TID, $sub, $totS){
	global $conn;
	$sub = json_encode($sub);
	$sub = str_replace("\\", "%28",(str_replace("'", "%27", $sub)));
	$sql = "UPDATE Results SET Subm='".$sub."', Fscore='".$totS."' WHERE U='".$user."' AND TestID=".$TID;
	if ($conn->query($sql) === true) { echo json_encode(1); } 
	else { echo json_encode(0); }
	return;
}	

function ReleaseResults($t){
	global $conn;
	$sql = "INSERT INTO ReleasedResults (TestID) VALUES ('".$t."')";
	if ($conn->query($sql) === true) { echo json_encode(1); } 
	else { echo json_encode(0); }	
	return;
}

function Released(){
	global $conn;
	$sql = "SELECT * FROM ReleasedResults";
	$res = array("TestIDs"=>array());
	$result = $conn->query($sql);
	while($row = $result->fetch_assoc()){
		array_push($res["TestIDs"], $row['TestID']);
	}
	echo json_encode($res);
	return;
}

	
$json_str = file_get_contents('php://input');
$json_obj = json_decode($json_str, true);

foreach ($json_obj as $key => $value){
	//Authenticate User
	if ($key === "User"){
		uAuth($value["user"], hash('sha256' , $value["pass"])); 
	}	
	//Insert question into db
	elseif ($key === "Question"){
		inQ($value["Fname"], $value["Params"], $value["Qtype"], $value["Otype"], $value["Tcases"], $value["Diff"], $value["Desc"]);
	}
	//Get all question
	elseif ($key === "GetQ"){
		getQ();
	}
	//Get requested question
	elseif ($key === "GetQID"){
		getQID($value);
	}
	//Create test
	elseif ($key === "Test"){
		createT(json_encode($value["Qs"]));
	}
	//Get all test
	elseif ($key === "GetT"){
		getT();	
	}
	//Get requested test
	elseif ($key === "GetTID"){
		getTID($value);
	}
	//Activate a test
	elseif ($key === "Activate"){
		actT($value);
	}
	//Get active test
	elseif ($key === "Active"){
		getactT();
	}
	//Deactivate current test
	elseif ($key === "Deactivate"){
		deAct();
	}
	//Get all selected test results (for instructor)
	elseif ($key === "GetResByTID"){
		allRes($value);
	}
	//Get all results per student
	elseif ($key === "GetResByStudent"){
		oneStuRes($value);
	}
	//Unknown ATM
	elseif($key === "GetRes"){
		oneRes($value["user"], $value["TID"]);
	}
	//Store result and score
	elseif ($key === "Submit"){
		storeRes($value["user"], $value["TID"], $value["Submission"], $value["TotalScore"]);
	}
	//Update result(submission and totalScore) in table
	elseif ($key === "UpdateRes"){
		updateResult($value["user"], $value["TID"], $value["Submission"], $value["TotalScore"]);
	}
	//Inserting exams with results
	elseif ($key === "ReleaseResults"){
		ReleaseResults($value);
	}
	//Get all exams with results
	elseif ($key === "Released"){
		Released();
	}
}
$conn->close();
?>

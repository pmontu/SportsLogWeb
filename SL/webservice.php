<?php
header('Content-type: application/json');
/* errors popup as text disturbing jason output
 * so its switched off*/
error_reporting(0); 
//controller
if(isset($_GET['action'])){
	switch($_GET['action']){
		case 'getUserLoginStatus': $obj = new UserLoginSignup();$obj->getUserLoginStatus();break;
		case 'getUserLogin': $obj = new UserLoginSignup();$obj->getUserLogin();break;
		case 'setNewUser': $obj = new UserLoginSignup();$obj->setNewUser();break;
		case 'logout': $obj = new UserLoginSignup();$obj->logout();break;
		case 'getRoots': $obj = new HomePage();$obj->getRoots();break;
		case 'setNewRoot': $obj = new HomePage();$obj->setNewRoot();break;
		case 'getDays': $obj = new HomePage();$obj->getDays();break;
		case 'setNewDay': $obj = new HomePage();$obj->setNewDay();break;
		case 'getFields': $obj = new HomePage();$obj->getFields();break;
		case 'setNewRowNewColumn' : $obj = new HomePage();$obj->setNewRowNewColumn();break;
		case 'setNewColumnField' : $obj = new HomePage();$obj->setNewColumnField();break;
	}
}
//web service return data object class
class ReturnData{
	private $Obj = array("Success" => false, "Error" => "", "Data" => "");
	function __construct() {
	}
	public function getObj(){
		return $this->Obj;
	}
	public function setObjData($Data){
		$this->Obj["Success"] = true;
		$this->Obj["Data"] = $Data;
	}
	public function setObjErrorAndDie($Msg){
		$this->Obj["Error"] = $Msg;
		$this->Obj["Success"] = false;
		die();
	}
}
class DataAccess{
	private $ReturnData;
	private $mysqli;
	function __construct($returndata) {
		$this->ReturnData = $returndata;
		try {
			$this->mysqli = mysqli_connect("localhost", "yizotech_athlete", "Montu$1234", "yizotech_sportslog");
			if (mysqli_connect_errno($this->mysqli)) {
				$this->ReturnData->setObjErrorAndDie(mysqli_connect_error());
			}
		}
		catch (Exception $e) {
			$this->ReturnData->setObjErrorAndDie($e->getMessage());
		}
	}
	function escape($str){
		return mysqli_real_escape_string($this->mysqli, $str);
	}
	function query($qry){
		return mysqli_query($this->mysqli,$qry);
	}
	function insert_id(){
		return mysqli_insert_id($this->mysqli);
	}
	function multi_query($qry){
		$res = array();
		$i=0;
		if ($this->mysqli->multi_query($qry)) {
			do {
				/* store first result set */
				if ($result = $this->mysqli->store_result()) {
					$res[$i] = $result;
					$i++;
				}
				/* print divider */
				if (!$this->mysqli->more_results()) {
					break;
				}
			} while ($this->mysqli->next_result());
		}
		return $res;
	}
	function __destruct() {
		try {
			mysqli_close($this->mysqli);
		}
		catch (Exception $e) {
			$this->ReturnData->setObjErrorAndDie($e->getMessage());
		}
	}
}
//Page 1 - Home
class UserLoginSignup{
	//web service return data object
	private $ReturnData;
	//data access
	private $DataAccess;
	//data and database settings initializing
	function __construct() {
		$this->ReturnData = new ReturnData();
		$this->DataAccess = new DataAccess($this->ReturnData);
	}
	/*destructor is the final control point on die from construtor,
	 * and functions - returns jason data output*/	
	function __destruct() {
		echo json_encode($this->ReturnData->getObj());
	}
	/* Web service 1 */
	public function setNewUser(){
		//Validation - posted
		if(!isset($_POST["id"]) or !isset($_POST["password"])) 
			$this->ReturnData->setObjErrorAndDie("Invalid Fields");
		$id = $_POST["id"];
		$password = $_POST["password"];
		//Validation - null
		if($id=='' or $password=='')
			$this->ReturnData->setObjErrorAndDie("Please fill up all the fields");
		//sql injection issues
		$id = $this->DataAccess->escape ($id);
		$password = $this->DataAccess->escape ($password);
		//database query - patientid is returned for an insert into the patient list
		$qry = "INSERT INTO `user` (`userid` ,`id` ,`password`) VALUES (NULL ,  '".$id."', PASSWORD(  '".$password."' ));";
		if($this->DataAccess->query($qry))
			$this->ReturnData->setObjData(array("Message" => "Successfully signed up, please login to continue","UserID" => $this->DataAccess->insert_id()));
		else $this->ReturnData->setObjErrorAndDie("We could not do it!\nPlease try again sometime\n");
	}
	/* Web Service 2 */
	public function getUserLogin(){
		//Validation - posted
		if(!isset($_POST["id"]) or !isset($_POST["password"]))
			$this->ReturnData->setObjErrorAndDie("Invalid Fields");
		$id = $_POST["id"];
		$password = $_POST["password"];
		//Validation - null
		if($id=='' or $password=='')
			$this->ReturnData->setObjErrorAndDie("Please fill up all the fields");
		//sql injection issues
		$id = $this->DataAccess->escape ($id);
		$password = $this->DataAccess->escape ($password);
		//database query
		$qry = "SELECT userid FROM user where id='".$id."' and password=PASSWORD('".$password."')";
		$result = $this->DataAccess->query($qry);
		if (!$result) {
			$this->ReturnData->setObjErrorAndDie("No users in database, please signup to continue\n".$qry);
		}
		//encapsulating result row data
		if($row = mysqli_fetch_assoc($result)){
	    	$userid = $row['userid'];
	    	session_start();
	    	$_SESSION['userid'] = $userid;
			$this->ReturnData->setObjData(array("Message" => "Successfully logged in","UserID" => $userid));
			$result->free();
		}
		else{
			$this->ReturnData->setObjErrorAndDie("Invalid credentials");
		}			
	}
	/* Web Service 3 */
	public function getUserLoginStatus(){
		session_start();
		if(!isset($_SESSION['userid'])){
			$this->ReturnData->setObjErrorAndDie("Please login to continue");
		}
		$userid = $_SESSION['userid'];
		$this->ReturnData->setObjData($userid);
	}
	/* Web Service 4 */
	public function logout(){
		session_start();
		if(isset($_SESSION['userid'])){
			unset($_SESSION['userid']);
			$this->ReturnData->setObjData(array("Message" => "Successfully logged out"));
		}
		else{
			$this->ReturnData->setObjErrorAndDie("User not logged in, Please login to continue");
		}
	}
}
class HomePage{
	//web service return data object
	private $ReturnData;
	//data access
	private $DataAccess;
	//data and database settings initializing
	function __construct() {
		$this->ReturnData = new ReturnData();
		$this->DataAccess = new DataAccess($this->ReturnData);
	}
	/*destructor is the final control point on die from construtor,
	 * and functions - returns jason data output*/	
	function __destruct() {
		echo json_encode($this->ReturnData->getObj());
	}
	/* Web service 1 */
	function getRoots(){
		session_start();
		if(!isset($_SESSION['userid'])){
			$this->ReturnData->setObjErrorAndDie("Please login to continue");
		}
		$userid = $_SESSION['userid'];
		$qry = "select data,dataid from data where userid=$userid and parentid=0 order by rowid asc";
		$result = $this->DataAccess->query($qry);
		if (!$result) {
			$this->ReturnData->setObjErrorAndDie("No data in database, please add continue");
		}
		//encapsulating result row data
		$data = array();
		$i=0;
		while($row = mysqli_fetch_assoc($result)){
			$data[$i]['root'] = $row['data'];
			$data[$i]['rootid'] = $row['dataid'];
			$i++;
		}
		$result->free();
		$this->ReturnData->setObjData($data);
	}	
	/* Web service 2 */
	function setNewRoot(){
		session_start();
		if(!isset($_SESSION['userid'])){
			$this->ReturnData->setObjErrorAndDie("Please login to continue");
		}
		$userid = $_SESSION['userid'];
		//Validation - posted
		if(!isset($_POST["root"]))
			$this->ReturnData->setObjErrorAndDie("Invalid Fields");
		$root = $_POST["root"];
		//Validation - null
		if($root=='')
			$this->ReturnData->setObjErrorAndDie("Please fill in the field");
		//sql injection issues
		$root = $this->DataAccess->escape ($root);
		//query
		//$qry = "call setNewRoot($userid,'$root')";
		$qry  = "set @userid = $userid,@NewRowID=0, @root='$root';";
		$qry .= "SELECT count(*)+1 into @NewRowID FROM `data` WHERE `parentid`=0;";
		$qry .= "INSERT INTO  `data` (`userid` ,`dataid` ,`parentid` ,`rowid` ,`columnid` ,`data`)";
		$qry .= "VALUES (@userid , NULL , 0 , @NewRowID ,  '0',  @root );";
		$qry .= "SELECT LAST_INSERT_ID() as 'DataID';";
		$res = $this->DataAccess->multi_query($qry);
		$result = $res[0];
		if (!$result) {
			$this->ReturnData->setObjErrorAndDie("Please try again later");
		}
		//encapsulating result row data
		if($row = mysqli_fetch_assoc($result)){
			$dataid = $row['DataID'];
			$result->free();
			$this->ReturnData->setObjData(array("RootID" => $dataid));
		}
		else{
			$this->ReturnData->setObjErrorAndDie("Please try again later");
		}
	}
	/* Web service 3 */
	function getDays(){
		session_start();
		if(!isset($_SESSION['userid'])){
			$this->ReturnData->setObjErrorAndDie("Please login to continue");
		}
		$userid = $_SESSION['userid'];
		//Validation - posted
		if(!isset($_POST["rootid"]))
			$this->ReturnData->setObjErrorAndDie("Invalid Fields");
		$rootid = $_POST["rootid"];
		//Validation - null
		if($rootid=='')
			$this->ReturnData->setObjErrorAndDie("Please fill in the field");
		//sql injection issues
		$rootid = $this->DataAccess->escape ($rootid);
		//query
		$qry = "select data,dataid from data where userid=$userid and parentid=$rootid order by rowid asc";
		$result = $this->DataAccess->query($qry);
		if (!$result) {
			$this->ReturnData->setObjErrorAndDie("No data in database, please add continue");
		}
		//encapsulating result row data
		$data = array();
		$i=0;
		while($row = mysqli_fetch_assoc($result)){
			$data[$i]['day'] = $row['data'];
			$data[$i]['dayid'] = $row['dataid'];
			$i++;
		}
		$result->free();
		$this->ReturnData->setObjData($data);
	}	
	/* Web service 4 */
	function setNewDay(){
		session_start();
		if(!isset($_SESSION['userid'])){
			$this->ReturnData->setObjErrorAndDie("Please login to continue");
		}
		$userid = $_SESSION['userid'];
		//Validation - posted
		if(!isset($_POST["rootid"]))
			$this->ReturnData->setObjErrorAndDie("Invalid Fields");
		if(!isset($_POST["day"]))
			$this->ReturnData->setObjErrorAndDie("Invalid Fields");
		$rootid = $_POST["rootid"];
		$day = $_POST["day"];
		//Validation - null
		if($rootid=='')
			$this->ReturnData->setObjErrorAndDie("Please fill in the field");
		if($day=='')
			$this->ReturnData->setObjErrorAndDie("Please fill in the field");
		//sql injection issues
		$rootid = $this->DataAccess->escape ($rootid);
		$day = $this->DataAccess->escape ($day);
		//query
		//$qry = "call setNewRoot($userid,'$root')";
		$qry  = "set @userid = $userid,@NewRowID=0, @rootid='$rootid', @day='$day';";
		$qry .= "SELECT count(*)+1 into @NewRowID FROM `data` WHERE `parentid`=@rootid;";
		$qry .= "INSERT INTO  `data` (`userid` ,`dataid` ,`parentid` ,`rowid` ,`columnid` ,`data`)";
		$qry .= "VALUES (@userid , NULL , @rootid , @NewRowID ,  '0',  @day );";
		$qry .= "SELECT LAST_INSERT_ID() as 'DataID';";
		$res = $this->DataAccess->multi_query($qry);
		$result = $res[0];
		if (!$result) {
			$this->ReturnData->setObjErrorAndDie("Please try again later");
		}
		//encapsulating result row data
		if($row = mysqli_fetch_assoc($result)){
			$dataid = $row['DataID'];
			$result->free();
			$this->ReturnData->setObjData(array("dayid" => $dataid));
		}
		else{
			$this->ReturnData->setObjErrorAndDie("Please try again later");
		}
	}
	/* Web Service 5 */
	function getFields(){
		session_start();
		if(!isset($_SESSION['userid'])){
			$this->ReturnData->setObjErrorAndDie("Please login to continue");
		}
		$userid = $_SESSION['userid'];
		//Validation - posted
		if(!isset($_POST["dayid"]))
			$this->ReturnData->setObjErrorAndDie("Invalid Fields");
		$dayid = $_POST["dayid"];
		//Validation - null
		if($dayid=='')
			$this->ReturnData->setObjErrorAndDie("Please fill in the field");
		//sql injection issues
		$dayid = $this->DataAccess->escape ($dayid);
		//query
		$qry = "select data,dataid,rowid,columnid from data where userid=$userid and parentid=$dayid order by rowid,columnid asc";
		$result = $this->DataAccess->query($qry);
		if (!$result) {
			$this->ReturnData->setObjErrorAndDie("No data in database, please add continue");
		}
		//encapsulating result row data
		while($row = mysqli_fetch_assoc($result)){
			$rowid = $row['rowid'];
			$columnid = $row['columnid'];
			$data[$rowid][$columnid]['field'] = $row['data'];
			$data[$rowid][$columnid]['fieldid'] = $row['dataid'];
		}
		$result->free();
		$this->ReturnData->setObjData($data);
	}
	/* Web Service 6 */
	function setNewRowNewColumn(){
		session_start();
		if(!isset($_SESSION['userid'])){
			$this->ReturnData->setObjErrorAndDie("Please login to continue");
		}
		$userid = $_SESSION['userid'];
		//Validation - posted
		if(!isset($_POST["dayid"]))
			$this->ReturnData->setObjErrorAndDie("Invalid Fields");
		if(!isset($_POST["newrownewcolumn"]))
			$this->ReturnData->setObjErrorAndDie("Invalid Fields");
		$dayid = $_POST["dayid"];
		$newrownewcolumn = $_POST["newrownewcolumn"];
		//Validation - null
		if($dayid=='')
			$this->ReturnData->setObjErrorAndDie("Please fill in the field");
		if($newrownewcolumn=='')
			$this->ReturnData->setObjErrorAndDie("Please fill in the field");
		//sql injection issues
		$dayid = $this->DataAccess->escape ($dayid);
		$newrownewcolumn = $this->DataAccess->escape ($newrownewcolumn);
		//query
		//$qry = "call setNewRoot($userid,'$root')";
		$qry  = "set @userid = $userid,@NewRowID=0, @dayid='$dayid', @newrownewcolumn='$newrownewcolumn', @dataid=0;";
		$qry .= "SELECT count(distinct(rowid))+1 into @NewRowID FROM `data` WHERE `parentid`=@dayid;";
		$qry .= "INSERT INTO  `data` (`userid` ,`dataid` ,`parentid` ,`rowid` ,`columnid` ,`data`)";
		$qry .= "VALUES (@userid , NULL , @dayid , @NewRowID ,  1,  @newrownewcolumn );";
		$qry .= "SELECT LAST_INSERT_ID() into @dataid;";
		$qry .= "SELECT @dataid as 'DataID', @NewRowID as 'rowid', 1 as 'columnid';";
		$res = $this->DataAccess->multi_query($qry);
		$result = $res[0];
		if (!$result) {
			$this->ReturnData->setObjErrorAndDie("Please try again later");
		}
		//encapsulating result row data
		if($row = mysqli_fetch_assoc($result)){
			$dataid["fieldid"] = $row['DataID'];
			$dataid["rowid"] = $row['rowid'];
			$dataid["columnid"] = $row['columnid'];
			$result->free();
			$this->ReturnData->setObjData($dataid);
		}
		else{
			$this->ReturnData->setObjErrorAndDie("Please try again later");
		}
	}
	function setNewColumnField(){
		session_start();
		if(!isset($_SESSION['userid'])){
			$this->ReturnData->setObjErrorAndDie("Please login to continue");
		}
		$userid = $_SESSION['userid'];
		//Validation - posted
		if(!isset($_POST["dayid"]))
			$this->ReturnData->setObjErrorAndDie("Invalid Fields");
		if(!isset($_POST["newcolumnfield"]))
			$this->ReturnData->setObjErrorAndDie("Invalid Fields");
		if(!isset($_POST["rowid"]))
			$this->ReturnData->setObjErrorAndDie("Invalid Fields");
		$dayid = $_POST["dayid"];
		$newcolumnfield = $_POST["newcolumnfield"];
		$rowid = $_POST["rowid"];
		//Validation - null
		if($dayid=='')
			$this->ReturnData->setObjErrorAndDie("Please fill in the field");
		if($newcolumnfield=='')
			$this->ReturnData->setObjErrorAndDie("Please fill in the field");
		if($rowid=='')
			$this->ReturnData->setObjErrorAndDie("Please fill in the field");
		//sql injection issues
		$dayid = $this->DataAccess->escape ($dayid);
		$newcolumnfield = $this->DataAccess->escape ($newcolumnfield);
		$rowid = $this->DataAccess->escape ($rowid);
		//query
		//$qry = "call setNewRoot($userid,'$root')";
		$qry  = "set @userid = $userid,@RowID='$rowid', @dayid='$dayid', @newcolumnfield='$newcolumnfield', @dataid=0, @newcolumnid=0;";
		$qry .= "select count(*)+1 into @newcolumnid from data where parentid=@dayid and rowid=@RowID;";
		$qry .= "INSERT INTO  `data` (`userid` ,`dataid` ,`parentid` ,`rowid` ,`columnid` ,`data`)";
		$qry .= "VALUES (@userid , NULL , @dayid , @RowID ,  @newcolumnid,  @newcolumnfield );";
		$qry .= "SELECT LAST_INSERT_ID() into @dataid;";
		$qry .= "SELECT @dataid as 'DataID', @RowID as 'rowid', @newcolumnid as 'columnid';";
		$res = $this->DataAccess->multi_query($qry);
		$result = $res[0];
		if (!$result) {
			$this->ReturnData->setObjErrorAndDie("Please try again later");
		}
		//encapsulating result row data
		if($row = mysqli_fetch_assoc($result)){
			$dataid["fieldid"] = $row['DataID'];
			$dataid["rowid"] = $row['rowid'];
			$dataid["columnid"] = $row['columnid'];
			$result->free();
			$this->ReturnData->setObjData($dataid);
		}
		else{
			$this->ReturnData->setObjErrorAndDie("Please try again later");
		}
	}
}
?>
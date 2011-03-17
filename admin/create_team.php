<?php if (!isset($_SESSION)) { session_start(); } ?>

<?php include_once '../path.inc.php'; ?>

<?php
include_once 'i18n.inc.php';
if (!isset($_SESSION['userid'])) {
  echo T_("Sorry, you need to <a href='../'>login</a> to access this page.");
  exit;
} 
?>

<?php
   $_POST[page_name] = T_("CoDev Administration : Team Creation"); 
   include 'header.inc.php'; 
?>

<?php include 'login.inc.php'; ?>
<?php include 'menu.inc.php'; ?>
<br/>
<?php include 'menu_admin.inc.php'; ?>

<script language="JavaScript">

function addTeam(){
   // check fields
   foundError = 0;
   msgString = "Les champs suivants ont &eacute;t&eacute; oubli&eacute;s:\n\n"
       
   if (0 == document.forms["teamCreationForm"].team_name.value)     { msgString += "Team Name\n"; ++foundError; }
   if (0 == document.forms["teamCreationForm"].team_desc.value)     { msgString += "Team Description\n"; ++foundError; }
   if (0 == document.forms["teamCreationForm"].teamleader_id.value) { msgString += "Team Leader\n";  ++foundError; }
                  
   if (0 == foundError) {
     document.forms["teamCreationForm"].action.value="addTeam";
     document.forms["teamCreationForm"].submit();
   } else {
     alert(msgString);    
   }    
 }

function updateTeamCreationForm() {

   document.forms["teamCreationForm"].action.value="updateTeamCreationForm";
   document.forms["teamCreationForm"].is_modified.value= "true";
   document.forms["teamCreationForm"].submit();
}

</script>

<div id="content">


<?php 
include_once "constants.php";
include_once "tools.php";
include_once "user.class.php";
include_once "team.class.php";


// -----------------------------
/**
 * 
 * @param unknown_type $isCreateSTProj // true: checkbox CHECKED
 */
function displayCreateTeamForm($team_name, $teamleader_id, $team_desc,
                               $isCreateSTProj, $stproj_name,
                               $isCatIncident, $isCatTools, $isCatOther, 
                               $isTaskProjManagement, $isTaskMeeting, $isTaskIncident, $isTaskTools, $isTaskOther,
                               $task_projManagement, $task_meeting, $task_incident, $task_tools, $task_other1,
                               $is_modified = "false"
                               ) {

  echo "<form action='create_team.php' method='post' name='teamCreationForm'>\n";
  
  // ----------- Team Info
  echo "<hr align='left' width='20%'/>\n";
  echo "<h2>".T_("Team Info")."</h2>\n";
  echo "<table class='invisible'>\n";
  echo "  <tr>\n";
  echo "    <td>".T_("Team Name")."</td>\n";
  echo "    <td><input size='30' value='$team_name' name='team_name' type='text' id='team_name' onblur='javascript: updateTeamCreationForm()'> <span style='color:red'>*</span></td>\n";
  echo "  </tr>\n";
  echo "  <tr>\n";
  echo "    <td>".T_("Description")."</td>\n";
  echo "    <td><input size='100' value='$team_desc' name='team_desc' type='text' id='team_desc'> <span style='color:red'>*</span></td>\n";
  echo "  </tr>\n";
  echo "  <tr>\n"; 
  echo "    <td>".T_("Team Leader")."</td>\n";
  echo "    <td>\n";
  $query = "SELECT DISTINCT id, username, realname FROM `mantis_user_table` ORDER BY username";
  echo "      <select name='teamleader_id'>\n";
  echo "        <option value='0'></option>\n";
  $result = mysql_query($query) or die("Query failed: $query");
  while($row = mysql_fetch_object($result))
  {  
      if ($row->id == $teamleader_id) {
         echo "        <option selected value='".$row->id."'>$row->username</option>\n";
      } else {
         echo "        <option value='".$row->id."'>$row->username</option>\n";
      }
  }
  echo "      </select>\n";
  echo "      <span style='color:red'>*</span>";
  echo "    </td>\n";
  echo "  </tr>\n";
  echo "</table>\n";

  echo "  <br/>\n";
  echo "  <br/>\n";
  // ----------- associated SideTaskProject
  
  echo "<hr align='left' width='20%'/>\n";
  echo "<h2>".T_("SideTasks Project")."</h2>\n";

  echo "<input type=CHECKBOX CHECKED DISABLED name='cb_commonSideTaskProj' id='cb_commonSideTaskProj' >".
       T_("Add common SideTasks Project")."</input> <span style='color:red'>*</span><br/>\n";
  
  echo "  <br/>\n";
       
  $isChecked = $isCreateSTProj ? "CHECKED" : "";
  echo "<input type=CHECKBOX $isChecked name='cb_createSideTaskProj' id='cb_createSideTaskProj' onChange='javascript: updateTeamCreationForm()' >".
       T_("Create specific SideTask Project")."</input>\n";
  

  $isDisplayed = $isCreateSTProj ? "" : "display:none";
  echo "<div style='$isDisplayed'>\n";
  echo "<ul>\n";
  echo "<li><b>".T_("Project Name")."</b>  <input size='30' value='$stproj_name' type='text' name='stproj_name'  id='stproj_name'> <span style='color:red'>*</span></li>\n";
  
  echo "  <br/>\n";

  echo "<li><b>".T_("Categories")."</b><br/>\n";
  echo "<table class='invisible'>\n";
  
  echo "  <tr>\n";
  echo "    <td><input type=CHECKBOX DISABLED name='cb_catInactivity' id='cb_catInactivity'>".
       "<span title='".T_("This category is declared in CommonSideTaskProject")."' style='color:lightgrey'>".T_("Inactivity")."</span></input></td>\n";
  echo "  </tr>\n";
  
  echo "  <tr>\n";
  echo "    <td><input type=CHECKBOX CHECKED DISABLED name='cb_catProjManagement' id='cb_catProjManagement'>".T_("ProjectManagement")."</input>\n";
  echo "    <span style='color:red'>*</span></td>\n";
  echo "    <td><span class='help_font'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;(".T_("Meeting, Pre-sales, ProjectManagement, ...").")</span></td>\n";
  echo "  </tr>\n";

  echo "  <tr>\n";
  $isChecked = $isCatIncident ? "CHECKED" : "";
  echo "    <td><input type=CHECKBOX $isChecked name='cb_catIncident' id='cb_catIncident'>".
       T_("Incident")."</input></td>\n";
  echo "    <td><span class='help_font'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;(".T_("Server or Platform down, ...").")</span></td>\n";
  echo "  </tr>\n";

  echo "  <tr>\n";
  $isChecked = $isCatTools ? "CHECKED" : "";
  echo "    <td><input type=CHECKBOX $isChecked  name='cb_catTools' id='cb_catTools'>".
       T_("Tools")."</input></td>\n";
  echo "    <td><span class='help_font'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;(".T_("Sys. Admin, Scritps, ...").")</span></td>\n";
  echo "  </tr>\n";

  echo "  <tr>\n";
  $isChecked = $isCatOther ? "CHECKED" : "";
  echo "    <td><input type=CHECKBOX $isChecked name='cb_catOther' id='cb_catOther' >".
       T_("Other")."</input></td>\n";
  echo "    <td><span class='help_font'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;(".T_("Support, Doc, Training, Wiki, ...").")</span></td>\n";
  echo "  </tr>\n";
  
  echo "</table>\n";
  
  echo "  <br/>\n";
  
  
  echo "</li>\n";
  echo "<li><b>".T_("Default SideTasks")."</b><br/>\n";
  echo "<table class='invisible'>\n";
  echo "  <tr>\n";
  $isChecked = $isTaskProjManagement ? "CHECKED" : "";
  echo "    <td><input type=CHECKBOX $isChecked name='cb_taskProjManagement' id='cb_taskProjManagement'>".
       T_("ProjectManagement")."</input></td>\n";
  echo "    <td><input size='100' type='text' name='task_projManagement'  id='task_projManagement' value='$task_projManagement'></td>\n";
  echo "  </tr>\n";
  echo "  <tr>\n";

  echo "  <tr>\n";
  $isChecked = $isTaskMeeting ? "CHECKED" : "";
  echo "    <td><input type=CHECKBOX $isChecked name='cb_taskMeeting' id='cb_taskMeeting'>".
       T_("ProjectManagement")."</input></td>\n";
  echo "    <td><input size='100' type='text' name='task_meeting'  id='task_meeting' value='$task_meeting'></td>\n";
  echo "  </tr>\n";
  echo "  <tr>\n";
  
  $isChecked = $isTaskIncident ? "CHECKED" : "";
  echo "    <td><input type=CHECKBOX $isChecked name='cb_taskIncident' id='cb_taskIncident'>".
       T_("Incident")."</input></td>\n";
  echo "    <td><input size='100' type='text' name='task_incident'  id='task_' value='$task_incident'></td>\n";
  echo "  </tr>\n";
  echo "  <tr>\n";
  $isChecked = $isTaskTools ? "CHECKED" : "";
  echo "    <td><input type=CHECKBOX $isChecked name='cb_taskTools' id='cb_taskTools'>".
       T_("Tools")."</input></td>\n";
  echo "    <td><input size='100' type='text' name='task_tools'  id='task_tools' value='$task_tools'></td>\n";
  echo "  </tr>\n";
  echo "  <tr>\n";
  $isChecked = $isTaskOther ? "CHECKED" : "";
  echo "    <td><input type=CHECKBOX $isChecked name='cb_taskOther' id='cb_taskOther'>".
       T_("Other")."</input></td>\n";
  echo "    <td><input size='100' type='text' name='task_other1'  id='task_other1' value='$task_other1'></td>\n";
  echo "  </tr>\n";
  echo "</table>\n";
  echo "</li>\n";
  echo "</ul>\n";
  
  echo "</div>\n"; # display:none
  
  
  echo "  <br/>\n";
  echo "  <br/>\n";
  echo "<input type=button value='".T_("Create")."' onClick='javascript: addTeam()'>\n";
  
  echo "<input type=hidden name=action      value=noAction>\n";
  echo "<input type=hidden name=is_modified value=$is_modified>\n";
  
  echo("</form>\n");
}







// ================ MAIN =================

global $admin_teamid;
global $defaultSideTaskProject;
global $sideTaskProjectType;

$cat_projManagement = T_("Project Management");
$cat_incident       = T_("Incident");
$cat_tools          = T_("Tools");
$cat_other            = T_("Other");

$defaultSideTaskProjectName = T_("SideTasks")." my_team";

$link = mysql_connect($db_mantis_host, $db_mantis_user, $db_mantis_pass) 
  or die(T_("Could not connect to database"));
mysql_select_db($db_mantis_database) or die(T_("Could not select database"));


// ---- if not codev admin then stop now.
// REM: who is allowed to create a new team ? anyone ?
#$session_user = new User($_SESSION['userid']);
#if (false == $session_user->isTeamDeveloper($admin_teamid)) {
#  echo ("Sorry, you need to be Codev Administrator to access this page.");
#  exit;
#}

$action      = $_POST[action];
$is_modified = isset($_POST[is_modified]) ? $_POST[is_modified] : "false";


// Form user selections
$team_name = isset($_POST[team_name]) ? $_POST[team_name] : "";
$team_desc = isset($_POST[team_desc]) ? $_POST[team_desc] : "";
$teamleader_id = isset($_POST[teamleader_id]) ? $_POST[teamleader_id] : "";

if ("false" == $is_modified) {
   $isCreateSTProj       = true;
   $isCatIncident        = true;
   $isCatTools           = true;
   $isCatOther           = true;
   $isTaskProjManagement = true;
   $isTaskMeeting        = true;
   $isTaskIncident       = false;
   $isTaskTools          = false;
   $isTaskOther          = false;
   
   $stproj_name = $defaultSideTaskProjectName;
   
} else {
   $isCreateSTProj       = $_POST[cb_createSideTaskProj];
   $isCatIncident        = $_POST[cb_catIncident];
   $isCatTools           = $_POST[cb_catTools];
   $isCatOther           = $_POST[cb_catOther];
   $isTaskProjManagement = $_POST[cb_taskProjManagement];
   $isTaskMeeting        = $_POST[cb_taskMeeting];
   $isTaskIncident       = $_POST[cb_taskIncident];
   $isTaskTools          = $_POST[cb_taskTools];
   $isTaskOther          = $_POST[cb_taskOther];
      
   $stproj_name = ("" == $team_name) ? $defaultSideTaskProjectName : T_("SideTasks")." $team_name";        	
}

$task_projManagement = isset($_POST[task_projManagement]) ? $_POST[task_projManagement] : T_("(generic) Project Management");
$task_meeting = isset($_POST[task_meeting]) ? $_POST[task_meeting] : T_("(generic) Meeting");
$task_incident = isset($_POST[task_incident]) ? $_POST[task_incident] : T_("(generic) Dev Platform is down");
$task_tools = isset($_POST[task_tools]) ? $_POST[task_tools] : T_("(generic) Compilation Scripts");
$task_other1 = isset($_POST[task_other1]) ? $_POST[task_other1] : T_("(generic) Internal Support");


#unset($_SESSION['teamid']);

if ("addTeam" == $action) {
	echo T_("Create")." $team_name !<br/>";
	
	
	$formatedDate  = date("Y-m-d", time());
	$now = date2timestamp($formatedDate);

	// TODO check if Team name exists !
   // TODO check if SideProject name exists !
	
	// 1) --- create new Team
   Team::create($team_name, $team_desc, $teamleader_id, $now);

   $teamid = Team::getIdFromName($team_name);
   echo "teamId = $teamid !<br/>";
   
   
   
   if (-1 != $teamid) {
   	
      $team = new Team($teamid);
   	
      // 2) --- add default SideTaskProject
      $team->addCommonSideTaskProject();
      
	   
      if ($isCreateSTProj) {
         
      	// 3) --- add <team> SideTaskProject
      	$stproj_id = $team->createSideTaskProject($stproj_name);
      	if ($stproj_id < 0) {
      		die ("ERROR: SideTaskProject creation FAILED.<br/>\n");
      	} else {
            $stproj = new Project($stproj_id);
      	
            // 4) --- add SideTaskProject Categories
      	   $stproj->addCategoryProjManagement($cat_projManagement);
	     
      	   if ($isCatIncident) {
               $stproj->addCategoryIncident($cat_incident);
	         }
            if ($isCatTools) {
               $stproj->addCategoryTools($cat_tools);
            }
            if ($isCatOther) {
               $stproj->addCategoryDoc($cat_other);
            }
        
            // 5) --- add SideTaskProject default SideTasks
            if ($isTaskProjManagement) {
        	      $stproj->addIssueProjManagement($task_projManagement);
            }
            if ($isTaskMeeting) {
               $stproj->addIssueProjManagement($task_meeting);
            }
            if ($isTaskIncident) {
               $stproj->addIssueIncident($task_incident);
            }
            if ($isTaskTools) {
               $stproj->addIssueTools($task_tools);
            }
            if ($isTaskOther) {
               $stproj->addIssueOther($task_other1);
            }
            
            // 6) --- open EditTeam Page
            $_SESSION['teamid'] = $teamid;
            echo ("<script> parent.location.replace('./edit_team.php'); </script>"); 
      	}
      }
      
   }
   
} elseif ("editTeam" == $action) {
   echo T_("Create and go to Edit")."<br/>";
   //echo ("<script> parent.location.replace('./edit_team.php'); </script>");

}else if ("noAction" == $action) {
    echo "browserRefresh<br/>";
} # else {
	
	// display form
   displayCreateTeamForm($team_name, $teamleader_id, $team_desc,
                         $isCreateSTProj, $stproj_name,
                         $isCatIncident, $isCatTools, $isCatOther,
                         $isTaskProjManagement, $isTaskMeeting, $isTaskIncident, $isTaskTools, $isTaskOther,
                         $task_projManagement, $task_meeting, $task_incident, $task_tools, $task_other1,
                         $is_modified);
#}


?>
</div>

<?php include 'footer.inc.php'; ?>

<?php
/*
* Copyright (C) 2011 OpenSIPS Project
*
* This file is part of opensips-cp, a free Web Control Panel Application for
* OpenSIPS SIP server.
*
* opensips-cp is free software; you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation; either version 2 of the License, or
* (at your option) any later version.
*
* opensips-cp is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with this program; if not, write to the Free Software
* Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
*/

require("../../../../config/db.inc.php");
require("template/header.php");
require("lib/".$page_id.".main.js");
require("../../../../config/tools/admin/list_admins/db.inc.php");
require("../../../../config/tools/admin/list_admins/local.inc.php");
include("lib/db_connect.php");
require("../../../../config/globals.php");
require_once("../../../common/cfg_comm.php");

$table=$config->table_list_admins;
$current_page="current_page_list_admins";

if (isset($_POST['action'])) $action=$_POST['action'];
else if (isset($_GET['action'])) $action=$_GET['action'];
else $action="";

if (isset($_GET['page'])) $_SESSION[$current_page]=$_GET['page'];
else if (!isset($_SESSION[$current_page])) $_SESSION[$current_page]=1;

#################
# start add new #
#################

if ($action=="add")
{
        extract($_POST);
        if(!$_SESSION['read_only'])
        {
                require("template/".$page_id.".add.php");
                require("template/footer.php");
                exit();
        }else {
                $errors= "User with Read-Only Rights";
        }

}

#################
# end add new   #
#################


#################
# start add new #
#################
if ($action=="add_verify")
{
  if(!$_SESSION['read_only']){
	  require("lib/".$page_id.".test.inc.php");
	  if ($form_valid) {
                if ($config->admin_passwd_mode==0) {
                	$ha1  = '';
                        $add_passwd = $_POST['add_passwd'];
                } else if ($config->admin_passwd_mode==1) {
                        $ha1 = md5($add_uname.":".$_POST['add_passwd']);
                        $add_passwd = '';
                }


		$sql = 'INSERT INTO '.$table.' (last_name,first_name,username,password,ha1) VALUES '. 
			' (?, ?, ?, ?, ?)';
                $stm = $link->prepare($sql);
		if ($stm === false) {
			die('Failed to issue query ['.$sql.'], error message : ' . print_r($link->errorInfo(), true));
		}
		$stm->execute( array($add_lname, $add_fname, $add_uname, $add_passwd, $ha1) );

		$lname=NULL;
		$fname=NULL;
		$uname=NULL;
		$passwd=NULL;
		$confirm_passwd=NULL;
	}
	  if ($form_valid) {
		print "New Admin added!";
		$action="add";
	  } else {
		print $form_error;
		$action="add_verify";
	  }

 } else {
 	$errors= "User with Read-Only Rights";
	}
}
###############
# end add new #
###############


##############
# start edit #
##############
if ($action=="edit")
{

	if(!$_SESSION['read_only']){

		extract($_POST);

		require("template/".$page_id.".edit.php");
		require("template/footer.php");
		exit();
	}else{
		$errors= "User with Read-Only Rights";
	}
}
##############
# end edit   #
##############

#################
# start modify	#
#################
if ($action=="modify")
{
  if(!$_SESSION['read_only']){
	  $id = $_GET['id'];
          require("lib/".$page_id.".inc.php");
	  $listuname = $_POST['listuname'];	
	  $listfname = $_POST['listfname'];	
	  $listlname = $_POST['listlname'];	
          if ($form_valid) {
	  	if (($_POST['listpasswd']=="") || ($_POST['conf_passwd']=="")) {

			$sql = "UPDATE ".$table." SET username=?, first_name=?, last_name=? WHERE id=?";
                	$stm = $link->prepare($sql);
			if ($stm === false) {
				die('Failed to issue query ['.$sql.'], error message : ' . print_r($link->errorInfo(), true));
			}
			$stm->execute( array($listuname,$listfname,$listlname,$id) );
			print "Admins info was modified, but password remained the same!\n";

		} else if (($_POST['listpasswd']!="") && ($_POST['conf_passwd']!="")) {
			if ($config->admin_passwd_mode==0) {
				$ha1  = "";
				$listpasswd = $_POST['listpasswd'];	
			} else if ($config->admin_passwd_mode==1) {
				$ha1 = md5($listuname.":".$_POST['listpasswd']);
				$listpasswd = '';	
			}

			$sql = "UPDATE ".$table." SET username=?, first_name=?, last_name=?, password=?, ha1=? WHERE id=?";
                	$stm = $link->prepare($sql);
			if ($stm === false) {
				die('Failed to issue query ['.$sql.'], error message : ' . print_r($link->errorInfo(), true));
			}
			$stm->execute( array($listuname,$listfname,$listlname,$listpasswd,$ha1,$id) );
			print "Admin's info was modified!\n";
		}

		$link->disconnect();
	   }
          if ($form_valid) {
                $action="edit";
          } else {
                print $form_error;
                $action="modify";
          }

  } else {
          $errors= "User with Read-Only Rights";
         }

}
#################
# end modify 	#
#################

####################
# start edit tools #
####################
if ($action=="edit_tools")
{

        //if(!$_SESSION['read_only']){

                extract($_POST);

                require("template/".$page_id.".edit_tools.php");
                require("template/footer.php");
                exit();
        //}else{
           //     $errors= "User with Read-Only Rights";
         //} 
}
##################
# end edit tools #
##################


######################
# start modify tools #
######################
if ($action=="modify_tools")
{
  if(!$_SESSION['read_only']){
	extract($_POST);
	$id = $_GET['id'];
	$uname = $_GET['uname'];
	$perm = "";
	$tool = "";
        $modules=get_modules();
	$state=$_POST['state'];
 	foreach($modules['Admin'] as $key=>$value ){
		$permissionKey = "permission_$key";
		//if (!empty($_POST["$permissionKey"])) {
			$perms[$key] = $_POST["$permissionKey"];
		//}
	}	
 	foreach($modules['Users'] as $key=>$value ){
		$permissionKey = "permission_$key";
		//if (isset($_POST["$permissionKey"])) {
			$perms[$key] = $_POST["$permissionKey"];
		//}
	}		
 	foreach($modules['System'] as $key=>$value ){
		$permissionKey = "permission_$key";
		//if (isset($_POST["$permissionKey"])) {
			$perms[$key] = $_POST["$permissionKey"];
		//}
	}
	$modules_nr = count($modules['Admin'])+count($modules['Users'])+count($modules['System']);
	if($modules_nr==count($state)) {
		$tools="all";
		if (!in_array('read-only',$perms)) {
			$permiss="all";
		} else {	
			foreach ($state as $key=>$val)
			{
				foreach($perms as $k=>$v)	
				if ($key==$k) {
					$perm .= $perms[$key].",";
				}
			
			}
			$permiss=substr($perm,0,-1);
		}	
	} else if (count($state)>0 && count($state)<$modules_nr) {
		foreach ($state as $key=>$val)
		{
			foreach($perms as $k=>$v)	
				if ($key==$k) {
					$perm .= $v.",";
					$tool .= $key.",";
			}
			
		}
		$tools=substr($tool,0,-1);
		$permiss=substr($perm,0,-1);
	} else if (count($state)==0) {
		$tools = "";
		$permiss = "";
	}
        $sql = "UPDATE $table SET available_tools=?, permissions=?  WHERE id=?";
      	$stm = $link->prepare($sql);
	if ($stm === false) {
		die('Failed to issue query ['.$sql.'], error message : ' . print_r($link->errorInfo(), true));
	}
	$stm->execute( array($tools,$permiss,$id) );
        $info="Admin credentials were modified";

                $link->disconnect();
  } else {
          $errors= "User with Read-Only Rights";
         } 


}

####################
# end modify tools #
####################


################
# start delete #
################
if ($action=="delete")
{
	if(!$_SESSION['read_only']){

		$id = $_GET['id'];

		$sql = "DELETE FROM ".$table." WHERE id=?";
      		$stm = $link->prepare($sql);
		if ($stm === false) {
			die('Failed to issue query ['.$sql.'], error message : ' . print_r($link->errorInfo(), true));
		}
		$stm->execute( array($id) );
	}else{

		$errors= "User with Read-Only Rights";
	}
}
##############
# end delete #
##############


################
# start search #
################
if ($action=="dp_act")
{

	$_SESSION['list_id']=$_POST['list_id'];

	$_SESSION[$current_page]=1;
	extract($_POST);
	if ($show_all=="Show All") {
		$_SESSION['list_uname']="";
		$_SESSION['list_fname']="";
		$_SESSION['list_lname']="";
	} else if($search=="Search"){
		$_SESSION['list_uname']=$_POST['list_uname'];
		$_SESSION['list_fname']=$_POST['list_fname'];
		$_SESSION['list_lname']=$_POST['list_lname'];
	} 
}
##############
# end search #
##############

##############
# start main #
##############

require("template/".$page_id.".main.php");
if($errors)
echo('!!! ');echo($errors);
require("template/footer.php");
exit();

##############
# end main   #
##############
?>

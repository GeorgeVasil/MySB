<?php
// ----------------------------------
require  '/etc/MySB/web/inc/includes_before.php';
// ----------------------------------
//  __/\\\\____________/\\\\___________________/\\\\\\\\\\\____/\\\\\\\\\\\\\___        
//   _\/\\\\\\________/\\\\\\_________________/\\\/////////\\\_\/\\\/////////\\\_       
//    _\/\\\//\\\____/\\\//\\\____/\\\__/\\\__\//\\\______\///__\/\\\_______\/\\\_      
//     _\/\\\\///\\\/\\\/_\/\\\___\//\\\/\\\____\////\\\_________\/\\\\\\\\\\\\\\__     
//      _\/\\\__\///\\\/___\/\\\____\//\\\\\________\////\\\______\/\\\/////////\\\_    
//       _\/\\\____\///_____\/\\\_____\//\\\____________\////\\\___\/\\\_______\/\\\_   
//        _\/\\\_____________\/\\\__/\\_/\\\______/\\\______\//\\\__\/\\\_______\/\\\_  
//         _\/\\\_____________\/\\\_\//\\\\/______\///\\\\\\\\\\\/___\/\\\\\\\\\\\\\/__ 
//          _\///______________\///___\////__________\///////////_____\/////////////_____
//			By toulousain79 ---> https://github.com/toulousain79/
//
//#####################################################################
//
//	Copyright (c) 2013 toulousain79 (https://github.com/toulousain79/)
//	Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:
//	The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.
//	THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT.
//	IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
//	--> Licensed under the MIT license: http://www.opensource.org/licenses/mit-license.php
//
//#################### FIRST LINE #####################################

// Users table
$database = new medoo();

if(isset($_POST)==true && empty($_POST)==false) {
	$success = true;
	
	if (isset($_POST['add_tracker'])) {
		$count = count($_POST['input_id']);
		
		for($i=1; $i<=$count; $i++) {
			$last_id_trackers_list = $database->insert("trackers_list", [
																			"tracker" => $_POST['tracker_domain'][$i],
																			"tracker_domain" => $_POST['tracker_domain'][$i],
																			"origin" => "users",
																			"is_active" => $_POST['is_active'][$i]
																		]);	
																		
			if (!isset($last_id_trackers_list)) {
				$success = false;
			}																		
		}
	}

	if (isset($_POST['delete'])) {
		$count = count($_POST['delete']);
		
		foreach($_POST['delete'] as $key => $value) {
			$result = $database->delete("trackers_list", [
				"AND" => [
					"id_trackers_list" => $key
				]
			]);
			
			if ( $result = 0 ) {
				$success = false;
			}			
		}
	}
	
	if ( $success == true ) {
		?><script type="text/javascript">generate_message('success', 'Success !!');</script><?php
	} else {
		?><script type="text/javascript">generate_message('error', 'Failed !');</script><?php
	}		
}

$TrackersList = $database->select("trackers_list", "*", ["origin" => "users", "ORDER" => "trackers_list.tracker_domain ASC"]);
?>

<div align="center" style="margin-top: 10px; margin-bottom: 20px;">
	<form id="myForm" class="form_settings" method="post" action="">
		<div id="input1" class="clonedInput">
			<input class="input_id" id="input_id" name="input_id[1]" type="hidden" value="1" />
			Domain: <input class="input_tracker_domain" id="tracker_domain" name="tracker_domain[1]" type="text" required="required" />
			Is active ?:	<select class="select_is_active" id="is_active" name="is_active[1]" style="width:60px; cursor: pointer;" required="required">
								<option value="0" selected="selected">No</option>
								<option value="1">Yes</option>
							</select>
		</div>
	 
		<div style="margin-top: 10px; margin-bottom: 20px;">
			<input type="button" id="btnAdd" value="Add tracker domain" style="cursor: pointer;" />
			<input type="button" id="btnDel" value="Remove last" style="cursor: pointer;" />
		</div>
		
		<input class="submit" style="width:150px; margin-top: 10px; margin-bottom: 10px;" name="add_tracker" type="submit" value="Add my trackers now !">
	</form>	
</div>	

<form class="form_settings" method="post" action="">	
	<div align="center">
	
		<table style="border-spacing:1;">
			<tr>
				<th style="text-align:center;">Domain</th>
				<th style="text-align:center;">Address</th>
				<th style="text-align:center;">Origin</th>
				<th style="text-align:center;">IPv4</th>
				<th style="text-align:center;">SSL ?</th>
				<th style="text-align:center;">Active ?</th>
				<th style="text-align:center;">Delete ?</th>
			</tr>						
				
<?php
foreach($TrackersList as $Tracker) {
	switch ($Tracker["is_ssl"]) {
		case '0':
			$is_ssl = '	<select name="is_ssl[]" style="width:60px; background-color:#FEBABC;" disabled>
							<option value="0" selected="selected">No</option>
						</select>';
			break;		
		default:
			$is_ssl = '	<select name="is_ssl[]" style="width:60px; background-color:#B3FEA5;" disabled>
							<option value="1" selected="selected">Yes</option>
						</select>';
			break;
	}
	
	switch ($Tracker["is_active"]) {
		case '0':
			$is_active = '	<select name="is_active[]" style="width:60px; cursor: pointer; background-color:#FEBABC;">
								<option value="0" selected="selected">No</option>
								<option value="1">Yes</option>
							</select>';
			break;		
		default:
			$is_active = '	<select name="is_active[]" style="width:60px; cursor: pointer; background-color:#B3FEA5;">
								<option value="0">No</option>
								<option value="1" selected="selected">Yes</option>
							</select>';
			break;
	}
?>				
			<tr>
				<td>
					<input style="width:150px;" type="hidden" name="tracker_domain[]" value="<?php echo $Tracker["tracker_domain"]; ?>" />
					<?php echo $Tracker["tracker_domain"]; ?>
				</td>
				<td>
					<input style="width:180px;" type="hidden" name="tracker[]" value="<?php echo $Tracker["tracker"]; ?>" />
					<?php echo $Tracker["tracker"]; ?>
				</td>
				<td>
					<input style="width:60px;" type="hidden" name="origin[]" value="<?php echo $Tracker["origin"]; ?>" />
					<?php echo $Tracker["origin"]; ?>
				</td>					
				<td>
					<select style="width:140px;">
<?php
						foreach(array_map('trim', explode(" ",$Tracker["ipv4"])) as $IPv4) {					
							echo '<option>' .$IPv4. '</option>';
						}
?>								
					</select>
				</td>
				<td>
					<?php echo $is_ssl; ?>	
				</td>
				<td>
					<?php echo $is_active; ?>	
				</td>
				<td>
					<input class="submit" name="delete[<?php echo $Tracker["id_trackers_list"]; ?>]" type="submit" value="Delete" />
				</td>					
			</tr>
<?php
} // foreach($TrackersList as $Tracker) {
?>			

		</table>
		
		<input class="submit" style="width:120px; margin-top: 10px;" name="submit" type="submit" value="Save Changes">
	</div>
</form>

<script type="text/javascript" src="<?php echo THEMES_PATH; ?>MySB/js/jquery-dynamically-adding-form-elements.js"></script>	
	
<?php
// -----------------------------------------
require  '/etc/MySB/web/inc/includes_after.php';
// -----------------------------------------
//#################### LAST LINE ######################################
?>
	
<?php
// -----------------------------------------
require  '/etc/MySB/web/inc/includes_after.php';
// -----------------------------------------
//#################### LAST LINE ######################################
?>
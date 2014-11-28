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

function Form() {
	$database = new medoo();
	// Users table
	$renting_datas = $database->select("renting", "*", ["id_renting" => 1]);
	
	$TotalUsers = CountingUsers();
	$PricePerUser = 0;
	$Model = '';
	$TVA = '';
	$GlobalCost = '';

	if ( (isset($renting_datas["model"])) && (isset($renting_datas["tva"])) && (isset($renting_datas["global_cost"])) ) {
		$Model = $renting_datas["model"];
		$TVA = $renting_datas["model"];
		$GlobalCost = $renting_datas["global_cost"];
	}
	
	echo '<form class="form_settings" method="post" action="">
		<div align="center"><table border="0">	
			<tr>
				<td>Model :</td>
				<td><input class="text_normal" name="model" type="text" value="' . $Model . '" required="required" /></td>
				<td><span class="Comments">Example:	Serveur Dedibox XC</span></td>
			</tr>
			<tr>
				<td>TVA (%)  :</td>
				<td><input class="text_small" name="tva" type="text" value="' . $TVA . '" required="required" /></td>
				<td><span class="Comments">Example:	20</span></td>
			</tr>
			<tr>
				<td>Unit price (per month)   :</td>
				<td><input class="text_small" name="global_cost" type="text" value="' . $GlobalCost . '" required="required" /></td>
				<td><span class="Comments">Example:	19.99 (value without tax)</span></td>
			</tr>
			<tr>
				<td>Total users   :</td>
				<td><input class="text_extra_small" readonly="readonly" type="text" value="' . $TotalUsers . '" /></td>
				<td><span class="Comments">Readonly, only for information.</span></td>
			</tr>
			<tr>
				<td>Price per user   :</td>
				<td><input class="text_extra_small" readonly="readonly" type="text" value="' . $PricePerUser . '" /></td>
				<td><span class="Comments">Readonly, only for information.</span></td>
			</tr>					
			<tr>
				<td colspan="3"><input class="submit" name="submit" type="submit" value="Submit" /></td>
			</tr>						
		</table></div>
	</form>';

}

if (isset($_POST['submit'])) {	
	$Model = $_POST['model'];
	$TVA = $_POST['tva'];
	$GlobalCost = $_POST['global_cost'];
	$TotalUsers = CountingUsers();
	
	if ( (isset($GlobalCost)) && (isset($TVA)) && (isset($TotalUsers)) && (isset($Model)) ) {
		$X = $GlobalCost / $TotalUsers;
		$Y = ($X * $TVA) / 100;
		$PricePerUsers = $X + $Y;
		$PricePerUsers = ceil($PricePerUsers);
	
		$database = new medoo();
		
		$exist = $database->get("renting", "id_renting", ["id_renting" => 1]);
echo '94 '.$exist.'<br>';		
		if ( ( $exist != 0 ) && (isset($TVA)) ) {
			$result = $database->update("renting", ["model" => "$Model",
										"tva" => "$TVA",
										"global_cost" => "$GlobalCost",
										"nb_users" => "$TotalUsers",
										"price_per_users" => "$PricePerUsers"],
										["id_renting" => 1]);

			echo '103 '.$result.'<br>';
		} else {
			$result = $database->insert("renting", ["model" => "$Model",
													"tva" => "$TVA",
													"global_cost" => "$GlobalCost",
													"nb_users" => "$TotalUsers",
													"price_per_users" => "$PricePerUsers"]);	
													
			echo '111 '.$result.'<br>';
		}
 
		Form();
		

			
/* 		if( $result != 0 ) {	
			if( $result = 1 ) {
				echo '<span class="Successful">Successfull 1 !</span>';
			} else {
				echo '<span class="Failed">Failed !</span>';
			}
													
		} else {
			echo '<p class="Successful">Successfull 2 !</p>';
		} */
		
		
	} else {
		Form();
	
		echo '<span class="Failed">Please, complete all fields.</span>';
	}
} else {
	Form();
}

// -----------------------------------------
require  '/etc/MySB/web/inc/includes_after.php';
// -----------------------------------------
//#################### LAST LINE ######################################
?>
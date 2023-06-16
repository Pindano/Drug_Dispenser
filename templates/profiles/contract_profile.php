<?php
/* ********************************************************************************************** *
 *
 * HEADING: contract_profile.php: Renders HTML required to display all details for specic 
 *          Contract Profile ID        
 * AUTHOR : Barasa Michael Murunga
 * EMAIL  : michael.barasa@strathmore.edu
 * NOTES  : User Sessions: The program utilizes session management as its core functionality, 
 *          allowing for seamless interaction throughout the application.
 *
 *          Efficient Data Retrieval: The program efficiently retrieves essential data from the 
 *          database and dynamically populates the relevant HTML elements. This ensures up-to-date 
 *          information is displayed to users, enhancing the overall user experience.
 *
 *          Access Control: The program incorporates robust access control measures, restricting 
 *          access to authorized individuals such as administrators, supervisors, pharmacists, and 
 *          pharmaceutical personnel. 
 *          Furthermore, it employs granular access controls to limit specific sections of the 
 *          application, bolstering accountability and security.
 *
 *          Enhanced User Interface: The program leverages the power of CSS3 and JavaScript to 
 *          enhance the visual appearance and interactivity of the application. This results in a 
 *          polished and modern user interface that offers a seamless and engaging user experience.
 *
 * ********************************************************************************************** */
require_once('../connect.php');
require_once('../config.php');

session_start();

/* ---------------------------------------------------------------------------------------------- *
 *            ALLOW ACCESS TO ADMINISTRATOR, SUPERVISOR, PHARMACY AND PHARMACEUTICAL              *
 * ---------------------------------------------------------------------------------------------- */
if ($_SESSION['role'] == 'patient' || $_SESSION['role'] == 'practitioner')
{
	http_response_code(403);
	header("Location: ../templates/errors/403.php");
	exit;
}

/* ---------------------------------------------------------------------------------------------- *
 *                             ENSURE ALL LINK PARAMETERS PROVIDED                                *
 * ---------------------------------------------------------------------------------------------- */
if (!$_GET['contractId'])
{
	header("Location: ../templates/errors/invalid_access.php");
	exit;
}
$contractId = $_GET['contractId'];
$role = $_SESSION['role'];

/* ---------------------------------------------------------------------------------------------- *
 *                            RETRIEVE RELEVANT RECORDS FROM DATABASE                             *
 * ---------------------------------------------------------------------------------------------- */
$databaseHandler = new DatabaseHandler($dsn, $username, $password);
$databaseHandler->connect();

$contract_query = "SELECT c.contractId, c.startDate, c.endDate, c.pharmaceuticalId, " .
	"c.pharmacyId, pharmacy.title as pharmacy_title, pharmaceutical.title as " .
	"pharmaceutical_title FROM contract as c RIGHT OUTER JOIN pharmaceutical " .
	"USING (pharmaceuticalId) RIGHT OUTER JOIN pharmacy USING (pharmacyId) " .
	"WHERE c.contractId = $contractId";

$supplies_query = "SELECT cs.contractSupplyId, cs.dateCreated, cs.lastUpdated, " .
	"SUM(si.costPrice * si.quantity) as costPrice, sum(si.sellingPrice * si.quantity) " .
	"as sellingPrice, (SUM(si.sellingPrice * si.quantity) - SUM(si.costPrice * si.quantity)) " .
	"as profit, cs.paymentComplete FROM contract_supply as cs JOIN supply_item as si USING " .
	"(contractSupplyId) WHERE cs.contractId = $contractId GROUP BY cs.contractSupplyId, " .
	"cs.dateCreated, cs.lastUpdated";

$contract = $databaseHandler->selectQuery($contract_query);
$supplies_items = $databaseHandler->selectQuery($supplies_query);
$databaseHandler->disconnect();

// Extract current contract record
$contract = $contract[0];

/* ---------------------------------------------------------------------------------------------- *
 *                                      SET PAGE TITLE                                            *
 * ---------------------------------------------------------------------------------------------- */
$title = "Contract Profile | Contract ID " . $contract['contractId'];


/* ---------------------------------------------------------------------------------------------- *
 *                       RECORDS OF ALL SUPPLIES FOR CURRENT CONTRACT                             *
 * ---------------------------------------------------------------------------------------------- */
$supplies_table_data = null;
$unique_id = 1;
foreach ($supplies_items as $item) {
	$supplies_table_data .= <<<_HTML
		<tr>
		<td>{$item['contractSupplyId']}</td>
		<td id = "dateCreated{$unique_id}">{$item['dateCreated']}</td>
		<td>{$item['costPrice']}</td>
		<td>{$item['sellingPrice']}</td>
		<td>{$item['profit']}</td>
		<td id = "paymentStatus{$unique_id}">{$item['paymentComplete']}</td>
		<td id = "lastUpdated{$unique_id}">{$item['lastUpdated']}</td>
		<td id = "action{$unique_id}">
		<a href = "confirm_supply_payment.php?contractSupplyId={$item['contractSupplyId']}" class = "btn btn-success">
		Confirm
		</a>
		</td>
		</tr>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>
		<script>
			var dateCreated = document.getElementById("dateCreated{$unique_id}");
			var lastUpdated = document.getElementById("lastUpdated{$unique_id}");
			var paymentStatus = document.getElementById("paymentStatus{$unique_id}");
			var action = document.getElementById("action{$unique_id}");
			
			dateCreated.innerText = moment(dateCreated.innerText).format(
				'dddd MMMM D, YYYY h:mm A');
			
			lastUpdated.innerText = moment(lastUpdated.innerText).format(
				'dddd MMMM D, YYYY h:mm A');

			if (paymentStatus.innerText == 1)
			{
				paymentStatus.innerText = "Complete";
				paymentStatus.style.color = "green";
			}
			else if (paymentStatus.innerText == 0)
			{
				paymentStatus.innerText = "Pending";
				paymentStatus.style.color = "red";
			}

			if ({$role} != 'administrator' || {$role} != 'supervisor')
			{
				action.innerText = "";
			}
		</script>
		_HTML;
	$unique_id += 1;
}

/* ---------------------------------------------------------------------------------------------- *
 *                          ACTUAL HTML CONTENT TO BE SENT TO BASE.PHP                            *
 * ---------------------------------------------------------------------------------------------- */
$content = <<<_HTML
	<!---------------------------------- CSS STYLESHEETS -------------------------------------->
	<link href = "../bootstrap.min.css" rel = "stylesheet">
	<link href = "static/css/contract_profile.css" rel = "stylesheet">
	<!---------------------------------- CONTRACT DETAILS ------------------------------------->
	<div class = "list-group">
	<div class = "list-group-item">
	<div style = "padding-top: 10px; padding-bottom: 10px; padding-left: 30px;">
	<ul class = "list-unstyled lead">
	<li><span class = "explanation">Contract ID:</span> {$contract['contractId']}</li>
	<li>
	<span class = "explanation">Pharmaceutical:</span> {$contract['pharmaceutical_title']}
	</li>
	<li><span class = "explanation">Pharmacy:</span> {$contract['pharmacy_title']}</li>
	<li>
	<span class = "explanation">Start Date:</span>
	<span id = "startDate">{$contract['startDate']}</span>
	</li>
	<li>
	<span class = "explanation">End Date:</span>
	<span id = "endDate">{$contract['endDate']}</span>
	</li>
	<li>
	<span class = "explanation">Period:</span>
	<span id = "period"></span>
	</li>
	</ul>
	<a class = "btn btn-primary btn-pill">Update Contract Details</a>
	</div>
	</div>
	</div>
	<!---------------------------- LINK TO CREATE NEW SUPPLY INSTANCE ------------------------->
	<div class = "new-supply">
	<a href = "../registration/register_contract_supply.php" class = "btn btn-primary btn-pill">
	Create New Supply Instance
	</a>
	</div>
	<!---------------------------------- SUPPLIES TABLE --------------------------------------->
	<div class = "list-group" style = "margin-top: 3%;">
	<div class = "list-group-item items-table">
	<table class = "table table-responsive table-hover table-striped">
	<thead class = "thead">
	<tr>
	<th>Supply ID</th>
	<th>Date Created</th>
	<th>Cost Price</th>
	<th>Selling Price</th>
	<th>Profit</th>
	<th>Payment</th>
	<th>Last Updated</th>
	<th>Action</th>
	</tr>
	</thead>
	<tbody>
	$supplies_table_data
	</tbody>
	</table>
	</div>
	</div>
	<!---------------------------------- JAVASCRIPT AND JQUERY -------------------------------->
	<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js">
	</script>
	<script>
		var startDate = document.getElementById("startDate");
		var endDate = document.getElementById("endDate");
		var period = document.getElementById("period");
		
		period.innerText = moment(endDate.innerText).format('YYYY') - moment(
			startDate.innerText).format('YYYY') + " year(s)";
		startDate.innerText = moment(startDate.innerText).format(
			'dddd MMMM D, YYYY') + ' (' + moment(startDate.innerText).fromNow() + ')';
			
		endDate.innerText = moment(endDate.innerText).format(
			'dddd MMMM D, YYYY') + ' (' + moment(endDate.innerText).fromNow() + ')';
	</script>
	_HTML;

require_once('../templates/base.php')
?>

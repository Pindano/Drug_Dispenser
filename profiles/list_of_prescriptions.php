<?php
require_once("../connect.php");
require_once("../config.php");

session_start();

/* ---------------------------------------------------------------------------------------------- *
 *                              ALLOW ADMINISTRATOR AND PHARMACY ACCESS                           *
 * ---------------------------------------------------------------------------------------------- */
if ($_SESSION['role'] != 'administrator' && $_SESSION['role'] != 'pharmacy')
{
	header("Location: ../templates/errors/403.php");
	exit;
}

if ($_SESSION['role'] == 'pharmacy') 
{
	$pharmacyId = $_SESSION['pharmacyId'];
}
else if ($_SESSION['role'] == 'administrator')
{
	$pharmacyId = $_GET['pharmacyId'];
}
/* ---------------------------------------------------------------------------------------------- *
 *                            RETRIEVE PRESCRIPTION RECORDS FROM DATABASE                         *
 * ---------------------------------------------------------------------------------------------- */
$dbHandler = new DatabaseHandler($dsn, $username, $password);
$dbHandler->connect();
$prescriptions_query = "SELECT p.prescriptionId, d.practitionerId, d.firstName as " .
	"practitionerFirstName, d.middleName as practitionerMiddleName, d.lastName as " .
	"practitionerLastName, pt.firstName as patientFirstName, pt.middleName as " .
	"patientMiddleName, pt.lastName as patientLastName, p.frequency, p.quantity, " .
	"p.assigned, p.dateCreated, p.lastUpdated, pt.patientId, " .
	"si.tradename, si.supplyItemId FROM prescription as p RIGHT OUTER JOIN " .
	"patient_practitioner USING (patientPractitionerId) RIGHT OUTER JOIN practitioner as d " .
	"USING (practitionerId) RIGHT OUTER JOIN supply_item as si USING (supplyItemId) " .
	"RIGHT OUTER JOIN contract_supply USING (contractSupplyId) RIGHT OUTER JOIN contract " .
	"USING (contractId) RIGHT OUTER JOIN pharmacy USING (pharmacyId) " .
	"RIGHT OUTER JOIN patient as pt USING (patientId) WHERE pharmacy.pharmacyId = " .
	":pharmacyId ORDER BY p.dateCreated";

$prescriptions = $dbHandler->selectQuery($prescriptions_query, ['pharmacyId' => $pharmacyId]);
$dbHandler->disconnect();

/* ---------------------------------------------------------------------------------------------- *
 *                                       DISPLAY HEADING OF PAGE                                  *
 * ---------------------------------------------------------------------------------------------- */
$content = <<<_HTML
	<div>
	<link rel = 'stylesheet' href = '../bootstrap.min.css'>
	<h3 style = "color = green;" class = "page-header">Prescription Assignments</h3>
	_HTML;

/* ---------------------------------------------------------------------------------------------- *
 *                                DISPLAY  PRESCRIPTIONS IN TABLE                                 *
 * ---------------------------------------------------------------------------------------------- */
$prescriptions_table_data = null;
$unique_id = 1;
foreach ($prescriptions as $prescription)
{
	$prescriptions_table_data .= <<<_HTML
		<tr>
		<td>{$prescription['prescriptionId']}</td>
		<td>
		{$prescription['practitionerFirstName']} 
		{$prescription['practitionerMiddleName']} 
		{$prescription['practitionerLastName']}
		</td>
		<td>
		{$prescription['patientFirstName']} 
		{$prescription['patientMiddleName']} 
		{$prescription['patientLastName']}
		</td>
		<td>{$prescription['tradename']}</td>
		<td>{$prescription['quantity']}</td>
		<td>{$prescription['frequency']}</td>
		<td id = "assigned-{$unique_id}">{$prescription['assigned']}</td>
		<td id = "dateCreated-{$unique_id}">
		{$prescription['dateCreated']}
		</td>	
		<td id = "lastUpdated-{$unique_id}">
		{$prescription['lastUpdated']}
		</td>
		<td>
		<a href = "assign_prescription.php?patientId={$prescription['patientId']}" class = "btn btn-success" id = "assign-btn-{$unique_id}">
		Assign
		</a>
		</td>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>
		<script>
			var dateCreated = document.getElementById("dateCreated-" + $unique_id);
			var lastUpdated = document.getElementById("lastUpdated-" + $unique_id);
			var assigned = document.getElementById("assigned-" + $unique_id);
			var assign_btn = document.getElementById("assign-btn-" + $unique_id);
		
			dateCreated.innerText = moment(dateCreated.innerText).format(
				'dddd MMMM D, YYYY h:mm A');
			
			lastUpdated.innerText = moment(lastUpdated.innerText).format(
				'dddd MMMM D, YYYY h:mm A');

			if (assigned.innerText == 1)
			{
				assigned.innerText = "Assigned";
				assigned.style.color = "green";
				assign_btn.removeAttribute("href");
				assign_btn.onclick = function(event) {
					event.preventDefault();
				};
				assign_btn.innerText = "Disabled";
				assign_btn.classList.remove("btn-success");
				assign_btn.classList.add("btn-danger");
			}
			else if (assigned.innerText == 0)
			{
				assigned.innerText = "Pending";
				assigned.style.color = "red";
			}	
		</script>
		_HTML;
	$unique_id += 1;
}

$content .= <<<_HTML
	<table class = 'table table-striped table-responsive table-hover'>
	<thead>
	<tr>
	<th>Id</th>
	<th>Practitioner</th>
	<th>Patient</th>
	<th>Drug</th>
	<th>Quantity</th>
	<th>Frequency</th>
	<th>Assigned</th>
	<th>Date</th>
	<th>Last Updated</th>
	<th>Action</th>
	</tr>
	</thead>
	<tbody>
	$prescriptions_table_data
	</tbody>
	</table>
	</div>
	</div>
	_HTML;

/* ---------------------------------------------------------------------------------------------- *
 *                                       DISPLAY HEADING OF PAGE                                  *
 * ---------------------------------------------------------------------------------------------- */
$title = "Pharmacy Profile ID " . $_SESSION['pharmacyId'] . " - Assigned Prescriptions";

require_once('../templates/base.php');
?>

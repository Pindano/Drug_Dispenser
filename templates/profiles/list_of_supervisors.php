<?php
require_once("../connect.php");
require_once("../config.php");

session_start();

/* ---------------------------------------------------------------------------------------------- *
 *                                      ALLOW ADMINISTRATOR ACCESS                                *
 * ---------------------------------------------------------------------------------------------- */
if ($_SESSION['role'] != 'administrator')
{
	http_response_code(403);
	header("Location: ../templates/errors/403.php");
	exit;
}

/* ---------------------------------------------------------------------------------------------- *
 *                              RETRIEVE SUPERVISOR RECORDS FROM DATABASE                         *
 * ---------------------------------------------------------------------------------------------- */
$dbHandler = new DatabaseHandler($dsn, $username, $password);
$dbHandler->connect();
$result = $dbHandler->selectQuery('SELECT * FROM supervisor');
$dbHandler->disconnect();

/* ---------------------------------------------------------------------------------------------- *
 *                                       DISPLAY HEADING OF PAGE                                  *
 * ---------------------------------------------------------------------------------------------- */
$content = <<<_HTML
	<div>
	<link rel = 'stylesheet' href = '../bootstrap.min.css'>
	<h3 style = "color = green;" class = "page-header">List Of Supervisors</h3>
	_HTML;

/* ---------------------------------------------------------------------------------------------- *
 *                                    DISPLAY SUPERVISORS IN TABLE                                *
 * ---------------------------------------------------------------------------------------------- */
$content .= <<<_HTML
	<table class = 'table table-striped table-responsive table-hover'>
	<thead>
	<tr>
	<th>Supervisor ID</th>
	<th>Name</th>
	<th>Email Address</th>
	<th>Phone Number</th>
	<th>Status</th>
	</tr>
	</thead>
	<body>
	_HTML;

foreach ($result as $row)
{
	$content .= <<<_HTML
		<tr>
		<td>
		<a href = "supervisor_profile.php?supervisorId={$row['supervisorId']}">
		{$row['supervisorId']}
		</a>
		</td>
		<td>
		<a href = "supervisor_profile.php?supervisorId={$row['supervisorId']}">
		{$row['firstName']} {$row['middleName']} {$row['lastName']}
		</a>
		</td>
		<td>
		<a href = "mailto: {$row['emailAddress']}">
		{$row['emailAddress']}
		</a>
		</td>
		<td>
		<a href = "tel: {$row['phoneNumber']}">
		{$row['phoneNumber']}
		</a>
		</td>
		_HTML;

	if ($row['active'] == 1)
	{
		$content .= <<<_HTML
			<td style = "color: green;">Active</td>
			_HTML;
	}
	else
	{
		$content .= <<<_HTML
			<td style = "color: green;">Active</td>
			_HTML;
	}
	$content .= <<<_HTML
		</tr>
		_HTML;
}

$content .= <<<_HTML
	</body>
	</table>
	</div>
	_HTML;

/* ---------------------------------------------------------------------------------------------- *
 *                                       DISPLAY HEADING OF PAGE                                  *
 * ---------------------------------------------------------------------------------------------- */
$title = "List of Supervisors";

require_once('../templates/base.php');
?>

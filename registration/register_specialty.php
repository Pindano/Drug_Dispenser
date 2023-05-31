<?php

require_once "forms.php";
require_once "views.php";

if ($_SERVER["REQUEST_METHOD"] === "POST")
{
	handleRegisterSpecialtyFormSubmission();
	header("Location: templates/main/homepage.php");
	exit;
}

# Set page header
$content = <<<_HTML
	<div class = "page-header">
	<h3>Specialty Registration</h3>
	</div>
	_HTML;

# retrieve specialty registration form
ob_start();
renderRegisterSpecialtyForm();
$content .= ob_get_clean();

# set page title
$title = "Specialty Registration";

include "../templates/base.php";
?>

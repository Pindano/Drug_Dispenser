<?php

require_once "forms.php";
require_once "views.php";

if ($_SERVER["REQUEST_METHOD"] === "POST")
{
	handleRegisterSupervisorFormSubmission();
}

ob_start();
renderRegisterSupervisorForm();

$content = ob_get_clean();
$title = "Patient Login";

include "../templates/base.php";
?>

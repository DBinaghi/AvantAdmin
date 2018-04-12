<?php
$downForMaintenance = get_option('avantadmin_maintenance');
if (!$downForMaintenance)
{
    // If the user refreshes this maintenance page after the site has come back online, take them to the dashboard.
    $redirector = Zend_Controller_Action_HelperBroker::getStaticHelper('redirector');
    $redirector->gotoUrl(WEB_ROOT . '/admin/dashboard');
}

$pageTitle = __('Maintenance');
echo head(array('title' => $pageTitle));

$html = "<h3>The Digital Archive is temporarily down for maintenance</h3>";
$html .= "<p>We apologize for the inconvenience. Please come back in a little while.</p>";
echo $html;
echo foot();



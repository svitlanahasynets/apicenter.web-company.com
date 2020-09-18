<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
| -------------------------------------------------------------------------
| URI ROUTING
| -------------------------------------------------------------------------
| This file lets you re-map URI requests to specific controller functions.
|
| Typically there is a one-to-one relationship between a URL string
| and its corresponding controller class/method. The segments in a
| URL normally follow this pattern:
|
|   example.com/class/method/id/
|
| In some instances, however, you may want to remap this relationship
| so that a different class/function is called than the one
| corresponding to the URL.
|
| Please see the user guide for complete details:
|
|   http://codeigniter.com/user_guide/general/routing.html
|
| -------------------------------------------------------------------------
| RESERVED ROUTES
| -------------------------------------------------------------------------
|
| There area two reserved routes:
|
|   $route['default_controller'] = 'welcome';
|
| This route indicates which controller class should be loaded if the
| URI contains no data. In the above example, the "welcome" class
| would be loaded.
|
|   $route['404_override'] = 'errors/page_missing';
|
| This route will tell the Router what URI segments to use if those provided
| in the URL cannot be matched to a valid route.
|
*/

$route['login'] = "login";
$route['login/(:any)'] = "login/$1";
$route['login/login'] = "login/login";
$route['dologin/(:any)/(:any)/(:any)'] = "doLogin/index/$1/$2/$3";

$route['default_controller'] = "pages";
$route['404_override'] = '';

if ($route['default_controller'] == "pages") {
    $route['projects'] = "projects/index";

    // Logs section start
    $route['logs'] = "logs/index/metrics";
    $route['metrics'] = "logs/index/metrics";
    $route['invoices'] = "logs/index/invoices";
    $route['afas'] = "logs/index/afas";
    $route['salesentries'] = "logs/index/salesentries";
    $route['customers'] = "logs/index/customers";
    $route['products'] = "logs/index/products";
    $route['orders'] = "logs/index/orders";
    $route['shipments'] = "logs/index/shipments";
    $route['exact'] = "logs/index/exact";
    $route['custom_module'] = "logs/index/custom_module";
    $route['optiply'] = "logs/index/optiply";
    $route['optiply_buyorder'] = "logs/index/optiply_buyorder";
    $route['optiply_sellorder'] = "logs/index/optiply_sellorder";
    $route['optiply_suppliers'] = "logs/index/optiply_suppliers";
    $route['optiply_return'] = "logs/index/optiply_return";
    $route['admindebugging'] = "logs/index/admindebugging";
    // Logs section end

    // Admin section start
    $route['whmcsauth'] = "whmcsAuth/index";
    $route['whmcsauth/(:any)'] = "whmcsAuth/index/$1";
    
    $route['permissions'] = "permissions/index";
    $route['integration'] = "integrations/index";
    $route['settings'] = "integrationSettings/index";
    $route['schedule'] = "schedule/index";
    $route['features'] = "features/index";
    $route['manual-sync'] = "manualsync/index";
    $route['integration-settings/edit/id/(:num)'] = "integrationSettings/edit/$1";
    // Admin section end
    
    $route['projectList'] = "projects/projectList";
    $route['message-center'] = "messages/index";

    $route['admin-overview'] = "admin/overview";
    $route['admin-settings'] = "admin/settings";
    $route['admin-test-api-connection'] = "admin/testApiConnection";
    $route['admin-maintenance'] = "admin/maintenance";
    $route['admin-remove-tmp-files'] = "admin/remove_tmp_files";

    $route['admin-sendmessage'] = "admin/sendMessage";
    $route['partner-overview'] = "partner/overview";

    $route['authorize-eaccounting'] = "authorize/eaccounting";
}

$route['switch-language']['post'] = "switchLang/switch_language";
$route['get-projects'] = "projects/getProjects";
$route['get-dashboard-parameters'] = "pages/getDashboardParameters";

$route['sidevar-menu-update'] = "pages/sidevarMenuUpdate";
$route['set-project-id'] = "projects/setCurrentProjectId";
$route['notification-update'] = "pages/notificationUpdate";



/* End of file routes.php */
/* Location: ./application/config/routes.php */
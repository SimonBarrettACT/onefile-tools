<?php
defined('BASEPATH') OR exit('No direct script access allowed');

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
|	example.com/class/method/id/
|
| In some instances, however, you may want to remap this relationship
| so that a different class/function is called than the one
| corresponding to the URL.
|
| Please see the user guide for complete details:
|
|	http://codeigniter.com/user_guide/general/routing.html
|
| -------------------------------------------------------------------------
| RESERVED ROUTES
| -------------------------------------------------------------------------
|
| There are three reserved routes:
|
|	$route['default_controller'] = 'welcome';
|
| This route indicates which controller class should be loaded if the
| URI contains no data. In the above example, the "welcome" class
| would be loaded.
|
|	$route['404_override'] = 'errors/page_missing';
|
| This route will tell the Router which controller/method to use if those
| provided in the URL cannot be matched to a valid route.
|
|	$route['translate_uri_dashes'] = FALSE;
|
| This is not exactly a route, but allows you to automatically route
| controller and method names that contain dashes. '-' isn't a valid
| class or method name character, so it requires translation.
| When you set this option to TRUE, it will replace ALL dashes in the
| controller and method URI segments.
|
| Examples:	my-controller/index	-> my_controller/index
|		my-controller/my-method	-> my_controller/my_method
*/
$route['default_controller'] = 'welcome';
$route['404_override'] = '';
$route['translate_uri_dashes'] = TRUE;

/*
| -------------------------------------------------------------------------
| REST API Routes
| -------------------------------------------------------------------------
*/

$route['api/(:any)/classroom']                      = 'api/classroom';
$route['api/(:any)/classroom/(:num)']               = 'api/classroom/id/$2';
$route['api/(:any)/classroom/search']               = 'api/classroom/search';
$route['api/(:any)/classroom/search/(:num)']        = 'api/classroom/search/$2';
$route['api/(:any)/classroom/search/(:num)/(:num)'] = 'api/classroom/search/$2/$3';

$route['api/(:any)/customer']                       = 'api/customer';

$route['api/(:any)/debug/paths']                        = 'api/debug/paths';

$route['api/(:any)/organisation/(:num)']                = 'api/organisation/id/$2';
$route['api/(:any)/organisation/search']                = 'api/organisation/search';
$route['api/(:any)/organisation/search/(:num)']         = 'api/organisation/search/$2';
$route['api/(:any)/organisation/search/(:num)/(:num)']  = 'api/organisation/search/$2/$3';
$route['api/(:any)/organisation/(:num)/learningaimstatuses'] = 'api/organisation/learningaimstatuses/$2';
$route['api/(:any)/organisation/(:num)/learnerstatuses'] = 'api/organisation/learnerstatuses/$2';
$route['api/(:any)/organisation/(:num)/assignedstandards'] = 'api/organisation/assignedstandards/$2';

$route['api/(:any)/placement']                      = 'api/placement';
$route['api/(:any)/placement/(:num)']               = 'api/placement/id/$2';
$route['api/(:any)/placement/search']               = 'api/placement/search';
$route['api/(:any)/placement/search/(:num)']        = 'api/placement/search/$2';
$route['api/(:any)/placement/search/(:num)/(:num)'] = 'api/placement/search/$2/$3';

$route['api/(:any)/plan/(:num)']                  = 'api/plan/id/$2';
$route['api/(:any)/plan/search']                  = 'api/plan/search';
$route['api/(:any)/plan/search/(:num)']           = 'api/plan/search/$2';
$route['api/(:any)/plan/search/(:num)/(:num)']    = 'api/plan/search/$2/$3';

$route['api/(:any)/provider']                      = 'api/provider';
$route['api/(:any)/provider/(:num)']               = 'api/provider/id/$2';
$route['api/(:any)/provider/search']               = 'api/provider/search';
$route['api/(:any)/provider/search/(:num)']        = 'api/provider/search/$2';
$route['api/(:any)/provider/search/(:num)/(:num)'] = 'api/provider/search/$2/$3';

$route['api/(:any)/review']                         = 'api/review';
$route['api/(:any)/review/(:num)']                  = 'api/review/id/$2';
$route['api/(:any)/review/search']                  = 'api/review/search';
$route['api/(:any)/review/search/(:num)']           = 'api/review/search/$2';
$route['api/(:any)/review/search/(:num)/(:num)']    = 'api/review/search/$2/$3';

$route['api/(:any)/standard/(:num)/assign/(:num)']  = 'api/standard/assign/$2/$3';
$route['api/(:any)/standard/(:num)']                = 'api/standard/id/$2';
$route['api/(:any)/standard/search']                = 'api/standard/search';
$route['api/(:any)/standard/search/(:num)']         = 'api/standard/search/$2';
$route['api/(:any)/standard/search/(:num)/(:num)']  = 'api/standard/search/$2/$3';

$route['api/(:any)/unit/(:any)/assign/(:num)/(:num)'] = 'api/unit/assign/$2/$3/$4';
$route['api/(:any)/unit/(:any)']                      = 'api/unit/id/$2';
$route['api/(:any)/unit/search']                      = 'api/unit/search';
$route['api/(:any)/unit/search/(:num)']               = 'api/unit/search/$2';
$route['api/(:any)/unit/search/(:num)/(:num)']        = 'api/unit/search/$2/$3';

$route['api/(:any)/user']                           = 'api/user';
$route['api/(:any)/user/(:num)']                    = 'api/user/id/$2';
$route['api/(:any)/user/(:num)/assign']             = 'api/user/assign/$2';
$route['api/(:any)/user/(:num)/unitsummary']        = 'api/user/unitsummary/$2';

/*
| -------------------------------------------------------------------------
| REST Job Routes
| -------------------------------------------------------------------------
*/

// e.g. job/vX0aPh6MfG/show/reviews
$route['job/(:any)/(:any)/(:any)'] = 'jobs/$2/$3';

<?php
/**
 * Create your routes in here. The name is the lowercase name of the controller
 * without the controller part, the stuff after the hash is the method.
 * e.g. page#index -> OCA\FederalTimeline\Controller\PageController->index()
 *
 * The controller class has to be registered in the application.php file since
 * it's instantiated in there
 */
return [
    'routes' => [
	   ['name' => 'page#index', 'url' => '/', 'verb' => 'GET'],
	   ['name' => 'page#do_echo', 'url' => '/echo', 'verb' => 'POST'],
	   ['name' => 'timeline_api#index', 'url' => '/api/1.0/timeline', 'verb' => 'GET'],
	   ['name' => 'timeline_api#upload_file', 'url' => '/api/1.0/file', 'verb' => 'POST'],
	   ['name' => 'timeline_api#download_file', 'url' => '/api/1.0/file', 'verb' => 'GET'],
    ]
];

<?php

use OCP\AppFramework\App;

$app = new App('federaltimeline');
$container = $app->getContainer();

// $container->registerService('PageController', function($c) {
//     return new PageController(
//     	$c->query('AppName'),
//         $c->query('Request'),
//         $c->query('UserId'),
//         $c->query('ServerContainer'));
// });




$container->query('OCP\INavigationManager')->add(function () use ($container) {
	$urlGenerator = $container->query('OCP\IURLGenerator');
	$l10n = $container->query('OCP\IL10N');
	return [
		// the string under which your app will be referenced in Nextcloud
		'id' => 'federaltimeline',

		// sorting weight for the navigation. The higher the number, the higher
		// will it be listed in the navigation
		'order' => 10,

		// the route that will be shown on startup
		'href' => $urlGenerator->linkToRoute('federaltimeline.page.index'),

		// the icon that will be shown in the navigation
		// this file needs to exist in img/
		'icon' => $urlGenerator->imagePath('federaltimeline', 'app.svg'),

		// the title of your application. This will be used in the
		// navigation or on the settings page of your app
		'name' => $l10n->t('Federal Timeline'),
	];
});

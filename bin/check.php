#!/usr/bin/php
<?php


require __DIR__.'/../vendor/autoload.php';

$loop = React\EventLoop\Factory::create();
$factory = new React\Dns\Resolver\Factory();
$resolver = $factory->create('8.8.8.8', $loop);
$conn_factory = new React\Whois\ConnectionFactory($loop);
$whois_client = new React\Whois\Client($resolver, $conn_factory);

$wisdom = new Wisdom\Wisdom($whois_client);

$domains = array(
	'google.com',
	'yahoo.com',
	'apple.com',
	'microsoft.com',
	'amazon.com',
	'reactphp.org',
	'stevemeyers.net',
	'invaliddomain195435.com',
);


if (isset($argv[1]) && $argv[1] === "--single") {
	echo "Checking domains one at a time...\n\n";
	foreach ($domains as $domain) {
		$wisdom->check($domain)->then(function ($available) use ($domain) {
			printf("Domain %s is %s.\n", $domain, $available ? 'available' : 'taken');
		});
	}
} else {
	// This currently gives bogus results, as the mapping from domain to result isn't applied correctly
	// https://github.com/umpirsky/wisdom/pull/17
	echo "Checking domains all at once...\n\n";
	$wisdom->checkAll($domains)->then(function ($statuses) {
		foreach ($statuses as $domain => $available) {
			printf("Domain %s is %s.\n", $domain, $available ? 'available' : 'taken');
		}
	});
}

$loop->run();


echo "\n\nDone!\n";

<?php

require_once '../vendor/autoload.php';
require_once '../SlimTokenAuth.php';
require_once '/etc/freepbx.conf';
session_start();
define('FREEPBX_IS_AUTH', 1);

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

$app = new \Slim\App;

$app->add(new SlimTokenAuth());

$app->get('/cdr/{uniqueid}', function(Request $request, Response $response, array $args) use ($app) {
	$dbh = \FreePBX::Database();
	//error_log();
	$post_data = $request->getParsedBody();
	$route = $request->getAttribute('route');
	$uniqueid = $route->getArgument('uniqueid');
        $sth = $dbh->prepare('SELECT * FROM asteriskcdrdb.cdr WHERE uniqueid = ? OR linkedid = ?');
	$sth->execute([$uniqueid,$uniqueid]);
	$res = $sth->fetchAll(\PDO::FETCH_ASSOC);
	return $response->withJson($res,200);
});

$app->run();


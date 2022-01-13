<?php

/*
 * Copyright (C) 2021 Nethesis S.r.l.
 * http://www.nethesis.it - nethserver@nethesis.it
 *
 * This script is part of NethServer.
 *
 * NethServer is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License,
 * or any later version.
 *
 * NethServer is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with NethServer.  If not, see COPYING.
 */

class SlimTokenAuth
{
    private $token;

    /**
     * @throws \RuntimeException
     */
    public function __construct() {
	include_once '/etc/freepbx_db.conf';
	$sql = 'SELECT `val` FROM `kvstore_FreePBX_modules_Apicall` WHERE `key` = "token"';
	$sth = $db->prepare($sql);
	$sth->execute();
	$this->token = $sth->fetchAll()[0][0];
	if (empty($this->token)) throw new \RuntimeException('Apicall autentication token not configured', 1640251616);
    }

    public function __invoke($request, $response, $next)
    {
        if ($request->isOptions()) {
            $response = $next($request, $response);
        } elseif ($request->hasHeader('token')) {
            if($request->getHeaderLine('token') === $this->token) {
		// Correct authentication
		$response = $next($request, $response);
            } else {
                $results = array(
                    'title' => 'Access to resource is forbidden with current client privileges',
                    'detail' => 'Invalid client credentials'
                );
                $response = $response->withJson($results, 403);
                $response = $response->withHeader('Content-Type', 'application/problem+json');
                $response = $response->withHeader('Content-Language', 'en');
            }
        } else {
            $results = array(
                'title' => 'Access to resource is forbidden with current client privileges',
                'detail' => 'Missing authentication token',
            );
            $response = $response->withJson($results, 403);
            $response = $response->withHeader('Content-Type', 'application/problem+json');
            $response = $response->withHeader('Content-Language', 'en');
        }
        return $response;
    }
}


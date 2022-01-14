<?php

require_once '../vendor/autoload.php';
require_once '../SlimTokenAuth.php';

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

$app = new \Slim\App;

$app->add(new SlimTokenAuth());

$app->post('/aibot', function(Request $request, Response $response, array $args) use ($app) {
	$post_data = $request->getParsedBody();
	$user_text = $post_data['UserAnswerText'];
	error_log(print_r($post_data,1));
	foreach (json_decode($post_data['UserAnswerAlternatives'], $associative = true) as $alternative) {
		$text = $alternative['transcript'];
		error_log($text);
		if (strtolower($text) == "esci") {
			$result = array(
				"Language"=>"it_IT",
				"GoToDestination"=>"Hanghup,s,1",
				"MessageText"=>"Test terminato. Ciao ciao.",
			);
		} elseif (strtolower($text) == "lista") {
			$result = array(
				"Language"=>"it_IT",
                        	"UserInputMethod"=>"voice",
	                        "UserAnswerSTT"=>1,
        	                "MessageText"=>"I comandi disponibili sono: esci, per terminare il test. Attesa, per mettere questa chiamata in attesa per sempre. Codice, per sperimentare l'inserimento di un codice numerico tramite la tastiera. Codice vocale, per sperimentare l'inserimento di un codice tramite il riconoscimento vocale. Lista, per avere la lista dei comandi possibili",
                	        "NextMessageWebhookUrl"=>"https://makako.nethesis.it/aibot/aibot",
	                );
		} elseif (strtolower($text) == "attesa") {
			$result = array(
				"Language"=>"it_IT",
				"GoToDestination"=>"app-blackhole,musiconhold,1",
				"MessageText"=>"La chiamata verrà messa in attesa per sempre. Addio.",
        	        );
		} elseif (strtolower($text) == "codice") {
			$result = array(
				"Language"=>"it_IT",
                        	"UserInputMethod"=>"digits",
				"MessageText"=>"Inserisci un codice seguito dal tasto cancelletto",
				"MessageExitDigit" => "0123456789",
				"EndDigit" => "#",
				"NextMessageWebhookUrl"=>"https://makako.nethesis.it/aibot/code"
        	        );
		} elseif (strtolower($text) == "codice vocale") {
                        $result = array(
                                "Language"=>"it_IT",
                                "UserInputMethod"=>"voice",
                                "EndOfSpeakSilenceLength"=>1.5,
				"UserAnswerSTT"=>1,
                                "MessageText"=>"Pronuncia il codice",
                                "NextMessageWebhookUrl"=>"https://makako.nethesis.it/aibot/code"
                        );      
                }
	}
	if (empty($result)) {
		$result = array(
			"Language"=>"it_IT",
			"UserInputMethod"=>"voice",
			"UserAnswerSTT"=>1,
			"MessageText"=>$user_text,
			"NextMessageWebhookUrl"=>"https://makako.nethesis.it/aibot/aibot",
		);
	}
	error_log(json_encode($result));
	return $response->withJson($result,200);
});

$app->post('/code', function(Request $request, Response $response, array $args) use ($app) {
	$post_data = $request->getParsedBody();
	error_log(print_r($post_data,1));
	if (!empty($post_data['PressedDigits'])) {
		$answer = "Il codice inserito è:".implode(' ',str_split($post_data['PressedDigits']));
	} elseif (!empty($post_data['UserAnswerText'])) {
		$answer = "Il codice pronunciato è:".implode(' ',str_split($post_data['UserAnswerText']));
	} else {
		$answer = "Errore! codice non riconosciuto";
	}
	$result = array(
			"Language"=>"it_IT",
                        "UserInputMethod"=>"voice",
                        "UserAnswerSTT"=>1,
                        "MessageText"=>$answer,
                        "NextMessageWebhookUrl"=>"https://makako.nethesis.it/aibot/aibot",
                );
	error_log(json_encode($result));
	return $response->withJson($result,200);
});

$app->post('/status', function(Request $request, Response $response, array $args) use ($app) {
	$post_data = $request->getParsedBody();
	error_log('Called /status api');
	error_log(print_r($post_data,1));
	return $response->withStatus(200);
});

$app->run();

# API Call 

This FreePBX module allows to originate calls from FreePBX using Rest API.

## Install
Install googletts and apicall modules on FreePBX >= 14. Google tts require a valid google API key. Used only if message parameter is setted

### On NethVoice
- googletts is already insalled
- to install apicall, just download release .tar.gz file in /usr/src/nethvoice/modules/apicall.tar.gz and launch nethserver-nethvoice14-update event:
```
wget https://github.com/Stell0/apicall-freepbx/archive/refs/heads/main.tar.gz -O /usr/src/nethvoice/modules/apicall.tar.gz
signal-event nethserver-nethvoice-14-update
```

### On vanilla FreePBX

Use module admin interface to install [GoogleTTS module](https://github.com/nethesis/googletts) And this one.
At the moment, those modules will give the missing signature error on FreePBX interface. I'm not going to fix this in a near future because I find it boring and useless since you can check code by yourself. 

## Usage
call api
curl 'https://HOST/FREEPBX_WEB_ROOT/apicall/index.php' -H 'token: TOKEN' -H 'Content-Type: application/json;charset=utf-8' --data '{"tocall": "200"}'

TOKEN is generated at module installation and you can find it in FreePBX Api Call module page

POST data are passed in JSON format

tocall: extension or external number to call (mandatory)

message: text to play with Google TTS. It requires GoogleTTS https://github.com/nethesis/googletts FreePBX module installed

destination: FreePBX destination for the call. Default is app-blackhole,hangup,1

language: default it

voice: google tts voice to use

maxretires: number of time to retry if exension isn't available. Default 0

retrytime: default 60

waittime: default 30

callerid: caller id of the originated call. Defaul 999

# AI bot

This part allows to launch a call, give a message to the user when they answer and allow them to give back an answer both using their voice or dial on pad
The message given to the user could be an mp3 audio file or a text that will be converted to speech using google TTS.
The user answer can be saved as an mp3 file and retrived, or converted to text also using google cloud speech
After the message to the user has been given and the user gave back his feedback, a webook is called. The webook should return additional information to progress with the interaction or send the call to another dialplan destination.

## Installation
install required package lame and bcmath for php `yum install -y lame rh-php56-php-bcmath`
If you whant to use TTS and STT, you need to have it enabled on your google account and save credentials in /home/asterisk/google-auth.json

## Start call
POST https://${HOST}/freepbx/apicall/aibot.php

Authentication header: static token taken from  https://${HOST}/freepbx/admin/config.php?display=apicall

## Input parameters:
ContactId: optional - will be present into the webhooks calls

CampaignId: optional - will be present into the webhooks calls

PhoneNumber: mandatory - the phone number to call

Language: optional it_IT|en_US|... - language to use for STT and TTS. If not setted, channel language is used.

MessageUrl: optional - URL of an mp3 file that will be played when called answers

MessageText: optional - Text that will be converted into speech when called answers

UserInputMethod: optional - [voice|digits] how the called user is expected to insert input: voice record answer and return text converted by Google Speech to Text, digits for keypad pressed

EndOfSpeakSilenceLength - optional - length of silence to wait before sending the user answer. Default is 0.5 s

MessageExitDigit: ''|123456789\*# stop playing message and exit if one of digits is pressed. Usefull if UserInputMethod is digits, this interrupt the message that is beeing played.

NuberOfExpectedDigits: optional - end process and call webook after this number of digits are pressed by called user

EndDigit: optional - end process and call webook after this digit has been pressed (for instance user is asked to insert his code and then press #)

UserAnswerSTT: optional - if true, convert user answer to text using Google Speech to Text

CallStatusWebhookUrl: send status response to this url

CallStatusWebhookHeader: add this header to call status webhook

GoToDestination: if setted, go to this destination into Asterisk dialplan instead of hanghup call if no NextMessageWebhookUrl is given. (for instance, "app-blackhole,musiconhold,1" to put the call on hold forever)

NextMessageWebhookUrl: the webhook where results are posted. The result of webook should be a json, with same parameters as this API and is executed

NextMessageWebhookHeader: optional - header to add to the NextMessageWebhookUrl POST



Response is JSON and is sent to NextMessageWebhookUrl if it is setted, CallStatusWebhookUrl otherwise. It has:

ContactId: same ContactId given in POST

CampaignId: same given in POST

UniqueID: Asterisk UniqueID, same used in cdr

LinkedID: Asterisk LinkedID, used for transfered calls 

CallerIDNum: phone number of the caller

CallerIDName: name of the caller (if it is resolved)

ConnectedLineIDNum: phone number of the called

ConnectedLineIDName:name of the called (if it is resolved)

PressedDigits: if UserInputMethod is "digits", it contains digit pressed by the user

UserAnswerMp3Url: if UserInputMethod is "voice", it contains mp3 audio of user response

UserAnswerText: if UserInputMethod is "voice", it contains (more likely guess of) text of user response

UserAnswerAlternatives: if UserInputMethod is "voice", it contains all possible guess text of user response with probability



## Example:
With this curl, extension 201 is called and the text in MessageText is played using TTS. The user reply is then sent to https://${HOST}/apicall/demo-bot.php/aibot where the demo Slim application answers and interact with the user.

`curl -kv "https://$(hostname)/freepbx/apicall/aibot.php" -H 'token: f18cd60d1838ae65bf9df31bff79ed46' -H 'Content-Type: application/json;charset=utf-8' --data '{"PhoneNumber": "201","Language":"it_IT","UserInputMethod":"voice","GoToDestination":"Hanghup,s,1","MessageText":"Questo è un test echo, quello che dici verrà compreso e ripetuto. Alcune parole sono comandi speciali. Pronuncia. lista. Per avere la lista dei comandi disponibili.","UserAnswerSTT":"1","NextMessageWebhookUrl":"https://'$(hostname)'/apicall/demo-bot.php/aibot","NextMessageWebhookHeader":"token: f18cd60d1838ae65bf9df31bff79ed46"}'`

*Note: this is still in development and should not be used in production*

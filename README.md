# API Call 

This FreePBX module allows to originate calls from FreePBX using Rest API.

## Install
Install googletts and apicall modules on FreePBX >= 14. Google tts require a valid google API key. Used only if message parameter is setted

## Usage
call api
curl 'https://HOST/FREEPBX_WEB_ROOT/apicall/' -H 'token: TOKEN' -H 'Content-Type: application/json;charset=utf-8' --data '{"tocall": "200"}'

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

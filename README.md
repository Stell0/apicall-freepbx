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

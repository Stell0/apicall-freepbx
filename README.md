# API Call 

This FreePBX module allows to originate calls from FreePBX using Rest API.
It requires GoogleTTS https://github.com/nethesis/googletts FreePBX module installed

At the moment it is just a PoC, authentication and any kind of security are missing, don't use it in production!


## Install
Install googletts and apicall modules on FreePBX >= 14. Google tts require a valid google API key. Used only if message parameter is setted

## Usage
call api
curl -k 'https://HOST/freepbx/apicall/index.php?tok=123412346346234332&tocall=NUMBER_TO_CALL&message=MESSAGE_TO_PLAY_WITH_TTS'

other parameters:
destination: FreePBX destination for the call. Default is app-blackhole,hangup,1
language: default it
voice: google tts voice


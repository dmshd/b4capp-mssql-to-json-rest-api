# b4capp-mssql-to-json-rest-api

The react-native application source code relative to this php api is located and visible here : https://bitbucket.org/danielmuyshond/fetch-data2/src/master/
The big majority of the code I wrote is located in ./app/auth-flow/screens

I published this code for my "portfolio". For eventual recruiters or future colleagues so they can have a look on what I did or tried to do.

That can certainly be improved in many ways but keep in mind that I started from zero with very basic PHP experience and no knowledge about how to handle the requests or structure the files. 

Please note that all table path names in the sql request are normally replaced by something like "_censored_" because I don't want to bring easy informations about the database structure to eventual individuals who would want to break something...

### connect.php

contains the pdo connection (Normally you will not find the real credentials) and is imported in each files when needed.

### get_news.php  

is used to fetch the club activities in the "Activités" section of the react-native app.

### get_PPH.php  

is used to get the data about the individuals and their profile picture url if they have one, if not I put a standard picture url.
The request is send from the "Membres" section of the react-native app.

### get_profile_info.php

is used to fetch member details. The fetch is located in the MbrDetail.js screen in the react-native app.

### info.php 

just contains a phpinfo();

### placeholder300200.png

is an image used when the activities are displaying and there is no cover pic


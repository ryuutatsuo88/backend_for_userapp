Userapp AngularJS PHP Backend
=====

So long story short I loved the simplicity of Userapp.io user authentication system they build, they had a AngularJS
library I was looking for, I however did not want to use their backend. So I set out to use their open source front-end code 
and build my own backend that works with it. 

I used Slim PHP Framework for the backend and build all the routes. I used PHP UserCake for the User Managment system. 

Configuration
=============

1. .htaccess file in the v1 directory, change the rewritebase to what you need it to be

2. db-settings.php configure the the DB values 

3. create a DB from the SQL dump 

4. set baseaddress and secure in app.js


Completed 
=============
1. token.heartbeat service 
2. user.logout
3. user.login
4. user.get
5. user.save
6. user.verifyEmail
7. JSONP

Todo
=============
1. Social OAuth 
2. implement reset and change password
3. Clean up PHP to return error messages in all possible error scenarios


Issues
=============
1. There is a bug with cors calls


Things Used to build this
=======================================

[Slim PHP Framework](http://www.slimframework.com/)
[Usercake](http://usercake.com/)
[UserApp Javascript](https://github.com/userapp-io/userapp-javascript)
[UserApp AngularJS](https://github.com/userapp-io/userapp-angular)
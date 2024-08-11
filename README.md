# Dokuwiki redirectssl plugin  - Redirect actions (esp. Login) to HTTPS

Redirect the login page to https:// connection if it is not already that way. In addition to login, you may select any other action (e.g., "admin") to also be redirected to HTTPS. When you install this plugin, the actions to be redirected is empty by default. You can select the actions you want to redirect to HTTPS in the plugin configuration page. 


**Before you make any changes to the configuration, make sure your site has HTTPS working. You can do that by changing the URL address in your browser from http:// to https:// if it is not already https.**

Such redirection is typically done in server configuration, but if it hasn't been done, this plugin will make sure actions involving sensitive information will take place over HTTPS.

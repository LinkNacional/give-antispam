=== Antispam Donation for GiveWP ===
Contributors: linknacional
Donate link: https://www.linknacional.com/wordpress/plugins/
Tags: spam, antispam, givewp, give, donation
Requires at least: 5.5
Tested up to: 6.7
Stable tag: 1.4.1
Requires PHP: 7.2
License: GPLv3 or later
License URI: https://www.gnu.org/licenses/gpl-3.0.html

Performs verification and prevention of malicious donations.

== Description ==

The [Antispam Donation for GiveWP](https://www.linknacional.com/wordpress/plugins/) is an extension plugin for GiveWP which aims to protect donation forms against card testing and spam donations.


**Dependencies**

Antispam Donation for GiveWP plugin is dependent on GiveWP plugin, please make sure GiveWP is installed and properly configured before starting Antispam Donation for GiveWP installation.


**Third party APIs usage**

This plugin integrates with Google reCAPTCHA to prevent spam submissions. It uses the [Google reCAPTCHA API](https://www.google.com/recaptcha/api.js) to validate user interactions. For more details, see the [Google Privacy Policy](https://policies.google.com/privacy) and [Terms of Service](https://policies.google.com/terms).


**User instructions**

1. Now go to the Settings menu of the GiveWP;

2. Select the 'Access Control' option;

3. Look for the option 'Enable spam donation protection' and enable it;

4. Click on the 'Save changes' button;

5. New options will appear, you can leave the default values or change to something that fits your needs; 

6. Click on the 'Save changes' button;

7. If you have Recaptcha V3 access keys and want to enable it, look for the option 'Recaptcha donation form' and enable it;

8. Click on the 'Save changes' button;

9. New fields will appear, now enter your Recaptcha V3 credentials;

10. Click on the 'Save changes' button;

The Antispam Donation for GiveWP is now live and working.


== Installation ==

1. Look in the sidebar for the WordPress plugins area;

2. In installed plugins look for the 'add new' option in the header;

3. Click on the 'submit plugin' option in the page title and upload the give-antispam.zip plugin;

4. Click on the 'install now' button and then activate the installed plugin;

The Antispam Donation for GiveWP is now activated.


== Frequently Asked Questions ==

= What is the plugin license? =

* This plugin is released under a GPL license.

= What is needed to use this plugin? =

* GiveWP version 2.3.0 or latter installed and active.


== Screenshots ==

1. Nothing;

== Changelog ==
= 1.4.1 =
**02/04/2025**
* Fix error on the settings page.

= 1.4.0 =
**13/03/2025**
* Add compatibility with form 3.0.0;
* Fix errors from plugin checker.

= 1.3.2 =
**08/08/2024**
* Fix for HTML escaping issues.

= 1.3.1 =
**07/08/2024**
* Fixed recurring event not triggered;
* Fixed function with fatal error when re-enabling the plugin.

= 1.3.0 =
**03/06/2024**
* Added feature in admin panel: Enable/disable donation blocking based on user donation count.
* Addition of translation for the new feature.
* Implementation of global donation blocking when the global donation limit is exceeded.

= 1.2.2 =
**31/05/2023**
* Fixed error message not showing in legacy template.

= 1.2.1 =
**26/05/2023**
* Code refactoring;
* Removed plugin updater lib;
* Automatically remove old logs.

= 1.2.0 =
* Code refactoring and improvements;
* Add configuration to enable or disable generation of .logs;
* Add configuration for insertion of IP's banned from making donations.

= 1.1.2 =
* Fixed Google Recaptcha notice bug for fixed value forms;
* Adjusted logic to delete old logs;
* Adjusted antispam logs.

= 1.1.1 =
* There is now a link in the settings description that redirects to the Recaptcha V3 admin page;
* Adjusted title of Recaptcha setting.

= 1.1.0 =
* Antispam Donation for GiveWP now has option to enable and configure Recaptcha V3;
* Tweaks and fixes in plugin settings;
* Settings formatting tweaks.

= 1.0.0 =
* Plugin with the functionality to verify the IP's of donations and, if they are the same, block payment attempts.

== Upgrade Notice ==

= 1.0.0 =
* Plugin with the functionality to verify the IP's of donations and, if they are the same, block payment attempts.

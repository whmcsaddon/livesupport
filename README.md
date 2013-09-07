<h1>Updates and Support</h1>
We need your help to pay for the WHMCS license and coffees used to develop this script.
In exchange we will continue updating this addon and offering support through GitHub!

Thank you for your support!

<form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_top">
<input type="hidden" name="cmd" value="_s-xclick">
<input type="hidden" name="hosted_button_id" value="SAUA6QJJQ4HVS">
<input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_donateCC_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
<img alt="" border="0" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" width="1" height="1">
</form>

-----------------------------------------------------------------------------------
    WHMCS Addon Live Support - Provides a way for you to instantly communicate
    with your customers.
    Copyright (C) 2010-2013 WHMCS Addon (www.whmcsaddon.com)

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.
-----------------------------------------------------------------------------------

HOW TO INSTALL

Upload all the latest files to your WHMCS directory.

Once uploaded go to your WHMCS administration area and find the addon manager
under settings. Enable "WHMCS Addon Live Support" allowing your administrators
and/or support operators access to the addon.

If you have a previous version installed the script will automatically run
the upgrade commands. If this is your first time installation the script
will automatically install the addon.

Make sure to add your local server ip to the allowed API connections under
Setup -> General Settings -> Security -> API IP Access Restriction

Edit your live support settings by going to the now active addon link and
clicking settings. You have to setup what departments are used by the live
support application.

-----------------------------------------------------------------------------------

INSERTING INTO WHMCS'S TEMPLATE

Open up "/templates/YOUR-TEMPLATE-HERE/header.tpl"

By default WHMCS includes jQuery, make sure this is still in your template.
The jQuery include on your site template will look something like this:
	&#60;script type="text/javascript" src="../includes/jscript/jquery.js"&#62;&#60;/script&#62;

After the line that includes jQuery into your website add:
&#60;script type="text/javascript" src="http://YOUR-WHMCS-LOCATION-HERE/includes/jscript/livehelp.js.php"&#62;&#60;/script&#62;

Put this line anywhere in your template you want your live support icons to appear in your layout:
&#60;span class="livechat"&#62;&#60;/span&#62;

[Note: This same process can be used throughout the rest of your site.]

-----------------------------------------------------------------------------------

OPTIONAL ADMIN MANAGEMENT ADDON

In your admin template edit header.tpl ("/admin/templates/YOUR-TEMPLATE-HERE/header.tpl")

Insert the following line after jQuery is included
&#60;script type="text/javascript" src="../includes/jscript/adminchat-notify.js.php?module={$smarty.get.module}"&#62;&#60;/script&#62;

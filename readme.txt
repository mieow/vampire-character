=== Vampire Character Manager === 
Contributors: magent
Tags: vampire, character generation, rpg, lrp, larp
Requires at least: 6.2
Tested up to: 6.7.1
Stable tag: 2.13
Requires PHP: 7.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

For managing characters for LARPs and online vampire games.

== Description ==

This WordPress plugin is intended to manage vampire character sheets for LARPs and online vampire games.

Features are:

* Online character generation
* Track and assign changes in Experience and other ratings
* Output a PDF character for printing
* Configure character generation rules using templates
* Configure experience point and freebie point costs
* Add/Edit character and source data
* Storyteller approval of XP spends and character background updates
* Display area map using Google API
* Automatically list active characters and what their status is
* Get reports (CSV and PDF) such as character activity
* In-character Private Messaging system
* Send a newsletter with Experience point totals
* Can send notification emails via Mail or SMTP
* Export and import the database to create and restore backups
* (new) Custom Wordpress REST API endpoints to allow integration with other applications

== Installation ==

= Setting up for the first time =

1. Install the plugin as you would normally - either directly from the wordpress site or by downloading the zip file and uploading it into your site 
1. Activate the plugin
1. Navigate to the Settings -> Character Options page
1. Select the Page Links tab
	1. (Optional) Select which Wordpress page you want to use for the plugin output
	1. Click 'Save'
1. Navigate to the Characters -> Configuration page
1. Select the Features tab and enable any plugin feature you want to use
1. Select the Database tab and click the button to load in default data (e.g. Skills, Merits and Flaws). Alternatively, you can navigate to the Characters -> Data Tables page and enter it all in manually.
1. Go through the rest of the Configuration tabs and set the options for your game
1. Navigate to Characters -> Data Tables and review the data

= After updating the plugin = 

1. Go to Characters->Configuration. Check any new options and save your changes.
1. Go to Data Tables->Character Templates. Check any new template options and save.
1. Go through version log and make other any appropriate updates 

= Manually updating the plugin =

1. Download the latest version from the plugin site
1. Log into the administrator account on the Wordpress website
1. De-activate the character plugin, if already installed
1. Delete the plugin entirely (don't worry - you aren't deleting the database tables so nothing will be lost)
1. Add New Plugin -> upload
1. Activate Plugin 

== Frequently Asked Questions ==

= I want multiple characters under the same email address, each with a Wordpress login but Wordpress won't allow it =

I recommend the 'Allow Multiple Accounts' plugin by Scott Reilly to work around this issue.

= How do I stop Storytellers/Narrators/Games Masters having full admin access to the Wordpress site? =

Create a role for them called 'storyteller' with the 'manage_options' capability

= Can I have the same Wordpress account for multiple characters? =

No.  The plugin works under the premise that each Wordpress login links to only 1 character.

= How do I get a Google API Key for the map functions? =

You can get a Standard (and free) Google Maps Javascript API key from this google site: https://developers.google.com/maps/documentation/javascript/get-api-key

= Can I try the plugin out? =

You can try out character generation on the plugin website.

= I get a 404 page when I try to read a character mail =

Try refreshing the Permalinks.  If that doesn't fix the problem then please contact me.

= The format of the page is all messed up when I try to read a character mail =

The page format for viewing character mails comes from the default template in the plugin.  This template might not work with the theme you are using.  In that case, you can create a new template called 'vtmpm.php' in your theme directory.  Enter the below PHP to insert the message content:

    vtm_pm_render_pmmsg();

You can find the default template under vampire-character/templates/vtmpm.php to use as an example.

= How can I add things to the email template that the plugin sends? =

You can create a custom email template by copying vampire-character/templates/vtmemail.html to your theme directory and making edits there.

= Nothing happens when I try to spend experience! =

When there are alot of things to buy with experience then the plugin reaches the limit of the max_input_vars=1000 default PHP setting.

This setting needs to be increased in your site php.ini or .htaccess file.  This will need to be done by your website administrator or by the webhost.

Version 2.8 has fixed this issue for normal experience spends but not experience spends during character generation.

= I have an idea for a great new feature! =

Please email me at storyteller@plugin.gvlarp.com with your suggestion

= I found a problem with the plugin =

Please email me at storyteller@plugin.gvlarp.com with information on the problem. Include
exactly what you were doing when you saw the problem and a screen shot to demonstrate
the issue.  Also include any error messages.

== Screenshots ==

1. View your character sheet
2. Edit a character sheet
3. PDF character sheet
4. Spend Experience

== Changelog ==

= 2.13 = 

* Bug fix: Player pull-down now shows player name if the player of the character is Inactive
* Bug fix: When you delete a character, it also deletes any pending experience spends
* Bug fix: List of available sects to choose from in Character Generation now honours visible=no setting
* Bug fix: Current Willpower increases by 1 when Maximum Willpower is increased with experience
* Improvements: Page links now managed via Wordpress settings instead of from a database table
* Improvements: Manage Page Links and enabled Features by new "Character Options" page under the Wordpress "Settings" menu
* Improvements: Use in-built WP classes for admin page tabs
* Improvements: When reporting number of freebies left in character generation, flaws are added to the "available points" instead of the "have been spent" points
* New feature: Added custom endpoints for REST API
* New feature: Player's characters listed on Player Admin page
* New feature: Can delete players (and all their characters)

= 2.12 =

* Bug fix: Fixed formatting of combo disciplines on XP spend page

= 2.11 =

* Bug fix: Fixed email error when using default wordpress 'mail' config
* Bug fix: PDF character sheet format doesn't get messed up when it has large numbers of background
* Bug fix: PDF library updated for PHP7
* Bug fix: input box for character gen concept limited to 200 characters to stop database save failing when user enters more than that
* Improvements: Character sheet and XP spend page are now viewable on mobile devices
* New feature: Added support for specifying character pronouns


= 2.10 =

* Bug fix: fixed issue where merit & flaw costs were incorrectly set to level 1 when character approved
* Bug fix: fixed issue for PHP7.3 where database zip wasn't being created (NB: still fails for PHP7.4)
* Bug fix: fixed issue where XP spends could not be approved - database SQL error returned
* Bug fix: remove "Creating default object from empty value" warning message when resetting the database to defaults
* Bug fix: remove missing file error when reading in init database by checking if file exists first

= 2.9 =

* Bug fix: fixed issue where experience spends failed to approve
* Bug fix: fixed additional PHP7.3 warnings from shortcodes

= 2.8 = 

* New feature: New shortcode to display inbox contents
* Bug fix: All disciplines in Clan data table are now optional - required for Caitiff who have no clan disciplines
* Bug fix: Dead characters no longer show in list of recipients for messages
* Bug fix: max_input_vars issue resolved for Spending Experience (still to do for Character Generation)
* Bug fix: incorrect link to character from profile heading when logged in as ST is fixed
* Bug fix: fixed issue when viewing characters of very low generation where the bloodpool boxes didn't wrap every 10 boxes
* Bug fix: Doesn't list deleted contact details in profile page
* Improvements: Emails are now sent in html format
* Improvements: Users can now update their own profile quotes
* Improvements: Added a 'Contact details' tab in extended backgrounds where you can automatically create and delete phone numbers
* Improvements: Character creation date shown on printable character sheet and on character_details shortcode
* Improvements: Classes added to some private message elements for better CSS support
* Improvements: Supports PHP7.3


= 2.7 =

* Patch for situation where character was not generated using the character generation process and therefore didn't have an associated template ID

= 2.6 =

* New feature: Now supports Thaumaturgy and Necromancy Primary paths
* Bug fix: XP is now transferred from deleted characters when characters are purged from the database when XP is assigned by player, rather than by character
* Improvements: Database import now works for any data imported from version 2.3 and onwards
* Improvements: Publish button is now Send for In-character email
* Bug fix: Users can't set IC emails to 'Private' which was making the subject show up in everyone's inboxes under the private filter
* Bug fix: Fixed issue where profile image wasn't being displayed properly
* Bug fix: HTML in background description for approval now displaying correctly
* Improvement: warning given when max_input_vars limit reached on experience spend page (will be properly fixed in next version)
* Bug fix: Copes better with character generation steps appearing/disappearing depending on character options (e.g. adding majik disciplines and the rituals step appearing)

= 2.5 =

* Bug fix: Issue whereby wordpress IDs of deleted characters were stopping the wordpress 
ID being reused. This was an issue where multiple versions of a character were created
accidentally
* Improvement: Admins can now set the sector of a background when on the Edit character page
* Bug fix: Character generation templates cannot be deleted if characters exist that were 
created, or are in the process of being created, with that template
* Bug fix: Character generation now works if the literal 'Humanity' path is missing (e.g.
where Path of Humanity is used in Dark Ages games)
* Bug fix: users are no longer prompted about email confirmation emails in character 
generation when the character failed to save and no emails were sent 
* Bug fix: Remove spurious 'table is not empty' messages when updating the plugin
* Bug fix: Dot skinning and Portraits didn't work if the imagemagik library was not 
installed so added support for PHP GD library and disabled skinning options if neither
library is installed.
* Added a silly vampire icon to the plugin assets for the plugin listing


= 2.4 =

* Updates for Wordpress 4.5 compatibility
	* replace depreciated user info functions
	* selective refresh support for widgets

= 2.3 =

* Updates as a result of feedback from Wordpress.org plugin submission
	* Table data that may cause Copyright issues has been removed from initial data
	* Removed clan icons from images folder 
	* plugin name changed to vampire-character
* Added In-character private messaging system
* Added option in Character Generation template to correctly model virtues rules for non-Humanity paths
* Players can set and/or upload character portraits
* Option added to enable/disable news posts being added to the site blogroll
* Export, import and reset database data from the Configuration page
* Reports don't need to write out to tmp directory any more
* Added additional tabs on data tables page and did some renaming to reduce potential copyright risk
* Fixed bug with XP shortcode where sometimes output rows were missed
* Added admin notices when plugin upgrade takes place with version number and error info 
* Improved data table creation code so that it works better with dbDelta
* On profile page, newsletter selection defaults to 'Y' if unset in db 
* Cannot now choose the same discipline multiple times on clan data table

= 2.2 =

* Preparation for submitting to Wordpress.org

= 2.1 =

* Updated newline encoding for FPDF lib files so that they didn't get corrupted when WP
unzipped them and put in double-newlines thereby breaking the PHP
* Wordpress 4.2 WP_List_Tables class has changed and broke the plugin. Made custom class using old version of the WP code.

= 2.0 =

* Updated for inclusion in wordpress.org
* Email sent to Storytellers when players submit XP spends
* Deleted characters can be purged from database
* Added support for sending emails via SMTP
* Maximum levels for backgrounds during character generation can be set in the template
* Select Ability group from a pull-down list instead of typing into a box in Data Tables
* Cannot save character gen template if the name is blank
* Can buy secondary abilities in Ability step in Character gen
* Now supports narrow, wide and medium width themes with the configuration setting
* CSS improvements, e.g. button/step formatting in character gen, dot colours
* Added ability to send newsletter with XP totals, etc
* Fixed issue in Data Tables when filtering by Discipline for rituals
* Player can now specify background specialities
* Storyteller can now specify when background specialities are needed
* Changed how plugin pages are referred to so that if another plugin modifies a page
then the page still works
* Speed increase when on main site, as admin functions aren't loaded unless on admin pages
* String formatting fixed lots of places, including on PDF character sheet (removing slashes)
* Character gen template selection is now radio buttons with template description
* Can filter on PC/NPC/all for merit and background shortcodes
* renamed plugin to vtmcharacter from gvlarp-character
* Added ability to disable features that aren't being used (e.g.maps, Stat changes)
* Expanded config options into multiple tabs
* Display which character gen template was selected when ST is viewing list of non-approved characters
* Added Sect to background and merit shortcode filters
* When ST is editing characters, cannot now set skills or disciplines to level 0 (thereby confusing XP spends)


= 1.11.0 =
* Characters can now be added if there is an apostrophe in the Player name.
* Column ‘office’ added to backgrounds shortcode output.
* XP costs are listed on the spend page for the things you can’t afford yet. 
* You can update your email address from the Profile page. 
* Disciplines and Magik paths are now displayed if you already have them. 
* Willpower changes are not recorded for characters when the actual change is 0. 
* Paths of Enlightenment can be bought with freebie points at character generation. 
* V20 rules for whether you get free dots in virtues are now supported in the templates. 
* Descriptions will display when you hover over Merits/Flaws/skills/etc for freebie 
and XP spends.
* Rituals can be bought at character generation. 
* 7th generation characters and lower are now supported. 
* Confirm your email address by clicking on the link emailed out. 
* free background and abilities can be automatically added at character generation 
(e.g. status 1). 
* Don’t need to set Primary/Secondary/Tertiary for Abilities and Attributes any more – 
they are automatically worked out. 
* Character Generation Templates can be set to include/limit specific Sects. 
* Character Generation Templates can be set to include/limit specific Paths of 
Enlightenment.
* Only active players are listed on the pull-down on the Edit characters page. 
* Added a report for showing background with sectors for characters. 
* Path Changes admin page has added filters at the top of the page. 
* Can’t now save merits/flaws if there is another with the same name. 
* Fixed issue where decimal number wasn’t accepted in lat/long boxes in Google map config settings.

= 1.10.0 =
* Character Generation added.  
* Character names on View Character page show up correctly if they have an apostrophe 
in the name.  
* Non-Clan, Non-visible disciplines now show up to buy when the character already has 
a dot in it.  
* Ability to add extra blank columns in the sign-in report added. 
* Report added to show when characters were last updated.  
* Characters without a WordPress ID (e.g NPCs) can now be viewed.  
* Optional 4th Clan Discipline can now be specified for clans.  
* Initial level of the character road/path can only be set at initial character creation.  
* Validation performed on create character inputs (e.g. wordpress ID, required fields, 
duplicate character names).  
* Report when character could not be added to database.  
* Conscience/instinct/etc pull-down boxes only go to 5, and not to the generational maximum.  
* For login Widget: Removed download DT link, Pick up page URLs from database and Checkbox to set which links to show

= 1.9.0 =  
* Admin-related shortcodes have been moved to WP admin pages.   
* Generation table can now be managed and the default gen for new characters defined.    
* Sort fixed on XP Approval page and you can only assign XP to visible characters.    
* On Xp spend page, only get check-boxes if the character has enough XP.   
* Can buy over WP 5.   
* Thaum paths now show up on printable sheet.   
* Combo-disciplines can be purchased with XP.   
* Ritual descriptions show up on printable sheet.   
* Character info doesn't get displayed if you aren't logged in.   
* Pending XP changes don't get deleted if approval fails.   
* Extended Background widget added.   
* Show Feeding domains in a googlemaps api shortcode.   
* Fixed PDF report issue for data generated on the 2nd page and onward

= 1.8.0 =  
* Alot more admin pages created.   
* Caitiff XP Spends now supported.   
* Nature/Demeanour, sect membership, assigning xp by character now supported.   
* Solar Calc widget incorporated.  
* Shortcodes ‘status_list_block’, ‘dead_character_table’ and ‘prestige_list_block’ 
replaced with ‘background_table’ and ‘merit_table’ to support display of other backgrounds 
(e.g. Anarch Status).   
* Removed shortcode ‘xp_spend_table’ - Page now generated with content filter.   
* XP Spend page re-written to allow multiple spends at the one time.   
* Also shows pending spends and allows them to be cancelled.

= 1.7.0 = 
* Development fully taken over by Jane Houston
* PDF version of the character sheet now available.  
* Added wp-admin pages to manage Merits & Flaws, Rituals, Backgrounds, Sourcebooks, 
Extended Background questions, Sectors, clans, page locations & PDF customisations.  
* Added extended backgrounds, with functionality for STs to approve.  
* Split off main PHP file into include files.  
* Updated installation functions for properly upgrading the database when plugin 
is activated.   
* Added initial table data during installation for ST links, Sectors, Extended 
Background questions, Generations,player status, character status, Attributes/stats, Clans.

= 1.6.0 = 
* Specialisations and multiple versions of same skill during XP spend 
* High and Low Gen support on xp spend.  
* Added Temporary Blood and Temporary Willpower master table 
* Status table only show active characters 
* On Clan Prestige Table add court 
* allow 5 rituals to be added at the same time 
* master path table exclude not visible chars 
* make "Path Change" default option 
* Prestige List character name links to profile 
* On XP Approval and Profile character name links to character sheet for STs only 
* create monthly WP gain table

= 1.5.0 = 
* Character Admin default selection (PC, Not Visible, View Sheet) 
* Character Edit increase Harpy Quote to textarea 
* longer box for Portrait URL, Portrait (Show cstatus/comment, 
placeholder image, option to change Display Name/Password) 
* Fixed status list (display zeros & dead characters) 
* Added Clan Discipline Discount Configuration (simple) 
* Obituary Page 
* Merit/Flaw can be bought (off) with XP 
* Escape single/double quotes in harpy comment 
* CSS classes and ids added in remaining tables

= 1.4.0 =
* Fixed CLAN table link column, create default entry in ROAD_OR_PATH on character creation

= 1.3.2 =
* Profile
* CSS class ids
* Single width skill choice in edit character with separate groups for Talents, Skills, Knowledges

= 1.3.1 =
* Add training note to XP Spends, Hide inactive characters from Prestige/Master Path table

= 1.3.0 =
* DB description size increases
* book ref for paths, rituals, merits/flaw
* Improve XP spends
* User XP spends
* ST XP spend approvals
* Humanity changes expansion
* Character edit improvements
* add submit button to top.

= 1.2.2 =
* Put ST Links into a DB Table

= 1.2.1 =
* Prevent a player from being added with name New Player

= 1.2 =
* Preferred values hard-coded as initially selected
* Player Admin introduced
* added ability for STs to see Character Sheet and other pages as selected character
* officials selectable by court or by court and position.

= 1.1 =
* Character Admin added
* various bug fixes
* XP spend table limit
* status table

= 1.0 =
* Initial Release by Lambert Behnke

== Upgrade Notice ==

= Upgrading to 2.8 =

Review the new email settings for basic skinning of HTML emails.

Review the new settings for auto-generating phone numbers in the Messaging Configuration tab.

Have a look at your Caitiff entry in the Clan tab of the Data Tables page and, if required, set all clan discipline entries to [select]

= Upgrading to 2.6 / 2.7 =

Review the new Primary Path settings for each of your character generation templates. Save each template once you have updated the settings.

Then go through each character and manually select their primary path in the Disciplines section and add that path to their character (if it doesn't already exist).

Also, consider what to do with the path 'Thaumaturgical Countermagic'.  To conform with the V20 rules, I recommend removing it as a path and adding it to the disciplines data table.  The rules do not mention if this 'discipline' is Clan or Non-Clan for Tremere but it would be my ruling that it was Non-Clan.

== Shortcodes ==

= background_table =

Display a list of characters with a specific Background. Defaults are highlighted.

Options:

* character - Wordpress login name of character to be 'logged in' as (ST/admin only)
* background - which background to list (defaults to Status)
* liststatus - only list characters with a specific character status (*Alive*, Missing, Dead, Staked, Torpor)
* level - only list characters with a specific level of background (*all*, displayzeros, <number>)
* domain - only list characters in a specific domain (*home*, <domain>)
* heading - show or hide table headings (*1*, 0)
* columns - define which columns to display in a comma-separated list (*level, character, player, clan, domain, background, sector, comment, level, office*, sect)
* matchtype - advanced filtering options. Specify what kind of match to make:
	* sector - list backgrounds that match the specified sector
	* comment - list backgrounds that match the specified comment/specialisation
	* characteristic - list backgrounds that match a characteristic from a character
* match - use with matchtype. Specify what to filter on/match to
	* loggedinclan - list matches with the clan of the logged in user
	* loggedinsect - list matches with the sect of the logged in user
	* <value> - list matches the selected level

Examples:

* Display all active characters in the current domain

[background_table level=displayzeros columns="character,clan,office"]

* Display all dead characters

[background_table columns="character,clan,player" level=displayzeros court="" liststatus=Dead]

* Display a list of vampires with Clan Prestige in the same clan as you (logged in)

[background_table columns="level,character,clan" match=loggedinclan matchtype=comment background="Clan Prestige"]

Note that for this to work, on the character sheet the character must have the clan name entered 
into the comment/specialisation box for the background.

= character_detail_block =

Display character information for the logged in character.

Options:

* character - Wordpress login name of character to be 'logged in' as (ST/admin only)
* group - Sub-group of information to display
	* char_name - character name
	* domain - domain of residence
	* pub_clan - public clan, i.e. the clan that they publicly admit to being a member of
	* priv_clan - private clan, i.e the clan they actually are
	* sire - Name of Sire
	* gen - generation
	* blood_per_round - number of blood points that can be spent per round
	* path_name - Path of Enlightenment name
	* path_value - Level of Path of Enlightenment
	* bloodpool - Size of the bloodpool
	* nature - Character nature (if used)
	* demeanour - Character demeanour (if used, and note UK spelling)
	* date_of_birth - Date of Birth
	* date_of_embrace - Date of Embrace
	* status - Character Status (e.g. Alive, Dead)
	* status_comment - Comment on character status
	* last_updated - date character was last updated (e.g. XP spent)

"Your character was last updated on [character_detail_block group=last_updated]."

= character_offices_block =

Display the Offices of the logged-in character

Options:

* character - Wordpress login name of character to be 'logged in' as (ST/admin only)

[character_offices_block]

= character_road_or_path_table =

Display Path of Enlightenment changes for the logged-in character

Options:

* character - Wordpress login name of character to be 'logged in' as (ST/admin only)
* group - Sub-group of information to display
	* total - current path level

[character_road_or_path_table]

"Your path rating is [character_road_or_path_table group=total]."

= character_temp_stats =

Show information on Willpower or Blood spends

Options:

* character - Wordpress login name of character to be 'logged in' as (ST/admin only)
* showtable - show changes in a table (*0*, 1)
* limit - limit how many rows in the table (defaults to 5)
* stat - specify which stat to display (*Willpower*, Blood)

[character_temp_stats showtable=1 limit=10 stat=Blood]

= character_xp_table =

List the XP spends and assignments for the logged in character

Options:

* character - Wordpress login name of character to be 'logged in' as (ST/admin only)
* maxrecords - limit how many rows in the table (defaults to 20)

[character_xp_table maxrecords=100]

= feeding_map =

Display the domain/feeding map.  Domains and who owns them are defined in the Data Tables
admin section.  You will need a valid Google API code for this feature to work. 
(https://developers.google.com/maps/documentation/javascript/tutorial)

[feeding_map]

= merit_table =

Displays a table of characters with a specific Merit or Flaw.

Options:

* character - Wordpress login name of character to be 'logged in' as (ST/admin only)
* merit - Merit or Flaw name to list (Default is “Clan Friendship”)
* match - add a filter to the list
	* loggedinclan - Comment/specialisation must match that of the clan of the logged in user
	* <value> – match the specified value
* liststatus - only list characters with a specific character status (*Alive*, Missing, Dead, Staked, Torpor)
* heading - show or hide table headings (*1*, 0)
* domain - only list characters in a specific domain (*home*, <domain>)
* columns - define which columns to display in a comma-separated list (*level, character, player, clan, domain, merit, comment, level*, sect)

For example, display a list of character with Enmity towards your clan

[merit_table merit='Clan Enmity' match=loggedinclan]

= office_block =

List all the characters with an office or position of power

Options:

* character - Wordpress login name of character to be 'logged in' as (ST/admin only)
* domain - domain character is an official in
* office - specific office to display

"The ruler of Glasgow is [office_block domain=Glasgow office=Prince]."

= spend_button =

Displays a button for characters to click to spend Willpower or Bloodpoints.

Options:

* character - Wordpress login name of character to be 'logged in' as (ST/admin only)
* stat - which Stat the button is for:
	* Willpower
	* Blood
	
= inbox_summary =

List the last x private messages.

Options:

* list - define what messages to display

[inbox_summary list=5]

== Widgets ==

= Character Login Widget =

Displays useful links:

* Login/logout
* Character Sheet
* Character Profile
* Spend Experience
* Character Inbox (x unread)
* Addressbook
* Contact Details

= Character Background Widget =

Displays how much of the character background has been completed.

= Sunset/Sunrise Times = 

Display the times of sunset and sunrise.

= WordPress REST API Endpoints =

* _/wp-json/vampire-character/v1/character_ : return a list of the active characters (Storyteller only)
* _/wp-json/vampire-character/v1/character/<characterID>_ : return character information by character ID (Storyteller only)
* _/wp-json/vampire-character/v1/character/wpid&wordpress_id=<username>_ : return character information by wordpress username
* _/wp-json/vampire-character/v1/character/me_ : return character information for logged-in user


== Template Tags ==

None

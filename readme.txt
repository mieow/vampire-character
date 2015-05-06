=== Vampire:the Masquerade Character Manager ===
Contributors: magent
Tags: vampire, white wolf, masquerade, character, generation, roleplay
Requires at least: 4.1
Tested up to: 4.1
Stable tag: 2.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

For managing Vampire:the Masquerade character sheets for LARPs and on-line Vampire games.

== Description ==

This WordPress plugin is intended to manage Vampire:the Masquerade character sheets for LARPs and online Vampire games.

Features are:
* On-line character generation
* Track and assign changes in Experience, Path of Enlightenment, Willpower and Bloodpool
* Output a PDF character for printing
* Configure character generation rules using templates
* Configure experience point and freebie point costs
* Add/Edit data such as Abilities, Rituals, Clans, Merits/Flaws, etc 
* Storyteller approval of XP spends and character background updates
* Display domain/feeding area map using Google API
* Automatically list active characters and what their Status is
* Get reports (CSV and PDF) such as character activity
* Pre-loaded with data for V20 edition
* Send a character newsletter with XP totals and Storyteller message

== Installation ==

These are the instructions for manually installing the latest version of the plugin:
1. Download the latest version from the plugin site
1. Log into the administrator account on the Wordpress website
1. De-activate the character plugin, if already installed
1. Delete the plugin entirely (don't worry - you aren't deleting the database tables so nothing will be lost)
1. Add New Plugin -> upload
1. Activate Plugin 

After installation, we recommend:
1. Go to Characters->Configuration. Check any new options and save your changes.
1. Go through version log and make any appropriate updates 

== Frequently Asked Questions ==

= I want multiple characters, each with a Wordpress login but Wordpress won't allow it =

We recommend the 'Allow Multiple Accounts' plugin by Scott Reilly to work around this issue.

= How do I stop Storytellers having full admin access to the Wordpress site? =

Create a role for them called 'storyteller' with the 'manage_options' capability

= Can I have the same Wordpress account for multiple characters? =

No.  The plugin works under the premise that each Wordpress login links to only 1 character.

= Are there any other plugins you recommend for running a game of Vampire on Wordpress? =

These are the plugins I have used for the LARP(s) I have been involved in running.

* Members, by Justin Tadlock - useful for adding additional roles for Clans, Storytellers, etc
* User Access Manager, by Alexander Schneider - useful for controlling page access for groups of users cased on Clan, Sect, etc.
* Download Monitor, by Barry Kooij & Mike Jolley - useful for tracking and formatting download links for house rules, etc.
* Events Made Easy, by Franky Van Liedekerke - organising recurring events and deadlines

= Can I try the plugin out? =

You can try out character generation on the plugin website.

== Screenshots ==

1. View your character sheet
2. Edit a character sheet (storyteller)
3. PDF character sheet
4. Spend Experience

== Changelog ==

= 2.1 =

* updated newline encoding for FPDF lib files so that they didn't get corrupted when WP
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

= 2.0 =
Upgrade to this version as the name of the plugin has changed from 'gvlarp-character'
to 'vtm-character'.

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
** sector - list backgrounds that match the specified sector
** comment - list backgrounds that match the specified comment/specialisation
** characteristic - list backgrounds that match a characteristic from a character
* match - use with matchtype. Specify what to filter on/match to
** loggedinclan - list matches with the clan of the logged in user
** loggedinsect - list matches with the sect of the logged in user
** <value> - list matches the selected level

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
** char_name - character name
** domain - domain of residence
** pub_clan - public clan, i.e. the clan that they publicly admit to being a member of
** priv_clan - private clan, i.e the clan they actually are
** sire - Name of Sire
** gen - generation
** blood_per_round - number of blood points that can be spent per round
** path_name - Path of Enlightenment name
** path_value - Level of Path of Enlightenment
** bloodpool - Size of the bloodpool
** nature - Character nature (if used)
** demeanour - Character demeanour (if used, and note UK spelling)
** date_of_birth - Date of Birth
** date_of_embrace - Date of Embrace
** status - Character Status (e.g. Alive, Dead)
** status_comment - Comment on character status
** last_updated - date character was last updated (e.g. XP spent)

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
** total - current path level

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
** loggedinclan - Comment/specialisation must match that of the clan of the logged in user
** <value> – match the specified value
* liststatus - only list characters with a specific character status (*Alive*, Missing, Dead, Staked, Torpor)
* heading - show or hide table headings (*1*, 0)
* domain - only list characters in a specific domain (*home*, <domain>)
* columns - define which columns to display in a comma-separated list (*level, character, player, clan, domain, merit, comment, level*, sect)

For example, display a list of character with Enmity towards your clan

[merit_table merit='Clan Enmity' match=loggedinclan]

= office_block =

List all the characters with an office (e.g Prince, Primogen)

Options:
* character - Wordpress login name of character to be 'logged in' as (ST/admin only)
* domain - domain character is an official in
* office - specific office to display

"The Prince of Glasgow is [office_block domain=Glasgow office=Prince]."

= spend_button =

Displays a button for characters to click to spend Willpower or Bloodpoints.

Options:
* character - Wordpress login name of character to be 'logged in' as (ST/admin only)
* stat - which Stat the button is for:
** Willpower
** Blood

== Widgets ==

= Character Login Widget =

Displays useful links:
* Login/logout
* Character Sheet
* Character Profile
* Spend Experience
* Box for entering path to a private messaging/mail inbox

= Character Background Widget =

Displays how much of the character background has been completed.

= Sunset/Sunrise Times = 

Display the times of sunset and sunrise.

== Template Tags ==

None

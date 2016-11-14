<?php
/**
 * @package Show Local Url Titles
 * Language strings for the Show Local Url Titles mod.
 */

$txt['ShowLocalUrlTitles_tab'] = 'Show Local Url Titles';
$txt['ShowLocalUrlTitles_heading'] = 'Settings for the Show Local Url Titles mod';
$txt['ShowLocalUrlTitles_parsebbc'] = 'Parse local url titles on message view? (not recommended)';
$txt['ShowLocalUrlTitles_posting'] = 'Parse local url titles on message post? (recommended)';
$txt['ShowLocalUrlTitles_autolink'] = 'Autolink urls on post? (required to parse autolinked urls on post)';
$txt['ShowLocalUrlTitles_parse_existing'] = 'Click here to parse the local url titles for all existing messages. (warning: this may take a while! backup your database first!)';
$txt['ShowLocalUrlTitles_parse_existing_confirmation'] = 'Are you sure you want to parse all of the posts in your forum?\n\nThis process cannot be undone!\nMake sure that you have made a backup of your database!';

$helptxt['ShowLocalUrlTitles_parsebbc'] = 'This setting enables the parsing of local url titles in messages, when the message is viewed. This adds database queries to each page view, making it quite inefficient, it is not recommended that you use this setting for forums where efficiency is an issue.';
$helptxt['ShowLocalUrlTitles_posting'] = 'This setting enables the parsing of local url titles in messages, when the message is posted. This setting is recommended because the mod only has to look up each url title once (when the post is created/modified), which is far more efficient than parsing the url titles on every page view.';
$helptxt['ShowLocalUrlTitles_autolink'] = 'This setting enables the autolinking of urls when a message is created/modified. It is necessary if you want to parse autolinked urls when a post is modified/created, rather than when it is viewed. <br /><br />Autolink urls are urls that are not contained within url or iurl tags, instead SMF automatically detects them and makes them into links.';
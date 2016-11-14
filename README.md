# SMF Show Local Url Titles mod
* **Original author:** Nathaniel Baxter
* **Additional author:** digger
* **License:** BSD
* **Compatible with:** SMF 2.0

## Description
This mod turns the boring Local/Internal URLs for Posts, Threads, Boards and User profile's into their actual titles so that your forums look better and more professional. Its very similar to a functionality from vB which does a similar thing. The mod has settings for parsing the titles on Posting and Page view, as well as a maintenance function that can convert the urls for each of your forum's existing posts.

Example:
This URL (for new websites): "www.yoursite.com/index.php?topic=1"
Would be turned into this title which links to the same page but has different text "Welcome to SMF!".

Settings are found in the Admin panel:
Look in the "Configuration" -> "Modification Settings" -> "Miscellaneous" section of your admin panel.

Backup your database:
Please note that you should always backup your database before using the "Click here to parse the local url titles for all existing messages" function, the mod author takes no liability for the corruption of data.

The License for this mod should be contained within the mod package, in a text file called LICENSE.

## Version Changes:
   v2.0.1 - 21 June 2011:
   Support for SMF 2.0 final. Minor fix required.
   
   v2.0 - 22 November 2010
   Support added for SMF 2 RC4.
   Support removed for all earlier versions of SMF, including the SMF 1.1.x branch.
   Complete rewrite of the mod.
   Added autolinking of urls on post.
   Added parsing of url titles on post.
   Added maintenance function to parse existing messages.
   
   v1.1
   Minor bugfix, any bbc tags inside the url tags were not being parsed.
   
   v1.0
   Original Mod release.

## Описание
Мод добавляет к внутренним ссылкам форума анкоры с названиями сообщений/тем/разделов.

Пример: Для этой ссылки (на свежеустановленном форуме): "www.yoursite.com/index.php?topic=1" добавится анкор "Welcome to SMF!".
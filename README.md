Eve Crawl
===

This is a project I started working on and abandoned in 2009. It is a spider which
was specifically built for crawling websites which contained EVE Kill Mail's. In the
game of EVE, every time you kill a player, an in-game 'mail' is sent to you containing
information. Players would copy this informaiton and paste it into a website, which
would show statistics about the kill.

The purpose of this project was to take information about all of the kills and display
them on an interactive 2D heatmap. There would also be a slider which represented time,
and a user could slide this slider. The purpose of the project was to show which areas
in space were the most dangerous.

Unfortunately, I stopped playing EVE and abandoned the project. The crawler, which
worked in 2009, is likely no longer useful. It would download pages and run regular
expressions to find data, as most 'kill mail' websites used the same software. But, I'm
sure the software has changed by now.

File Information
==
    cache.txt           ID of the last crawled page
    ccp_map_data.zip    Solar system coordinate data; released by CCP
    crawler.php         Main crawler
    downloader.php      Page downloading class
    eve-map-bg.jpg      Background graphic for map
    eve-map.xml.php     PHP script for generating XML data for flash
    evecrawler.sql.7z   189MB database file of a bunch of data
    map.fla             Raw map building flash file
    map.swf             Compiled map flash file
    mysql.ssi.php       Configuration file for database settings
    rendered.xml        Example of the rendered XML data
    sample-crawl.htm    An example of the kill mail pages circa 2009

What Works
==

I honestly don't remember what the state of the project is. I do know that the crawler
works just fine; I was able to grab a couple million records. The flash map stuff is
probably all broken and whatnot. Back in 2009 we didn't have any fancy canvas rendering,
a better developer would replace the flash with canvas.

The crawler wasn't as efficient as it could be. Most of the slowness would be due to
network latency, so paralell downloads should be used.

None of the interface development for clicking regions, doing heat maps, or scrolling
through time was ever implemented.

License
==

Released under the BSD License.

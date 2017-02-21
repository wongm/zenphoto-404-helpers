# zenphoto-404-helpers
Plugins for the Zenphoto open-source gallery that makes 404 pages more useful for end users. Contains two plugins:

- redirect on 404: redirects the browser to the correct image or album if a matching item is found in the database.
- search on 404: sarches for possibly related images or albums based on the current URLs

These plugins can be use separatly, or together.

For best results, all images in your gallery have a unique file name. Duplicated file names will mean the redirect on 404 plugin won't be able to find the relevant image page.

# Installation

1. Copy redirect_on_404.php and/or search_on_404.php into the /plugins directory of your Zenphoto installation.
2. Enable the 'redirect on 404' and/or 'search on 404' plugin in the Zenphoto backend.
3. Edit the existing 404.php file in your current Zenphoto theme folder to include calls to the new methods, using 404.php.sample-theme as an example.
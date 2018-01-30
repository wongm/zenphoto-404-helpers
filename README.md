# zenphoto-404-helpers
Plugins for the Zenphoto open-source gallery that makes 404 pages more useful for end users. Contains two plugins:

- redirect on 404: redirects the browser to the correct image or album if a matching item is found in the database.
- search on 404: searches for possibly related images or albums based on the current URL

These plugins can be use separately, or together.

For best results, all images in your gallery have a unique file name. Duplicated file names will mean the redirect on 404 plugin won't be able to find the relevant image page.

# Examples

This is a working image URL:
https://zenphoto.wongm.com/hong-kong/5253355011_0c878b49c4_o.jpg

Try to access the same image but with an incorrect folder name - user will be redirected:
https://zenphoto.wongm.com/not-a-folder/5253355011_0c878b49c4_o.jpg

Try to access an image that doesn't exist - user will be presented with a list of possible matching albums:
https://zenphoto.wongm.com/hong-kong/5253355011/

This is a working album URL:
https://zenphoto.wongm.com/hong-kong/

Try to access the same album but under a different parent album - user will be redirected:
https://zenphoto.wongm.com/not-a-folder/hong-kong/

Try to access an album that doesn't exist - user will be presented with a list of possible matching albums:
https://zenphoto.wongm.com/hong/

![image](https://user-images.githubusercontent.com/916546/35544027-59d756cc-05bc-11e8-8240-609378c690b1.png)

# Installation

1. Copy redirect_on_404.php and/or search_on_404.php into the /plugins directory of your Zenphoto installation.
2. Enable the 'redirect on 404' and/or 'search on 404' plugin in the Zenphoto backend.
3. Edit the existing 404.php file in your current Zenphoto theme folder to include calls to the new methods, using 404.php.sample-theme as an example.

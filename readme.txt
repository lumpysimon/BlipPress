=== BlipPress ===
Contributors: lumpysimon
Donate link: http://blippress.com/donate
Tags: blipfoto, blipfoto.com, blip, foto, journal, photography, photo, photos, diary, images, gallery, galleries
Requires at least: 3.5
Tested up to: 3.6.1
Stable tag: trunk

Display entries from your Blipfoto journal and post to Blipfoto from your WordPress website.

== Description ==

[Blipfoto](http://blipfoto.com) is an online daily photo journal. Each day you can upload one photo and add some words. It is also a very friendly community where people comment on and rate each other's photos, choose favourite 'blips' (the informal name given to journal entries), follow journals, join groups and take part in discussions.

BlipPress lets you easily integrate your Blipfoto journal into your WordPress website. You can display single or multiple entries from your or other people's journals in your posts and pages or in a widget, as well as posting to your journal directly from within WordPress.

= Displaying Blipfoto journal entries =

There are a number of shortcodes you can use. Just type these into any post or page.

`[bliplatest]` displays the most recent blip from your journal. You can also specify the user: `[bliplatest user=lumpysimon]`

`[blips]` displays a gallery of your recent blips. By default it uses the settings for the number of blips (default 16) and size (default large). You can override these, and also the user: `[blips num=5 size=small user=lumpysimon]`

`[blip]` displays a single blip by entry ID. For example: `[blip id=3317861]`

`[blipdate]` displays a single blip from your journal for a particular date. The date should be in the format dd-mm-yyyy. For example: `[blipdate date=22-09-2013]`. You can also specify the user: `[blipdate date=03-08-2013 user=lumpysimon]`

`[blippostdate]` displays a single blip from your journal corresponding to the date of the current post.

= Posting to Blipfoto from WordPress =

Add a new post, enter the title and content, then save a draft of the post. Now click "Choose an image" in the BlipPress box to select an existing image from your media library or to upload a new one. When you click "Blip it!", an entry will be created in your Blipfoto journal, using the image, title and content from your post.

Please note that the journal entry date will be based on the date your photo was taken, not on the publication date of your post.

When you publish your post, the journal entry will automatically be displayed at the top of your post.

= Widgets =

There are three BlipPress widgets:

* 'Multi' displays thumbnails of your latest journal entries. You can specify how many to display.
* 'Latest' displays the latest entry from your or another user's journal.
* 'Single' displays an entry by ID number.

= This is a beta plugin! =

This is the first release of this plugin, so it is still under continuous development. I am fairly sure there are likely to be some issues, so I recommend that you always check your journal entry after posting from your WordPress website. Please report any problems via the [support forum](http://wordpress.org/support/plugin/blippress).

== Frequently Asked Questions ==

= How do I get the ID number of a journal entry? =

Look at the URL of the journal entry on blipfoto.com, it will look like blipfoto.com/entry/3475466 - the entry ID is the number at the end (3475466 in this case).

= Why is there sometimes a delay before I see my latest journal entry on my website? =

When BlipPress retrieves journal entry details from blipfoto.com it caches them for 10 minutes. This is to avoid exceeding the API limits specified by Blipfoto and to make blips on your website load faster. This sometimes means that you may have to wait up to 10 minutes for your latest entry to update on your website. You can clear the cache by going to the BlipPress settings page and clicking 'Save settings'.

= Do you have a Blipfoto journal? =

Of course! You can find me at [blipfoto.com/lumpysimon](http://blipfoto.com/lumpysimon).

== Installation ==

1. Search for 'blippress' from the 'Add new plugin' page in your WordPress website.
2. Activate the plugin.
3. Go to the Authentication page to grant permission for BlipPress to access your Blipfoto account.
4. (optional) Configure the settings.
5. Add one of the shortcodes to a post or page, add a widget, or create a Blipfoto journal entry from the post editing screen.

== Changelog ==

= 0.1 =
* Initial beta release
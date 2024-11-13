[![Software License](https://img.shields.io/badge/License-AGPLv3-green.svg?style=flat-square)](LICENSE) [![Bludit 3.15.x](https://img.shields.io/badge/Bludit-3.15.x-blue.svg?style=flat-square)](https://bludit.com) [![Bludit 3.16.x](https://img.shields.io/badge/Bludit-3.16.x-blue.svg?style=flat-square)](https://bludit.com)

# What's Up (whats-up) plugin for Bludit

This is a calendar agenda display plugin for Bludit 3.15.x and 3.16.x. Later 3.x versions may work.

## Description

This plugin allows you to display events from an .ics source as an agenda with previous, current, and upcoming events on your website.

You can display the calendar agenda almost anywhere in Bludit, but a dedicated static page, or a sticky page, is probably the best place for it.

This plugin may also be a good fit for a signage environment where you want to display upcoming events.

_The plugin contains no tracking code of any kind_

## Demo

You can see the plugin in action on [bludit-bs5simplyblog.joho.se/whatsup](https://bludit-bs5simplyblog.joho.se/whatsup)

## Requirements

Bludit version 3.15.x or 3.16.x

## Installation

1. Download the latest release from the repository or GitHub
2. Extract the zip file into a folder, such as `tmp`
3. Upload the `whats-up` folder to your web server or hosting and put it in the `bl-plugins` folder where Bludit is installed
4. Go your Bludit admin page
5. Klick on Plugins and activate the `What's Up` plugin

## Usage

Simply put `[whatsup]This text is displayed when there are no events[/whatsup]`, `[whatsup]` or `[whatsup/]` somewhere in your content.

If you include `This text is displayed when there are no events` between the open and close tags, that text will be displayed if there are no events.

The plugin will respect `<pre>..</pre>` and not parse for the shortcodes in that HTML block.

### Disabling the plugin

In the settings section for the plugin, you may choose to temporarily disable the plugin without uninstalling it, thus allowing settings, etc to be retained.

### Local .ics source

You may place a valid `.ics` file in the plugin's `ics` folder and use it as the .ics source instead of a remote URL. The local .ics source will only be used if no URL is specified.

The file `Development.ics` distributed with the plugin is a sample file that you may use to test the plugin.

### Event window

You may need to experiment a bit before finding the optimal values for your .ics source. Due to the nature of .ics sources, the maximum windows is 180 days (90 days past, 90 days future). Each setting has a maximum of 90 days.

### CSS

The plugin uses inline CSS, i.e. it will read the contents of two files and create inline `<style>` tags. The main file is `whatsup.css` located in the plugin's `css` folder. This files relies heavily on CSS variables.

The CSS variables used are stored in the file `whatsup_vars.css` in the `css` folder. You may change these to your liking.

By default the plugin detects the visitor web browser's preference for light or dark mode.

To avoid duplicate inline CSS, the inline CSS is output in the `<head>` section of the generated content.

### Sidebar

You can enable the display of the agenda in the Bludit sidebar, and customizing the title for the sidebar.

If you enable the sidebar hook, no shortcode is required to make the agenda appear in the sidebar. If there is no agenda content, no output is generated.

You may want to customize the CSS for the sidebar display to tighten the whitespace padding, adding a vertical scroll after so many lines, etc. Towards the bottom of the `whatsup.css` file, there are some specific sidebar overrides.

## Translations

The plugin has been localized to Swedish and English. If you want to add a translation, please don't hesitate to reach out with your translation so that it can be included in the official distribution.

## Other things I've created for Bludit

* [BS5Docs](https://bludit-bs5docs.joho.se), a fully featured Bootstrap 5 documentation theme for Bludit
* [BS5SimplyBlog](https://bludit-bs5simplyblog.joho.se), a fully featured Bootstrap 5 blog theme for Bludit
* [BS5Plain](https://bludit-bs5plain.joho.se), a simplistic and clean Bootstrap 5 blog theme for Bludit
* [Chuck Norris Quotes](https://github.com/joho1968/bludit-chucknorrisquotes), provides random Chuck Norris quotes for your Bludit page content
* [Are We Open](https://github.com/joho1968/bludit-areweopen), display availability and/or business operating open/closed notice

## Changelog

### 1.0.0 (2024-11-13)
* Initial release

## Other notes

This plugin has only been tested with PHP 8.1.x, but should work with other versions too. If you find an issue with your specific PHP version, please let me know and I will look into it.

## License

Please see [LICENSE](LICENSE) for a full copy of AGPLv3.

Copyright 2024 [Joaquim Homrighausen](https://github.com/joho1968); all rights reserved.

This file is part of whats-up. whats-up is free software.

whats-up is free software: you may redistribute it and/or modify it  under
the terms of the GNU AFFERO GENERAL PUBLIC LICENSE v3 as published by the
Free Software Foundation.

whats-up is distributed in the hope that it will be useful, but WITHOUT
ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or
FITNESS FOR A PARTICULAR PURPOSE. See the GNU AFFERO GENERAL PUBLIC LICENSE
v3 for more details.

You should have received a copy of the GNU AFFERO GENERAL PUBLIC LICENSE v3
along with the whats-up package. If not, write to:
```
The Free Software Foundation, Inc.,
51 Franklin Street, Fifth Floor
Boston, MA  02110-1301, USA.
```

## Credits

The What's Up plugin for Bludit was written by Joaquim Homrighausen while converting :coffee: into code.

Kudos to [Diego Najar](https://github.com/dignajar) for [Bludit](https://bludit.com) :blush:

This Bludit plugin uses the excellent [sabre/vobject](https://sabre.io/vobject/) package :blush:

### Whatever

Commercial support and customizations for this plugin is available from WebbPlatsen i Sverige AB.

For commercial usage, I kindly ask that you pay USD/EUR 25 per site to sponsor further development and support.

If you find this Bludit add-on useful, feel free to donate, review it, and or spread the word :blush:

If there is something you feel to be missing from this Bludit add-on, or if you have found a problem with the code or a feature, please do not hesitate to reach out to bluditcode@webbplatsen.se.

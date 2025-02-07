INTRODUCTION
============

This module integrates the 'Pattern.css' library is a CSS only library
to fill your empty background with beautiful patterns:
  - https://github.com/bansal/pattern.css


FEATURES
--------

'Pattern.css' library is:

  - Less than 1KB minified and gzipped!

  - Easy to use

  - Supports all modern browsers

  - Usage with and also without Javascript

  - Responsive

  - Customizable and Full color control.


REQUIREMENTS
============

You don't need to download library, Already included.


INSTALLATION
============

1. Download 'PatternCSS' module:
   - https://www.drupal.org/project/patterncss

2. Extract and place it in the root of contributed modules directory i.e.
   - /modules/contrib/patterncss (or /modules/patterncss)

3. Now, enable 'PatternCSS' module.


USAGE
=====

pattern.css can be used with any css framework.

BASIC USAGE
-----------

&lt;div class="pattern-checks-sm bg-blue white"&gt;...&lt;/div&gt;


WITH BOOTSTRAP FRAMEWORK
------------------------

&lt;div class="pattern-checks-sm bg-primary text-white"&gt;...&lt;/div&gt;


USAGE WITH JAVASCRIPT
---------------------

You can do a bunch of other stuff with pattern.css when you combine
it with Javascript. A simple example:

const element = document.querySelector('.my-element');
element.classList.add('pattern-checks-sm', 'bg-blue');


HOW DOES IT WORK?
=================

  * It's very simple, if you just want to load the library and use its
    css classes manually in your theme, just enable the PatternCSS module.

  * But if you don't want to touch the code, also enable PatternCSS UI
    submodule to give you more settings for the pages you want to use this
    library and easily without the need programming in the areas you
    want to add pattern. Add your desired style pattern by entering
    the selector and customize pattern style.


PatternCSS module
-----------------

1. Enable "PatternCSS" module, Follow INSTALLATION in above.

2. Add pattern.css classes to templates or add classed with javascript file
   in your theme, Follow USAGE in above.

3. Enjoy that.


PatternCSS UI module
--------------------

1. Enable "PatternCSS UI" sub-module.

2. Go to /admin/config/user-interface/patterncss

3. Click 'Add pattern' button in top of the page.

4. Enter area (Segment) selector you want to add pattern
   by using right-click on your website, choose "Inspect"
   or "View page source (CTRL + U)" of context menu  in your Chrome browser
   and find exact valid text class, ID or HTML tag.

  - Use class with dot(.) and ID with hash(#) prefix or HTML tags.
    e.g. ".page-title", "#main" or "body" etc.

  - Nested selector is allowed.
    e.g. ".sidebar .block-contact", "body.page-node-type-article .main-content"

5. Select segment (Determines your pattern type).

6. Choose you pattern style.

7. From right sidebar you can change pattern style such as:
  - Pattern color
  - Background color (with opacity)
  - Background position (with opacity)
  - Pattern size.
  * For Separator and image shadow you will have other
    option like height and transform.

8. You will css your final pattern style in preview section.

9. Now Save pattern form and Enjoy that.


MAINTAINERS
===========

Current module maintainer:

 * Mahyar Sabeti - https://www.drupal.org/u/mahyarsbt


DEMO
====
https://bansal.io/pattern-css

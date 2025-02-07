

What is Typogrify.module?
=========================

Typogrify.module brings the typographic refinements of Typogrify to Drupal.

* Wraps ampersands (the "&" character) with <span class="amp">&amp</span>.

* Prevents single words from wrapping onto their own line using Shaun Inman's
  Widont technique.

* Converts straight quotation marks to typographer's quotation marks, using
  SmartyPants.

* Converts multiple hyphens to en dashes and em dashes (according to your
  preferences), using SmartyPants.

* Wraps multiple capital letters with <span class="caps">CAPS</span>.

* Wraps abbreviations with <span class="abbr">t.l.a.</span>.

* Wraps initial quotation marks with <span class="quo"></span> or
  <span class="dquo"></span>.

* Adds a css style sheet that uses the <span> tags to substitute a showy
  ampersand in headlines, switch caps to small caps, and hang initial quotation
  marks.

## Twig support

Typogrify provides basic twig filters for usage within the templates.

If no parameters are passed, all supported filters will be performed on the
variable.

```twig
{{ variable|typogrify }}
```

With parameters, only selected filters will be run. Available filters are:
`amp`, `widont`, `smartypants`, `caps`, `initialQuotes`, `dash`.

```twig
{{ variable|typogrify(['amp', 'widont']) }}
```

### Twig auto-escaping

The HTML tags added by the `typogrify` Twig filter will not be escaped, but tags
in the variable passed to the filter may be escaped:

```twig
<p>{{ 'PHP & Twig works.'|typogrify(['amp']) }}</p>
{{ '<p>PHP & Twig does not work.</p>'|typogrify(['amp']) }}
```

Similarly, tags in strings will be escaped, but not tags in markup objects:

```php
function mytheme_preprocess_my_template(array &$variables): void {
  $variables['test_string'] = 'test <strong>string</strong> & apply typogrify';
  $variables['test_object'] = t('test <strong>object</strong> & apply typogrify');
}
```

Those variables can be used in a template:

```twig
{# File my-template.html.twig #}
  <p>Escaped 'strong' tag: {{ test_string|typogrify(['amp']) }}</p>
  <p>This works as expected: {{ test_object|typogrify(['amp']) }}</p>
```

## Learn more about Typogrify

Typogrify originated as Python code by Christian Metts. Typogrify.module uses
Hamish Macpherson's port, php-typogrify.

Project Page:
http://code.google.com/p/typogrify/

Typogrify.module uses PHP SmartyPants:
http://www.michelf.com/projects/php-smartypants/

To learn more about Widont:
http://www.shauninman.com/archive/2006/08/22/widont_wordpress_plugin

## Learn more about setting type for the web

The Elements of Typographic Style Applied to the Web
http://webtypography.net/

Five simple steps to better typography
http://www.markboulton.co.uk/journal/five-simple-steps-to-better-typography

Thinking With Type
http://www.thinkingwithtype.com/

And if you're going to buy one book...
http://www.amazon.com/Elements-Typographic-Style-Robert-Bringhurst/dp/0881791326

[Prism](https://prismjs.com/) is a lightweight, extensible syntax highlighter, built with modern web
standards in mind.

This module integrates the 3rd-party library with Drupal. And provides the
following features:

- A text_long_prism field type, formatter, and widget. Allows adding a field to
  any entity that will collect, and display, a code snippet and language the
  code is written in.
- A text filter for highlighting code as part of a text formatter.
- Configuration for the libraries settings.

## Installation

This module requires the _prism.js_, and _prism.css_ files be added to your
project's _/libraries_ directory.

You can download the files from https://prismjs.com/download.html. You can
either customize the bundle to support only the necessary languages, or download
the complete set. Either will work fine.

And place them in:

- _libraries/prism/prism.js_
- _libraries/prism/prism.css_

Once enabled your code simply needs to be wrapped in the correct syntax and
inserted into any text area. Here is an example using css highlighting.

Using `[prism:*]` tags:

```
[prism:css]
a {
	color: #7BC673;
}
[/prism:css]
```

Using `<pre><code>` tags:

```
<pre><code class="language-css">
a {
	color: #7BC673;
}
</code></pre>
```

This currently only works if the `<code>` tag includes a `language-*` class.

### Use with Markdown

Markdown supports adding code blocks with backticks (`), and code fences (```). If the Markdown filter runs before the Prism.js filter, and the Markdown filter converts code blocks to `<pre><code class="language-*">` format then the two should work well together.

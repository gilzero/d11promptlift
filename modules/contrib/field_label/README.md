# Field label

Field Label extends all field formatters to allow authorized users to:
- Change field label text values
- Set a field label plural value for fields with multiple cardinality
- Choose an HTML tag from a configurable list to use as the label wrapper
- Add one or more classes to the label tag

Each of the above features can be enabled/disabled according to the needs of
the project. Additionally, Field Label provides a permission for each feature to
allow granular control access to its features.

Field formatter permissions can be particularly useful when Layout Builder is
in use, and layout overrides potentially expose field formatters to users who
have never had access to settings that were only available to administrators or
site builders in the past (e.g. manage display).

- For a full description of the module, visit the
  [project page](https://www.drupal.org/project/field_label).

- Submit bug reports and feature suggestions, or track changes in the
  [issue queue](https://www.drupal.org/project/issues/field_label).


## Requirements

- Drupal core 8.7.6 or higher

- Field Label requires #3029627: [FormatterBase should pass along third party settings](https://www.drupal.org/project/drupal/issues/3029627), which was
added to core with the 8.7.6 patch release.


## Installation

- Install as you would normally install a contributed Drupal module. For further
information, see
[Installing Drupal Modules](https://www.drupal.org/docs/extending-drupal/installing-drupal-modules).

- **IMPORTANT** If your theme implements field templates (e.g. field.html.twig,
  field__*.html.twig, etc.) you'll need to update them to use the `label_tag`
  variable to get Field Label's features. For example, a typical field template
  implementation that renders the label like this:

  ```
  <div{{ title_attributes }}>
    {{ label }}
  </div>
  ```

  Would need to be updated to something like this:

  ```
  <{{ label_tag|default('div') }}{{ title_attributes }}>
    {{ label }}
  </{{ label_tag|default('div') }}>
  ```


## Configuration

- Configure the user permissions in Administration » People » Permissions:

  - Edit field label values

    Users with this permission are able to overwrite the field label used in
    entity displays.

  - Edit field plural label

    Users with thie permission are able to choose a plural label for fields
    with multiple cardinality.

  - Add "free-form" classes to field label tags

    Users with this permission are able to enter a list of space-separated
    CSS class identifiers to add to the label wrapper tag.

  - Add a class to field label tags from a configured list

    Users with this permission are able to select a class identifier from a
    pre-configured list of classes. The class will be added to to the label
    wrapper tag.

  - Select a wrapper tag for field labels

    Users with this permission are able to select a tag from a pre-configured
    list of HTML tags to use as the label wrapper.

- Customize Field Labels settings, including enabled features, CSS Classes, and
  label wrapper tags at Administration » Configuration » Content authoring »
  Field Label.

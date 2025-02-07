# Component form

The biggest feature of UI Patterns is the generation of forms from the components definition:

| From component definition....     | ... to component form   |
| --------------------------------- | ----------------------- |
| ![](images/bs-alert-example.webp) | ![](images/form-1.webp) |

Those forms will be shown everywhere a component is used:

- [as blocks](1-as-block.md)
- [as layouts](1-as-block.md)
- [in field formatters](1-as-block.md)
- [in views](1-as-block.md)
- ...

## Data sources

Values are taken from sources:

- "Widgets": simple form elements storing directly the data filled by the user. For example, a `textfield` for a string or a `checkbox` for a boolean.
- Sources retrieving data from Drupal API: they can be context agnostic (ex: a menu for links) or context specific (ex: the title field for a string)
- Context switchers: They don't retrieve data but they give access to other data sources. For example, the author fields from an article content.

If there is only a single source available, the source form is directly displayed:

![](images/sources-1.webp)

If there are at least 2, a source selector is shown:

![](images/sources-2.webp)

Some sources doesn't have a form, selecting the source is enough:

![](images/sources-3.webp)

## Context

Sometimes sources require another object in order to retrieve the data. This is known as context.

Some source doesn't need a context and are site wide, for example:

- All “widgets” which are source plugins with only direct input: Textfield, Select, Checkbox…
- List of all menus
- The breadcrumb
- …

But some of them will need context. Examples:

| Context         | Source              | Prop type | Description                                |
| --------------- | ------------------- | --------- | ------------------------------------------ |
| Content entity  | Data from a field   |           | Switch to a Field context.                 |
| Content entity  | Entity link         | URL       |
| Content entity  | Referenced entities |           | Switch to an other Content entity context. |
| Field           | Field formatter     | Slot      |
| Field           | Field label         | String    |
| Field           | Field prop : \*     | (many)    |
| Reference field | Field prop: entity  |           | Switch to a Content entity context.        |
| View            | View title          | String    |
| View            | View rows           | Slot      |
| View row        | View title          | String    |
| View row        | View field          | Slot      |

## Variant selector

Some components have variants, a list of different "look" of the component. Variants doesn't change the model or the meaning of the component, only the look.

![](images/variant-1.webp)

## Slots

SDC components are made of slots & props:

- **Slots**: “areas” for free renderables only, like Drupal blocks or other SDC components for example.
- **Props**: strictly typed data only, for some UI logic in the template.

You can draw the slots areas in a component screenshot:

![](images/slots-vs-props.webp)

For each slots, its is possible to add one or many sources:

![](images/slot-1.webp)

For example:

- "Component": nest another SDC component in the slot.
- "Block": add a Drupal block plugin
- "Wysiwyg": a simple text editor, using the text formats defined in your site.

Other modules can add other sources. For example, "Icon" in this screenshot is brought by https://www.drupal.org/project/ui_icons

Once a source is added, it can be configured and we can add more because the selector is still present:

![](images/slot-2.webp)

Sources can be reordered inside a slot.

Using the "Component" source, we have access to the embedded component slots and we can nest data:

![](images/slot-3.webp)

## Props

A bit like the form for slots with 2 main differences:

- We don’t allow multiple items, so we can replace the source but not add some (and of course no reordering)
- The default source form is already loaded.

![](images/props-1.webp)

The available sources are varying according to both:

- the prop type. Each prop as a type, which is related to its JSON schema typing, but not exactly the same. You can check what type has a prop in the [component library](../2-authors/1-stories-and-library.md). A prop without a type is not displayed in the form.
- the context, as explained before.

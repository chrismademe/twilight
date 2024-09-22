# Sub Components

Sometimes you may want to split a large component, but it doesn't make sense to use a top-level component because it requires context of the parent, for example an `<Accordion />`. To create a sub component, you can place a directory inside the parent.

```
components/
    Accordion/
        Item/
            template.twig
```

To use a sub component, include a `.` in the component name:

```html
<Accordion>
    <Accordion.Item>...</Accordion.Item>
</Accordion>
```
# Slots

You will often want to pass additional content to your components. You can do this via Slots. In it's most basic form, to render content inside a component, use the `<Children />` slot component:

```html
<a class="btn" :href="href">
    <Children />
</a>
```

This is good, but what if we want to allow an icon to be placed inside the button? We could offer an `icon` prop, but this may become an issue for customisability. Instead, we can include slots before and after the main content:

```html
<a class="btn" :href="href">
    {{ slots.before | raw }}
    <Children />
    {{ slots.after | raw }}
</a>
```

Here's how this would look if we were to pass an icon before the main content:

```html
<Button href="/contact">
    <Slot name="before">
        <Icon name="phone">
    </Slot>
    Contact Us
</Button>
```
# Creating Components

To create a component, first create a `components` directory in the root of your views directory. Each component should have a directory with the component name, and a `template.twig` file. Here's how you'd create a simple `<Button />` component

```
views/
    components/
        Button/
            template.twig
```

template.twig
```html
<a class="btn" :href="href">
    <Children />
</a>
```

To use it, place it in one of your views:

```html
<div class="content">
    <h2>Contact Us for a Quote</h2>
    <Button href="/contact">Contact Us</Button>
</div>
```
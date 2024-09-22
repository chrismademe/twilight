# Passing Data to Components

To pass data to components, you can use HTML attributes. Hard-coded values can be passed as simple HTML attributes, as you would on any other element. To pass Twig expressions or variables, you should prefix the attribute name with a `:` character:

```html
<Button variant="success" :href="page.url">
    Contact Us
</Button>
```

**Note** If you're an AlpineJS user, you should use `x-bind:` instead of the shorthand `:` syntax to ensure the attributes are included in the output.
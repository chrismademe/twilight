# Dynamic Elements

There are times where you may want to change the output HTML element based on a condition. To do this, you can use the `<Element />` component.

This component accepts a special `is` attribute, which tells the compiler what to render the element as. In it’s most basic form, it can be a static element name (which is a little pointless) but here’s an example:

```html
<Element is="button" type="submit">Submit</Element>
```

Which would result in:

```html
<button type="submit">Submit</button>
```

There’s really no value in doing that; the real value comes in when you use a dynamic attribute to set the `is` value. A good example of this a button, which may actually be a link.

```html
<Element :is="href ? 'a' : 'button'" :href="href" :type="type">
	Button
</Element>
```

By using a dynamic `is` attribute, the element rendered will be determined by the condition.

All attributes (apart from `is`) will be passed to the HTML element.
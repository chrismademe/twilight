# WIP

This project is work in progress. Documentation will come later, below are a few examples of what can be done with this.

## For Loop

**Input**

```twig
<ul>
	<li @for="item in items" @html="item" />
</ul>
```

**Output**

```twig
<ul>
	{% for item in items %}
	    <li>{{ item | raw }}</li>
	{% endfor %}
</ul>
```

## Conditional, Directives and Dynamic Attributes

**Input**

```twig
{% set imageURL = '/images/example.png' %}
<img @if="imageURL" :src="imageURL" alt="Example" />
<p @unless="imageURL" @text="Image coming soon" />
```

**Output**

```twig
{% if imageURL %}
    <img src="/images/example.png" alt="Example">
{% endif %}

{% if not imageURL %}
    <p>Image coming soon</p>
{% endif %}
```

## Component

**Input**

```twig
<Container>
    <Button variant="brand" size="sm" type="submit">
        Button
    </Button>
</Container>
```

**Output**

```twig
{% set Container_f11a5e0e13_children %}
    {% set Button_ee70f5738f_children %}
        Button
    {% endset %}
    {{ render_component("Button", {
        variant: "brand",
        size: "sm",
        type: "submit",
        children: Button_ee70f5738f_children
    }) }}
{% endset %}
{{ render_component("Container", {
    children: Container_f11a5e0e13_children
}) }}
```

**Note** This library does not implement the `render_component` Twig function. You must do that in the way that suits you.

# Directives

Directives are special attributes that instruct the compiler to do something with the output of an element. Commonly this is wrapping the markup with some Twig syntax, like an if conditional. Directives always start with an @ symbol.

## if
Wraps an element in an if condition

```html
<p @if="someCondition">
    I will only show up is someCondition is true
</p>
```

## unless
Wraps an element in an if condition, checking for a falsey value

```html
<p @unless="someCondition">
    I will only show up is someCondition is false
</p>
```

## for

Wraps an element in a for loop. You may also use `@each`, if you prefer.

```html
<ul>
	<li @for="post in posts">{{ post.title }}</li>
</ul>
```

## checked

Will add a `checked` attribute to a Checkbox input if the passed value is true.

```html
<input
    type="checkbox"
    name="isComplete"
    @checked="task.isComplete"
/>
```

## disabled

Will add a `disabled` attribute to an element if the passed value is true.

```html
<input
    type="checkbox"
    name="isComplete"
    @disabled="task.isComplete"
/>
```

## selected

Will add a `selected` attribute to an element (usually an `<option>`) if the expression passed matches that of the `value` attribute, for example if `user.country` is "UK", the UK option will be selected.

```html
<select name="country">
    <option value="UK" @selected="user.country">United Kingdom</option>
    <option value="US" @selected="user.country">United States</option>
</select>
```

## text

Inserts the content of a variable inside the element, with HTML escaped.

```html
<span @text="post.author" />
```

## html

Inserts the content of a variable inside the element, with the `raw` filtered applied

```html
<datetime @html="post.date()" />
```

## attributes

Adds a collection of attributes to the current element. Can be a simple string of attributes or an array with key, value pairs. You may omit a value for this directive, and it will assume the variable name is `attributes`

```html
<div @attributes /> <!-- The same as @attributes="attributes" -->
```
# Dynamic Components

In much the same way you can dynamically set an HTML element tag, you can use the `<Component />` component to do so with components. In the example below we have a `<FeaturedPost />` component which we want to use when the current post is featured.

```html
<Component :is="post.is_featured ? 'FeaturedPost' : 'Post'" :id="post.id" />
```

All properties will be passed to the component.
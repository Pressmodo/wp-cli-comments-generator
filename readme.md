## WP CLI Comments generator

Generate random comments for all posts on your WordPress website using available users.

Each blog post will have 1 comment from each user registered on the site. The content of the comment is generated with generic lorem ipsum text.

## Why?

This command has been created for testing purposes when developing WordPress themes.

## Install
WP CLI Comments generator requires Composer and WP-CLI to function.

```
wp package install pressmodo/wp-cli-comments-generator
```

## Commands

#### Generate comments

```
wp comments-generator generate
```

#### Delete all blog posts comments

```
wp comments-generator wipe
```

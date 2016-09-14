Sandstone documentation
=======================

The documentation is built with [Jekyll](http://jekyllrb.com/).

## Contribute

### Installation

``` bash
cd sandstone/doc
bundle install
```


### Preview

``` bash
bundle exec jekyll serve
```

Then go to [http://localhost:4000/sandstone/](http://localhost:4000/sandstone/) (don't forget trailing slash).

It will watch for file changes, so just refresh after your changes.


## Publish

``` bash
cd sandstone/doc
bundle exec jekyll build --destination /tmp/sandstone/
cd ..
git checkout gh-pages
rm -fr *
mv /tmp/sandstone/* .
rm Gemfile Gemfile.lock README.md
git add -A
git ci -m "Publish"
git push origin gh-pages
git checkout dev # or the branch you were
rm -fr /tmp/sandstone
```

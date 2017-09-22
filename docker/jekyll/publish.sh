#!/bin/bash

rm -fr /tmp/sandstone /tmp/sandstone-build

bundle install
bundle exec jekyll build --destination /tmp/sandstone-build/
cd ..

cd /tmp
git clone https://github.com/eole-io/sandstone.git
cd sandstone/

git config --global user.email "julien.maulny@protonmail.com"
git config --global user.name "Alcalyn"

git fetch origin
git checkout gh-pages
rm -fr *
mv /tmp/sandstone-build/* .
rm Gemfile Gemfile.lock README.md
git add -A
git commit -m "Publish"
echo "---"
echo "Password: generate access token from: https://github.com/settings/tokens"
echo "---"
git push origin gh-pages
cd /

rm -fr /tmp/sandstone /tmp/sandstone-build

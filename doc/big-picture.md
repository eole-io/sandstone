---
layout: page
title: Big picture
---

<h1 class="no-margin-top">Big picture</h1>

Sandstone runs a RestApi server and a Websocket server.

This is two stacks, running on a different processus.
The big picture show all stacks components, and list all steps
in a standard use case, a RestApi request that triggers a Push event.

<img
    src="img/sandstone-big-picture.png"
    alt="Sandstone big picture"
    class="img-responsive"
/>

### Step 0

Javascript client open a connection to websocket server.
It then subscribes to `articles` topic in order to receive
all new published articles as soon as someone posted one.

> See `ChatTopic` class, or Javascript client implementation example in
> [Full example]({{ site.baseurl }}/examples/multichannel-chat.html).

### Step 1

A web client creates a new article. He then POST it to RestApi server.

### Step 2

Nginx handle the HTTP request, and aks for php-fpm to execute PHP application
to return processed result.

### Step 3

PHP runs the application. In Sandstone, it means:
 - solve the route,
 - execute the ArticleController
 - persist the article in database
 - dispatch an `ARTICLE_CREATED` event through the Event Dispatcher

### Step 4

If the `ARTICLE_CREATED` evnt has been marked as *forward* with
`$app->forwardEventToPushServer('article.created');`,
Sandstone will automagically forward it to the websocket server process.

The magic trick here is to use a ZMQ socket. Then Sandstone serialize the event,
send it through the ZMQ socket. The websocket process then deserialize it,
and dispatch it in his Event dispatcher.

### Step 5

Your `ArticlesTopic`, which is listening to the `ARTICLE_CREATED`,
calls the listener. This listener will JSON serialize and `broadcast` the event
to the `articles` websocket topic.

> See `ChatTopic` class to see how to listen to event:
> [Full example]({{ site.baseurl }}/examples/multichannel-chat.html).

### Step 6

The Javascript client, which subscribed to `articles` topic,
receives the JSON serialized event.
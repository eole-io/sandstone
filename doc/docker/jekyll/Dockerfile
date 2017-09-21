FROM ruby:2

RUN apt-get update && \
    apt-get install locales && \
    curl -sL https://deb.nodesource.com/setup_8.x | bash - && \
    apt-get install -y nodejs && \
    locale-gen en_US.UTF-8 && \
    localedef -i en_US -f UTF-8 en_US.UTF-8

ENV LANG en_US.UTF-8
ENV LC_ALL en_US.UTF-8

RUN gem install bundler

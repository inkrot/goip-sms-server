FROM i386/debian:7-slim

ENV DEBIAN_FRONTEND noninteractive
ENV DATE_TIMEZONE Europe/Moscow

RUN echo 'deb http://archive.debian.org/debian wheezy main contrib non-free\ndeb http://archive.debian.org/debian-security/ wheezy/updates main' > /etc/apt/sources.list \
	&& apt-get --allow-unauthenticated -o Acquire::Check-Valid-Until=false update \
	&& apt-get install --allow-unauthenticated -f --no-install-recommends -y \
		apache2 \
		libapache2-mod-php5 \
		php5-mysql \
	&& apt-get -y clean \
	&& rm -rf /var/lib/apt/lists/*

COPY ./goip_install /goip_install
COPY ./entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh
RUN chmod +x /goip_install/goip_install.sh
RUN /goip_install/goip_install.sh

EXPOSE 44444
EXPOSE 44444/udp

ENTRYPOINT ["/entrypoint.sh"]

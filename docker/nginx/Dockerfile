FROM nginx:1.14.2

MAINTAINER Lisa Ridley "lisa@codementality.com"

COPY ./default.conf /etc/nginx/conf.d/default.conf

# Add entrypoint script
COPY docker-entrypoint.sh /usr/local/bin/
# Make sure it's executable
RUN chmod a+x /usr/local/bin/docker-entrypoint.sh

ENTRYPOINT ["/usr/local/bin/docker-entrypoint.sh"]

CMD ["nginx","-g daemon off;"]

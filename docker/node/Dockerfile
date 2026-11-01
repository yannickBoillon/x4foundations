FROM node:24-alpine

COPY --chmod=755 docker-entrypoint.sh /usr/local/bin/docker-entrypoint

ENV HOME=/tmp \
    npm_config_cache=/tmp/.npm

WORKDIR /app

EXPOSE 5173

ENTRYPOINT ["docker-entrypoint"]
CMD ["npm", "run", "dev", "--", "--host", "0.0.0.0", "--port", "5173"]

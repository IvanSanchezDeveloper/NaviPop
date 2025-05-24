FROM node:20

WORKDIR /var/www/html/app/frontend

COPY package*.json ./

RUN npm install

EXPOSE 3000
CMD ["npm", "start"]

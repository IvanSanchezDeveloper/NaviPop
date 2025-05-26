FROM node:20

WORKDIR /var/www/html/app/frontend

COPY package.json package-lock.json*  ./
RUN npm install

COPY . .

EXPOSE 3000
CMD ["npm", "run", "dev", "--", "--host", "--port", "3000"]

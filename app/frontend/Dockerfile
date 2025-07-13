FROM node:20

RUN apt update && apt install -y \
    xvfb \
    libgtk2.0-0 \
    libgtk-3-0 \
    libgbm-dev \
    libnotify-dev \
    libgconf-2-4 \
    libnss3 \
    libxss1 \
    libasound2 \
    libxtst6 \
    && apt clean

WORKDIR /var/www/html/app/frontend

COPY package.json package-lock.json* ./
RUN npm install

COPY . .

EXPOSE 3000

RUN chmod +x ./build-env.sh

ENTRYPOINT ["./build-env.sh"]
CMD ["sh", "-c", "npm run dev -- --host --port 3000"]

# AmoCRM Integration Project (https://your-name.amocrm.ru/amo-market)

This project integrates with AmoCRM using OAuth2 for user authentication and provides list of leads.

### Table of Contents

1. [Introduction](#introduction)
2. [Installation](#installation)
3. [Usage](#usage)
4. [Important Notice](#important-notice)

### Introduction

This project allows users to authenticate with AmoCRM and manage leads directly from the application. It provides a seamless integration experience with AmoCRM's API.

### Installation

1. Clone the repository:
   ```bash
   git clone git@github.com:DavSar06/amocrm.git
   cp .env.example .env
   php artisan key:generate

2. Create AmoCRM free account

3. Enter AmoMarket and Press three dots on top-right corner

4. Press Create Integration

5. After creating integration fill redirect_uri, integration_id, client_secret, base_domain in .env file

6.
     ````bash
     php artisan config:clear
     composer install
     php artisan migrate
7. If running locally you need service like ngrok to host your localhost into cloud so you can get the redirect_uri.
   Download <a href="https://ngrok.com/">ngrok</a> and install. Open the ngrok.exe file and run command
    ````bash
    ngrok http 8000 \\ngrok.exe
    php artisan serve \\In folder with the project
Then put the link in redirect_uri designated place.

### Usage

In the home page ('http://localhost:8000/') you have an option to login.
Pressing the button sends you to AmoCRM website where you need login or choose the logged user to access.
When pressing Разрешить you will get back into home page with different ui. 
Pressing the button Get Leads fetches all the leads of the website. Using simple search and pagination you can navigate through list.

### Important Notice

I handled wrong requests by throwing the relevant error screen if needed, for example 403 access-denied if the status is changed on callback.

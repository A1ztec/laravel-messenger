# Messenger App

A simple real-time messenger application built using **Laravel** and **Vue.js**, with **Pusher** for real-time updates, **Laravel Breeze** for authentication, and **Laravel Echo** for broadcasting events. The app allows users to add friends and send real-time messages.

## Features

- Real-time messaging using **Pusher** and **Laravel Echo**
- User authentication with **Laravel Breeze**
- Add friends and send messages
- Responsive design for mobile and desktop
- Vue.js for dynamic, reactive front-end interactions
- Laravel-powered backend for robust API handling

## Tech Stack

- **Laravel**: Backend framework
- **Vue.js**: Frontend JavaScript framework
- **Pusher**: Real-time WebSocket server
- **Laravel Echo**: Event broadcasting
- **Laravel Breeze**: For basic authentication scaffolding

## Installation and Setup

### Prerequisites

Ensure you have the following tools installed on your system:

- **PHP** >= 8.1
- **Node.js** >= 18.x
- **Composer**
- **MySQL** or another supported database
- **Pusher account** for WebSockets

### Steps to Install

1. **Clone the Repository**

         ```bash
        git clone https://github.com/A1ztec/messenger-app.git
        cd messenger-app


2. **Install Backend Dependencies**

   Run the following command to install all Laravel and PHP dependencies:

           ```bash
           composer install
   
3. **Install Frontend Dependencies**
     
   Next, install all frontend dependencies using npm:

           ```bash
           npm install
   
4. **Set Up Environment Variables**

   Create a new `.env` file by copying the `.env.example` file:

          ```bash
          cp .env.example .env

Then, update the following values in the `.env` file:

- **Database Configuration**: Set your MySQL or preferred database credentials.

- **Pusher Configuration**: Add your Pusher API credentials (you can create an account and get the API keys from [Pusher](https://pusher.com)):

           ```env
           PUSHER_APP_ID=your-pusher-app-id
           PUSHER_APP_KEY=your-pusher-app-key
           PUSHER_APP_SECRET=your-pusher-app-secret
           PUSHER_APP_CLUSTER=your-pusher-cluster

5. **Generate Application Key**

   Generate a new application key using the `artisan` command:

       ```bash
       php artisan key:generate

6. **Run Migrations**

   Run the database migrations to create the necessary tables:

        ```bash
        php artisan migrate

## Build the Frontend Assets

Build the front-end using Laravel Mix:

       ```bash
       npm run dev

## Start the Development Server

Finally, start the development server to run the application:

        ```bash
        php artisan serve

The application will be available at `http://localhost:8000`.


# Appointment Reminder System API

A RESTful API built with **Laravel 12** for managing appointments and sending automated reminders. Designed for businesses to create appointments for their clients and schedule notifications in a timezone-aware, asynchronous way.

---

## 🚀 Features

- User registration and login via **Laravel Sanctum**
- Create one-time or **recurring appointments**
- Automatic **reminder scheduling using queues**
- **Timezone-aware** appointment handling
- Simulated reminder delivery (log-based)
- **Configurable reminder offset** per appointment
- View upcoming and past appointments
- View scheduled and sent reminders
- Built with **Laravel Sail** (Docker) for local development

---

## 🧱 Tech Stack

- PHP 8.3, Laravel 12
- Sanctum (API auth)
- MySQL (via Docker)
- Mailpit (optional)
- Queues via Database driver
- Docker (Laravel Sail)

---

## 🧪 API Endpoints

### 🔐 Authentication

| Method | Endpoint     | Description                             |
|--------|--------------|-----------------------------------------|
| POST   | `/register`  | Register a new user and get a token     |
| POST   | `/login`     | Log in and get token                    |
| POST   | `/logout`    | Revoke current token                    |

### 📅 Appointments

| Method | Endpoint                         | Description                       |
|--------|----------------------------------|-----------------------------------|
| POST   | `/appointments`                  | Create an appointment             |
| GET    | `/appointments/status/{status}`  | View upcoming or past appointments (`status = upcoming|past`) |
| GET    | `/appointments/{id}`             | View appointment details          |
| PUT    | `/appointments/{id}`             | Update an appointment             |
| DELETE | `/appointments/{id}`             | Delete an appointment             |

### 🔁 Reminders

| Method | Endpoint                         | Description                       |
|--------|----------------------------------|-----------------------------------|
| GET    | `/reminders/status/{status}`     | View scheduled or sent reminders (`status = scheduled|sent`) |

---

## Important notes
1. The project was created using Laravel Sail on M1 Mac with macOS Sequoia 15.4.1

2. Test user created from db seed has the following credentials:  
Email:    test@example.com   
Password: 123456789

3. The user authentication is made with Laravel Sanctum. Sanctum allows you to issue API tokens that may be used to authenticate API requests to the application. When making requests using API tokens, the token should be included in the **Authorization** header as a **Bearer** token.
The tokens for an existing user are recieved from the `/login` endpoint.

4. Please, take a look at the following [Postman documentation](https://documenter.getpostman.com/view/6991599/2sB2qUo57f) in order to see the expected parameters for the different endpoints

5. The sending of reminders is simulated in the log file - `storage/logs/laravel.log`

## ⚙️ Installation (Laravel Sail / Docker)

```bash
# 1. Clone the repo
git clone https://github.com/nikolaylachev/appointment_reminder.git
cd appointment_reminder

# 2. Copy .env and install dependencies
cp .env.example .env
composer install

# 3. Generate app key
php artisan key:generate

# 4. Start Sail; Docker engine has to be started beforehand
./vendor/bin/sail up -d

# 5. Seed the database - Seeds will create a test user and 3 sample clients for the user
./vendor/bin/sail artisan migrate --seed

# 6. Start the queue
./vendor/bin/sail artisan queue:work

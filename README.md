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

- PHP 8.4, Laravel 12
- Sanctum (API auth)
- MySQL (via Docker)
- Mailpit (optional)
- Queues via Database driver
- Docker (Laravel Sail)

---

## 🧪 API Endpoints

### 🔐 Authentication

| Method | Endpoint         | Description                             |
|--------|------------------|-----------------------------------------|
| POST   | `/api/register`  | Register a new user and get a token     |
| POST   | `/api/login`     | Log in and get token                    |
| POST   | `/api/logout`    | Revoke current token                    |

### 📅 Appointments

| Method | Endpoint                             | Description                                                     |
|--------|--------------------------------------|-----------------------------------------------------------------|
| POST   | `/api/appointments`                  | Create an appointment                                           |
| GET    | `/api/appointments/status/{status}`  | View upcoming or past appointments (`status = upcoming/past`)   |
| GET    | `/api/appointments/{id}`             | View appointment details                                        |
| PUT    | `/api/appointments/{id}`             | Update an appointment                                           |
| DELETE | `/api/appointments/{id}`             | Delete an appointment                                           |

### 🔁 Reminders

| Method | Endpoint                             | Description                                                  |
|--------|--------------------------------------|--------------------------------------------------------------|
| GET    | `/api/reminders/status/{status}`     | View scheduled or sent reminders (`status = scheduled/sent`) |

---

## Important notes
1. The project was created using Laravel Sail on M1 Mac with macOS Sequoia 15.4.1

2. Test user created from db seed has the following credentials:  
Email:    test@example.com   
Password: 123456789

3. The user authentication is made with Laravel Sanctum. Sanctum allows you to issue API tokens that may be used to authenticate API requests to the application. When making requests using API tokens, the token should be included in the **Authorization** header as a **Bearer** token.
The tokens for an existing user are recieved from the `/api/login` endpoint.

4. Please, take a look at the following [Postman documentation](https://documenter.getpostman.com/view/6991599/2sB2qUo57f) in order to see the expected parameters for the different endpoints

5. The sending of reminders is simulated in the log file - `storage/logs/laravel.log`

6. If composer hits GitHub's rate limits for unauthenticated API requests when you install the dependencies using `composer install`, you can try to:  
    #### Step 1: Create a token

    Visit this link to generate a token (leave all scopes unchecked):

    👉 https://github.com/settings/tokens/new?scopes=&description=Composer+Token

    - Token will have **read-only access to public packages**
    - Safe to use — no write permissions

    #### Step 2: Add token to Composer - Save token globally
    Run:

    ```bash
    composer config --global github-oauth.github.com YOUR_TOKEN_HERE
    ```
    You can now run: `composer install` without hitting GitHub's API limits.
7. The POST	`/api/appointments` endpoint has the following parameters in it's body:
    ```
    {
        "client_id": 3, // The client id for the appointment
        "start_time": "2025-05-15T15:45:00", // The start time of the appointment in the client's local time
        "timezone": "Europe/Sofia", // The client's timezone
        "recurrence": "weekly", // weekly/monthly recurrence or null for no recurrence
        "notes": "Weekly follow-up",
        "reminder_offset": 2, // The reminder offset in minutes
        "repeat_until": "2025-05-31T12:00:00" // When should the appointment stop recurring if needed. In the client's local time
    }
    ```
8. The `appointments:generate-recurring` command which generates recurring appointments is scheduled to run every day at midnight in `bootstrap/app.php`. You can see that it's scheduled using: `./vendor/bin/sail artisan schedule:list`  
If you want to manually test the command, you can run it directly:  
`./vendor/bin/sail artisan appointments:generate-recurring`  
or if you prefer, you can use `./vendor/bin/sail artisan schedule:test` to run the command  
The command writes its output in `storage/logs/laravel.log`
---
## ⚙️ Installation (Laravel Sail / Docker)

```bash
# 1. Clone the repo
git clone https://github.com/nikolaylachev/appointment_reminder.git
# or git clone git@github.com:nikolaylachev/appointment_reminder.git depending on your configuration
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

# 7. List all scheduled tasks and manually run the command for generating recurring appointments (Optional)
./vendor/bin/sail artisan schedule:list
./vendor/bin/sail artisan appointments:generate-recurring
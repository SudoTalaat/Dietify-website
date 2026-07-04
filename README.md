 # HealthyFood Web Application

A secure, PHP-based web application featuring two-factor authentication (2FA), rate limiting, and an integrated Web Application Firewall (WAF).

## Features
- **User Authentication & 2FA**: Secure login with Two-Factor Authentication via email.
- **Security & Rate Limiting**: Redis-backed rate limiting and security measures to prevent abuse.
- **Secret Management**: API keys and sensitive credentials are safely managed via a `.env` file.
- **Web Application Firewall (WAF)**: Configurable batch scripts (`waf_level_1.bat`, `waf_level_2.bat`, etc.) for network-level security.

## Prerequisites (Windows Environment)
This project is optimized for a Windows environment. Ensure you have the following installed:
1. **XAMPP**: For Apache and MySQL. Recommended installation path is the `C:\` drive.
2. **Memurai**: A native Windows port for Redis (NoSQL in-memory database), which is strictly required for rate limiting and 2FA functionality.
   - Download the Developer Edition here: [https://www.memurai.com/get-memurai](https://www.memurai.com/get-memurai)

## Installation Guide

### 1. Set Up the Server
- Install XAMPP and start the **Apache** and **MySQL** modules from the XAMPP Control Panel.
- Clone or extract this application folder into your XAMPP `htdocs` directory (e.g., `C:\xampp\htdocs\app`).

### 2. Configure the Database
- Open `phpMyAdmin` (typically available at `http://localhost/phpmyadmin`).
- Create a new database for the application.
- Import the provided database dump file (either `healthyfood.sql` or `healthyfood-no-data.sql` depending on if you want sample data) located in the app folder into your new database.

### 3. Install Redis (Memurai)
- Run the Memurai installer to set up the Redis instance on your machine. Ensure the Memurai service is running.

### 4. Environment Variables
- Copy the provided `.env.example` file and rename it to `.env`.
- Fill in the required credentials inside the `.env` file (e.g., database connection details, API keys).
- *Note: The `.env` file is included in `.gitignore` to prevent sensitive keys from being exposed on GitHub.*

### 5. Install Dependencies (Composer)
This project relies on [Composer](https://getcomposer.org/), the dependency manager for PHP, to install essential third-party libraries.

1. **Install Composer**: If you do not have Composer installed, download and install it from [getcomposer.org](https://getcomposer.org/download/).
3. **(Optional) Install Packages**: The required dependencies (like `phpdotenv`, `predis`, `stripe`, etc.) are already included in the `vendor/` folder within the repository. You do not need to run Composer unless you want to update or add new packages. If needed, you can run:
   ```bash
   composer install
   ```
   This command reads the `composer.json` file and downloads all required dependencies into the `vendor/` folder, including:
   - `vlucas/phpdotenv`: For securely loading environment variables from the `.env` file.
   - `predis/predis`: For interacting with the Redis server to handle rate limiting and OTPs.
   - `phpmailer/phpmailer`: For sending 2FA emails and security alerts.
   - `stripe/stripe-php`: For secure credit card payment processing.
   - `pragmarx/google2fa`: For handling Time-Based One-Time Passwords (TOTP).

## Firewall / WAF Scripts
NOTE: you can use this becuse you will need to install modsec sparately also the bat files might need changing the service name for some resone
The repository includes batch scripts that act as a basic Web Application Firewall:
- `waf_level_1.bat`
- `waf_level_2.bat`
- `waf_on.bat` / `waf_off.bat`

You can run these scripts as Administrator to enforce additional network-level security.

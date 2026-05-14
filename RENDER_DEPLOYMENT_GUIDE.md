# Render + Aiven MySQL Deployment Guide

This guide explains how to deploy MyFintechApp to **Render** using **Aiven MySQL** as the database.

---

## SECTION 3: Environment Setup & Render Configuration

### What's Already Done ✅

1. **Procfile** - Configured with web, worker, and scheduler processes
2. **render.yaml** - Deployment manifest for Render
3. **.env** - Base configuration (local mode)
4. **Database** - Aiven MySQL already configured in .env

### What You Need to Do 📝

#### PART 1: Aiven MySQL Configuration

Your Aiven MySQL is already in `.env`. Verify these credentials:

```env
DB_CONNECTION=mysql
DB_HOST=kafka-12be93c2-faithfulnesssemilore-098b.c.aivencloud.com
DB_PORT=27521
DB_DATABASE=defaultdb
DB_USERNAME=avnadmin
DB_PASSWORD=AVNS_uPaNNMRmJGA7H3izvD5  # CHANGE THIS - it's exposed
```

**ACTION REQUIRED:**
1. Go to [Aiven Console](https://console.aiven.io)
2. Navigate to your MySQL service
3. Regenerate password (Services → MySQL → Users → avnadmin → Reset password)
4. Copy new password to `.env` → `DB_PASSWORD`
5. Run locally: `php artisan migrate` to verify connection
6. Then add this new password to Render dashboard (see PART 3)

#### PART 2: Render Project Setup

**Step 1: Create Render Account**
- Go to [https://render.com](https://render.com)
- Sign up with GitHub (recommended for auto-deploys)
- Create new team if needed

**Step 2: Connect GitHub Repository**
- In Render dashboard: Settings → Git Integrations
- Authorize GitHub and select your repository
- Link repository to Render

**Step 3: Deploy Web Service**
- Click "New +" → "Web Service"
- Select your GitHub repo
- Configuration:
  ```
  Name: fintech-app
  Runtime: PHP 8.4
  Build Command: 
    composer install --no-dev
    npm ci && npm run build
    php artisan migrate --force
    php artisan optimize
  Start Command: vendor/bin/heroku-php-apache2 public/
  Region: us-oregon (or nearest to you)
  Plan: Free (start here, upgrade later)
  ```

**Step 4: Set Environment Variables**
- In Render dashboard: Web Service → Environment
- Add these variables:

| Key | Value | Source |
|-----|-------|--------|
| `APP_ENV` | `production` | Manual |
| `APP_DEBUG` | `false` | Manual |
| `APP_URL` | `https://your-app.onrender.com` | Render URL |
| `APP_KEY` | `base64:9CGZEX4lYHhAaTi7wqgDuzRjItyKt+dwCUO3wl/p74o=` | From .env |
| `DB_CONNECTION` | `mysql` | Manual |
| `DB_HOST` | From Aiven console | **YOU PROVIDE** |
| `DB_PORT` | From Aiven console | **YOU PROVIDE** |
| `DB_DATABASE` | From Aiven console | **YOU PROVIDE** |
| `DB_USERNAME` | From Aiven console | **YOU PROVIDE** |
| `DB_PASSWORD` | From Aiven console (regenerated) | **YOU PROVIDE** |
| `PAYSTACK_PUBLIC_KEY` | From Paystack dashboard | **YOU PROVIDE** |
| `PAYSTACK_SECRET_KEY` | From Paystack dashboard | **YOU PROVIDE** |
| `MAIL_MAILER` | `log` or `smtp` | Manual |
| `MAIL_HOST` | Mailtrap host (if using SMTP) | **YOU PROVIDE** or skip |
| `MAIL_PORT` | Mailtrap port | **YOU PROVIDE** or skip |
| `MAIL_USERNAME` | Mailtrap username | **YOU PROVIDE** or skip |
| `MAIL_PASSWORD` | Mailtrap password | **YOU PROVIDE** or skip |
| `MAIL_FROM_ADDRESS` | `noreply@yourapp.com` | Manual |
| `MAIL_FROM_NAME` | `FinTech App` | Manual |
| `LOG_CHANNEL` | `stack` | Manual |
| `CACHE_STORE` | `database` | Manual |
| `SESSION_DRIVER` | `database` | Manual |
| `QUEUE_CONNECTION` | `database` | Manual |

#### PART 3: Credentials You Need to Provide

**When you're ready, provide these to me:**

1. **Aiven MySQL** (from your Aiven console):
   - DB_HOST
   - DB_PORT
   - DB_DATABASE
   - DB_USERNAME
   - DB_PASSWORD (new one after regeneration)

2. **Paystack API** (from Paystack dashboard):
   - PAYSTACK_PUBLIC_KEY (starts with `pk_`)
   - PAYSTACK_SECRET_KEY (starts with `sk_`)

3. **Email Service** (optional - can use log driver):
   - MAIL_MAILER: `mailtrap` or `smtp` or `log`
   - MAIL_HOST
   - MAIL_PORT
   - MAIL_USERNAME
   - MAIL_PASSWORD

4. **Twilio** (optional - for SMS OTP):
   - TWILIO_SID
   - TWILIO_AUTH_TOKEN
   - TWILIO_PHONE_NUMBER

---

## Local Development Setup

### Prerequisites
```bash
# Install PHP 8.4+ locally
php --version

# Install Composer
composer --version

# Install Node.js
node --version
npm --version
```

### Setup Steps

```bash
cd "/Users/semilore/Desktop/Fintech MVP/MyFintechApp"

# 1. Install dependencies
composer install
npm install

# 2. Copy .env (already done - verify values)
cat .env | grep DB_

# 3. Generate APP_KEY if missing
php artisan key:generate

# 4. Run migrations against Aiven MySQL
php artisan migrate

# 5. Build frontend assets
npm run dev  # or `npm run build` for production

# 6. Start development server
php artisan serve

# 7. Open in browser: http://localhost:8000
```

### Test Aiven Connection

```bash
# Verify database connection
php artisan tinker
>>> DB::connection()->getPdo();
# Should show PDO connection (no error)

# Check migrations status
php artisan migrate:status

# Run seeders if needed
php artisan db:seed
```

---

## Render Deployment Steps

### Option A: Using render.yaml (Recommended)

```bash
# 1. Commit render.yaml and Procfile
git add render.yaml Procfile .env.example
git commit -m "Add Render deployment config"
git push origin main

# 2. In Render dashboard:
# - New → Web Service
# - Select GitHub repository
# - Choose "Deploy from render.yaml"
# - Render automatically detects render.yaml config

# 3. Set environment variables in Render dashboard
# (see PART 2 above)

# 4. Click "Deploy"
```

### Option B: Manual Configuration

```bash
# 1. Create Web Service in Render
# - Name: fintech-app
# - GitHub: your-repo
# - Region: us-oregon
# - Plan: Free

# 2. Add build & start commands:
Build: 
  composer install --no-dev
  npm ci && npm run build
  php artisan migrate --force
  php artisan optimize

Start: 
  vendor/bin/heroku-php-apache2 public/

# 3. Add environment variables (see PART 2)

# 4. Deploy
```

---

## Important Files for Deployment

| File | Purpose |
|------|---------|
| `Procfile` | Tells Render how to run web, worker, scheduler |
| `render.yaml` | Infrastructure-as-code configuration |
| `.env.example` | Template for environment variables |
| `composer.json` | PHP dependencies |
| `package.json` | Node.js dependencies (frontend build) |
| `vite.config.js` | Frontend build config |
| `app/` | Application source code |
| `database/migrations/` | Database schema |
| `routes/` | URL routes |

---

## Troubleshooting

### "Database connection refused"
- Check Aiven MySQL is running: [Aiven Console](https://console.aiven.io)
- Verify credentials in Render dashboard match Aiven
- Check firewall: Aiven → IP allowlist (may need to add Render IPs)

### "Migration failed"
```bash
# Check migration status locally
php artisan migrate:status

# Fix migration issues locally first
php artisan migrate --rollback
php artisan migrate
```

### "Assets not loading" (CSS/JS 404)
- Check `npm run build` ran successfully
- Verify `vite.config.js` has correct asset path
- Check `public/build/` directory exists after build

### "Queue not processing"
- Verify `QUEUE_CONNECTION=database` in .env
- Check `jobs` table exists: `php artisan migrate`
- Monitor: Render dashboard → Logs

### "Memory limit exceeded"
- Upgrade Render plan from Free to Standard
- Optimize database queries (see SECTION 8)
- Reduce queue batch size

---

## Post-Deployment Checklist

- [ ] Health check: `/up` endpoint returns 200
- [ ] Login page loads: `/login`
- [ ] Database connection working
- [ ] Queue worker running (check logs)
- [ ] Email sending (test notification)
- [ ] Paystack webhook receiving events
- [ ] Storage symlink created: `php artisan storage:link`
- [ ] Cache cleared: `php artisan optimize:clear` (if issues)

---

## Next Steps

1. **Provide credentials** (Aiven, Paystack, Mailtrap, etc.)
2. **Proceed to SECTION 4-7** for feature completion
3. **Complete SECTION 8** for production optimization
4. **Test SECTION 10** for final integration

---

## Questions?

- Render Docs: https://render.com/docs
- Aiven MySQL Docs: https://docs.aiven.io/docs/products/mysql
- Laravel Deployment: https://laravel.com/docs/12.x/deployment

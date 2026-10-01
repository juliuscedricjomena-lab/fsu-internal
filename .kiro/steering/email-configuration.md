# Email Configuration Guide

## Email Setup for FSU Internal System

### Development: Mailtrap

Mailtrap is perfect for development and testing.

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_username
MAIL_PASSWORD=your_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@fsu-internal.local
MAIL_FROM_NAME="FSU Internal Finance System"
```

### Production: SendGrid

```env
MAIL_MAILER=sendgrid
SENDGRID_API_KEY=your_sendgrid_api_key
MAIL_FROM_ADDRESS=noreply@your-domain.com
MAIL_FROM_NAME="FSU Internal Finance System"
```

Install SendGrid:
```bash
composer require symfony/sendgrid-mailer
```

---

## Creating Mailable Classes

```bash
php artisan make:mail UserCreated
php artisan make:mail PayslipGenerated
php artisan make:mail TransactionConfirmed
```

---

## Testing Emails

Via Tinker:
```bash
php artisan tinker
>>> Mail::raw('Test', function ($m) { $m->to('test@test.com')->subject('Test'); })
```

---

## FSU System Email Use Cases

### 1. User Account Created Email
- Welcome message
- Temporary password (if sent)
- Login instructions

### 2. Payslip Generation Email
- Payslip month/year
- Link to view payslip
- Salary details (summary)

### 3. Transaction Confirmation Email
- Transaction reference
- Amount received
- Receipt attachment

### 4. Remittance Upload Email
- File name
- Upload time
- Status

### 5. Disbursement Approval Email
- Disbursement amount
- Purpose
- Approval reference

---

## Email Template Example

```blade
@component('mail::message')
# Welcome to FSU Internal Finance System

Hello {{ $user->name }},

Your account has been created successfully.

**Account Details:**
- Email: {{ $user->email }}
- Role: {{ $user->role->name }}

@component('mail::button', ['url' => config('app.url') . '/login'])
Login to System
@endcomponent

Thanks,
FSU Finance Team
@endcomponent
```

---

## Security Notes

- Never commit `.env` with real credentials
- Use `.env.example` template
- Rotate API keys if exposed
- Add unsubscribe links for newsletters
- Log all email sends for audit trail


# Zalo account verification

## Database

On shared hosting, import `database/sql/2026-07-28_all_updates.sql`
once through phpMyAdmin before publishing the PHP changes.

## Environment

Add these values to `.env` after the OTP template has been approved for the OA:

```ini
zalo.otpEnabled = true
zalo.accessToken = "OA_ACCESS_TOKEN"
zalo.otpTemplateId = "APPROVED_TEMPLATE_ID"
zalo.otpField = "otp"
zalo.otpExpiryField = ""
zalo.verifySsl = true
```

`zalo.otpField` and `zalo.otpExpiryField` must match the parameters declared in
the approved Zalo template. Leave OTP disabled until the token, template, and
permission to send Template Messages by phone number are ready. Registration
will then use email verification automatically.

Never commit the OA access token to Git.

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Password Recovery</title>
</head>
<body style="font-family: Arial, sans-serif; background-color: #f9fafb; padding: 20px; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; background-color: #ffffff; padding: 40px; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.05); text-align: center;">
        
        <h2 style="color: #1a1a1a; margin-top: 0;">Reset Your Password</h2>
        
        <p style="font-size: 16px; color: #555; line-height: 1.5; margin-bottom: 30px;">
            Hi {{ $guestAuth->getFullName() }},<br><br>
            We received a request to reset the password for your Dara Meas Hotel account.
            Enter the following 6-digit code on the verification page:
        </p>
        
        <div style="background-color: #f3f4f6; border: 1px solid #e5e7eb; border-radius: 6px; padding: 20px; display: inline-block; margin-bottom: 30px;">
            <span style="font-size: 32px; font-weight: bold; letter-spacing: 6px; color: #c8a96e;">{{ $otpCode }}</span>
        </div>
        
        <p style="font-size: 14px; color: #777; margin-bottom: 0;">
            This code will expire in 10 minutes. If you did not request a password reset, you can safely ignore this email.
        </p>

    </div>
</body>
</html>

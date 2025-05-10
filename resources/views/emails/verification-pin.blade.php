<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Email Verification</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .container {
            background-color: #f9f9f9;
            border-radius: 5px;
            padding: 20px;
            margin: 20px 0;
        }
        .pin {
            font-size: 32px;
            font-weight: bold;
            color: #2d3748;
            text-align: center;
            padding: 20px;
            background-color: #edf2f7;
            border-radius: 5px;
            margin: 20px 0;
        }
        .footer {
            font-size: 12px;
            color: #718096;
            text-align: center;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Welcome to Parkly!</h2>
        <p>Thank you for registering. To verify your email address, please use the following PIN:</p>
        
        <div class="pin">{{ $pin }}</div>
        
        <p>This PIN will expire in 10 minutes.</p>
        
        <p>If you didn't request this verification, please ignore this email.</p>
    </div>
    
    <div class="footer">
        <p>This is an automated message, please do not reply to this email.</p>
    </div>
</body>
</html> 
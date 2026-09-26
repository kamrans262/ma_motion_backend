<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>MA Verification Code</title>
</head>
<body style="margin:0;padding:32px 12px;background:#0F2419;font-family:'Helvetica Neue LT Std','Helvetica Neue',Helvetica,Arial,sans-serif;color:#FFFFFF;">
    <div style="display:none;font-size:1px;color:#0F2419;line-height:1px;max-height:0;max-width:0;opacity:0;overflow:hidden;">Your MA verification code expires in {{ $minutes }} minutes.</div>
    <table role="presentation" cellpadding="0" cellspacing="0" border="0" style="width:100%;max-width:560px;margin:0 auto;background:#0F2419;border-collapse:collapse;">
        <tr><td style="padding:32px 26px 16px;text-align:center;color:#904AFF;font-size:20px;font-weight:600;letter-spacing:1px;">MA</td></tr>
        <tr><td style="padding:12px 26px;text-align:center;color:#904AFF;font-size:32px;line-height:1.18;font-weight:500;letter-spacing:-0.3px;">Verify your email</td></tr>
        <tr><td style="padding:8px 26px 20px;text-align:center;color:#FFFFFF;font-size:16px;line-height:1.5;">Enter this one-time code in MA to continue.</td></tr>
        <tr><td style="padding:12px 26px;text-align:center;"><div style="display:inline-block;background:#1C2830;border:1px solid #904AFF;padding:19px 26px;color:#FFFFFF;font-size:34px;line-height:1.2;font-weight:600;letter-spacing:8px;">{{ $code }}</div></td></tr>
        <tr><td style="padding:18px 26px 14px;text-align:center;color:#FFFFFF;font-size:14px;line-height:1.5;">This code expires in {{ $minutes }} minutes.</td></tr>
        <tr><td style="padding:4px 26px 32px;text-align:center;color:#A3A3A3;font-size:14px;line-height:1.5;">If you didn't request this code, you can safely ignore this email. Do not share your code with anyone.</td></tr>
    </table>
</body>
</html>

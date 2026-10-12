<!DOCTYPE html>
<html>
<body style="font-family: Arial, Helvetica, sans-serif; color: #26231F; line-height: 1.6;">
    <h2 style="color: #86653D; margin-bottom: 4px;">HOA Dues Reminder</h2>
    <p>Hi {{ $resident->name }},</p>
    <p>This is a friendly reminder that your current outstanding HOA balance is
        <strong>&#8369;{{ number_format($balance, 2) }}</strong>.</p>
    <p>You can settle this securely via GCash through the payments section of the SubSync resident portal.</p>
    <p>If you have already paid, please disregard this message.</p>
    <p style="color: #5F5A53;">Terra Nova Homeowners Association</p>
</body>
</html>

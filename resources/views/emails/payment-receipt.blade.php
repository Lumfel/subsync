<!DOCTYPE html>
<html>
<body style="font-family: Arial, Helvetica, sans-serif; color: #26231F; line-height: 1.6;">
    <h2 style="color: #86653D; margin-bottom: 4px;">Payment Receipt</h2>
    <p>Hi {{ $payment->resident?->name ?? 'Resident' }},</p>
    <p>Your GCash payment has been <strong>validated</strong>. Here is your receipt summary:</p>
    <table cellpadding="8" style="border-collapse: collapse; border: 1px solid #E6DFD5;">
        <tr><td><strong>Amount</strong></td><td>&#8369;{{ number_format((float) $payment->amount, 2) }}</td></tr>
        <tr><td><strong>Reference No.</strong></td><td>{{ $payment->reference_no ?: '—' }}</td></tr>
        <tr><td><strong>Date Paid</strong></td><td>{{ $payment->paid_at?->format('M d, Y') ?: '—' }}</td></tr>
        <tr><td><strong>Validated</strong></td><td>{{ $payment->validated_at?->format('M d, Y g:i A') ?: '—' }}</td></tr>
    </table>
    <p>Thank you for keeping your account up to date.</p>
    <p style="color: #5F5A53;">Terra Nova Homeowners Association</p>
</body>
</html>

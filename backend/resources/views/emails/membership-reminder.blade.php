<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Membership Will Expire Soon</title>
</head>
<body style="margin:0; padding:0; background-color:#f1f5f9; font-family: Arial, Helvetica, sans-serif;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f1f5f9; padding:24px 0;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:600px; background-color:#ffffff; border-radius:8px; overflow:hidden; box-shadow:0 1px 3px rgba(0,0,0,0.08);">
                    <tr>
                        <td style="background-color:#0f172a; padding:24px 32px;">
                            <span style="color:#ffffff; font-size:20px; font-weight:bold;">YourGYM</span>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:32px;">
                            <h1 style="margin:0 0 16px; font-size:20px; color:#0f172a;">
                                Hi {{ $client->first_name }} {{ $client->last_name }},
                            </h1>

                            <p style="margin:0 0 16px; font-size:15px; line-height:1.6; color:#334155;">
                                This is a friendly reminder that your membership at
                                <strong>{{ $gym->name }}</strong> is about to expire. We'd love to keep
                                you with us &mdash; here are the details of your current registration:
                            </p>

                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:20px 0; border:1px solid #e2e8f0; border-radius:6px;">
                                <tr>
                                    <td style="padding:12px 16px; font-size:13px; color:#64748b; border-bottom:1px solid #e2e8f0;">Registration Type</td>
                                    <td style="padding:12px 16px; font-size:13px; color:#0f172a; font-weight:bold; text-align:right; border-bottom:1px solid #e2e8f0; text-transform:capitalize;">{{ $client->registration_type }}</td>
                                </tr>
                                <tr>
                                    <td style="padding:12px 16px; font-size:13px; color:#64748b; border-bottom:1px solid #e2e8f0;">Start Date</td>
                                    <td style="padding:12px 16px; font-size:13px; color:#0f172a; font-weight:bold; text-align:right; border-bottom:1px solid #e2e8f0;">{{ $client->registration_start->format('F j, Y') }}</td>
                                </tr>
                                <tr>
                                    <td style="padding:12px 16px; font-size:13px; color:#64748b; border-bottom:1px solid #e2e8f0;">End Date</td>
                                    <td style="padding:12px 16px; font-size:13px; color:#0f172a; font-weight:bold; text-align:right; border-bottom:1px solid #e2e8f0;">{{ $client->registration_end->format('F j, Y') }}</td>
                                </tr>
                                <tr>
                                    <td style="padding:12px 16px; font-size:13px; color:#64748b;">Remaining Days</td>
                                    <td style="padding:12px 16px; font-size:13px; color:#b91c1c; font-weight:bold; text-align:right;">{{ $remainingDays }} day(s)</td>
                                </tr>
                            </table>

                            <p style="margin:0 0 24px; font-size:15px; line-height:1.6; color:#334155;">
                                Don't lose access to your training &mdash; renew your membership today
                                to continue enjoying all the benefits without interruption.
                            </p>

                            <table role="presentation" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td style="border-radius:6px; background-color:#0f172a;">
                                        <a href="mailto:{{ $gym->email }}" style="display:inline-block; padding:12px 24px; font-size:14px; font-weight:bold; color:#ffffff; text-decoration:none;">
                                            Renew My Membership
                                        </a>
                                    </td>
                                </tr>
                            </table>

                            <hr style="margin:32px 0; border:none; border-top:1px solid #e2e8f0;">

                            <p style="margin:0 0 4px; font-size:13px; color:#64748b;">Questions? Reach out to your gym directly:</p>
                            <p style="margin:0; font-size:14px; color:#0f172a; font-weight:bold;">{{ $gym->name }}</p>
                            <p style="margin:0; font-size:13px; color:#334155;">{{ $gym->email }}</p>
                            @if($gym->phone)
                                <p style="margin:0; font-size:13px; color:#334155;">{{ $gym->phone }}</p>
                            @endif
                        </td>
                    </tr>

                    <tr>
                        <td style="background-color:#f8fafc; padding:20px 32px; text-align:center;">
                            <p style="margin:0; font-size:12px; color:#94a3b8;">
                                This email was sent by {{ $gym->name }} via YourGYM. If you believe you
                                received this by mistake, please contact the gym directly.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>

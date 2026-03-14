<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? config('app.name') }}</title>
</head>
<body style="margin:0;padding:0;background-color:#080c14;font-family:'Segoe UI',Tahoma,Geneva,Verdana,sans-serif;-webkit-font-smoothing:antialiased;">
    <!--[if mso]><table role="presentation" width="100%"><tr><td><![endif]-->
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color:#080c14;">
        <tr>
            <td align="center" style="padding:40px 20px;">
                <table role="presentation" width="580" cellspacing="0" cellpadding="0" border="0" style="max-width:580px;width:100%;">
                    
                    {{-- ── Filmstreifen-Header ── --}}
                    <tr>
                        <td style="background:linear-gradient(135deg,#1a1f35 0%,#0d1117 100%);border-radius:20px 20px 0 0;border:1px solid #1e293b;border-bottom:none;padding:0;">
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
                                {{-- Perforations-Leiste oben --}}
                                <tr>
                                    <td style="padding:12px 20px 0;font-size:0;line-height:0;">
                                        <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="width:100%;">
                                            <tr>
                                                <td style="width:8px;height:8px;background:#0f172a;border-radius:50%;"></td>
                                                <td style="width:30px;"></td>
                                                <td style="width:8px;height:8px;background:#0f172a;border-radius:50%;"></td>
                                                <td style="width:30px;"></td>
                                                <td style="width:8px;height:8px;background:#0f172a;border-radius:50%;"></td>
                                                <td></td>
                                                <td style="width:8px;height:8px;background:#0f172a;border-radius:50%;"></td>
                                                <td style="width:30px;"></td>
                                                <td style="width:8px;height:8px;background:#0f172a;border-radius:50%;"></td>
                                                <td style="width:30px;"></td>
                                                <td style="width:8px;height:8px;background:#0f172a;border-radius:50%;"></td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                                {{-- App-Titel --}}
                                <tr>
                                    <td style="padding:30px 40px 20px;text-align:center;">
                                        <span style="font-size:11px;font-weight:800;letter-spacing:0.3em;text-transform:uppercase;color:#3b82f6;">★ ★ ★</span>
                                        <h1 style="margin:12px 0 0;font-size:26px;font-weight:800;letter-spacing:0.05em;text-transform:uppercase;color:#f1f5f9;line-height:1.2;">{{ config('app.name', 'MovieShelf') }}</h1>
                                        <p style="margin:6px 0 0;font-size:12px;letter-spacing:0.15em;text-transform:uppercase;color:#475569;">Film-Datenbank</p>
                                    </td>
                                </tr>
                                {{-- Perforations-Leiste unten --}}
                                <tr>
                                    <td style="padding:0 20px 12px;font-size:0;line-height:0;">
                                        <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="width:100%;">
                                            <tr>
                                                <td style="width:8px;height:8px;background:#0f172a;border-radius:50%;"></td>
                                                <td style="width:30px;"></td>
                                                <td style="width:8px;height:8px;background:#0f172a;border-radius:50%;"></td>
                                                <td style="width:30px;"></td>
                                                <td style="width:8px;height:8px;background:#0f172a;border-radius:50%;"></td>
                                                <td></td>
                                                <td style="width:8px;height:8px;background:#0f172a;border-radius:50%;"></td>
                                                <td style="width:30px;"></td>
                                                <td style="width:8px;height:8px;background:#0f172a;border-radius:50%;"></td>
                                                <td style="width:30px;"></td>
                                                <td style="width:8px;height:8px;background:#0f172a;border-radius:50%;"></td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    
                    {{-- ── Trennlinie --}}
                    <tr>
                        <td style="height:2px;background:linear-gradient(90deg,transparent 0%,#3b82f6 30%,#6366f1 70%,transparent 100%);"></td>
                    </tr>
                    
                    {{-- ── Content-Area ── --}}
                    <tr>
                        <td style="background-color:#111827;border-left:1px solid #1e293b;border-right:1px solid #1e293b;padding:40px 40px 32px;">
                            @yield('content')
                        </td>
                    </tr>
                    
                    {{-- ── Footer ── --}}
                    <tr>
                        <td style="background-color:#0d1117;border:1px solid #1e293b;border-top:none;border-radius:0 0 20px 20px;padding:24px 40px;text-align:center;">
                            <p style="margin:0;font-size:11px;color:#475569;letter-spacing:0.05em;">
                                &copy; {{ date('Y') }} {{ config('app.name') }} &middot; Automatisch generiert
                            </p>
                        </td>
                    </tr>
                    
                </table>
            </td>
        </tr>
    </table>
    <!--[if mso]></td></tr></table><![endif]-->
</body>
</html>

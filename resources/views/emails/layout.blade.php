<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? config('app.name') }}</title>
</head>
<body style="margin:0;padding:0;background-color:#f0f0ee;font-family:'Segoe UI',Tahoma,Geneva,Verdana,sans-serif;-webkit-font-smoothing:antialiased;">
    <!--[if mso]><table role="presentation" width="100%"><tr><td><![endif]-->
    <table role="presentation" width="100%" style="background-color:#f0f0ee; border-collapse: collapse; border: 0;">
        <tr>
            <td style="padding:40px 20px; text-align: center;">
                <table role="presentation" width="580" style="max-width:580px;width:100%; border-collapse: collapse; border: 0; margin-left: auto; margin-right: auto;">
                    {{-- ── Header mit Logo ── --}}
                    <tr>
                        <td style="background-color:#ffffff;border-radius:16px 16px 0 0;border:1px solid #e2e2df;border-bottom:none;padding:0;">
                            <table role="presentation" width="100%" style="border-collapse: collapse; border: 0;">
                                {{-- Filmstreifen-Perforationen --}}
                                <tr>
                                    <td style="padding:10px 24px 0;font-size:0;line-height:0;">
                                        <table role="presentation" style="width:100%; border-collapse: collapse; border: 0;">
                                            <tr>
                                                <td style="width:7px;height:7px;background:#e8e8e5;border-radius:50%;"></td>
                                                <td style="width:28px;"></td>
                                                <td style="width:7px;height:7px;background:#e8e8e5;border-radius:50%;"></td>
                                                <td style="width:28px;"></td>
                                                <td style="width:7px;height:7px;background:#e8e8e5;border-radius:50%;"></td>
                                                <td></td>
                                                <td style="width:7px;height:7px;background:#e8e8e5;border-radius:50%;"></td>
                                                <td style="width:28px;"></td>
                                                <td style="width:7px;height:7px;background:#e8e8e5;border-radius:50%;"></td>
                                                <td style="width:28px;"></td>
                                                <td style="width:7px;height:7px;background:#e8e8e5;border-radius:50%;"></td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                                {{-- Logo --}}
                                <tr>
                                    <td style="padding:28px 40px 24px;text-align:center;">
                                        @php
                                            $logoPath = public_path('img/logo/logo.png');
                                            $logoData = file_exists($logoPath) ? base64_encode(file_get_contents($logoPath)) : null;
                                        @endphp
                                        @if($logoData)
                                            <img src="data:image/png;base64,{{ $logoData }}" alt="{{ config('app.name') }}" style="max-height:80px;max-width:100%;display:inline-block;" />
                                        @else
                                            <span style="font-size:22px;font-weight:800;color:#1f2937;letter-spacing:0.05em;text-transform:uppercase;">{{ config('app.name') }}</span>
                                        @endif
                                    </td>
                                </tr>
                                {{-- Perforationen unten --}}
                                <tr>
                                    <td style="padding:0 24px 10px;font-size:0;line-height:0;">
                                        <table role="presentation" style="width:100%; border-collapse: collapse; border: 0;">
                                            <tr>
                                                <td style="width:7px;height:7px;background:#e8e8e5;border-radius:50%;"></td>
                                                <td style="width:28px;"></td>
                                                <td style="width:7px;height:7px;background:#e8e8e5;border-radius:50%;"></td>
                                                <td style="width:28px;"></td>
                                                <td style="width:7px;height:7px;background:#e8e8e5;border-radius:50%;"></td>
                                                <td></td>
                                                <td style="width:7px;height:7px;background:#e8e8e5;border-radius:50%;"></td>
                                                <td style="width:28px;"></td>
                                                <td style="width:7px;height:7px;background:#e8e8e5;border-radius:50%;"></td>
                                                <td style="width:28px;"></td>
                                                <td style="width:7px;height:7px;background:#e8e8e5;border-radius:50%;"></td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    {{-- ── Akzent-Linie ── --}}
                    <tr>
                        <td style="height:3px;background:linear-gradient(90deg,#d4d4d0 0%,#9ca3af 40%,#6b7280 60%,#d4d4d0 100%);"></td>
                    </tr>
                    {{-- ── Content ── --}}
                    <tr>
                        <td style="background-color:#fafaf9;border-left:1px solid #e2e2df;border-right:1px solid #e2e2df;padding:40px 40px 32px;">
                            @yield('content')
                        </td>
                    </tr>
                    {{-- ── Footer ── --}}
                    <tr>
                        <td style="background-color:#f5f5f3;border:1px solid #e2e2df;border-top:none;border-radius:0 0 16px 16px;padding:20px 40px;text-align:center;">
                            <p style="margin:0;font-size:11px;color:#9ca3af;letter-spacing:0.05em;">
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
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? config('app.name') }}</title>
</head>
<body style="margin:0;padding:0;background-color:#f0f0ee;font-family:'Segoe UI',Tahoma,Geneva,Verdana,sans-serif;-webkit-font-smoothing:antialiased;">
    <!--[if mso]><table role="presentation" style="width: 100%;"><tr><td><![endif]--> <!-- NOSONAR -->
    <table role="presentation" style="width: 100%; background-color: #f0f0ee; border-collapse: collapse; border: 0;"> <!-- NOSONAR -->
        <tr>
            <td style="padding:40px 20px; text-align: center;">
                <table role="presentation" style="max-width: 580px; width: 100%; border-collapse: collapse; border: 0; margin-left: auto; margin-right: auto;"> <!-- NOSONAR -->
                    {{-- ── Header mit Logo ── --}}
                    <tr>
                        <td style="background-color: #ffffff; border-radius: 16px 16px 0 0; border: 1px solid #e2e2df; border-bottom: none; padding: 0;">
                            {{-- Header Container --}}
                            <div style="width: 100%; display: block;">
                                {{-- Filmstreifen-Perforationen oben --}}
                                <div style="padding: 10px 24px 0; font-size: 0; line-height: 0; text-align: center;">
                                    @for($i = 0; $i < 6; $i++)
                                        <img src="data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNyIgaGVpZ2h0PSI3IiB2aWV3Qm94PSIwIDAgNyA3IiBmaWxsPSJub25lIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciPjxjaXJjbGUgY3g9IjMuNSIgY3k9IjMuNSIgcj0iMy41IiBmaWxsPSIjZThlOGU1Ii8+PC9zdmc+" alt="" style="width: 7px; height: 7px; display: inline-block; vertical-align: middle;" />
                                        @if($i < 5)<span style="display: inline-block; width: 28px;"></span>@endif
                                    @endfor
                                </div>

                                {{-- Logo --}}
                                <div style="padding: 28px 40px 24px; text-align: center;">
                                    @php
                                        $logoPath = public_path('img/logo/logo.png');
                                        $logoData = file_exists($logoPath) ? base64_encode(file_get_contents($logoPath)) : null;
                                    @endphp
                                    @if($logoData)
                                        <img src="data:image/png;base64,{{ $logoData }}" alt="{{ config('app.name') }}" style="max-height: 80px; max-width: 100%; display: inline-block;" />
                                    @else
                                        <span style="font-size: 22px; font-weight: 800; color: #1f2937; letter-spacing: 0.05em; text-transform: uppercase;">{{ config('app.name') }}</span>
                                    @endif
                                </div>

                                {{-- Perforationen unten --}}
                                <div style="padding: 0 24px 10px; font-size: 0; line-height: 0; text-align: center;">
                                    @for($i = 0; $i < 6; $i++)
                                        <img src="data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNyIgaGVpZ2h0PSI3IiB2aWV3Qm94PSIwIDAgNyA3IiBmaWxsPSJub25lIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciPjxjaXJjbGUgY3g9IjMuNSIgY3k9IjMuNSIgcj0iMy41IiBmaWxsPSIjZThlOGU1Ii8+PC9zdmc+" alt="" style="width: 7px; height: 7px; display: inline-block; vertical-align: middle;" />
                                        @if($i < 5)<span style="display: inline-block; width: 28px;"></span>@endif
                                    @endfor
                                </div>
                            </div>
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
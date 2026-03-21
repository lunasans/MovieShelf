@extends('emails.layout')

@section('content')
    <table role="none" style="width: 100%; border-collapse: collapse; border: 0;"> <!-- NOSONAR -->
        {{-- Status-Badge --}}
        <tr>
            <td style="padding-bottom:28px; text-align: center;">
                <table role="none" style="border-collapse: collapse; border: 0; margin-left: auto; margin-right: auto;"> <!-- NOSONAR -->
                    <tr>
                        <td style="background-color:#ecfdf5;border:1px solid #a7f3d0;border-radius:100px;padding:8px 20px;">
                            <span style="font-size:11px;font-weight:800;letter-spacing:0.15em;text-transform:uppercase;color:#059669;">● Verbindung steht</span>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>

        {{-- Hauptnachricht --}}
        <tr>
            <td style="padding-bottom:32px; text-align: center;">
                <h2 style="margin:0 0 12px;font-size:22px;font-weight:700;color:#1f2937;line-height:1.3;">Dein Mail-Server funktioniert</h2>
                <p style="margin:0;font-size:15px;color:#6b7280;line-height:1.6;max-width:440px; margin-left: auto; margin-right: auto;">
                    Diese Test-E-Mail bestätigt, dass deine SMTP-Konfiguration korrekt ist. Ab jetzt kann deine Film-Datenbank E-Mails versenden.
                </p>
            </td>
        </tr>

        {{-- Trennlinie --}}
        <tr>
            <td style="padding-bottom:28px;">
                <table role="none" style="width: 100%; border-collapse: collapse; border: 0;"> <!-- NOSONAR -->
                    <tr>
                        <td style="height:1px;background-color:#e5e7eb;"></td>
                    </tr>
                </table>
            </td>
        </tr>

        {{-- Konfigurationsdetails --}}
        <tr>
            <td style="padding-bottom:28px;">
                <table role="none" style="width: 100%; background-color:#ffffff;border:1px solid #e5e7eb;border-radius:12px; border-collapse: collapse;"> <!-- NOSONAR -->
                    <tr>
                        <td style="padding:16px 24px 8px;">
                            <span style="font-size:10px;font-weight:800;letter-spacing:0.2em;text-transform:uppercase;color:#9ca3af;">Verbindungs-Info</span>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:0 24px;">
                            <table role="none" style="width: 100%; border-collapse: collapse; border: 0;"> <!-- NOSONAR -->
                                <tr>
                                    <td style="padding:10px 0;border-bottom:1px solid #f3f4f6;">
                                        <span style="font-size:13px;color:#9ca3af;">Gesendet am</span>
                                    </td>
                                    <td style="padding:10px 0;border-bottom:1px solid #f3f4f6;text-align:right;">
                                        <span style="font-size:13px;color:#374151;font-family:monospace;">{{ date('d.m.Y') }} um {{ date('H:i') }} Uhr</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding:10px 0;border-bottom:1px solid #f3f4f6;">
                                        <span style="font-size:13px;color:#9ca3af;">Mailer</span>
                                    </td>
                                    <td style="padding:10px 0;border-bottom:1px solid #f3f4f6;text-align:right;">
                                        <span style="font-size:13px;color:#374151;font-family:monospace;">{{ strtoupper(config('mail.default', 'smtp')) }}</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding:10px 0;">
                                        <span style="font-size:13px;color:#9ca3af;">Server</span>
                                    </td>
                                    <td style="padding:10px 0;text-align:right;">
                                        <span style="font-size:13px;color:#374151;font-family:monospace;">{{ config('mail.mailers.smtp.host', '–') }}:{{ config('mail.mailers.smtp.port', '–') }}</span>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr><td style="height:12px;"></td></tr>
                </table>
            </td>
        </tr>

        {{-- Hinweis --}}
        <tr>
            <td style="text-align: center;">
                <p style="margin:0;font-size:13px;color:#9ca3af;line-height:1.5;">
                    Du erhältst diese Nachricht, weil in deinem Admin-Panel auf<br>
                    <span style="color:#6b7280;">„Test-Mail senden"</span> geklickt wurde.
                </p>
            </td>
        </tr>
    </table>
@endsection
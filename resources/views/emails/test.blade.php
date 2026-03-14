@extends('emails.layout')

@section('content')
    <div style="text-align: center;">
        <div style="width: 64px; height: 64px; background: rgba(16, 185, 129, 0.1); border-radius: 20px; display: inline-flex; align-items: center; justify-content: center; margin-bottom: 24px;">
            <svg style="width: 32px; height: 32px; color: #10b981;" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
            </svg>
        </div>
        <h2 style="margin: 0 0 16px 0; color: #ffffff; font-size: 24px; font-weight: 700;">Verbindung erfolgreich!</h2>
        <p style="margin: 0 0 32px 0; color: #94a3b8;">Herzlichen Glückwunsch! Wenn du diese E-Mail liest, sind deine SMTP-Einstellungen korrekt konfiguriert und die Film-Datenbank ist bereit für den E-Mail-Versand.</p>
        
        <div style="background: rgba(255, 255, 255, 0.05); border-radius: 16px; padding: 24px; text-align: left; border: 1px solid rgba(255, 255, 255, 0.1);">
            <h4 style="margin: 0 0 12px 0; color: #3b82f6; text-transform: uppercase; font-size: 11px; letter-spacing: 0.1em; font-weight: 800;">Details der Konfiguration</h4>
            <table style="width: 100%; font-size: 14px; border-collapse: collapse;">
                <tr>
                    <td style="padding: 8px 0; color: #64748b; width: 120px;">Zeitpunkt:</td>
                    <td style="padding: 8px 0; color: #f8fafc; font-family: monospace;">{{ date('d.m.Y H:i:s') }}</td>
                </tr>
                <tr>
                    <td style="padding: 8px 0; color: #64748b;">Mailer:</td>
                    <td style="padding: 8px 0; color: #f8fafc; font-family: monospace;">{{ config('mail.default') }}</td>
                </tr>
            </table>
        </div>

        <div class="hr"></div>
        
        <p style="font-size: 14px; color: #64748b; margin-bottom: 0;">Viel Spaß mit deiner Sammlung!</p>
    </div>
@endsection

<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class CentralDeletionController extends Controller
{
    /**
     * Confirm and execute the deletion of a tenant (Shelf).
     * This route is protected by a signed URL.
     */
    public function confirm(Request $request, Tenant $tenant)
    {
        $tenantId = $tenant->id;
        $email = $tenant->email;

        Log::info("Final deletion initiated for tenant: {$tenantId} ({$email})");

        try {
            // 1. Physically delete the tenant storage directory
            // According to TenantSQLiteManager, it is storage/tenant{id}/
            $storagePath = storage_path("tenant{$tenantId}");
            
            if (File::exists($storagePath)) {
                Log::info("Deleting storage directory: {$storagePath}");
                File::deleteDirectory($storagePath);
            }

            // 2. Delete the tenant record
            // stancl/tenancy will handle domain deletion and DB manager's deleteDatabase()
            $tenant->delete();

            Log::info("Tenant {$tenantId} successfully deleted.");

            return redirect()->route('landing')->with('success', "Das Regal '{$tenantId}' wurde erfolgreich und unwiderruflich gelöscht.");
        } catch (\Exception $e) {
            Log::error("Error deleting tenant {$tenantId}: " . $e->getMessage());
            return redirect()->route('landing')->with('error', "Fehler beim Löschen des Regals. Bitte kontaktiere den Support.");
        }
    }
}

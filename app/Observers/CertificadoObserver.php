<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Certificado;

class CertificadoObserver
{
    /**
     * Handle the Certificado "created" event.
     */
    public function created(Certificado $certificado): void
    {
        //
    }

    /**
     * Handle the Certificado "updated" event.
     */
    public function updated(Certificado $certificado): void
    {
        //
    }

    /**
     * Handle the Certificado "deleted" event.
     */
    public function deleted(Certificado $certificado): void
    {
        //
    }

    /**
     * Handle the Certificado "restored" event.
     */
    public function restored(Certificado $certificado): void
    {
        //
    }

    /**
     * Handle the Certificado "force deleted" event.
     */
    public function forceDeleted(Certificado $certificado): void
    {
        //
    }

    /**
     * Handle the Certificado "deleting" event.
     */
    public function deleting(Certificado $certificado): void
    {
        if ($certificado->isForceDeleting()) {
            $certificado->firmaDigital()->withTrashed()->forceDelete();

            return;
        }

        $certificado->firmaDigital()->delete();
    }
}

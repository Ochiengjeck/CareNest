<?php

namespace App\Services;

use App\Models\User;

class SignatureService
{
    public function getSignatureDataUri(?User $user): ?string
    {
        if (! $user) {
            return null;
        }

        return $user->getSignatureDataUri();
    }

    /**
     * Write signature to a temporary PNG file for PhpWord image embedding.
     * Caller is responsible for cleaning up the temp file.
     */
    public function getSignatureTempFile(?User $user): ?string
    {
        $dataUri = $this->getSignatureDataUri($user);

        if (! $dataUri) {
            return null;
        }

        // Extract base64 data from data URI
        $parts = explode(',', $dataUri, 2);
        if (count($parts) !== 2) {
            return null;
        }

        $decoded = base64_decode($parts[1], true);
        if ($decoded === false) {
            return null;
        }

        $tempFile = tempnam(sys_get_temp_dir(), 'sig_').'.png';
        file_put_contents($tempFile, $decoded);

        return $tempFile;
    }
}

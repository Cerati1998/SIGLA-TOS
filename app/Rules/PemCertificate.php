<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Http\UploadedFile;
use Illuminate\Translation\PotentiallyTranslatedString;

class PemCertificate implements ValidationRule
{
    /**
     * Check that the file holds a certificate together with its private key, without a passphrase.
     *
     * @param  Closure(string, ?string=): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! $value instanceof UploadedFile || ! $this->holdsCertificateAndKey((string) $value->get())) {
            $fail('El archivo debe ser un PEM con el certificado y su clave privada, sin contraseña.');
        }
    }

    /**
     * Read the certificate and the key, silencing the warnings OpenSSL raises on invalid content.
     */
    private function holdsCertificateAndKey(string $pem): bool
    {
        $certificate = @openssl_x509_read($pem);
        $privateKey = @openssl_pkey_get_private($pem);

        return $certificate !== false
            && $privateKey !== false
            && openssl_x509_check_private_key($certificate, $privateKey);
    }
}

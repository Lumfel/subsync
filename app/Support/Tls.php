<?php

namespace App\Support;

/**
 * Locates a CA bundle for outbound HTTPS.
 *
 * Laragon's PHP leaves curl.cainfo/openssl.cafile empty, so Guzzle/cURL fails
 * every HTTPS request with "unable to get local issuer certificate" - which
 * silently broke Google sign-in (Socialite token exchange).
 */
class Tls
{
    public static function caBundle(): ?string
    {
        $candidates = [
            env('SSL_CA_BUNDLE'),
            ini_get('curl.cainfo') ?: null,
            ini_get('openssl.cafile') ?: null,
            'C:\\laragon\\etc\\ssl\\cacert.pem',
            dirname((string) PHP_BINARY).'\\extras\\ssl\\cacert.pem',
            '/etc/ssl/certs/ca-certificates.crt',
        ];

        foreach ($candidates as $path) {
            if (is_string($path) && $path !== '' && is_file($path)) {
                return $path;
            }
        }

        return null;
    }
}

<?php

namespace MCordingley\LaravelSapient;

use Illuminate\Support\ServiceProvider;
use MCordingley\LaravelSapient\Console\GenerateSealingKeyPair;
use MCordingley\LaravelSapient\Console\GenerateSharedAuthenticationKey;
use MCordingley\LaravelSapient\Console\GenerateSharedEncryptionKey;
use MCordingley\LaravelSapient\Console\GenerateSigningKeyPair;
use ParagonIE\ConstantTime\Base64UrlSafe;

final class Provider extends ServiceProvider
{
    /**
     * @return void
     */
    public function register()
    {
        $this->app->when(Sapient::class)->needs(AdapterInterface::class)->give(SymfonyAdapter::class);

        $this->bindKey(SealingSecretKey::class, 'sapient.sealing.private_key')
            ->bindKey(SharedAuthenticationKey::class, 'sapient.shared.authentication_key')
            ->bindKey(SharedEncryptionKey::class, 'sapient.shared.encryption_key')
            ->bindKey(SigningSecretKey::class, 'sapient.signing.private_key');
    }

    /**
     * @param string $concrete
     * @param string $configKey
     * @return Provider
     */
    private function bindKey(string $concrete, string $configKey): self
    {
        $this->app->when($concrete)->needs('$key')->give(Base64UrlSafe::decode(config($configKey)));

        return $this;
    }

    /**
     * @return void
     */
    public function boot()
    {
        $this->commands([
            GenerateSealingKeyPair::class,
            GenerateSharedAuthenticationKey::class,
            GenerateSharedEncryptionKey::class,
            GenerateSigningKeyPair::class,
        ]);

        $this->publishes([
            __DIR__ . '/config.php' => config_path('sapient.php'),
        ]);
    }
}

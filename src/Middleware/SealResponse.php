<?php

namespace MCordingley\LaravelSapient\Middleware;

use Closure;
use function GuzzleHttp\Psr7\stream_for;
use Illuminate\Http\Request;
use MCordingley\LaravelSapient\Contracts\KeyResolver;
use ParagonIE\Sapient\CryptographyKeys\SealingPublicKey;
use ParagonIE\Sapient\Simple;
use Symfony\Bridge\PsrHttpMessage\Factory\DiactorosFactory;
use Symfony\Bridge\PsrHttpMessage\Factory\HttpFoundationFactory;
use Symfony\Component\HttpFoundation\Response;
use Zend\Diactoros\Response as DiactorosResponse;

final class SealResponse
{
    /** @var DiactorosFactory */
    private $psrFactory;

    /** @var HttpFoundationFactory */
    private $symfonyFactory;

    /** @var KeyResolver */
    private $resolver;

    /**
     * @param KeyResolver $resolver
     */
    public function __construct(KeyResolver $resolver)
    {
        $this->psrFactory = new DiactorosFactory;
        $this->symfonyFactory = new HttpFoundationFactory;
        $this->resolver = $resolver;
    }

    /**
     * @param Request $request
     * @param Closure $next
     * @return Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        /** @var Response $response */
        $response = $next($request);

        /** @var DiactorosResponse $psrResponse */
        $psrResponse = $this->psrFactory->createResponse($response);

        $key = new SealingPublicKey($this->resolver->resolveKey());
        $cipherText = Simple::seal($psrResponse->getBody(), $key);

        return $this->symfonyFactory->createResponse($psrResponse->withBody(stream_for($cipherText)));
    }
}

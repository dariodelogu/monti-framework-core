<?php
	namespace App\System\Http;

    use Psr\Http\Message\ServerRequestInterface;
    use Laminas\Diactoros\UriFactory;
    use Laminas\Diactoros\ServerRequestFilter\FilterServerRequestInterface;
    use Laminas\Diactoros\ServerRequestFilter\FilterUsingXForwardedHeaders;
    use function Laminas\Diactoros\normalizeServer;
    use function Laminas\Diactoros\normalizeUploadedFiles;
    use function Laminas\Diactoros\marshalHeadersFromSapi;
    use function Laminas\Diactoros\parseCookieHeader;
    use function Laminas\Diactoros\marshalMethodFromSapi;
    use function Laminas\Diactoros\marshalProtocolVersionFromSapi;

    class ServerRequestFactory extends \Laminas\Diactoros\ServerRequestFactory {

        /**
         * @var string $apacheRequestHeaders The function name used to retrieve Apache request headers.
         */
        private static $apacheRequestHeaders = 'apache_request_headers';

        /**
         * Create a ServerRequest instance from PHP global variables.
         *
         * @param array|null $server The server parameters, typically $_SERVER.
         * @param array|null $query The query parameters, typically $_GET.
         * @param array|null $body The body parameters, typically $_POST.
         * @param array|null $cookies The cookies, typically $_COOKIE.
         * @param array|null $files The uploaded files, typically $_FILES.
         * @param FilterServerRequestInterface|null $requestFilter The request filter to apply.
         * @return ServerRequestInterface The created ServerRequest instance.
         */
        public static function fromGlobals(
            ?array $server = null,
            ?array $query = null,
            ?array $body = null,
            ?array $cookies = null,
            ?array $files = null,
            ?FilterServerRequestInterface $requestFilter = null
        ): ServerRequestInterface {
            $requestFilter ??= FilterUsingXForwardedHeaders::trustReservedSubnets();

            $server  = normalizeServer(
                $server ?? $_SERVER,
                is_callable(self::$apacheRequestHeaders) ? self::$apacheRequestHeaders : null
            );
            $files   = normalizeUploadedFiles($files ?? $_FILES);
            $headers = marshalHeadersFromSapi($server);

            if (null === $cookies && array_key_exists('cookie', $headers)) {
                $cookies = parseCookieHeader($headers['cookie']);
            }

            //\App\System\Http\ServerRequest instead of \Laminas\Diactoros\ServerRequest
            return $requestFilter(new ServerRequest(
                $server,
                $files,
                UriFactory::createFromSapi($server, $headers),
                marshalMethodFromSapi($server),
                'php://input',
                $headers,
                $cookies ?? $_COOKIE,
                $query ?? $_GET,
                $body ?? $_POST,
                marshalProtocolVersionFromSapi($server)
            ));
        }

    }
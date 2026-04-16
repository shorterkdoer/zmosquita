<?php

declare(strict_types=1);

namespace ZMosquita\Core\Http;

final class Kernel
{
    public function __construct(
        private Router $router,
        private Dispatcher $dispatcher
    ) {
    }

    public function handle(Request $request): mixed
    {
        $match = $this->router->match($request);

        if ($match === null) {
            http_response_code(404);
            echo 'Ruta no encontrada';
            return null;
        }

        $result = $this->dispatcher->dispatch(
            $request,
            $match['route'],
            $match['params']
        );

        if ($result instanceof Response) {
            $result->send();
            return null;
        }

        return $result;
    }

    public function run(): void
    {
        $this->handle(Request::capture());
    }
}
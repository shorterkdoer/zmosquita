<?php

namespace Foundation\Middleware;

/**
 * Class BaseMiddleware
 *
 * Esta clase sirve como plantilla para todos los middlewares de la aplicación.
 * Cualquier middleware concreto debe extender esta clase e implementar el método handle(),
 * el cual contendrá la lógica que se deba ejecutar antes de acceder al controlador.
 */
abstract class BaseMiddleware
{
    /**
     * Método que se debe implementar en cada middleware concreto.
     * Aquí se define la lógica que se ejecutará antes de que se llame al controlador.
     *
     * Si la condición del middleware falla, se puede emitir una respuesta (por ejemplo, un error o redirección)
     * y detener la ejecución usando exit.
     *
     * @return void
     */
    abstract public function handle(): void;
}

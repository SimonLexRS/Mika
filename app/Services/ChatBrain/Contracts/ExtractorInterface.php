<?php

namespace App\Services\ChatBrain\Contracts;

interface ExtractorInterface
{
    /**
     * Extraer una entidad del mensaje.
     *
     * @param string $message El mensaje del usuario
     * @return mixed|null El valor extraído o null si no se encontró
     */
    public function extract(string $message): mixed;

    /**
     * Obtener el nombre de la entidad que extrae.
     */
    public function getEntityName(): string;
}

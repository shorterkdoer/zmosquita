<?php

declare(strict_types=1);

namespace ZMosquita\Core\Generators\Shared;

use RuntimeException;

final class StubRenderer
{
    /**
     * @param array<string, mixed> $vars
     */
    public function render(string $templatePath, array $vars = []): string
    {
        if (!is_file($templatePath)) {
            throw new RuntimeException("Stub template not found: {$templatePath}");
        }

        $template = file_get_contents($templatePath);

        if ($template === false) {
            throw new RuntimeException("Unable to read stub template: {$templatePath}");
        }

        foreach ($vars as $key => $value) {
            $template = str_replace('{{ ' . $key . ' }}', (string)$value, $template);
        }

        return $template;
    }
}
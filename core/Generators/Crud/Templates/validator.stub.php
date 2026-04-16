<?php

declare(strict_types=1);

namespace {{ namespace }};

use ZMosquita\Core\Validation\Validator;

final class {{ validator_class }}
{
    private Validator $validator;

    public function __construct()
    {
        $this->validator = new Validator();
    }

    public function validate(array $data): array
    {
        return $this->validator->validate(
            $data,
            [
{{ rules }}
            ],
            [
{{ labels }}
            ]
        );
    }
}
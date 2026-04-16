<?php

declare(strict_types=1);

namespace {{ namespace }};

use ZMosquita\Core\Models\BaseModel;

final class {{ model_class }} extends BaseModel
{
    protected string $table = '{{ table_name }}';

    protected string $primaryKey = '{{ primary_key }}';

    /** @var string[] */
    protected array $fillable = [{{ fillable }}];

    protected bool $hasTenantColumn = {{ has_tenant_column }};
}
<?php
declare(strict_types=1);

namespace WebWizardry\Data;

use WebWizardry\Data\Hydrate\Hydrator;

trait DTOReadable
{
    public function __get(string $name): mixed
    {
        return Hydrator::get($this, $name);
    }
}
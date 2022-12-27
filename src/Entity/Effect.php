<?php

namespace App\Entity;

use App\Utils\EffectType;

class Effect
{
    private string $type;

    private int $value;

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     * @return Effect
     */
    public function setType(string $type): self
    {
        if (EffectType::isEffectType($type)) {
            $this->type = $type;
        }

        return $this;
    }

    /**
     * @return int
     */
    public function getValue(): int
    {
        return $this->value;
    }

    /**
     * @param int $value
     * @return Effect
     */
    public function setValue(int $value): self
    {
        $this->value = $value;
        return $this;
    }

}

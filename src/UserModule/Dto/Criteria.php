<?php

namespace App\UserModule\Dto;

/**
 * @phpstan-type TParams = array{name-eq?: string, email-eq?: string}
 */
class Criteria
{
    /**
     * @var TParams
     */
    private array $params = [];

    public function withNameEq(string $value): static
    {
        $clone = clone $this;
        $clone->params['name-eq'] = $value;

        return $clone;
    }

    public function withEmailEq(string $value): static
    {
        $clone = clone $this;
        $clone->params['email-eq'] = $value;

        return $clone;
    }

    /**
     * @return TParams
     */
    public function getParams(): array
    {
        return $this->params;
    }
}

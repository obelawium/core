<?php

namespace Obelaw\Ium\Core\Contracts;

interface IumConfigEnum extends \Stringable
{
    public function module(): string;

    public function key(): string;

    public function value(): string;
}

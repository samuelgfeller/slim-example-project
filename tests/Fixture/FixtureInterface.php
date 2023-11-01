<?php

namespace App\Test\Fixture;

interface FixtureInterface
{
    // Attributes are public but php doesn't support class properties in interfaces so getters are needed
    public function getTable(): string;

    public function getRecords(): array;
}

<?php

/**
 * Test for PackedRoom
 */
class PackedRoomTest extends PHPUnit\Framework\TestCase
{

    protected $sut;

    protected function setUp(): void
    {
        $this->sut = new Trismegiste\MapGen\PackedRoom();
    }

    public function testGenerate()
    {
        $map = $this->sut->generate(30, 10, 1, 0.3);
        $this->assertIsArray($map);
    }

    public function testTrimmedGenerate()
    {
        $map = $this->sut->generate(30, 10, 1, 0.3, true);
        $this->assertIsArray($map);
    }

}

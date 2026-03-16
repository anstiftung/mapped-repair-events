<?php
declare(strict_types=1);

namespace App\Test\TestCase\src\Controller\Component;

use App\Controller\Component\StringComponent;
use Cake\TestSuite\TestCase;

class StringComponentTest extends TestCase
{
    public function testStripUtf8mb4CharsRemovesEmojis(): void
    {
        $this->assertSame('hello  world', StringComponent::stripUtf8mb4Chars('hello 😀 world'));
        $this->assertSame('test', StringComponent::stripUtf8mb4Chars('test🚀'));
        $this->assertSame('', StringComponent::stripUtf8mb4Chars('🎉🎊🎈'));
    }

    public function testStripUtf8mb4CharsKeepsBmpChars(): void
    {
        $this->assertSame('münchen', StringComponent::stripUtf8mb4Chars('münchen'));
        $this->assertSame('café résumé', StringComponent::stripUtf8mb4Chars('café résumé'));
        $this->assertSame('straße', StringComponent::stripUtf8mb4Chars('straße'));
    }

    public function testStripUtf8mb4CharsPreservesAscii(): void
    {
        $input = 'Berlin 10115';
        $this->assertSame($input, StringComponent::stripUtf8mb4Chars($input));
    }

    public function testStripUtf8mb4CharsHandlesEmptyString(): void
    { 
        $this->assertSame('', StringComponent::stripUtf8mb4Chars(''));
    }

    public function testStripUtf8mb4CharsMixedContent(): void
    {
        $this->assertSame(
            'search  keyword  here',
            StringComponent::stripUtf8mb4Chars('search 💡 keyword 🔧 here'),
        );
    }

    public function testStripUtf8mb4CharsRemovesFullwidthForms(): void
    {
        $this->assertSame('test', StringComponent::stripUtf8mb4Chars("test\u{FF07}"));
        $this->assertSame('hello', StringComponent::stripUtf8mb4Chars("hello\u{FF02}"));
        $this->assertSame('ab', StringComponent::stripUtf8mb4Chars("a\u{FF3C}b"));
        $this->assertSame('ab', StringComponent::stripUtf8mb4Chars("a＇b"));
    }
}

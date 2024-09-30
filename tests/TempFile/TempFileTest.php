<?php

namespace TempFile;

use PHPUnit\Framework\TestCase;

/**
 * Tests for the TempFile class.
 */
class TempFileTest extends TestCase
{
    /**
     * Tests the __construct method of TempFile class.
     */
    public function testConstruct()
    {
        // Test with default options.
        $tempFile = new TempFile(null);
        $this->assertTrue(is_file($tempFile->getFilepath()));
        $this->assertStringEndsWith('.tmp', $tempFile->getFilename());

        // Test with custom options.
        $customOptions = [
            Options::Extension => 'txt',
            Options::Filename => 'testfile',
            Options::Prefix => 'prefix_',
            Options::Suffix => '_suffix',
            Options::RandomLength => 5,
            Options::Directory => sys_get_temp_dir(),
        ];
        $tempFile = new TempFile($customOptions);
        $this->assertStringEndsWith('.txt', $tempFile->getFilename());
        $this->assertStringStartsWith('prefix_', $tempFile->getFilename());
        $this->assertStringEndsWith('_suffix.txt', $tempFile->getFilename());
        $this->assertSame($customOptions[Options::Directory], dirname($tempFile->getFilepath()));

        // Test when a non-string and non-integer value is given to any options.
        $customOptions[Options::Prefix] = [];
        $this->expectException(\InvalidArgumentException::class);
        new TempFile($customOptions);

        // Test when an invalid option is given.
        $customOptions = ['invalid_option' => 'value'];
        $this->expectException(\InvalidArgumentException::class);
        new TempFile($customOptions);

        // Test when a directory that does not exist is given.
        $customOptions = [Options::Directory => '/nonexistent/directory'];
        $this->expectException(\InvalidArgumentException::class);
        new TempFile($customOptions);

        // Test when a directory that is not writable is given.
        $customOptions = [Options::Directory => '/'];
        $this->expectException(\InvalidArgumentException::class);
        new TempFile($customOptions);

        // Test if is writeable
        $customOptions = [Options::Directory => sys_get_temp_dir()];
        $tempFile = new TempFile($customOptions);
        $this->assertTrue(is_writable($tempFile->getFilepath()));
    }
}
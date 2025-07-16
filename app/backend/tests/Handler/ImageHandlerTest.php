<?php

namespace App\Tests\Handler;

use App\Handler\ImageHandler;
use App\Exception\ImageMaxSizeExceededException;
use App\Exception\ImageTypeNotAllowedException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ImageHandlerTest extends TestCase
{
    public function testValidateImageWithValidImageDoesNotThrowException(): void
    {
        $uploadedFile1 = $this->createMockUploadedFile('image/png', 1024 * 1024); // 1MB
        $uploadedFile2 = $this->createMockUploadedFile('image/jpeg', 1024 * 1024); // 1MB

        $this->expectNotToPerformAssertions();
        ImageHandler::validateImage($uploadedFile1);
        ImageHandler::validateImage($uploadedFile2);
    }

    public function testValidateImageThrowsExceptionForInvalidMimeType(): void
    {
        $uploadedFile = $this->createMockUploadedFile('image/gif', 1024 * 1024);

        $this->expectException(ImageTypeNotAllowedException::class);
        ImageHandler::validateImage($uploadedFile);
    }

    public function testValidateImageThrowsExceptionForFileTooLarge(): void
    {
        $uploadedFile = $this->createMockUploadedFile('image/jpeg', 6 * 1024 * 1024); // 6MB

        $this->expectException(ImageMaxSizeExceededException::class);
        ImageHandler::validateImage($uploadedFile);
    }

    public function testGetUniqueFileNameReturnsStringWithCorrectExtension(): void
    {
        $uploadedFile = $this->createMock(UploadedFile::class);
        $uploadedFile->method('guessExtension')->willReturn('jpg');

        $fileName = ImageHandler::getUniqueFileName($uploadedFile);

        $this->assertStringEndsWith('.jpg', $fileName);
    }

    public function testGetUniqueFileNameGeneratesUniqueNames(): void
    {
        $uploadedFile = $this->createMock(UploadedFile::class);
        $uploadedFile->method('guessExtension')->willReturn('png');

        $fileName1 = ImageHandler::getUniqueFileName($uploadedFile);
        $fileName2 = ImageHandler::getUniqueFileName($uploadedFile);

        $this->assertNotEquals($fileName1, $fileName2);
    }

    public function testSaveImageCreatesDirectoryIfNotExists(): void
    {
        $uploadedFile = $this->createMock(UploadedFile::class);
        $uploadDir = sys_get_temp_dir() . '/test_upload_' . uniqid();
        $fileName = 'test.jpg';

        $uploadedFile->expects($this->once())
            ->method('move')
            ->with($uploadDir, $fileName);

        ImageHandler::saveImage($uploadedFile, $uploadDir, $fileName);

        $this->assertTrue(is_dir($uploadDir));

        if (is_dir($uploadDir)) {
            rmdir($uploadDir);
        }
    }

    private function createMockUploadedFile(string $mimeType, int $size): UploadedFile
    {
        $uploadedFile = $this->createMock(UploadedFile::class);
        $uploadedFile->method('getMimeType')->willReturn($mimeType);
        $uploadedFile->method('getSize')->willReturn($size);

        return $uploadedFile;
    }
}
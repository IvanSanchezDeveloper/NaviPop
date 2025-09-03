<?php

namespace App\Tests\UnitTests\Service;

use App\Entity\Product;
use App\Entity\User;
use App\Exception\ImageMaxSizeExceededException;
use App\Exception\ImageTypeNotAllowedException;
use App\Handler\ImageHandler;
use App\Repository\ProductRepository;
use App\Service\ProductManager;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ProductManagerTest extends TestCase
{
    private ProductRepository $productRepository;
    private ProductManager $productManager;
    private string $imagesPath;

    private const NOT_ALLOWED_IMG_TYPE = 'image/gif';
    private const NOT_ALLOWED_IMG_SYZE = 6 * 1024 * 1024;

    protected function setUp(): void
    {
        parent::setUp();

        $this->productRepository = $this->createMock(ProductRepository::class);
        $this->imagesPath = sys_get_temp_dir() . '/test/images';
        $this->productManager = new ProductManager($this->productRepository, $this->imagesPath);
    }

    public function testCreateProductSuccess(): void
    {
        $user = new User();
        $name = 'Test Product';
        $price = '19.99';

        $uploadedFile = $this->createMock(UploadedFile::class);
        $uploadedFile->method('getMimeType')->willReturn('image/jpeg');
        $uploadedFile->method('getSize')->willReturn(1024 * 1024); // 1MB
        $uploadedFile->method('guessExtension')->willReturn('jpg');

        $imagickMock = $this->createMock(\Imagick::class);
        $imagickMock->expects($this->once())->method('setImageFormat')->with('webp');
        $imagickMock->expects($this->once())->method('setImageCompressionQuality')->with(80);
        $imagickMock->expects($this->once())->method('writeImage')->with($this->isType('string'));
        $imagickMock->expects($this->once())->method('clear');

        ImageHandler::setImagick($imagickMock);

        $this->productRepository->expects($this->once())
            ->method('createProduct')
            ->with($user, $name, $price, $this->isType('string'));

        $this->productManager->createProduct($user, $name, $price, $uploadedFile);

        ImageHandler::setImagick(null);
    }

    public function testGetAllProductsReturnsArray(): void
    {
        $products = [new Product(), new Product()];

        $this->productRepository->expects($this->once())
            ->method('findAll')
            ->willReturn($products);

        $result = $this->productManager->getAllProducts();

        $this->assertSame($products, $result);
    }

    public function testGetProductByIdReturnsProduct(): void
    {
        $product = new Product();
        $productId = 1;

        $this->productRepository->expects($this->once())
            ->method('find')
            ->with($productId)
            ->willReturn($product);

        $result = $this->productManager->getProductById($productId);

        $this->assertSame($product, $result);
    }

    public function testGetProductByIdReturnsNullWhenNotFound(): void
    {
        $productId = 999;

        $this->productRepository->expects($this->once())
            ->method('find')
            ->with($productId)
            ->willReturn(null);

        $result = $this->productManager->getProductById($productId);

        $this->assertNull($result);
    }

    public function testFormatProductDataReturnsCorrectArray(): void
    {
        $product = new Product();
        $product->setName('Test Product');
        $product->setPrice('19.99');
        $product->setImagePath('test.jpg');

        $baseUrl = 'https://example.com';

        $result = $this->productManager->formatProductData($product, $baseUrl);

        $expected = [
            'id' => null,
            'name' => 'Test Product',
            'price' => '19.99',
            'image' => 'https://example.com/uploads/products/test.jpg'
        ];

        $this->assertEquals($expected, $result);
    }

    public function testCreateProductValidatesImage(): void
    {
        $user = new User();
        $uploadedFile = $this->createMock(UploadedFile::class);
        $uploadedFile->method('getMimeType')->willReturn(self::NOT_ALLOWED_IMG_TYPE);
        $uploadedFile->method('getSize')->willReturn(1024);

        $this->expectException(ImageTypeNotAllowedException::class);

        $this->productManager->createProduct($user, 'Test', '10.00', $uploadedFile);
    }

    public function testCreateProductValidatesImageSize(): void
    {
        $user = new User();
        $uploadedFile = $this->createMock(UploadedFile::class);
        $uploadedFile->method('getMimeType')->willReturn('image/jpeg');
        $uploadedFile->method('getSize')->willReturn(self::NOT_ALLOWED_IMG_SYZE); // 6MB > 5MB

        $this->expectException(ImageMaxSizeExceededException::class);

        $this->productManager->createProduct($user, 'Test', '10.00', $uploadedFile);
    }


}
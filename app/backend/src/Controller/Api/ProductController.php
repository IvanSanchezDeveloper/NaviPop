<?php declare(strict_types=1);

namespace App\Controller\Api;

use App\Entity\Product;
use App\Exception\AbstractApiException;
use App\Handler\ImageHandler;
use App\Repository\ProductRepository;
use App\Service\Auth\AuthResponse;
use App\Service\ProductManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ProductController extends AbstractController
{
    private ProductManager $productManager;
    public function __construct(
        ProductManager $productManager,
    ) {
        $this->productManager = $productManager;
    }

    #[Route('/api/product/new', name: 'create_product', methods: ['POST'])]
    public function create(
        Request $request,
    ): JsonResponse
    {
        $user = $this->getUser();
        $name = $request->request->get('name');
        $price = $request->request->get('price');
        $uploadedFile = $request->files->get('image');

        try {
            $this->productManager->createProduct($user, $name, $price, $uploadedFile);
        }
        catch (AbstractApiException $e) {
            return new JsonResponse([
                'error' => $e->getMessage()
            ], $e->getStatusCode());
        }
        catch (\Exception $e) {
            return new JsonResponse([
                'error' => $e->getMessage()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        return new JsonResponse([
        ], Response::HTTP_OK);
    }

    #[Route('/api/products', name: 'get_products', methods: ['GET'])]
    public function getProducts(
        Request $request
    ): JsonResponse
    {
        try {
            $products = $this->productManager->getAllProducts();
            $baseUrl = $request->getSchemeAndHttpHost();

            $productsData = array_map(
                fn(Product $product) => $this->productManager->formatProductData($product, $baseUrl),
                $products
            );

            return new JsonResponse([
                'success' => true,
                'data' => $productsData
            ], Response::HTTP_OK);

        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'error' => 'Error retrieving products'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/api/products/{id}', name: 'get_product', methods: ['GET'])]
    public function getProduct(
        Request $request,
        int $id
    ): JsonResponse
    {
        try {
            $product = $this->productManager->getProductById($id);
            $baseUrl = $request->getSchemeAndHttpHost();

            if (!$product) {
                return new JsonResponse([
                    'success' => false,
                    'error' => 'Product not found'
                ], Response::HTTP_NOT_FOUND);
            }

            return new JsonResponse([
                'success' => true,
                'data' => $this->productManager->formatProductData($product, $baseUrl)
            ], Response::HTTP_OK);

        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'error' => 'Error retrieving product'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

}
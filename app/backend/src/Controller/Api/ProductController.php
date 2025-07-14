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
        $data = json_decode($request->getContent(), true);

        $user = $this->getUser();
        $name = $data['name'];
        $price = $data['price'];
        $uploadedFile = $request->files->get('image');

        try {
            $this->productManager->createProduct($user, $name, $price, $uploadedFile);
        }
        catch (AbstractApiException $e) {
            return AuthResponse::json([
                'error' => $e->getMessage()
            ], null, $e->getCode());
        }
        catch (\Exception $e) {
            return AuthResponse::json([
                'error' => $e->getMessage()
            ], null, Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        return new JsonResponse([

        ], Response::HTTP_OK);
    }
}
<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/categories')]
class CategoryController extends AbstractController
{
    #[Route('', name: 'app_category_index', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();
        
        $categories = $entityManager->getRepository(Category::class)->findByUser($user->getId());
        
        return $this->json([
            'categories' => $categories
        ], Response::HTTP_OK, [], ['groups' => ['category:read']]);
    }
    
    #[Route('', name: 'app_category_create', methods: ['POST'])]
    public function create(
        Request $request, 
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator,
        SerializerInterface $serializer
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);
        
        if (!isset($data['name'])) {
            return $this->json(['message' => 'Name is required'], Response::HTTP_BAD_REQUEST);
        }
        
        $category = new Category();
        $category->setName($data['name']);
        
        if (isset($data['color'])) {
            $category->setColor($data['color']);
        }
        
        if (isset($data['isIncome'])) {
            $category->setIsIncome($data['isIncome']);
        }
        
        // System categories should only be created by admin
        $category->setIsSystem(false);
        
        /** @var User $user */
        $user = $this->getUser();
        $category->setUser($user);
        
        // Validate entity
        $errors = $validator->validate($category);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getMessage();
            }
            return $this->json(['errors' => $errorMessages], Response::HTTP_BAD_REQUEST);
        }
        
        $entityManager->persist($category);
        $entityManager->flush();
        
        return $this->json([
            'message' => 'Category created successfully',
            'category' => $category
        ], Response::HTTP_CREATED, [], ['groups' => ['category:read']]);
    }
    
    #[Route('/{id}', name: 'app_category_show', methods: ['GET'])]
    public function show(int $id, EntityManagerInterface $entityManager): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();
        
        $category = $entityManager->getRepository(Category::class)->find($id);
        
        if (!$category) {
            return $this->json(['message' => 'Category not found'], Response::HTTP_NOT_FOUND);
        }
        
        // Check if category belongs to user or is system
        if ($category->getUser() !== null && $category->getUser()->getId() !== $user->getId()) {
            return $this->json(['message' => 'Access denied'], Response::HTTP_FORBIDDEN);
        }
        
        return $this->json([
            'category' => $category
        ], Response::HTTP_OK, [], ['groups' => ['category:read']]);
    }
    
    #[Route('/{id}', name: 'app_category_update', methods: ['PUT'])]
    public function update(
        int $id,
        Request $request, 
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator
    ): JsonResponse {
        /** @var User $user */
        $user = $this->getUser();
        
        $category = $entityManager->getRepository(Category::class)->find($id);
        
        if (!$category) {
            return $this->json(['message' => 'Category not found'], Response::HTTP_NOT_FOUND);
        }
        
        // Check if category belongs to user
        if ($category->getUser() === null || $category->getUser()->getId() !== $user->getId()) {
            return $this->json(['message' => 'Access denied'], Response::HTTP_FORBIDDEN);
        }
        
        // Prevent modification of system categories
        if ($category->isSystem()) {
            return $this->json(['message' => 'Cannot modify system categories'], Response::HTTP_FORBIDDEN);
        }
        
        $data = json_decode($request->getContent(), true);
        
        if (isset($data['name'])) {
            $category->setName($data['name']);
        }
        
        if (isset($data['color'])) {
            $category->setColor($data['color']);
        }
        
        if (isset($data['isIncome'])) {
            $category->setIsIncome($data['isIncome']);
        }
        
        // Validate entity
        $errors = $validator->validate($category);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getMessage();
            }
            return $this->json(['errors' => $errorMessages], Response::HTTP_BAD_REQUEST);
        }
        
        $entityManager->flush();
        
        return $this->json([
            'message' => 'Category updated successfully',
            'category' => $category
        ], Response::HTTP_OK, [], ['groups' => ['category:read']]);
    }
    
    #[Route('/{id}', name: 'app_category_delete', methods: ['DELETE'])]
    public function delete(int $id, EntityManagerInterface $entityManager): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();
        
        $category = $entityManager->getRepository(Category::class)->find($id);
        
        if (!$category) {
            return $this->json(['message' => 'Category not found'], Response::HTTP_NOT_FOUND);
        }
        
        // Check if category belongs to user
        if ($category->getUser() === null || $category->getUser()->getId() !== $user->getId()) {
            return $this->json(['message' => 'Access denied'], Response::HTTP_FORBIDDEN);
        }
        
        // Prevent deletion of system categories
        if ($category->isSystem()) {
            return $this->json(['message' => 'Cannot delete system categories'], Response::HTTP_FORBIDDEN);
        }
        
        // // Check if category is used in transactions
        // if (!$category->getTransactions()->isEmpty()) {
        //     return $this->json(['message' => 'Cannot delete category that is used in transactions'], Response::HTTP_BAD_REQUEST);
        // }
        
        // // Check if category is used in budgets
        // if (!$category->getBudgetCategories()->isEmpty()) {
        //     return $this->json(['message' => 'Cannot delete category that is used in budgets'], Response::HTTP_BAD_REQUEST);
        // }
        
        $entityManager->remove($category);
        $entityManager->flush();
        
        return $this->json(['message' => 'Category deleted successfully'], Response::HTTP_OK);
    }
}
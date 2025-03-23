<?php

namespace App\Command;

use App\Entity\Category;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:create-default-categories',
    description: 'Creates default system categories for all users',
)]
class CreateDefaultCategoriesCommand extends Command
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('user-id', InputArgument::OPTIONAL, 'User ID to create categories for (all users if not specified)');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $userId = $input->getArgument('user-id');
        
        $defaultExpenseCategories = [
            ['name' => 'Housing', 'color' => '#4285F4', 'isIncome' => false],
            ['name' => 'Transportation', 'color' => '#EA4335', 'isIncome' => false],
            ['name' => 'Food', 'color' => '#34A853', 'isIncome' => false],
            ['name' => 'Utilities', 'color' => '#FBBC05', 'isIncome' => false],
            ['name' => 'Healthcare', 'color' => '#8E44AD', 'isIncome' => false],
            ['name' => 'Shopping', 'color' => '#F06292', 'isIncome' => false],
            ['name' => 'Entertainment', 'color' => '#FF9800', 'isIncome' => false],
            ['name' => 'Personal', 'color' => '#607D8B', 'isIncome' => false],
            ['name' => 'Other Expenses', 'color' => '#757575', 'isIncome' => false],
        ];
        
        $defaultIncomeCategories = [
            ['name' => 'Salary', 'color' => '#2E7D32', 'isIncome' => true],
            ['name' => 'Investments', 'color' => '#3949AB', 'isIncome' => true],
            ['name' => 'Gifts', 'color' => '#D81B60', 'isIncome' => true],
            ['name' => 'Other Income', 'color' => '#00796B', 'isIncome' => true],
        ];
        
        $defaultCategories = array_merge($defaultExpenseCategories, $defaultIncomeCategories);
        
        if ($userId) {
            $user = $this->entityManager->getRepository(User::class)->find($userId);
            
            if (!$user) {
                $output->writeln("<error>User not found</error>");
                return Command::FAILURE;
            }
            
            $this->createCategoriesForUser($user, $defaultCategories, $output);
        } else {
            $users = $this->entityManager->getRepository(User::class)->findAll();
            
            foreach ($users as $user) {
                $this->createCategoriesForUser($user, $defaultCategories, $output);
            }
        }
        
        $output->writeln("<info>Default categories created successfully</info>");
        return Command::SUCCESS;
    }
    
    private function createCategoriesForUser(User $user, array $categories, OutputInterface $output): void
    {
        $existingCategories = $this->entityManager->getRepository(Category::class)
            ->findBy(['user' => $user]);
        
        if (count($existingCategories) > 0) {
            $output->writeln("User {$user->getUsername()} already has categories. Skipping.");
            return;
        }
        
        foreach ($categories as $categoryData) {
            $category = new Category();
            $category->setName($categoryData['name']);
            $category->setColor($categoryData['color']);
            $category->setIsIncome($categoryData['isIncome']);
            $category->setIsSystem(true);
            $category->setUser($user);
            
            $this->entityManager->persist($category);
        }
        
        $this->entityManager->flush();
        $output->writeln("Created categories for user {$user->getUsername()}");
    }
}
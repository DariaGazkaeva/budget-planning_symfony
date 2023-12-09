<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\MoneyOperation;
use App\Repository\CategoryRepository;
use App\Repository\UserRepository;
use App\Service\MoneyOperationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\LimitService;

class ProfileController extends AbstractController
{
    private LimitService $limitService;
    private Security $security;
    private int $userId;
    private MoneyOperationService $moneyOperationService;
    private CategoryRepository $categoryRepository;

    public function __construct(Security $security, MoneyOperationService $moneyOperationService, CategoryRepository $categoryRepository, LimitService $limitService)
    {
        $this->security = $security;
        $this->userId = $this->security->getUser()->getId();
        $this->moneyOperationService = $moneyOperationService;
        $this->categoryRepository = $categoryRepository;
        $this->limitService = $limitService;
    }
    #[Route('/profile', name: 'profile')]
    public function index(Request $request) {
        $incomeSum = $this->moneyOperationService->getSumForMonth($this->userId, true);
        $expenseSum = $this->moneyOperationService->getSumForMonth($this->userId, false);

        $limits = $this->limitService->findAllByUserId($this->userId);

        $incomeForm = $this->createMoneyOperationForm(true);
        $expenseForm = $this->createMoneyOperationForm(false);
        $incomeForm->handleRequest($request);
        $expenseForm->handleRequest($request);

        if ($incomeForm->isSubmitted() && $incomeForm->isValid()) {
            $data = $incomeForm->getData();
            $moneyOperation = new MoneyOperation();
            $moneyOperation->setIsIncome(true);
            $moneyOperation->setCategory($data['category']);
            $moneyOperation->setSum($data['sum']);
            $moneyOperation->setDate($data['date']);
            $moneyOperation->setDescription($data['description']);
            $moneyOperation->setOwner($this->security->getUser());
            $this->moneyOperationService->add($moneyOperation);
            return $this->redirectToRoute("profile");
        }

        if ($expenseForm->isSubmitted() && $expenseForm->isValid()) {
            $data = $expenseForm->getData();
            $moneyOperation = new MoneyOperation();
            $moneyOperation->setIsIncome(false);
            $moneyOperation->setCategory($data['category']);
            $moneyOperation->setSum($data['sum']);
            $moneyOperation->setDate($data['date']);
            $moneyOperation->setDescription($data['description']);
            $moneyOperation->setOwner($this->security->getUser());
            $this->moneyOperationService->add($moneyOperation);
            return $this->redirectToRoute("profile");
        }

        return $this->render("profile.html.twig",
            [
                'user' => $this->security->getUser(),
                'balance_modal' => false,
                'income_sum' => $incomeSum,
                'expense_sum' => $expenseSum,
                'income_form' =>$incomeForm->createView(),
                'expense_form' =>$expenseForm->createView(),
                'limits' => $limits,
            ]);
    }

    #[Route('/profile', name: 'profile_init')]
    public function indexInit(Request $request) {
        $incomeSum = $this->moneyOperationService->getSumForMonth($this->userId, true);
        $expenseSum = $this->moneyOperationService->getSumForMonth($this->userId, false);

        $limits = $this->limitService->findAllByUserId($this->userId);

        $incomeForm = $this->createMoneyOperationForm(true);
        $expenseForm = $this->createMoneyOperationForm(false);
        $incomeForm->handleRequest($request);
        $expenseForm->handleRequest($request);

        if ($incomeForm->isSubmitted() && $incomeForm->isValid()) {
            $data = $incomeForm->getData();
            $moneyOperation = new MoneyOperation();
            $moneyOperation->setIsIncome(true);
            $moneyOperation->setCategory($data['category']);
            $moneyOperation->setSum($data['sum']);
            $moneyOperation->setDate($data['date']);
            $moneyOperation->setDescription($data['description']);
            $moneyOperation->setOwner($this->security->getUser());
            $this->moneyOperationService->add($moneyOperation);
            return $this->redirectToRoute("profile");
        }

        if ($expenseForm->isSubmitted() && $expenseForm->isValid()) {
            $data = $expenseForm->getData();
            $moneyOperation = new MoneyOperation();
            $moneyOperation->setIsIncome(false);
            $moneyOperation->setCategory($data['category']);
            $moneyOperation->setSum($data['sum']);
            $moneyOperation->setDate($data['date']);
            $moneyOperation->setDescription($data['description']);
            $moneyOperation->setOwner($this->security->getUser());
            $this->moneyOperationService->add($moneyOperation);
            return $this->redirectToRoute("profile");
        }

        return $this->render("profile.html.twig",
            [
                'user' => $this->security->getUser(),
                'balance_modal' => true,
                'income_sum' => $incomeSum,
                'expense_sum' => $expenseSum,
                'income_form' =>$incomeForm->createView(),
                'expense_form' =>$expenseForm->createView(),
                'limits' => $limits
            ]);
    }

    private function createMoneyOperationForm(bool $type) : FormInterface {
        return $this->createFormBuilder()
            ->add('category', ChoiceType::class, [
                'choices' => $this->categoryRepository->findAllByType($type),
                'choice_value' => 'id',
                'choice_label' => function (Category $category): string {
                    return $category->getName();
                },
            ])
            ->add('sum', NumberType::class)
            ->add('date', DateType::class, ['widget' => 'single_text',])
            ->add('description', TextType::class)
            ->getForm();
    }

    #[Route('/profile/balance', name: 'change_balance')]
    public function changeBalance(Request $request, UserRepository $userRepository) {
        $defaults = [
            'balance' => $this->security->getUser()->getBalance(),
        ];
        $form = $this->createFormBuilder($defaults)
            ->add('balance', NumberType::class)
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $balance = $form->getData()['balance'];
            $user = $this->security->getUser()->setBalance($balance);
            $userRepository->update($user);
            return $this->redirectToRoute("profile");
        }

        return $this->render("balance.html.twig", [
            'form' => $form->createView()
        ]);
    }
}
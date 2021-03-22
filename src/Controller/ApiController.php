<?php

namespace App\Controller;

use App\Service\ApiHelper;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class ApiController extends AbstractController {

    private ApiHelper $apiHelper;

    public function __construct(ApiHelper $apiHelper) {
        $this->apiHelper = $apiHelper;
    }

    /**
     * @Route("/", methods="GET", name="home")
     */
    public function index(): JsonResponse {
        return $this->json([
            'message' => 'Home endpoint',
        ]);
    }

    /**
     * @Route("/days/{date}", methods="GET", name="days")
     */
    public function days(string $date): JsonResponse {

        if (!$this->apiHelper->checkDate($date)) {
            return $this->json([
                'error' => 'Invalid date',
            ], 400);
        }

        return $this->json([
            'message' => 'Day endpoint',
        ]);
    }

    /**
     * @Route("/months/{monthDate}", methods="GET", name="months")
     */
    public function months(string $monthDate): JsonResponse {

        if (!$this->apiHelper->checkMonthDate($monthDate)) {
            return $this->json([
                'error' => 'Invalid date',
            ], 400);
        }

        return $this->json([
            'message' => 'Day endpoint',
        ]);
    }

    /**
     * @Route("/years/{year<\d+>}", methods="GET", name="years")
     */
    public function years(): JsonResponse {
        return $this->json([
            'message' => 'Year endpoint',
        ]);
    }

    /**
     * @Route("/range", methods="GET", name="range")
     */
    public function range(): JsonResponse {
        return $this->json([
            'message' => 'Range endpoint',
        ]);
    }

    /**
     * @Route("/collection", methods="GET", name="collection")
     */
    public function collection(): JsonResponse {
        return $this->json([
            'message' => 'Collection endpoint',
        ]);
    }

    /**
     * @Route("/login", methods="GET", name="login")
     */
    public function login(): JsonResponse {
        return $this->json([
            'message' => 'Login endpoint',
        ]);
    }

}

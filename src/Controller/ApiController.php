<?php

namespace App\Controller;

use App\Model\User;
use App\Service\ApiHelper;
use App\Service\TauronService;
use DateTime;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class ApiController extends AbstractController {

    private ApiHelper $apiHelper;
    private TauronService $tauronService;
    private string $dateFormat;

    public function __construct(ApiHelper $apiHelper, TauronService $tauronService, string $dateFormat) {
        $this->apiHelper = $apiHelper;
        $this->tauronService = $tauronService;
        $this->dateFormat = $dateFormat;
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
    public function days(Request $request, string $date): JsonResponse {
        $this->apiHelper->checkContentType($request);
        $this->apiHelper->checkDate($date);

        $date = DateTime::createFromFormat($this->dateFormat, $date);

        $token = $request->headers->get('Authorization');
        $user = User::createFromToken($token);

        $dayUsage = $this->tauronService->getDayUsage($date, $user);

        return $this->json($dayUsage);
    }

    /**
     * @Route("/months/{monthDate}", methods="GET", name="months")
     */
    public function months(string $monthDate): JsonResponse {

        $this->apiHelper->checkMonthDate($monthDate);

        return $this->json([
            'message' => 'Day endpoint',
        ]);
    }

    /**
     * @Route("/years/{year<\d+>}", methods="GET", name="years")
     */
    public function years(int $year): JsonResponse {

        $this->apiHelper->checkYear($year);

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
     * @Route("/login", methods="POST", name="login")
     */
    public function login(Request $request): JsonResponse {
        $content = json_decode($request->getContent());

        $user = User::createFromJSON($content);
        $this->tauronService->login($user);

        return $this->json(['token' => $user->createToken()]);
    }

}

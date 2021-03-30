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
    private string $monthDateFormat;

    public function __construct(ApiHelper $apiHelper, TauronService $tauronService, string $dateFormat, string $monthDateFormat) {
        $this->apiHelper = $apiHelper;
        $this->tauronService = $tauronService;
        $this->dateFormat = $dateFormat;
        $this->monthDateFormat = $monthDateFormat;
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
    public function months(Request $request, string $monthDate): JsonResponse {
        $this->apiHelper->checkContentType($request);
        $this->apiHelper->checkMonthDate($monthDate);

        $monthDate = DateTime::createFromFormat($this->monthDateFormat, $monthDate);
        $token = $request->headers->get('Authorization');
        $user = User::createFromToken($token);

        $monthUsage = $this->tauronService->getMonthUsage($monthDate, $user);

        return $this->json($monthUsage);
    }

    /**
     * @Route("/years/{year<\d+>}", methods="GET", name="years")
     */
    public function years(Request $request, int $year): JsonResponse {
        $this->apiHelper->checkContentType($request);
        $this->apiHelper->checkYear($year);

        $token = $request->headers->get('Authorization');
        $user = User::createFromToken($token);

        $yearUsage = $this->tauronService->getYearUsage($year, $user, false);

        return $this->json($yearUsage);
    }

    /**
     * @Route("/range", methods="GET", name="range")
     */
    public function range(Request $request): JsonResponse {
        $this->apiHelper->checkContentType($request);
        $this->apiHelper->checkQueryParameterExist($request, 'startDate');
        $this->apiHelper->checkQueryParameterExist($request, 'endDate');

        $startDateStr = $request->query->get('startDate');
        $endDateStr = $request->query->get('endDate');

        $this->apiHelper->checkRange($startDateStr, $endDateStr);

        $startDate = DateTime::createFromFormat($this->monthDateFormat, $startDateStr);
        $endDate = DateTime::createFromFormat($this->monthDateFormat, $endDateStr);

        $token = $request->headers->get('Authorization');
        $user = User::createFromToken($token);

        $rangeUsage = $this->tauronService->getRangeUsage($startDate, $endDate, $user);

        return $this->json($rangeUsage);
    }

    /**
     * @Route("/all", methods="GET", name="all")
     */
    public function all(Request $request): JsonResponse {
        $this->apiHelper->checkContentType($request);
        $user = $this->getUserFromToken($request);
        $allUsage = $this->tauronService->getAllUsage($user);

        return $this->json($allUsage);
    }

    /**
     * @Route("/collection", methods="GET", name="collection")
     */
    public function collection(Request $request): JsonResponse {
        $this->apiHelper->checkContentType($request);

        $days = $request->query->all('days');
        $months = $request->query->all('months');
        $years = $request->query->all('years');

        $this->apiHelper->checkCollection($days, $months, $years);

        $user = $this->getUserFromToken($request);
        $collection = $this->tauronService->getCollection($days, $months, $years, $user);

        return $this->json($collection);
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

    private function getUserFromToken(Request $request): User {
        $token = $request->headers->get('Authorization');
        return User::createFromToken($token);
    }
}

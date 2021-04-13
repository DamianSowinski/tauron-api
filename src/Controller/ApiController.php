<?php

namespace App\Controller;

use App\Model\User;
use App\Service\ApiHelper;
use App\Service\MockTauronService;
use App\Service\TauronService;
use DateTime;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ApiController extends AbstractController {

    private ApiHelper $apiHelper;
    private TauronService $tauronService;
    private MockTauronService $mockTauronService;
    private string $dateFormat;
    private string $monthDateFormat;
    private string $siteUrl;

    public function __construct(ApiHelper $apiHelper,
                                TauronService $tauronService,
                                MockTauronService $mockTauronService,
                                string $dateFormat,
                                string $monthDateFormat,
                                string $siteUrl) {
        $this->apiHelper = $apiHelper;
        $this->tauronService = $tauronService;
        $this->dateFormat = $dateFormat;
        $this->monthDateFormat = $monthDateFormat;
        $this->siteUrl = $siteUrl;
        $this->mockTauronService = $mockTauronService;
    }

    /**
     * @Route("/", methods="GET", name="home")
     */
    public function index(Request $request): Response {
        $acceptFormat = $request->headers->get('Accept');

        $routes = [
            'home' => [
                'name' => 'Home',
                'route' => '/',
            ],
            'login' => [
                'name' => 'Login',
                'route' => '/login',
                'parameters' => [
                    'pointId' => 'Tauron device ID, pass as json in POST request',
                    'username' => 'Tauron username, pass as json in POST request',
                    'password' => 'Tauron password, pass as json in POST request',
                ],
            ],
            'days' => [
                'name' => 'Day',
                'route' => '/days/{data}',
                'parameters' => [
                    'date' => 'Data in dd-mm-yyyy format',
                ],
            ],
            'months' => [
                'name' => 'Month',
                'route' => '/months/{data}',
                'parameters' => [
                    'date' => 'Data in mm-yyyy format',
                ],
            ],
            'years' => [
                'name' => 'Year',
                'route' => '/years/{year}',
                'parameters' => [
                    'year' => 'Data in yyyy format',
                ],
            ],
            'range' => [
                'name' => 'Range',
                'route' => '/range?{query-string}',
                'parameters' => [
                    'query string' => 'Query string in format startDate=mm-yyyy&endDate=mm-yyyy',
                ],
            ],
            'all' => [
                'name' => 'All',
                'route' => '/all',
            ],
            'collection' => [
                'name' => 'Collection',
                'route' => '/collection?{query-string}',
                'parameters' => [
                    'query string' => 'Query string in format days[]=dd-mm-yyyy&months[]=mm-yyyy&years[]=yyyy',
                ],
            ],
        ];

        if ('application/json' === $acceptFormat) {
            $body = [
                'title' => 'Tauron API',
                'route list' => $routes,
            ];

            return $this->json($body);
        }

        return new Response($this->generateHomeHTML($routes));
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

        $dayUsage = $user->isTestUser() ?
            $this->mockTauronService->getDayUsage($date) :
            $this->tauronService->getDayUsage($date, $user);

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

        $monthUsage = $user->isTestUser() ?
            $this->mockTauronService->getMonthUsage($monthDate) :
            $this->tauronService->getMonthUsage($monthDate, $user);

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

        $yearUsage = $user->isTestUser() ?
            $this->mockTauronService->getYearUsage($year) :
            $this->tauronService->getYearUsage($year, $user);

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

        $rangeUsage = $user->isTestUser() ?
            $this->mockTauronService->getRangeUsage($startDate, $endDate) :
            $this->tauronService->getRangeUsage($startDate, $endDate, $user);

        return $this->json($rangeUsage);
    }

    /**
     * @Route("/all", methods="GET", name="all")
     */
    public function all(Request $request): JsonResponse {
        $this->apiHelper->checkContentType($request);
        $user = $this->getUserFromToken($request);

        $allUsage = $user->isTestUser() ?
            $this->mockTauronService->getAllUsage() :
            $this->tauronService->getAllUsage($user);

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
        $collection = $user->isTestUser() ?
            $this->mockTauronService->getCollection($days, $months, $years) :
            $this->tauronService->getCollection($days, $months, $years, $user);

        return $this->json($collection);
    }

    /**
     * @Route("/login", methods="POST", name="login")
     */
    public function login(Request $request): JsonResponse {
        $content = json_decode($request->getContent());

        $user = User::createFromJSON($content);

        if (!$user->isTestUser()) {
            $this->tauronService->login($user);
        }

        return $this->json(['token' => $user->createToken()]);
    }

    private function getUserFromToken(Request $request): User {
        $token = $request->headers->get('Authorization');
        return User::createFromToken($token);
    }

    private function generateHomeHTML(array $routes): string {

        $routeList = '';
        $siteUrl = $this->siteUrl;

        foreach ($routes as $route) {
            $parameters = '';

            if (array_key_exists('parameters', $route)) {
                $parameters .= '<span>Parameters:</span><dl class="data__parameters">';

                foreach ($route['parameters'] as $key => $value) {
                    $parameters .= <<<HTML
                    <dt class="h-gray">${key}</dt>
                    <dd>${value}</dd>
                HTML;
                }

                $parameters .= '</dl>';
            }

            $routeList .= <<<HTML
                <dt class="c-definitions__term">${route['name']}</dt>
                <dd class="c-definitions__data">           
                    <div class="data__details">
                        <a class="o-link" href="${siteUrl}${route['route']}">${route['route']}</a>
                        ${parameters}
                    </div>  
                </dd>  
            HTML;
        }

        return <<<HTML
            <!doctype html>
            <html lang="en">
                <head>
                    <meta charset="UTF-8">
                    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
                    <meta http-equiv="X-UA-Compatible" content="ie=edge">
                    <link rel="shortcut icon" href="favicon.ico" type="image/x-icon">
                    <title>Tauron API</title>
                    
                    <style>
                        body {
                            font-family: Helvetica, sans-serif;
                            font-size: 16px;
                            background-color: #333;
                            color: #ddd;
                            margin: 0;
                            padding: 0;
                            display: flex;
                            justify-content: center;
                        }
                        
                        .l-container {
                            margin: 64px 32px;
                            display: grid;
                            grid-template-columns: 1fr;
                            grid-gap: 32px;
                            align-items: center;
                            justify-content: center;
                            width: 800px;
                            
                        }
                        
                        .o-title, .o-subtitle {
                            font-weight: 300;
                            margin: 0;
                            padding: 0;
                            
                        }   
                        
                        .o-title {
                            text-align: center;
                            font-size: 1.75rem;
                        }    
                        
                        .o-title .ico {
                            width: 24px;
                            height: 24px;
                            fill: #ddd;
                        }             
                               
                        .o-subtitle {
                            font-size: 1.125rem;
                        }
                        
                        .o-link {
                            color: #a0c2ff; 
                        }
                        
                        .c-definitions {
                            display: grid;
                            grid-template-columns: auto 1fr;
                            grid-column-gap: 16px; 
                            grid-row-gap: 54px; 
                            margin: 0 0 0 48px;
                            
                        }
                        
                        .c-definitions .data__details {
                            display: grid;
                            grid-template-columns: 1fr;
                            grid-gap: 16px;
                        }
                        
                        .c-definitions .data__parameters {
                            display: grid;
                            grid-template-columns: auto 1fr;
                            grid-gap: 8px; 
                            margin: 0 0 0 48px;
                        }
                        
                        .h-gray {
                           color: #aaa;
                        }
                        
                    </style>
                </head>
                <body>
                    <div class="l-container">
                        <h1 class="o-title">
                            <svg class="ico" viewBox="0 0 512 512"><path d="M256 496c26.4 0 48-21.6 48-48h-96c0 26.4 21.6 48 48 48zM184 424h144c13.2 0 24-10.8 24-24s-10.8-24-24-24H184c-13.2 0-24 10.8-24 24s10.8 24 24 24z"/><path d="M256 16C156.64 16 76 96.64 76 196c0 91.68 63.84 140.64 90.48 156h179.04C372.16 336.64 436 287.68 436 196c0-99.36-80.64-180-180-180zm0 24.8c85.67 0 155.2 69.53 155.2 155.2 0 73.37-47.44 115-72.64 131.2H173.44C148.24 311 100.8 269.37 100.8 196c0-85.67 69.53-155.2 155.2-155.2z"/><path d="M256 149.12a50.65 50.65 0 00-50.63 50.63A50.65 50.65 0 00256 250.38a50.65 50.65 0 0050.63-50.63A50.65 50.65 0 00256 149.12zm-101.27 60.76h20.25c5.57 0 10.13-4.56 10.13-10.13 0-5.57-4.56-10.13-10.13-10.13h-20.25a10.16 10.16 0 00-10.13 10.13c0 5.57 4.56 10.13 10.13 10.13zm182.29 0h20.25c5.57 0 10.13-4.56 10.13-10.13 0-5.57-4.56-10.13-10.13-10.13h-20.25a10.16 10.16 0 00-10.13 10.13c0 5.57 4.56 10.13 10.13 10.13zm-91.15-111.4v20.25c0 5.57 4.56 10.13 10.13 10.13 5.57 0 10.13-4.56 10.13-10.13V98.48c0-5.57-4.56-10.13-10.13-10.13a10.16 10.16 0 00-10.13 10.13zm0 182.29v20.25c0 5.57 4.56 10.13 10.13 10.13 5.57 0 10.13-4.56 10.13-10.13v-20.25c0-5.57-4.56-10.13-10.13-10.13a10.16 10.16 0 00-10.13 10.13zM195.14 124.6a10.08 10.08 0 00-14.28 0 10.08 10.08 0 000 14.28l10.73 10.73a10.08 10.08 0 0014.28 0c3.85-3.95 3.95-10.43 0-14.28l-10.73-10.73zM320.4 249.88a10.08 10.08 0 00-14.28 0 10.08 10.08 0 000 14.28l10.73 10.73a10.08 10.08 0 0014.28 0 10.08 10.08 0 000-14.28l-10.73-10.73zm10.73-111a10.08 10.08 0 000-14.27 10.08 10.08 0 00-14.28 0l-10.73 10.73a10.08 10.08 0 000 14.28c3.95 3.85 10.43 3.95 14.28 0l10.73-10.73zM205.87 264.17a10.08 10.08 0 000-14.28 10.08 10.08 0 00-14.28 0l-10.73 10.73a10.08 10.08 0 000 14.28c3.95 3.85 10.43 3.95 14.28 0l10.73-10.73z"/></svg>                        
                            <span>Tauron API</span>
                            </h1>
                       
                        <h2 class="o-subtitle">Route List:</h2>
                        <dl class="c-definitions">
                            ${routeList}               
                        </dl>
                    </div>
                    
                </body>
            </html>  
        HTML;
    }
}

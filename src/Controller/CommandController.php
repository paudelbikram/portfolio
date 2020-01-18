<?php
declare(strict_types=1);


namespace App\Controller;


use App\Terminal\Terminal;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class CommandController extends AbstractController
{
    /**
     * @Route(path="/command/")
     * @param Request $request
     * @param Terminal $terminal
     * @return JsonResponse
     */
    public function __invoke(Request $request, Terminal $terminal)
    {
        try {
            $content = $request->getContent();
            $payload = json_decode($content, false, 512, JSON_THROW_ON_ERROR);

            if (empty($payload->command)) {
                throw new \InvalidArgumentException();
            }

            $output = $terminal->command($payload->command);

            return new JsonResponse([
                'stdout' => $output->getStdout(),
                'alert' => $output->getAlert(),
            ]);
        } catch (Exception $e) {
            return new JsonResponse([], 400);
        }
    }
}

<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

use App\Service\MapManager;

class MapRegionController extends AbstractController
{
    /**
     * @Route("/map_data", name="map_data")
     * @param \App\Service\MapManager       $mapManager
     */
    public function mapData(
        MapManager $mapManager
    ): JsonResponse
    {
        $countByRegion = $mapManager->getUsersByRegion();

        return $this->json($countByRegion);
    }
}

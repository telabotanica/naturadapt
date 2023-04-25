<?php

namespace App\Util;


use App\Util\Point;
use App\Util\Polygon;
use App\Util\MultiPolygon;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class Geocoder
{

    private $params;

    public function __construct(ParameterBagInterface $params)
    {
        $this->params = $params;
    }

    private function pointInPolygon($point, $polygon) {
        $numPoints = count($polygon->components);
        $i = 0;
        $j = $numPoints - 1;
        $c = false;
    
        for (; $i < $numPoints; $j = $i++) {
            $point1 = $polygon->components[$i];
            $point2 = $polygon->components[$j];
            
            $point1_lat = $point1->coords[1];
            $point1_lon = $point1->coords[0];
            $point2_lat = $point2->coords[1];
            $point2_lon = $point2->coords[0];
            
            $test = (($point1_lat > $point->coords[1]) != ($point2_lat > $point->coords[1])) &&
                ($point->coords[0] < ($point2_lon - $point1_lon) * ($point->coords[1] - $point1_lat) / ($point2_lat - $point1_lat) + $point1_lon);
            
            if ($test) {
                $c = !$c;
            }
        }
    
        return $c;
    }

    private function pointInMultiPolygon($point, $multiPolygon) {
        foreach ($multiPolygon->components as $polygon) {
            if ($this->pointInPolygon($point, $polygon)) {
                return true;
            }
        }
        return false;
    }


    public function getNutsId(float $latitude, float $longitude): ?String
    {
        // Charger le fichier GeoJSON
        $geojson = file_get_contents($this->params->get('kernel.project_dir') . "/public/data/NUTS_RG_60M_2021_4326.geojson");
        $geojson_data = json_decode($geojson, true);
    
        // Créer un point à partir des coordonnées
        $point = new Point($longitude, $latitude);
    
        // Trouver le polygone qui contient le point
        $polygonContainingPoint = null;
    
        if ($geojson_data['type'] === 'FeatureCollection') {
            foreach ($geojson_data['features'] as $feature) {
                $geometryType = $feature['geometry']['type'];
                $coordinates = $feature['geometry']['coordinates'];

                if ($geometryType === 'Polygon') {
                    $polygon = new Polygon(
                        array_map(function ($coords) {
                            return new Point($coords[0], $coords[1]);
                        }, $coordinates[0])
                    );
        
                    if ($this->pointInPolygon($point, $polygon)) {
                        $polygonContainingPoint = $feature;
                        break;
                    }
                } elseif ($geometryType === 'MultiPolygon') {
                    $multiPolygon = new MultiPolygon(
                        array_map(function ($polygonCoords) {
                            return new Polygon(
                                array_map(function ($coords) {
                                    return new Point($coords[0], $coords[1]);
                                }, $polygonCoords[0])
                            );
                        }, $coordinates)
                    );
        
                    if ($this->pointInMultiPolygon($point, $multiPolygon)) {
                        $polygonContainingPoint = $feature;
                        break;
                    }
                }
            }
        }
    
        if ($polygonContainingPoint) {
            echo "Le point se trouve dans le polygone suivant : " . json_encode($polygonContainingPoint['properties']) . "\n";
            return $polygonContainingPoint['properties']['NUTS_ID'];
        } else {
            null;
        }
    
        return null;
    }

    function searchCoords($city, $countryCode, $postcode = null) {
        $endpoint = 'https://nominatim.openstreetmap.org/search';
        $address = urlencode("$city" . ($postcode ? ", $postcode" : ""));
        // Country code is required to avoid ambiguous results (ISO_A2 in BDD)
        $url = "$endpoint?q=$address&format=json&countrycodes=$countryCode";

        // Add a custom User-Agent to the request header
        $options = array(
            'http' => array(
                'header' => "User-Agent: naturadapt/1.0 (antoine.schlegel@rnfrance.org)\r\n",
            ),
        );
        $context = stream_context_create($options);
    
        $data = json_decode(file_get_contents($url, false, $context), true);
        if (count($data) == 0) {
            return array('lat' => null, 'lng' => null);
        }
        $coords = $data[0];
        return array('lat' => $coords['lat'], 'lng' => $coords['lon']);
    }

}

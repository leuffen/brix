<?php

namespace Leuffen\Brix\Functions;

use Lack\OpenAi\Attributes\AiFunction;
use Lack\OpenAi\Attributes\AiParam;

class GoogleMapsFunctions
{

    public function __construct(private string $apiToken)
    {

    }

    #[AiFunction("Query Google Maps API to retrieve Map Links to specified address. Will return a direct map link URL and a embedding URL.")]
    public function getGoogleMapsUrls(
        #[AiParam("The full address e.g. Longstreet 1 45130 Essen Germany")] string $address
    ){
        // Encode the address to be URL-friendly
        $encodedAddress = urlencode($address);

        // Construct the URL for the Geocoding API
        $geocodingUrl = "https://maps.googleapis.com/maps/api/geocode/json?address={$encodedAddress}&key={$this->apiToken}";

        try {
            // Call the Geocoding API to retrieve latitude and longitude
            $response = file_get_contents($geocodingUrl);
            $data = json_decode($response, true);

            // Extract latitude and longitude from the API response
            $location = $data['results'][0]['geometry']['location'];
            $latitude = $location['lat'];
            $longitude = $location['lng'];

            // Construct the URLs for the embedded map and map link
            $embeddedMapUrl = "https://www.google.com/maps/embed/v1/place?q={$latitude},{$longitude}";
            $mapLinkUrl = "https://www.google.com/maps/place/{$encodedAddress}/@{$latitude},{$longitude}";

            return array("map embedding url"=> $embeddedMapUrl, "map link url" => $mapLinkUrl);

        } catch (\Exception $e) {
            echo "An error occurred: " . $e->getMessage();
            return array(null, null);
        }
    }

}

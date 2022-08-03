<?php
namespace App\Service;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\Cities;
class WeatherDownloader {
    private $client;
    private $apiKey;
    function __construct(HttpClientInterface $client, ManagerRegistry $doctrine) {
        $this->client = $client;
        $this->apiKey = '5ee18b58fc1cffcf76382dc6769b7360';
        $this->languages = [
            'en',
            'pl',
        ];
        $this->entityManager = $doctrine->getManager();
    }
    function downloadCities(string $name, int $limit = 10): array {
        $content = $this->client->request('GET', "http://api.openweathermap.org/geo/1.0/direct?q=$name,&limit=$limit&appid=$this->apiKey")->toArray();
        $cities = [];
        foreach ($content as $city) {
            $params = [
                'name' => $city['name'],
                'country' => $city['country'],
                'lat' => $city['lat'],
                'lon' => $city['lon'],
                'pl' => $city['local_names']['pl'] ?? null,
                'en' => $city['local_names']['en'] ?? null,
            ];
            $cities[] = $params;
        }
        return $cities;
    }

    function downloadWeather(array|Cities $cities) {
        if (is_array($cities)) {
            foreach ($cities as $city) $this->downloadWeatherForOneCity($city);
        }
        else $this->downloadWeatherForOneCity($cities, false);
        $this->entityManager->flush();
    }

    public function downloadWeatherForOneCity(Cities $city, bool $flush = true, string $units = 'metric') {
        $icons = [
            '03n' => '03d',
            '04d' => '03d',
            '04n' => '03d',
            '09n' => '09d',
            '10n' => '10d',
            '11n' => '11d',
            '13n' => '13d',
        ];
        $lat = $city->getLat();
        $lon = $city->getLon();
        $url = "https://api.openweathermap.org/data/2.5/weather?lat=$lat&lon=$lon&appid=$this->apiKey&units=$units";
        $content = $this->client->request('GET', $url)->toArray();
        $icon = $content['weather'][0]['icon'];
        $city->setIcon($icons[$icon] ?? $icon);
        $city->setTempC((int) $content['main']['temp']);
        $this->entityManager->persist($city);
        if ($flush) $this->entityManager->flush();
    }
}
?>
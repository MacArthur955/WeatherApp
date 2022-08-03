<?php
namespace App\Command;

use App\Repository\CitiesRepository;
use App\Service\WeatherDownloader;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'app:weather:update')]
class UpdateWeatherCommand extends Command
{
    private $repositoryCities;
    public function __construct(CitiesRepository $citiesRepository, WeatherDownloader $weatherDownloader) {
        $this->repositoryCities = $citiesRepository;
        $this->weatherDownloader = $weatherDownloader;
        parent::__construct();
    }
    protected function configure() {
        $this->setDescription('Update the weather for every city in database\'s table "Cities"');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int {
        $start = time();
        $io = new SymfonyStyle($input, $output);
        $cities = $this->repositoryCities->getUsingCities();
        $this->weatherDownloader->downloadWeather($cities);
        $io->success(count($cities) . ' cities was updated in ' . time() - $start . ' seconds');
        return 0;
    }
}
?>
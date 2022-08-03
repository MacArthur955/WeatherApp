<?php
namespace App\Controller;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

use App\Service\WeatherDownloader;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\Cities;
use App\Entity\User;
use App\Repository\CitiesRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\User\UserInterface;

class HomePageController extends AbstractController {
    function __construct(ManagerRegistry $doctrine, EntityManagerInterface $entityManager) {
        $this->repositoryCities = $doctrine->getRepository(Cities::class);
        $this->repositoryUser = $doctrine->getRepository(User::class);
        $this->entityManager = $entityManager;
    }
    #[Route('/{_locale<en|pl>?}', name:'homepage')]
    function homepage($_locale, Request $request, SessionInterface $session): Response {
        $user = $this->getUser();
        if ($user) {
            if ($this->isGranted('ROLE_ADMIN')) $cities = $this->repositoryCities->getDefaultCities();
            else $cities = $this->repositoryCities->getCitiesById($user->getCities()->toArray());
            $userCity = $this->repositoryCities->getCityById($user->getUserCity())[0];
            if (null !== $_locale && $user->getLocale() !== $_locale) {
                $user->setLocale($_locale);
                $this->entityManager->flush();
            }
        }
        else {
            $cities = $this->repositoryCities->getDefaultCities();
            if ($userCity = $request->cookies->get('userCity')) $userCity = $this->repositoryCities->getCity($userCity);
            $userCity = $session->get('userCity', $userCity ?? $cities[0]);
        }
        return $this->render('homepage/homepage.html.twig', [
            'userCity' => $userCity,
            'cities' => $cities,
        ]);
    }
    #[Route('/update', name:'update')]
    function update(Request $request, SessionInterface $session, EntityManagerInterface $entityManager, WeatherDownloader $weatherDownloader) {
        if ($request->isXmlHttpRequest()) {
            $user = $this->getUser();
            $isVerified = $user ? $user->isVerified() : null;
            if ($data = $request->request->get('userCity')) {
                $data = json_decode($data, true);
                $id = (int) $data['city'];
                if ($data['delete'] && $user) {
                    $city = $this->repositoryCities->find($id);
                    if ($this->isGranted('ROLE_ADMIN')) $city->setDef(false);
                    else $user->removeCity($city);

                }
                else {
                    $userCity = $this->repositoryCities->getCity($id);
                    if ($user) $user->setUserCity($id);
                    else $session->set('userCity', $userCity);
                }
                if ($user) $entityManager->flush();
            }
            else if ($request->request->get('refresh')) {
                if ($user) return $this->json($this->repositoryCities->getCitiesById($user->getCities()->toArray()));
                return $this->json($this->repositoryCities->getDefaultCities());
            }
            else if ($locale = $request->request->get('locale')) {
                if ($user) {
                    $user->setLocale($locale);
                    $entityManager->flush();
                }
            }
            else if ($isVerified) {
                if ($prefix = $request->request->get('searchedCity')) {
                    return $this->json($weatherDownloader->downloadCities($prefix));
                }
                else if ($choosenCity = $request->request->get('choosenCity')) {
                    $choosenCity = json_decode($choosenCity, true);
                    $city = $this->repositoryCities->findOneBy([
                        'lat' => (float)$choosenCity['lat'],
                        'lon' => (float)$choosenCity['lon'],
                    ]);
                    $city = $city ? $city : $this->repositoryCities->create(
                        $choosenCity['name'],
                        (float)$choosenCity['lat'],
                        (float)$choosenCity['lon'],
                        $choosenCity['country'] ?? null,
                        $choosenCity['en'] ?? null,
                        $choosenCity['pl'] ?? null,
                    );
                    $new = false;
                    $user->setUserCity($city->getId());
                    if ($this->isGranted('ROLE_ADMIN') && !$city->isDef()) {
                        if (!$city->isHasUser()) $weatherDownloader->downloadWeatherForOneCity($city);
                        $city->setDef(true);
                        $new = true;
                    }
                    else {
                        if (!$user->hasCity($city)) {
                            $new = true;
                            $user->addCity($city);
                        }
                        if (!$city->isHasUser()) {
                            if (!$city->isDef()) $weatherDownloader->downloadWeatherForOneCity($city);
                            $city->setHasUser(true);
                        }
                    }
                    $entityManager->flush();
                    return $this->json([
                        'city' => $this->repositoryCities->getCityById($city->getId())[0],
                        'new' => $new,
                    ]);
                }
            }
            
            return $this->json(0);
        }
    }
    #[Route('/piaskownica/{prefix<\D>?P}', name:'piaskownica')]
    function piaskownica(Request $request, SessionInterface $session, EntityManagerInterface $entityManager, $prefix) {
        return $this->render('piaskownica.html.twig', [
            'test' => $this->getUser(),
        ]);
    }
}
?>
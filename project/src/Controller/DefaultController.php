<?php

namespace App\Controller;

use App\Repository\CardRepository;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

class DefaultController extends AbstractController
{
    /**
     * @Route("/trickWinnersNT", name="trickWinnersNT", methods={"GET"})
     * @param Request $request
     * @param SerializerInterface $serializer
     * @param CardRepository $cardRepository
     * @return Response
     */
    public function trickWinnersNT(Request $request, SerializerInterface $serializer, CardRepository $cardRepository)
    {
        $play = $request->query->get('play');

        if (!$play) {
            return new Response('No parameters', Response::HTTP_EXPECTATION_FAILED, [
                'Content-Type' => 'application/json'
            ]);
        }

        $cards = explode('-', $play);

        $weight_array = array();

        foreach ($cards as $card) {

            $entity_card = $cardRepository->findOneBy(array("name" => $card));
            $weight = $entity_card->getWeight();
            $weight_array[$card] = $weight;
        }

        $max = max($weight_array);

        $top_cards = array_filter($weight_array, function ($card) use ($max) { return $card == $max; });

        $data = $serializer->serialize(array_keys($top_cards), 'json');

        return new Response($data, Response::HTTP_OK, [
            'Content-Type' => 'application/json'
        ]);
    }


      /**
     * @Route("/trickWinners", name="trickWinners", methods={"GET"})
     * @param Request $request
     * @param SerializerInterface $serializer
     * @param CardRepository $cardRepository
     * @return Response
     */
    public function trickWinners(Request $request, SerializerInterface $serializer, CardRepository $cardRepository)
    {
        $play = $request->query->get('play');

        $trump = $request->query->get('trump');

        if (!$play) {
            return new Response('No parameters', Response::HTTP_EXPECTATION_FAILED, [
                'Content-Type' => 'application/json'
            ]);
        }

        $cards = explode('-', $play);

        $weight_array = array();

        foreach ($cards as $card) {

            $entity_card = $cardRepository->findOneBy(array("name" => $card));
            $weight = $entity_card->getWeight();

            $weight_array[$card] = $weight;
        }
        $max = max($weight_array);

        $top_cards = array_filter($weight_array, function ($card) use ($max) { return $card == $max; });
        
        if ($trump) {
            $cards_trump = explode('-', $trump);

            $weight_array_trump = array();

            foreach ($cards_trump as $card_trump) {
                $entity_card = $cardRepository->findOneBy(array("name" => $card_trump));
                $weight = $entity_card->getWeight();
                $weight_array_trump[$card_trump] = $weight;
            }

            foreach ($weight_array_trump as $key => $value) {
                $color_trump = substr($key, -1);
                $color = substr(array_keys($top_cards)[0], -1);

                if ($color != $color_trump) {
                    $weight_array_trump[$key] = 0;
                }
            }


            $max_trump = max($weight_array_trump);

            $top_cards_trump = array_filter($weight_array_trump, function ($card_trump) use ($max_trump) { return $card_trump == $max_trump; });

            if (reset($top_cards_trump) > reset($top_cards)) {
                $top_cards = $top_cards_trump;   
               }
        }

        $data = $serializer->serialize(array_keys($top_cards), 'json');

        return new Response($data, Response::HTTP_OK, [
            'Content-Type' => 'application/json'
        ]);
    }
}

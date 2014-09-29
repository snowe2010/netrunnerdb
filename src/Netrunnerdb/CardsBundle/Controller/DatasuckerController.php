<?php

namespace Netrunnerdb\CardsBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

class DatasuckerController extends Controller
{
    public function loadAction($url, Request $request)
    {
        $response = new Response();
        $response->setPublic();
        $response->setMaxAge($this->container->getParameter('long_cache'));

        $curl_data = null;
        $ch = curl_init("http://$url");
        curl_setopt($ch,CURLOPT_RETURNTRANSFER, true);
        if($curl_response = curl_exec($ch)) {
            $content_type = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
            if($content_type === "application/json; charset=utf-8") {
                $curl_data = json_decode($curl_response, true);
            } else {
                echo "Wrong content type: $content_type";
            }
        } else {
            echo "Curl request failed";
        }

        $data = array();
        if($curl_data) {
            foreach($curl_data as $i => $card) {
                foreach($card as $k => $v) {
                    $card[$k] = stripslashes(html_entity_decode($card[$k]));
                    $card[$k] = str_replace(array("<br />", " class='bbc'"), array("\r\n", ''), $card[$k]);
                    $card[$k] = preg_replace('/[\r\n]+/', "\r\n", $card[$k]);
                    if(preg_match('/^\d+$/', $card[$k]) && $k != "code") $card[$k] = intval($card[$k]);
                }
                $card['type_code'] = strtolower($card['type']);
                $card['subtype_code'] = strtolower($card['subtype']);
                $card['side_code'] = strtolower($card['side']);
                $card['faction_code'] = strtolower($card['faction']);
                $card['faction_letter'] = substr($card['faction_code'], 0, 1);
                $card['setname'] = $card['set'];
                $card['limited'] = $card['maxperdeck'] == 1;
                if(isset($card['mindecksize'])) $card['minimumdecksize'] = $card['mindecksize'];

                $data[$card['code']] = $card;
            }
        } else {
            echo "Empty curl response";
        }

        ksort($data);

        $response->setContent(json_encode(array_values($data)));
        return $response;

    }
}
<?php

namespace Slippy\GSMArena;

use GuzzleHttp\Client;
use simple_html_dom;

class Scraper
{
    /**
     * Display all brands
     *
     * @return array
     */
    public static function allBrands()
    {
        $curl = static::guzzle()->get('https://www.gsmarena.com/makers.php3');
        if ($curl->getStatusCode() == 200) {
            $dom = static::dom($curl->getBody());
            foreach ($dom->find('td') as $td) {
                $result[] = [
                    'name' => trim(str_replace($td->find('span', 0)->plaintext, null, $td->find('a', 0)->plaintext)),
                    'total' => preg_replace('/([a-z\s]*)/i', null, $td->find('span', 0)->plaintext),
                    'link' => $td->find('a', 0)->attr['href'],
                ];
            }
        }
        return isset($result) ? $result : [];
    }

    /**
     * View brand
     *
     * @param string $link
     * @return array
     */
    public static function viewBrand(string $link)
    {
        $curl = static::guzzle()->get('https://www.gsmarena.com/' . $link);
        if ($curl->getStatusCode() == 200) {
            $dom = static::dom($curl->getBody());
            $wraper = $dom->find('.makers', 0);
            $pages = $dom->find('.nav-pages', 0)->find('a');
            $max_page = end($pages)->plaintext;
            foreach ($wraper->find('li') as $list) {
                $result[] = [
                    'link' => $list->find('a', 0)->attr['href'],
                    'image' => $list->find('img', 0)->attr['src'],
                    'title' => $list->find('span', 0)->plaintext,
                    'description' => $list->find('img', 0)->attr['title'],
                ];
            }
        }
        return isset($result) ? $result : [];
    }

    /**
     * View phone
     *
     * @param string $link
     * @return array
     */
    public static function viewPhone(string $link)
    {
        $curl = static::guzzle()->get('https://www.gsmarena.com/' . $link);
        if ($curl->getStatusCode() == 200) {
            $dom = static::dom($curl->getBody());
            foreach ($dom->find('table[cellspacing=0]') as $table) {
                foreach ($table->find('tr') as $tr) {
                    $key = $tr->find('td', 0) ? $tr->find('td', 0)->plaintext : null;
                    if (!is_null($key)) {
                        $spec_detail[$key] = $tr->find('td', 1)->plaintext;
                    }
                }
                $specs[$table->find('th', 0)->plaintext] = $spec_detail;
                $spec_detail = null;
            }
            $result = [
                'image' => $dom->find('.pricing-scroll-container', 0)->attr['data-img'],
                'title' => $dom->find('.specs-phone-name-title', 0)->plaintext,
                'specs' => $specs,
            ];
        }
        return isset($result) ? $result : [];
    }

    /**
     * Dom instance
     *
     * @param string $str
     * @return void
     */
    public static function dom(string $str)
    {
        return new simple_html_dom($str);
    }

    /**
     * Guzzle instance
     *
     * @return void
     */
    public static function guzzle()
    {
        return new Client;
    }
}

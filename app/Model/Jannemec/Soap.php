<?php
namespace jannemec;
/**
 * Description of Soap
 *
 * @author nemec
 */
class Soap {
    
    /**
     *
     * @var int 
     */
    private static $cacheExpire = 864000; //10 * 24 * 3600;
    
    /**
     *
     * @var \Nette\Caching\Cache 
     */
    private static $cache;
    
    public function __construct(\Nette\Caching\Cache $cache) { //\DibiConnection $dbf, \Nette\Caching\Cache $cache) {
        self::$cache = $cache;
    }
    
    private static $veryfyVatsWSDL = 'https://adisrws.mfcr.cz/adistc/axis2/services/rozhraniCRPDPH.rozhraniCRPDPHSOAP?wsdl';
    //private static $veryfyVatsWSDL = 'https://adisrws.mfcr.cz/adistc/axis2/services/rozhraniCRPDPH.rozhraniCRPDPHSOAP';
    
    //private static $orWSDL = 'http://wwwinfo.mfcr.cz/ares/xml_doc/wsdl/or.wsdl';
    private static $orWSDL = 'http://wwwinfo.mfcr.cz/ares/xml_doc/wsdl/standard.wsdl';
    
    /**
     * Kontroluje registr ověřených plátců DPH - soap
     * @param array $vats
     * @return array
     */
    public function verifyVats($vats) {

        $arrContextOptions=array(
            'ssl'   => array(
                'verify_peer'       => false,
                'verify_peer_name'  => false,
            ),
            'http'  => array(
                'user_agent'        => 'PHPSoapClient'
            )
        );  


        $context = stream_context_create($arrContextOptions);
        $client = new \SoapClient(self::$veryfyVatsWSDL
                ,['stream_context' => $context]
                );
        
        $fncs = $client->__getFunctions();
        
        //\Nette\Diagnostics\Debugger::dump($fncs);// exit;
        
        $out = array();
        // Musíme odesílat po částech
        while(count($vats) > 0) {
            $outO = array_slice($vats, 0, min(90, count($vats)));
            array_splice($vats, 0, min(90, count($vats)));
            if (!empty($out)) {
                sleep(2); // nastavení zpoždění - pokud je již další dotaz
            }
            $result = $client->getStatusNespolehlivyPlatce($outO);
            // Vyzkoušíme, jestli proběhlo korektně ...
            //echo 'AAA';
            //echo '<pre>'; var_dump($result); echo '</pre>'; exit;
            if (!is_object($result) || !isset($result->status) || !isset($result->status->statusCode) || $result->status->statusCode != 0) {
                // Neproběhlo - zkusíme ještě jednou !!!
                sleep(5);
                $result = $client->getStatusNespolehlivyPlatce($outO);
            }
            if (is_object($result) && isset($result->status) && $result->status->statusCode == 0) {
                $dt = strtotime($result->status->odpovedGenerovana);
                //echo '<pre>';var_dump($result->statusPlatceDPH); echo '</pre>';
                if (!is_array($result->statusPlatceDPH)) {
                    $result->statusPlatceDPH = array($result->statusPlatceDPH);
                }
                foreach($result->statusPlatceDPH as $platce) {
                    if (isset($platce->dic)) {
                        $out[$platce->dic] = array('status' => $platce->nespolehlivyPlatce);
                        if ($platce->nespolehlivyPlatce == 'NE') {
                            $out[$platce->dic]['accounts'] = array();
                            //\Nette\Diagnostics\Debugger::dump($platce->zverejneneUcty); exit;
                            if (isset($platce->zverejneneUcty) && is_array($platce->zverejneneUcty->ucet)) {
                                foreach($platce->zverejneneUcty->ucet as $account) {
                                    if (isset($account->standardniUcet)) {
                                        $out[$platce->dic]['accounts'][] = array(
                                            'acc' => $account->standardniUcet->cislo
                                            , 'bankcode' => isset($account->standardniUcet->kodBanky) ? $account->standardniUcet->kodBanky : ''
                                            , 'prefix' => isset($account->standardniUcet->predcisli) ? $account->standardniUcet->predcisli : ''
                                            , 'dt' => strtotime($account->datumZverejneni)
                                            );
                                    } else {
                                        //\Nette\Diagnostics\Debugger::dump($account); exit;
                                        $out[$platce->dic]['accounts'][] = array(
                                            'acc' => $account->nestandardniUcet->cislo
                                            , 'bankcode' => isset($account->nestandardniUcet->kodBanky) ? $account->nestandardniUcet->kodBanky : ''
                                            , 'prefix' => isset($account->nestandardniUcet->predcisli) ? $account->nestandardniUcet->predcisli : ''
                                            , 'dt' => strtotime($account->datumZverejneni)
                                            );
                                    }
                                }
                            } elseif (isset($platce->zverejneneUcty)) {
                                $account = $platce->zverejneneUcty->ucet;
                                if (isset($account->standardniUcet)) {
                                    $out[$platce->dic]['accounts'][] = array(
                                        'acc' => $account->standardniUcet->cislo
                                        , 'bankcode' => isset($account->standardniUcet->kodBanky) ? $account->standardniUcet->kodBanky : ''
                                        , 'prefix' => isset($account->standardniUcet->predcisli) ? $account->standardniUcet->predcisli : ''
                                        , 'dt' => strtotime($account->datumZverejneni)
                                        );
                                } else {
                                    //\Nette\Diagnostics\Debugger::dump($account); exit;
                                    $out[$platce->dic]['accounts'][] = array(
                                        'acc' => $account->nestandardniUcet->cislo
                                        , 'bankcode' => isset($account->nestandardniUcet->kodBanky) ? $account->nestandardniUcet->kodBanky : ''
                                        , 'prefix' => isset($account->nestandardniUcet->predcisli) ? $account->nestandardniUcet->predcisli : ''
                                        , 'dt' => strtotime($account->datumZverejneni)
                                        );
                                }
                            } else {
                                // Není žádný účet
                            }
                        } 
                    }
                }
            } else {
                // Došlo k chybě
                mail('jan.nemec@otk.cz', 'OTK intranet - kontrola DPH', 'Chyba načítání dat z MF' . "\n" . implode("\n", $outO) . "\n\n" . print_r($result, true));
            }
            //$out = array_merge($out, $result);
        }

        //echo'<pre>'; var_dump($out); echo '</pre>'; exit;
        return($out);
    }
    
    
    
    
    
    
    
    /**
     * Získání základní informace o společnosti - typ, adresa, název
     * @param string $ico
     * @return AresCompany 
     */
    public static function getCompanyByIco($ico, $nocache = false, $onlyActive = true) {
        $ico = str_pad($ico, 8, '0', STR_PAD_LEFT);
        if (!$nocache && (self::$cache instanceOf \Nette\Caching\Cache) && 
                 ($out = self::$cache->load('SOAP.ICO.' . $ico)) && (!is_null($out))) {
            //$out = self::$cache['SOAP.ICO.' . $ico];
        } elseif (strlen(trim($ico)) > 8) {
            $out = new AresCompany($ico);
            // nIc neděláme - špatný počet znaků ....
        } else {
            // via GET request
            //$ico = '28961048';
            $url = self::getLocation(2) . '?ico=' . $ico;
            if (!$onlyActive) {
                $url .= '&aktivni=false';
            }
            $response = new \DOMDocument();
            $response->load($url);
            $xpath = new \DOMXPath($response);
            $out = new AresCompany($ico);
            $query = '//are:Ares_odpovedi/are:Odpoved/are:Zaznam/are:Obchodni_firma';
            $entries = $xpath->query($query);
            if ((count($entries) > 0) && is_object($entries) && !is_null($entries->item(0)) && !is_null($entries->item(0)->textContent)) {
                $out->setName($entries->item(0)->textContent);
                $query = '//are:Ares_odpovedi/are:Odpoved/are:Zaznam/are:Identifikace/are:Adresa_ARES/dtt:Nazev_obce';
                $entries = $xpath->query($query);
                if ((count($entries) > 0) && is_object($entries) && !is_null($entries->item(0)) && !is_null($entries->item(0)->textContent)) {
                    $query = '//are:Ares_odpovedi/are:Odpoved/are:Zaznam/are:Identifikace/are:Adresa_ARES/dtt:Nazev_okresu';
                    $entries = $xpath->query($query);
                    if ((count($entries) > 0) && !is_null($entries->item(0)) && !is_null($entries->item(0)->textContent)) {
                        $out->setArea($entries->item(0)->textContent);
                    }
                    $query = '//are:Ares_odpovedi/are:Odpoved/are:Zaznam/are:Identifikace/are:Adresa_ARES/dtt:Nazev_obce';
                    $entries = $xpath->query($query);
                    if ((count($entries) > 0) && !is_null($entries->item(0)) && !is_null($entries->item(0)->textContent)) {
                        $out->setCity($entries->item(0)->textContent);
                    }
                    $query = '//are:Ares_odpovedi/are:Odpoved/are:Zaznam/are:Identifikace/are:Adresa_ARES/dtt:Nazev_casti_obce';
                    $entries = $xpath->query($query);
                    if ((count($entries) > 0) && !is_null($entries->item(0)) && !is_null($entries->item(0)->textContent)) {
                        $out->setSubcity($entries->item(0)->textContent);
                    }
                    $query = '//are:Ares_odpovedi/are:Odpoved/are:Zaznam/are:Identifikace/are:Adresa_ARES/dtt:Nazev_mestske_casti';
                    $entries = $xpath->query($query);
                    if ((count($entries) > 0) && !is_null($entries->item(0)) && !is_null($entries->item(0)->textContent)) {
                        $out->setMestskaCast($entries->item(0)->textContent);
                    }
                    $query = '//are:Ares_odpovedi/are:Odpoved/are:Zaznam/are:Identifikace/are:Adresa_ARES/dtt:Nazev_ulice';
                    $entries = $xpath->query($query);
                    if ((count($entries) > 0) && !is_null($entries->item(0)) && !is_null($entries->item(0)->textContent)) {
                        $out->setStreet($entries->item(0)->textContent);
                    }
                    $query = '//are:Ares_odpovedi/are:Odpoved/are:Zaznam/are:Identifikace/are:Adresa_ARES/dtt:Cislo_domovni';
                    $entries = $xpath->query($query);
                    if ((count($entries) > 0) && !is_null($entries->item(0)) && !is_null($entries->item(0)->textContent)) {
                        //debug::dump(array($entries, count($entries), $entries->item(0), $entries->item(0)->textContent));
                        $out->setHome_nr($entries->item(0)->textContent);
                    }
                    if (is_null($out->getHome_nr()) || (trim($out->getHome_nr()) == '')) {
                        // Vyzkoušíme ještě 
                        $query = '//are:Ares_odpovedi/are:Odpoved/are:Zaznam/are:Identifikace/are:Adresa_ARES/dtt:Cislo_do_adresy';
                        $entries = $xpath->query($query);
                        if ((count($entries) > 0) && !is_null($entries->item(0)) && !is_null($entries->item(0)->textContent)) {
                            //debug::dump(array($entries, count($entries), $entries->item(0), $entries->item(0)->textContent));
                            $out->setHome_nr($entries->item(0)->textContent);
                        }
                    }
                    $query = '//are:Ares_odpovedi/are:Odpoved/are:Zaznam/are:Identifikace/are:Adresa_ARES/dtt:Cislo_orientacni';
                    $entries = $xpath->query($query);
                    if ((count($entries) > 0) && !is_null($entries->item(0)) && !is_null($entries->item(0)->textContent)) {
                        //debug::dump(array($entries, count($entries), $entries->item(0), $entries->textContent));
                        $out->setStreet_nr($entries->item(0)->textContent);
                    }
                    $query = '//are:Ares_odpovedi/are:Odpoved/are:Zaznam/are:Identifikace/are:Adresa_ARES/dtt:PSC';
                    $entries = $xpath->query($query);
                    if ((count($entries) > 0) && !is_null($entries->item(0)) && !is_null($entries->item(0)->textContent)) {
                        $out->setPsc($entries->item(0)->textContent);
                    }
                } else {
                    // Varianta osoby
                    $query = '//are:Ares_odpovedi/are:Odpoved/are:Zaznam/are:Identifikace/are:Osoba/dtt:Bydliste/dtt:Nazev_okresu';
                    $entries = $xpath->query($query);
                    if ((count($entries) > 0) && !is_null($entries->item(0)) && !is_null($entries->item(0)->textContent)) {
                        $out->setArea($entries->item(0)->textContent);
                    }
                    $query = '//are:Ares_odpovedi/are:Odpoved/are:Zaznam/are:Identifikace/are:Osoba/dtt:Bydliste/dtt:Nazev_obce';
                    $entries = $xpath->query($query);
                    if ((count($entries) > 0) && !is_null($entries->item(0)) && !is_null($entries->item(0)->textContent)) {
                        $out->setCity($entries->item(0)->textContent);
                    }
                    $query = '//are:Ares_odpovedi/are:Odpoved/are:Zaznam/are:Identifikace/are:Osoba/dtt:Bydliste/dtt:Nazev_casti_obce';
                    $entries = $xpath->query($query);
                    if ((count($entries) > 0) && !is_null($entries->item(0)) && !is_null($entries->item(0)->textContent)) {
                        $out->setSubcity($entries->item(0)->textContent);
                    }
                    $query = '//are:Ares_odpovedi/are:Odpoved/are:Zaznam/are:Identifikace/are:Osoba/dtt:Bydliste/dtt:Nazev_mestske_casti';
                    $entries = $xpath->query($query);
                    if ((count($entries) > 0) && !is_null($entries->item(0)) && !is_null($entries->item(0)->textContent)) {
                        $out->setMestskaCast($entries->item(0)->textContent);
                    }
                    $query = '//are:Ares_odpovedi/are:Odpoved/are:Zaznam/are:Identifikace/are:Osoba/dtt:Bydliste/dtt:Nazev_ulice';
                    $entries = $xpath->query($query);
                    if ((count($entries) > 0) && !is_null($entries->item(0)) && !is_null($entries->item(0)->textContent)) {
                        $out->setStreet($entries->item(0)->textContent);
                    }
                    $query = '//are:Ares_odpovedi/are:Odpoved/are:Zaznam/are:Identifikace/are:Osoba/dtt:Bydliste/dtt:Cislo_domovni';
                    $entries = $xpath->query($query);
                    if ((count($entries) > 0) && !is_null($entries->item(0)) && !is_null($entries->item(0)->textContent)) {
                        //debug::dump(array($entries, count($entries), $entries->item(0), $entries->item(0)->textContent));
                        $out->setHome_nr($entries->item(0)->textContent);
                    }
                    if (is_null($out->getHome_nr()) || (trim($out->getHome_nr()) == '')) {
                        // Vyzkoušíme ještě 
                        $query = '//are:Ares_odpovedi/are:Odpoved/are:Zaznam/are:Identifikace/are:Osoba/dtt:Bydliste/dtt:Cislo_do_adresy';
                        $entries = $xpath->query($query);
                        if ((count($entries) > 0) && !is_null($entries->item(0)) && !is_null($entries->item(0)->textContent)) {
                            //debug::dump(array($entries, count($entries), $entries->item(0), $entries->item(0)->textContent));
                            $out->setHome_nr($entries->item(0)->textContent);
                        }
                    }
                    $query = '//are:Ares_odpovedi/are:Odpoved/are:Zaznam/are:Identifikace/are:Osoba/dtt:Bydliste/dtt:Cislo_orientacni';
                    $entries = $xpath->query($query);
                    if ((count($entries) > 0) && !is_null($entries->item(0)) && !is_null($entries->item(0)->textContent)) {
                        //debug::dump(array($entries, count($entries), $entries->item(0), $entries->textContent));
                        $out->setStreet_nr($entries->item(0)->textContent);
                    }
                    $query = '//are:Ares_odpovedi/are:Odpoved/are:Zaznam/are:Identifikace/are:Osoba/dtt:Bydliste/dtt:PSC';
                    $entries = $xpath->query($query);
                    if ((count($entries) > 0) && !is_null($entries->item(0)) && !is_null($entries->item(0)->textContent)) {
                        $out->setPsc($entries->item(0)->textContent);
                    }
                }
            }
            if (self::$cache instanceOf \Nette\Caching\Cache) {
                self::$cache->save('SOAP.ICO.' . $ico, $out, array(
                    \Nette\Caching\Cache::EXPIRE => time() + self::$cacheExpire,
                    \Nette\Caching\Cache::SLIDING => FALSE));
            }
        }
        return($out);
    }

    /**
     * Seznam registrů, ve kterých je záznam o společnosti
     * @param string $ico
     * @return array
     */
    public static function getRegistersByIco($ico, $nocache = false) {
        $ico = str_pad($ico, 8, '0', STR_PAD_LEFT);
        if ($this->verifyIC($ic)) {
            if (!$nocache && (self::$cache instanceOf \Nette\Caching\Cache) && 
                     ($out = self::$cache->load('SOAP.REGISTERS.' . $ico)) && (!is_null($out))) {

            } else {
            // via GET request
                $url = self::getLocation(5) . '?ico=' . $ico . '&xml=1';
                $response = new \DOMDocument();
                $response->load($url);
                $xpath = new \DOMXPath($response);
                $out = array();
                $query = '//are:Ares_odpovedi/Odpoved/Registry/Registr';
                $entries = $xpath->query($query);
                foreach($entries as $entry) {
                    $kodr = $xpath->query('Typ_registru/Kod', $entry);
                    if (!is_null($kodr)) {
                        $registr = array('kod_registru' => $kodr->item(0)->textContent);
                        $registr['registr'] = self::registrByCode($registr['kod_registru']);
                        $tmp1 = $xpath->query('Pocet_zaznamu', $entry);
                        $registr['zaznamu'] = is_null($tmp1) ? 0 : $tmp1->item(0)->textContent;
                        $tmp1 = $xpath->query('Zaznam', $entry);
                        $registr['zaznamy'] = array();
                        foreach($tmp1 as $entry2) {
                            $tmp2 = $xpath->query('Stav_aktualni', $entry2);
                            $zaznam = array();
                            $zaznam['stav'] = (is_null($tmp2) || is_null($tmp2->item(0))) ? null : $tmp2->item(0)->textContent;
                            $tmp2 = $xpath->query('Datum_vzniku', $entry2);
                            $zaznam['rgdt'] = (is_null($tmp2) || is_null($tmp2->item(0))) ? null : $tmp2->item(0)->textContent;
                            $tmp2 = $xpath->query('Datum_aktualizace', $entry2);
                            $zaznam['lmdt'] = (is_null($tmp2) || is_null($tmp2->item(0))) ? null : $tmp2->item(0)->textContent;
                            $registr['zaznamy'][] = $zaznam;
                        }
                        $out[] = $registr;
                    }
                }
                if (self::$cache instanceOf \Nette\Caching\Cache) {
                    self::$cache->save('SOAP.REGISTERS.' . $ico, $out, array(
                        \Nette\Caching\Cache::EXPIRE => time() + self::$cacheExpire,
                        \Nette\Caching\Cache::SLIDING => FALSE));
                }
            }
            return($out);
        } else {
            return(null); // špartné IČO
        }
    }

    /**
     * Získání informací z konkrétního registru (OR, RES, ...)
     * @param string $ico
     * @param string $reg
     * @return array
     */
    public static function getCompanyRegisterInfo($ico, $reg = 'OR', $nocache = false) {
        $ico = str_pad($ico, 8, '0', STR_PAD_LEFT);
        if (!$nocache && (self::$cache instanceOf \Nette\Caching\Cache) && 
                 ($out = self::$cache->load('SOAP.REG.' . $reg . '.' . $ico)) && (!is_null($out))) { 
        } else {
            switch($reg) {
                case 'OR':
                    $url = self::getLocation(6) . '?ico=' . $ico . '&xml=1';
                    $file = file_get_contents($url);
                    $response = new \DOMDocument();
                    $response->loadXML($file);
                    $xpath = new \DOMXPath($response);
                    $out = array();
                    $query = '//are:Ares_odpovedi/are:Odpoved/D:Vypis_OR';
                    $data = $xpath->query($query);
                    if (!is_null($data) && !is_null($data->item(0))) {
                        $query = 'D:ZAU/D:POD';
                        $tmp = $xpath->query($query, $data->item(0));
                        //debug::dump(htmlspecialchars($tmp->item(0)->textContent));
                        if (!is_null($tmp) && !is_null($tmp->item(0))) {
                            $out['POD'] = $tmp->item(0)->textContent;
                        }
                        $query = 'D:ZAU/D:S/D:SSU';
                        $tmp = $xpath->query($query, $data->item(0));
                        if (!is_null($tmp) && !is_null($tmp->item(0))) {
                            $out['SSU'] = $tmp->item(0)->textContent;
                        }
                        $query = 'D:ZAU/D:ICO';
                        $tmp = $xpath->query($query, $data->item(0));
                        if (!is_null($tmp) && !is_null($tmp->item(0))) {
                            $out['ICO'] = $tmp->item(0)->textContent;
                        }
                        $query = 'D:ZAU/D:OF';
                        $tmp = $xpath->query($query, $data->item(0));
                        if (!is_null($tmp) && !is_null($tmp->item(0))) {
                            $out['OF'] = $tmp->item(0)->textContent;
                        }
                        $query = 'D:ZAU/D:PFO/D:KPF';
                        $tmp = $xpath->query($query, $data->item(0));
                        if (!is_null($tmp) && !is_null($tmp->item(0))) {
                            $out['PFO']['KPF'] = $tmp->item(0)->textContent;
                        }
                        $query = 'D:ZAU/D:PFO/D:NPF';
                        $tmp = $xpath->query($query, $data->item(0));
                        if (!is_null($tmp) && !is_null($tmp->item(0))) {
                            $out['PFO']['NPF'] = $tmp->item(0)->textContent;
                        }
                        $query = 'D:ZAU/D:PFO/D:PFO';
                        $tmp = $xpath->query($query, $data->item(0));
                        if (!is_null($tmp) && !is_null($tmp->item(0))) {
                            $out['PFO']['PFO'] = $tmp->item(0)->textContent;
                        }
                        $query = 'D:ZAU/D:PFO/D:TZU';
                        $tmp = $xpath->query($query, $data->item(0));
                        if (!is_null($tmp) && !is_null($tmp->item(0))) {
                            $out['PFO']['TZU'] = $tmp->item(0)->textContent;
                        }
                        $query = 'D:ZAU/D:SI/D:KS';
                        $tmp = $xpath->query($query, $data->item(0));
                        if (!is_null($tmp) && !is_null($tmp->item(0))) {
                            $out['SI']['KS'] = $tmp->item(0)->textContent;
                        }
                        $query = 'D:ZAU/D:SI/D:N';
                        $tmp = $xpath->query($query, $data->item(0));
                        if (!is_null($tmp) && !is_null($tmp->item(0))) {
                            $out['SI']['N'] = $tmp->item(0)->textContent;
                        }
                        $query = 'D:ZAU/D:SI/D:NU';
                        $tmp = $xpath->query($query, $data->item(0));
                        if (!is_null($tmp) && !is_null($tmp->item(0))) {
                            $out['SI']['NU'] = $tmp->item(0)->textContent;
                        }
                        $query = 'D:ZAU/D:SI/D:CA';
                        $tmp = $xpath->query($query, $data->item(0));
                        if (!is_null($tmp) && !is_null($tmp->item(0))) {
                            $out['SI']['CA'] = $tmp->item(0)->textContent;
                        }
                        $query = 'D:ZAU/D:SI/D:PSC';
                        $tmp = $xpath->query($query, $data->item(0));
                        if (!is_null($tmp) && !is_null($tmp->item(0))) {
                            $out['SI']['PSC'] = $tmp->item(0)->textContent;
                        }
                        $query = 'D:ZAU/D:DZOR';
                        $tmp = $xpath->query($query, $data->item(0));
                        if (!is_null($tmp) && !is_null($tmp->item(0))) {
                            $out['DZOR'] = $tmp->item(0)->textContent;
                        }
                        $query = 'D:REG/D:SZ/D:SD/D:K';
                        $tmp = $xpath->query($query, $data->item(0));
                        if (!is_null($tmp) && !is_null($tmp->item(0))) {
                            $out['SD']['K'] = $tmp->item(0)->textContent;
                        }
                        $query = 'D:REG/D:SZ/D:SD/D:T';
                        $tmp = $xpath->query($query, $data->item(0));
                        if (!is_null($tmp) && !is_null($tmp->item(0))) {
                            $out['SD']['T'] = $tmp->item(0)->textContent;
                        }
                        $query = 'D:OSK/D:T';
                        $tmp = $xpath->query($query, $data->item(0));
                        if (!is_null($tmp) && !is_null($tmp->item(0))) {
                            $out['OSK']['T'] = $tmp->item(0)->textContent;
                        }
                    } else {
                        $out = null;
                    };
                    break;
                /*case 'RES':
                    $url = self::getLocation(13) . '?ico=' . $ico . '&xml=1';
                    echo $url;
                    $file = file_get_contents($url);
                    $response = new \DOMDocument();
                    echo $response->saveXML(); exit;
                    $response->loadXML($file);
                    $xpath = new \DOMXPath($response);
                    $out = array();
                    $query = '//are:Ares_odpovedi/are:Odpoved/D:Vypis_OR';
                    $data = $xpath->query($query);
                    if (!is_null($data) && !is_null($data->item(0))) {
                        $query = 'D:ZAU/D:POD';
                        $tmp = $xpath->query($query, $data->item(0));
                        //debug::dump(htmlspecialchars($tmp->item(0)->textContent));
                        if (!is_null($tmp) && !is_null($tmp->item(0))) {
                            $out['POD'] = $tmp->item(0)->textContent;
                        }
                        $query = 'D:ZAU/D:S/D:SSU';
                        $tmp = $xpath->query($query, $data->item(0));
                        if (!is_null($tmp) && !is_null($tmp->item(0))) {
                            $out['SSU'] = $tmp->item(0)->textContent;
                        }
                        $query = 'D:ZAU/D:ICO';
                        $tmp = $xpath->query($query, $data->item(0));
                        if (!is_null($tmp) && !is_null($tmp->item(0))) {
                            $out['ICO'] = $tmp->item(0)->textContent;
                        }
                        $query = 'D:ZAU/D:OF';
                        $tmp = $xpath->query($query, $data->item(0));
                        if (!is_null($tmp) && !is_null($tmp->item(0))) {
                            $out['OF'] = $tmp->item(0)->textContent;
                        }
                        $query = 'D:ZAU/D:PFO/D:KPF';
                        $tmp = $xpath->query($query, $data->item(0));
                        if (!is_null($tmp) && !is_null($tmp->item(0))) {
                            $out['PFO']['KPF'] = $tmp->item(0)->textContent;
                        }
                        $query = 'D:ZAU/D:PFO/D:NPF';
                        $tmp = $xpath->query($query, $data->item(0));
                        if (!is_null($tmp) && !is_null($tmp->item(0))) {
                            $out['PFO']['NPF'] = $tmp->item(0)->textContent;
                        }
                        $query = 'D:ZAU/D:PFO/D:PFO';
                        $tmp = $xpath->query($query, $data->item(0));
                        if (!is_null($tmp) && !is_null($tmp->item(0))) {
                            $out['PFO']['PFO'] = $tmp->item(0)->textContent;
                        }
                        $query = 'D:ZAU/D:PFO/D:TZU';
                        $tmp = $xpath->query($query, $data->item(0));
                        if (!is_null($tmp) && !is_null($tmp->item(0))) {
                            $out['PFO']['TZU'] = $tmp->item(0)->textContent;
                        }
                        $query = 'D:ZAU/D:SI/D:KS';
                        $tmp = $xpath->query($query, $data->item(0));
                        if (!is_null($tmp) && !is_null($tmp->item(0))) {
                            $out['SI']['KS'] = $tmp->item(0)->textContent;
                        }
                        $query = 'D:ZAU/D:SI/D:N';
                        $tmp = $xpath->query($query, $data->item(0));
                        if (!is_null($tmp) && !is_null($tmp->item(0))) {
                            $out['SI']['N'] = $tmp->item(0)->textContent;
                        }
                        $query = 'D:ZAU/D:SI/D:NU';
                        $tmp = $xpath->query($query, $data->item(0));
                        if (!is_null($tmp) && !is_null($tmp->item(0))) {
                            $out['SI']['NU'] = $tmp->item(0)->textContent;
                        }
                        $query = 'D:ZAU/D:SI/D:CA';
                        $tmp = $xpath->query($query, $data->item(0));
                        if (!is_null($tmp) && !is_null($tmp->item(0))) {
                            $out['SI']['CA'] = $tmp->item(0)->textContent;
                        }
                        $query = 'D:ZAU/D:SI/D:PSC';
                        $tmp = $xpath->query($query, $data->item(0));
                        if (!is_null($tmp) && !is_null($tmp->item(0))) {
                            $out['SI']['PSC'] = $tmp->item(0)->textContent;
                        }
                        $query = 'D:ZAU/D:DZOR';
                        $tmp = $xpath->query($query, $data->item(0));
                        if (!is_null($tmp) && !is_null($tmp->item(0))) {
                            $out['DZOR'] = $tmp->item(0)->textContent;
                        }
                        $query = 'D:REG/D:SZ/D:SD/D:K';
                        $tmp = $xpath->query($query, $data->item(0));
                        if (!is_null($tmp) && !is_null($tmp->item(0))) {
                            $out['SD']['K'] = $tmp->item(0)->textContent;
                        }
                        $query = 'D:REG/D:SZ/D:SD/D:T';
                        $tmp = $xpath->query($query, $data->item(0));
                        if (!is_null($tmp) && !is_null($tmp->item(0))) {
                            $out['SD']['T'] = $tmp->item(0)->textContent;
                        }
                        $query = 'D:OSK/D:T';
                        $tmp = $xpath->query($query, $data->item(0));
                        if (!is_null($tmp) && !is_null($tmp->item(0))) {
                            $out['OSK']['T'] = $tmp->item(0)->textContent;
                        }
                    } else {
                        $out = null;
                    };
                    break;*/
                case 'ES':
                    //$ico = '00010545';
                    $url = self::getLocation(20) . '?ico=' . $ico . '';
                    $file = file_get_contents($url);
                    $response = new \DOMDocument();
                    $response->loadXML($file);
                    $xpath = new \DOMXPath($response);
                    $out = array();
                    $query = '//are:Ares_odpovedi/are:Odpoved//dtt:V/dtt:S';
                    $data = $xpath->query($query);
                    if (!is_null($data) && !is_null($data->item(0))) {
                        $query = 'dtt:p_dph';
                        $tmp = $xpath->query($query, $data->item(0));
                        if (!is_null($tmp) && !is_null($tmp->item(0))) {
                            $out['DIC'] = strtr($tmp->item(0)->textContent, array('dic=' => ''));
                        } else {
                            $out['DIC'] = null;
                        }
                        $query = 'dtt:dph';
                        $tmp = $xpath->query($query, $data->item(0));
                        if (!is_null($tmp) && !is_null($tmp->item(0))) {
                            $out['PLATCE'] = strtr($tmp->item(0)->textContent, array('' => ''));
                        } else {
                            $out['PLATCE'] = null;
                        }
                    } else {
                        $out = null;
                    };
                    break;
                default:
                    $out = null;
            }
            if (self::$cache instanceOf \Nette\Caching\Cache) {
                self::$cache->save('SOAP.REG.' . $reg . '.' . $ico, $out, array(
                    \Nette\Caching\Cache::EXPIRE => time() + self::$cacheExpire,
                    \Nette\Caching\Cache::SLIDING => FALSE));
            }
        }
        return($out);
    }

    
    
    
    
    
    /**
     * Ověří v evropském registru dané IČO
     * @param string $ico
     * @param string $countryCode
     * @return array
     */
    public static function checkVat($vatNo, $countryCode = 'CZ', $nocache = false) {
        if (!$nocache && (self::$cache instanceOf \Nette\Caching\Cache) && 
                 ($out = self::$cache->load('SOAP.VAT.' . $vatNo)) && (!is_null($out))) {
            //$out = self::$cache['SOAP.ICO.' . $ico];
        } else {
            try {
                $opts = array(
                    'http' => array(
                        'user_agent' => 'PHPSoapClient'
                    )
                );
                $context = stream_context_create($opts);
                $soapClientOptions = array(
                    'stream_context' => $context
                    , 'cache_wsdl' => WSDL_CACHE_NONE
                    , 'exceptions' => true
                );
                $context = stream_context_create($opts);
                $client = new \SoapClient(self::getLocation(12), $soapClientOptions);
                $params = array(
                    'countryCode' => $countryCode
                    , 'vatNumber' => $vatNo
                    );
                $out = $client->checkVat($params);
            } catch (\SoapFault $e) {
                $out = (object) array('valid' => false);
            }
            if (self::$cache instanceOf \Nette\Caching\Cache) {
                self::$cache->save('SOAP.VAT.' . $vatNo, $out, array(
                    \Nette\Caching\Cache::EXPIRE => time() + self::$cacheExpire,
                    \Nette\Caching\Cache::SLIDING => FALSE));
            } else {
            }
        }
        return($out);
    }

    /**
     * Ověří, jestli je IČO platné - má korektní počet znaků a je splňuje podmínky dělitelnosi
     * @param string $ic
     * @return boolean Dané IČO je platné (může existovat)
     */
    public static function verifyIC($ic) {
        // "be liberal in what you receive"
        $ic = preg_replace('#\s+#', '', $ic);
        // má požadovaný tvar?
        if (!preg_match('#^\d{8}$#', $ic)) {
            return (FALSE);
        }
        // kontrolní součet
        $a = 0;
        for ($i = 0; $i < 7; $i++) {
            $a += $ic[$i] * (8 - $i);
        }
        $a = $a % 11;
        if ($a === 0) $c = 1;
        elseif ($a === 10) $c = 1;
        elseif ($a === 1) $c = 0;
        else $c = 11 - $a;
        return ($ic[7] === $c);
    }

    /**
     * Kontrola platnosti rodného čísla (formát, dělitelnost)
     * @param String $rc
     * @return boolean  Rodné číslo is valid
     */
    public static function verifyRC($rc) {
        // "be liberal in what you receive"
        if (!preg_match('#^\s*(\d\d)(\d\d)(\d\d)[ /]*(\d\d\d)(\d?)\s*$#', $rc, $matches)) {
            return (FALSE);
        }
        list(, $year, $month, $day, $ext, $c) = $matches;
        // do roku 1954 přidělovaná devítimístná RČ nelze ověřit
        if ($c === '') {
            return ($year < 54);
        }
        // kontrolní číslice
        $mod = ($year . $month . $day . $ext) % 11;
        if ($mod === 10) $mod = 0;
        if ($mod !== (int) $c) {
            return (FALSE);
        }
        // kontrola data
        $year += $year < 54 ? 2000 : 1900;
        // k měsíci může být připočteno 20, 50 nebo 70
        if ($month > 70 && $year > 2003) $month -= 70;
        elseif ($month > 50) $month -= 50;
        elseif ($month > 20 && $year > 2003) $month -= 20;
        if (!checkdate($month, $day, $year)) {
            return (FALSE);
        }
        // cislo je OK
        return (TRUE);
    }

    public static function returnVariable($name, &$varfiles = array(), &$parsedvars = array()) {
        if (isset($parsedvars[$name])) {
            return($parsedvars[$name]);
        } else {
            $var = null;
            $tmp = explode(':', $name);
            $varname = $tmp[2];
            if (in_array($varname, array('int', 'short', 'long', 'string', 'boolean', 'float', 'double', 'anySimpleType'))) {
                switch($varname) {
                    case 'int':
                    case 'short':
                    case 'long':
                        return(0);
                    case 'float':
                    case 'double':
                        return(0.0);
                    case 'string':
                        return('');
                    case 'boolean':
                        return(false);
                    case 'anySimpleType':
                        return(null);
                    
                }
            }
            $vartmp = explode('/', $tmp[1]); //ares_types/ares_answer_v_1.0.1.xsd
            $varfile = array_pop($vartmp) . '.xsd';
            if (substr($varfile, 0, 2) == 'v_') {
                $varfile = array_pop($vartmp) . '_' . $varfile;
            }
            if (isset($varfiles[$varfile]) && is_array($varfiles[$varfile]) && isset($varfiles[$varfile]['parser'])) {
                $parser = $varfiles[$varfile]['parser'];
            } elseif (isset($varfiles[$varfile])) {
                $parser = new nusoap_xmlschema ($varfiles[$varfile]);
                $varfiles[$varfile] = array('filename' => $varfiles[$varfile]
                    , 'parser' => $parser);
            } else {
                $file = $tmp[0] . ':' . implode('/', $vartmp). '/' . $varfile;
                $varfiles[$varfile]['filename'] = $file;
                $parser = new nusoap_xmlschema ($file);
                $varfiles[$varfile]['parser'] = $parser;
            }
            $tmp = $parser->getTypeDef($varname);
            switch($tmp['phpType']) {
                case 'int':
                    $parsedvars[$name] = 0;
                    return($parsedvars[$name]);
                    break;
                case 'string':
                    $parsedvars[$name] = 0;
                    return($parsedvars[$name]);
                    break;
                case 'struct':
                    $out = array();
                    foreach($tmp['elements'] as $key => $val) {
                        $out[$key] = self::returnVariable($val['type'], $varfiles, $parsedvars);
                    }
                    $parsedvars[$name] = $out;
                    return($out);
                case 'scalar':
                    $out = self::returnVariable($tmp['type'], $varfiles, $parsedvars);
                    return($out);
                    switch($tmp['name']) {
                        case 'int':
                            $parsedvars[$name] = 0;
                            return($parsedvars[$name]);
                            break;
                        case 'string':
                            $parsedvars[$name] = '';
                            return($parsedvars[$name]);
                            break;
                        default;
                            debug::dump($tmp);
                            echo 'unknown variable subtype:' . $tmp['name'];
                            exit;
                    }
                    break;
                default;
                    \Tracy\Debugger::dump($tmp);
                    echo 'unknown variable type:' . $tmp['phpType'];
                    exit;

            }
            
        }
    }
    
    
    
    
    
    
    
    
    
    
    private static $location = array(
        0 => 'http://wwwinfo.mfcr.cz/ares/xml_doc/wsdl/standard.wsdl'   // SOAP standardní dotaz - vyhledávání
        , 1 => 'http://ec.europa.eu/taxation_customs/vies/api/checkVatPort?wsdl'    //SOAP ověření existence firmy (IČO) v rámci EU
        , 2 => 'http://wwwinfo.mfcr.cz/cgi-bin/ares/darv_std.cgi'   // GET - standardní dotaz
        , 3 => 'http://wwwinfo.mfcr.cz/ares/xml_doc/wsdl/basic.wsdl' // SOAP základní dotaz - vyhledávání
        , 4 => 'http://wwwinfo.mfcr.cz/cgi-bin/ares/xar.cgi'
        , 5 => 'http://wwwinfo.mfcr.cz/cgi-bin/ares/darv_reg.cgi' // GET Obsažení firmy v registrech
        , 6 => 'http://wwwinfo.mfcr.cz/cgi-bin/ares/darv_or.cgi' // GET Dotaz na obchodní rejstřík
        , 7 => 'http://wwwinfo.mfcr.cz/ares/xml_doc/wsdl/registry.wsdl' // SOAP Seznam registrů
        , 8 => 'http://wwwinfo.mfcr.cz/ares/xml_doc/wsdl/or.wsdl' // SOAP Zkratky v OR
        , 9 => 'http://wwwinfo.mfcr.cz/ares/xml_doc/wsdl/or.wsdl' //_1.0.2.wsdl' // SOAP OR výpis
        , 10 => 'http://wwwinfo.mfcr.cz/ares/xml_doc/wsdl/res.wsdl' //_1.0.0.wsdl' // SOAP RES výpis
        , 11 => 'http://wwwinfo.mfcr.cz/ares/xml_doc/wsdl/rzp.wsdl' // SOAP RES výpis
        , 12 => 'http://ec.europa.eu/taxation_customs/vies/checkVatService.wsdl'
        , 13 => 'http://wwwinfo.mfcr.cz/cgi-bin/ares/darv_res.cgi' // GET Dotaz na registr ekonomických subjektů
        , 14 => 'http://wwwinfo.mfcr.cz/cgi-bin/ares/darv_sko.cgi' // GET Dotaz na registr škol
        , 15 => 'http://wwwinfo.mfcr.cz/cgi-bin/ares/darv_psh.cgi' // GET Dotaz na registr politických stran a hnutí
        , 16 => 'http://wwwinfo.mfcr.cz/cgi-bin/ares/darv_rzp.cgi' // GET Dotaz na registr živnostenského podnikání
        , 17 => 'http://wwwinfo.mfcr.cz/cgi-bin/ares/darv_cns.cgi' // GET Dotaz na registr církve a náboženství
        , 18 => 'http://wwwinfo.mfcr.cz/cgi-bin/ares/darv_ceu.cgi' // GET Dotaz na registr úpadců
        , 19 => 'http://wwwinfo.mfcr.cz/cgi-bin/ares/darv_oss.cgi' // GET Dotaz na registr občanská sdružení
        , 20 => 'http://wwwinfo.mfcr.cz/cgi-bin/ares/ares_es.cgi' // ekonomické subjekty
        );

    private static $mail = 'jannemec@centrum.cz';

    public static function getLocation($index) {
        return(self::$location[$index]);
    }

    public static function getMail() {
        return(self::$mail);
    }

    /**
     * Typ adresy z - uvis_datatypes_v_1.0.3.xsd
     * @param   int
     * @return  String
     */
    public static function AddressType($type) {
        switch (intval($type)) {
            case 1:
                return('adresa organizace: u právnické osoby nebo fyzické osoby podnikající sídlo ekonomického subjektu (firmy) a pokud sídlo není určeno úplnou doručovací adresou, pak doručovací adresa');
                break;
            case 2:
                return('adresa provozní jednotky');
                break;
            case 3:
                return('pracoviště (jen tehdy, liší-li se od adresy organizace i od adresy provozní jednotky)');
                break;
            case 4:
                return('trvalé bydliště u fyzické osoby (občana nebo cizince)');
                break;
            case 5:
                return('přechodné bydliště u fyzické osoby (občana nebo cizince)');
                break;
            case 6:
                return('stanoviště u vozidla, pohyblivého stroje, popř. nepohyblivého zařízení');
                break;
            case 7:
                return('místo události (týká se zejména matričních událostí, nehod, úrazů ap.)');
                break;
            case 9:
                return('doručovací adresa - nejde-li o žádný z předchozích typů  (do tohoto typu patří též poste restante a poštovní přihrádka)');
                break;
            default:
                return(null);
                break;
        }
    }
    /**
     * převod číselného kódu na popis - dle číselníku ARESu
     * @param int $code
     * @return String 
     */
    public static function registrByCode($code) {
        switch (intval($code)) {
            case 2:
                return("OR\tObchodní rejstřík, Ministerstvo spravedlnosti - OR");
                break;
            case 3:
                return("RES\tRegistr ekonomických subjektů, Statistický úřad - RES");
                break;
            case 4:
                return("RŽP\tRegistr živnostenského podnikání,Ministerstvo hodpodářství - RŽP");
                break;
            case 5:
                return("RZZ\tRegistr zdravotnických zařízení - RZZ");
                break;
            case 6:
                return("DPH\tRegistr plátců daně z přidané hodnoty - DPH");
                break;
            case 7:
                return("DS\tRegistr plátců spotřební daně - DS");
                break;
            case 8:
                return("SCP\tStředisko cenných papírů - SCP");
                break;
            case 9:
                return("CEÚ\tRegistr centrální evidence úpadců - CEÚ");
                break;
            case 10:
                return(null);
                break;
            case 11:
                return("CEDR\tCentrální evidence dotací z rozpočtu - CEDR");
                break;
            case 12:
                return("RARIS");
                break;
            case 13:
                return("SDML");
                break;
            case 14:
                return("RCNS");
                break;
            case 15:
                return("PSH");
                break;
            case 16:
                return("PZ");
                break;
            case 17:
                return("OPM");
                break;
            case 18:
                return("ISPO");
                break;
            case 19:
                return("NNO");
                break;
            case 20:
                return("OSS");
                break;
            case 21:
                return("EZP");
                break;
            default:
                return(null);
        }

    }

    /**
     * Podrobný popis ze seznamu zxkratek ARESu
     * @param string $zkr
     * @return string
     */
    public static function getZkratka($zkr) {
        $tmp = explode("\n", self::$zkratky);
        foreach($tmp as $row) {
            $row = explode('"/"', $row);
            if ((count($row) > 1) && (substr($row[1], 0, -1) == $zkr)) {
                return(substr($row[0], 1));
            }
        }
        return(null);
    }

    protected static $zkratky = '"Zvlastni_spravci"/"ZVS"
"Zrizovatel_OZ"/"ZOZ"
"Zrizovatel_PR"/"ZPR"
"Zrizovatel_nadace"/"ZN"
"Zrizovatele_OZ"/"Z_OZ"
"Zrizovatele_PR"/"Z_PR"
"Zrizovatele_nadace"/"Z_N"
"Zmena_ICO"/"ZIC"
"Zkraceny_nazev_PF"/"ZPF"
"Zivnosti"/"ZI"
"Zivnostensky_urad"/"ZU"
"Zivnost"/"Z"
"Zastupci_spravce"/"ZAS"
"Zastavni_pravo"/"ZP"
"Zapisovany"/"ZAP"
"Zamitnuti"/"ZAM"
"Zakladni_vklad"/"ZAV"
"Zakladni_udaje"/"ZAU"
"Zakladni"/"ZA"
"Zakladatel_SP"/"ZSP"
"Zakladatel_OPS"/"ZOPS"
"Zakladatele_SP"/"Z_SP"
"Zakladatele_OPS"/"Z_OPS"
"V_jednani"/"VJ"
"Vysledek_hledani"/"VH"
"Vyrovnani"/"VY"
"Vypis_basic"/"VBAS"
"Vklad_spolecnika"/"VKS"
"Vklad"/"VK"
"Ve_funkci"/"VF"
"Vedouci_OZ"/"VOZ"
"Vedouci_OS"/"VOS"
"Vedouci_odstepnych_zavodu"/"VOU"
"Vedouci"/"V"
"Uvolneny_obchodni_podil"/"UOP"
"Uvod"/"UVOD"
"Usneseni"/"US"
"Urceny_majetek"/"UM"
"Upsany"/"UK"
"Umisteni_v_CR"/"UCR"
"Ulice_cislo"/"UC"
"udt"/"U"
"Udalosti"/"UI"
"Udalost"/"U"
"Udaje_KVZ"/"KVZ"
"Ucel_nad_fondu"/"UNF"
"Ucel_nadace"/"UN"
"TZU_osoba"/"TZU"
"Typ_ZZ_kod"/"ZZK"
"Typ_ZZ_nazev"/"ZZN"
"Typ_ZZ"/"ZZT"
"Typ_zmeny"/"TZM"
"Typ_vypisu"/"TV"
"Typ_ucetnictvi_kod"/"TUK"
"Typ_ucetnictvi"/"TU"
"Typ_fyzicke_osoby"/"TFO"
"Typ_dokumentu"/"TD"
"Typ_datumu"/"TDA"
"Typ_cislo_domovni"/"TCD"
"Titul_za"/"TZ"
"Titul_pred"/"TP"
"Texty"/"TY"
"Text_stavu"/"TS"
"Text_k_uvolnenemu_podilu"/"TUP"
"Text_ke_spolecnemu_podilu"/"TSP"
"Text"/"T"
"Telefon"/"TEL"
"Stav_zivnosti"/"SZI"
"Stav_vyrovnani"/"SVY"
"Stavy_subjektu"/"SY"
"Stav_subjektu_OR"/"SOR"
"Stav_subjektu_CEU"/"SCEU"
"Stav_subjektu_RCNS"/"SCI"
"Stav_subjektu"/"SSU"
"Stav_OS"/"OSS"
"Stav_konkurzu"/"SKO"
"Stav"/"S"
"Statutarni_organy"/"SOY"
"Statutarni_organ_zrizovatele_ZO"/"SOZ"
"Statutarni_organ_spolecnosti"/"SOS"
"Statutarni_organ_predstavenstvo"/"SOP"
"Statutarni_organ_komplementaru"/"SOK"
"Statutarni_organ"/"SO"
"Statisticke_udaje"/"SU"
"Spravni_rada"/"SR"
"Spravci_KP"/"SKP"
"Spolecny_podil"/"SP"
"Spolecnik_s_vkladem"/"SS"
"Spolecnik_bez_vkladu"/"SB"
"Spolecnici_s_vkladem"/"SSV"
"Spolecnici_bez_vkladu"/"SBV"
"Splaceno"/"SPL"
"Spisova_znacka"/"SZ"
"Soud"/"SD"
"Sidlo"/"SI"
"Rodne_cislo"/"RC"
"Revizori"/"REI"
"Revizor"/"RE"
"Resort_kod"/"RK"
"Resort"/"RS"
"Registrace_RZP"/"RRZ"
"Registrace_RCNS"/"RCI"
"Registrace_OR"/"ROR"
"Registrace"/"REG"
"PSC_obec"/"PB"
"Prvni_zivnost"/"ZI1"
"Provozovny"/"PRY"
"Provozovna_existuje"/"EPR"
"Provozovna"/"PR"
"Provozovane_zivnosti"/"PZI"
"Provozovana_zivnost"/"PZ"
"Prokurista"/"PRA"
"Prokura"/"PRO"
"Procenta"/"PRC"
"Priznaky_subjektu"/"PSU"
"Prijmeni"/"P"
"Predstavenstvo"/"PRE"
"Predmet_podnikani"/"PP"
"Predmet_cinn_pod"/"PCI"
"Predmet_cinnosti"/"PC"
"Predmety_podnikani"/"PPI"
"Predbezni_spravci"/"PRS"
"Pravni_moc_od"/"PMD"
"Pravni_forma_RZP"/"PFR"
"Pravni_forma_OR"/"PFO"
"Pravni_forma"/"PF"
"Pravnicka_osoba"/"PO"
"Pozadovane_datum_platnosti_dat"/"PDP"
"Pomocne_ID"/"PID"
"Podoba"/"PD"
"Podilnik"/"PIK"
"Pocet_zivnosti"/"PZI"
"Pocet_zaznamu"/"PZA"
"Pocet_provozoven"/"PPR"
"Pobyt_v_CR"/"PCR"
"Platnost_od"/"POD"
"Pism_cislo_orientacni"/"PCO"
"PF_zakladatele"/"PFZ"
"PF_osoba"/"PFO"
"PCD"/"PCD"
"Oznaceni_dokumentu"/"OD"
"Ostatni_skutecnosti"/"OSK"
"Ostatni"/"OST"
"Osoba_textem"/"OT"
"Osoba_RZP"/"ORZP"
"Organizacni_slozky"/"OSY"
"Organizacni_slozka"/"OS"
"Opravnena_osoba_nadacniho_fondu"/"OOF"
"Opravnena_osoba_nadace"/"OON"
"Okr_reg_kod"/"ORK"
"Okr_reg"/"ORE"
"Odstepny_zavod"/"OZ"
"Odstepne_zavody"/"OZY"
"Oddil_vlozka"/"OV"
"Obchodni_rejstrik"/"OR"
"Obchodni_podil"/"OP"
"Obchodni_firma"/"OF"
"Obec_zuj_kod"/"OZK"
"Obec_zuj"/"OZU"
"Obcanstvi"/"OB"
"Nazev_ZU"/"NZU"
"Nazev_ulice"/"NU"
"Nazev_statu"/"NS"
"Nazev_provozovny"/"NPR"
"Nazev_PF"/"NPF"
"Nazev_okresu"/"NOK"
"Nazev_OKEC"/"NO"
"Nazev_oboru"/"NRU"
"Nazev_obce"/"N"
"Nazev_mestske_casti"/"NMC"
"Nazev_FU"/"NFU"
"Nazev_casti_obce"/"NCO"
"Nastupci_zrizovatelu"/"NAU"
"Nastupce_zrizovatele"/"NAE"
"Nadpis"/"ND"
"Nadacni_majetek"/"NM"
"Nadacni_jmeni"/"NJ"
"Nadacni_fond"/"NF"
"Nadace"/"NAD"
"Misto_podnikani"/"MP"
"Minimalni_kmenove_jmeni"/"MKJ"
"Majetkovy_vklad"/"MV"
"Likvidatori"/"LII"
"Likvidator"/"LIR"
"Likvidace"/"LI"
"Kvalifikace_zivnosti"/"KZ"
"Konkurzy_vyrovnani"/"KV"
"Konkurz"/"KKZ"
"Komplementari"/"KPI"
"Komplementar"/"KPR"
"Komentar"/"KOM"
"Komanditiste"/"KME"
"Komanditista"/"KMA"
"Kod_ZU"/"KZU"
"Kod_zivnosti"/"KZI"
"Kod_ulice"/"KUL"
"Kod_statu_obcanstvi"/"KSB"
"Kod_statu"/"KS"
"Kod_spravniho_obvodu"/"KSO"
"Kod_sobvod"/"KSO"
"Kod_prazskeho_obvodu"/"KPO"
"Kod_pobvod"/"KPO"
"Kod_PF"/"KPF"
"Kod_okresu"/"KOK"
"Kod_oboru"/"KRU"
"Kod_oblasti"/"KOL"
"Kod_objektu"/"KOB"
"Kod_obce"/"KO"
"Kod_NUTS4"/"KN"
"Kod_nobvod"/"KN"
"Kod_mestske_casti"/"KMC"
"Kod_listu"/"KL"
"Kod_kraje"/"KK"
"Kod_FU"/"KFU"
"Kod_casti_obce"/"KCO"
"Kod_angm"/"KAN"
"Kod_adresy"/"KA"
"Kod"/"K"
"Kmenove_jmeni"/"KJ"
"Klic_ARES"/"LA"
"Kc"/"KC"
"Kategorie_poctu_pracovniku"/"KPP"
"Kapital"/"KAP"
"Jmeno"/"J"
"Inst_sec_kod"/"ISK"
"Institucionarni_sektor"/"ISE"
"ID_adresy"/"IDA"
"Hodnota"/"H"
"Hlavni_OS"/"OSH"
"Fyzicka_osoba_RZP"/"FO_RZP"
"Fyzicka_osoba_statutar"/"FOS"
"Fyzicka_osoba_OR"/"FOR"
"Fyzicka_osoba"/"FO"
"Funkce"/"F"
"Financni_urad_adr"/"FUA"
"Financni_urad"/"FU"
"Exekuce"/"EX"
"Evidencni_cislo"/"EVC"
"Evidence_od"/"EOD"
"Evidence_do"/"EDO"
"Error_text"/"ET"
"Error_kod"/"EK"
"Error"/"E"
"Emise"/"EM"
"dtt"/"D"
"Druzstevnik"/"DIK"
"Druzstevnici"/"DCI"
"Druh_zarizeni_kod"/"DRK"
"Druh_zarizeni"/"DRZ"
"Druh_zivnosti"/"DZI"
"Druh_vlastnictvi_kod"/"DVK"
"Druh_vlastnictvi"/"DVL"
"Druh_provozovny"/"DPR"
"Druh_akcie"/"DA"
"Dozorci_rada"/"DR"
"Doplnkova_cinnost"/"DKC"
"Dokumenty_zivnost"/"DKZ"
"Dokumenty"/"DKY"
"Dokument"/"DOK"
"Datum_zapisu_OR"/"DZOR"
"Datum_zaniku"/"DZ"
"Datum_zahajeni"/"DZH"
"Datum_zacatku"/"DZA"
"Datum_vzniku_OR"/"DVOR"
"Datum_vzniku"/"DV"
"Datum_vyveseni"/"DVE"
"Datum_vypisu"/"DVY"
"Datum_vydani"/"DVI"
"Datum_registrace"/"DRE"
"Datum_prohlaseni"/"DHL"
"Datum_pozastaveni_od"/"DAO"
"Datum_pozastaveni_do"/"DAD"
"Datum_podani"/"DP"
"Datum_omezeni"/"DOM"
"Datum_narozeni"/"DN"
"Datum_konce"/"DK"
"Dalsi_udaje"/"DAU"
"Clenove_sdruzeni"/"CLS"
"Clen_svazu"/"CSV"
"Clen_sdruzeni"/"CS"
"Clen_SOZ_ZO"/"CZO"
"Clen_SR"/"CSR"
"Clen_SOS"/"CSS"
"Clen_SOP"/"CSP"
"Clen_SOK"/"CSK"
"Clen_SO"/"CSO"
"Clen_predstavenstva"/"CPR"
"Clen_DR"/"CDR"
"Clenstvi"/"CLE"
"Clen"/"C"
"Cislo_provozovny"/"IPR"
"Cislo_orientacni"/"CO"
"Cislo_jednaci"/"CJ"
"Cislo_do_adresy"/"CA"
"Cislo_domovni"/"CD"
"Cislo_dokumentu"/"CDK"
"Cinnosti"/"CIN"
"Cas_vypisu"/"CAS"
"Bydliste"/"B"
"Aktualizace_DB"/"ADB"
"Aktivita_kod"/"AVK"
"Aktivita"/"AV"
"Akcionari"/"AKI"
"Akcionar"/"AKR"
"Adresa_v_CR"/"ACR"
"Adresa_UIR"/"AU"
"Adresa_textem"/"AT"
"Adresa_szr"/"AS"
"Adresa_provozovny"/"AP"
"Adresa_OS"/"AOS"
"Adresa_nadacniho_fondu"/"ANF"
"Adresa_nadace"/"AN"
"Adresa_ezp"/"AE"
"Adresa_dorucovaci"/"AD"
"Adresa_ARES"/"AA"
"Adresa"/"A"
v_1.0.0/v_1.0.3
v_1.0.1/v_1.0.3
v_1.0.2/v_1.0.3
v_1.0.4/v_1.0.5
"vyp_szr_glo.4gl"/"vyp_szr_glo_105.4gl"';
}